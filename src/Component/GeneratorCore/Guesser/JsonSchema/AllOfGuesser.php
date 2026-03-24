<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\JsonSchema;

use LogicException;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Generator\Naming;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\ChainGuesserAwareInterface;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\ChainGuesserAwareTrait;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\ClassGuesserInterface;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Guess\ClassGuess;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Guess\ObjectType;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Guess\Type;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\GuesserInterface;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\GuesserResolverTrait;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\PropertiesGuesserInterface;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\TypeGuesserInterface;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\JsonSchema\Model\JsonSchema;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\JsonSchema\Model\SchemaInterface;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Registry\Registry;
use LongTermSupport\OpenApiGenerator\Component\OpenApiRuntime\Reference;
use RuntimeException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class AllOfGuesser implements GuesserInterface, TypeGuesserInterface, ChainGuesserAwareInterface, PropertiesGuesserInterface, ClassGuesserInterface
{
    use ChainGuesserAwareTrait;
    use GuesserResolverTrait;

    public function __construct(
        DenormalizerInterface $denormalizer,
        protected Naming $naming,
    ) {
        $this->denormalizer = $denormalizer;
    }

    public function guessClass(mixed $object, string $name, string $reference, Registry $registry): void
    {
        if (!$object instanceof SchemaInterface) {
            throw new LogicException('Expected SchemaInterface, got ' . get_debug_type($object));
        }

        $hasSubObject = false;

        foreach ($object->getAllOf() ?? [] as $allOf) {
            if ($allOf instanceof Reference) {
                $allOf = $this->resolve($allOf, $this->getSchemaClass());
            }

            if ($this->isObjectSchema($allOf)) {
                $hasSubObject = true;
                break;
            }
        }

        if ($hasSubObject) {
            if (!$registry->hasClass($reference)) {
                $extensions = [];

                if (null !== $object->getAdditionalProperties() && false !== $object->getAdditionalProperties()) {
                    $extensionObject = null;

                    if (\is_object($object->getAdditionalProperties())) {
                        $extensionObject = $object->getAdditionalProperties();
                    }

                    $extensions['.*'] = [
                        'object'    => $extensionObject,
                        'reference' => $reference . '/additionalProperties',
                    ];
                } elseif (method_exists($object, 'getPatternProperties')) {
                    $patternProperties = $object->getPatternProperties();
                    if (\is_array($patternProperties)) {
                        foreach ($patternProperties as $pattern => $patternProperty) {
                            $extensions[(string)$pattern] = [
                                'object'    => $patternProperty,
                                'reference' => $reference . '/patternProperties/' . $pattern,
                            ];
                        }
                    }
                }

                $classGuess = $this->createClassGuess($object, $reference, $name, $extensions);
                if (null !== $object->getRequired()) {
                    $classGuess->setRequired($object->getRequired());
                }

                if (!($schema = $registry->getSchema($reference)) instanceof \LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Registry\Schema) {
                    throw new RuntimeException(\sprintf('Schema for reference %s could not be found', $reference));
                }

                $schema->addClass($reference, $classGuess);
            }

            foreach ($object->getAllOf() ?? [] as $allOfIndex => $allOf) {
                $properties = $this->getAllOfSchemaProperties($allOf);
                if (null !== $properties && [] !== $properties) {
                    foreach ($properties as $key => $property) {
                        $this->chainGuesser->guessClass($property, $name . $key, $reference . '/allOf/' . $allOfIndex . '/properties/' . $key, $registry);
                    }
                }
            }
        }
    }

    public function guessType(mixed $object, string $name, string $reference, Registry $registry): Type
    {
        if (!$object instanceof SchemaInterface) {
            throw new LogicException('Expected SchemaInterface, got ' . get_debug_type($object));
        }

        $type      = null;
        $allOfType = null;

        // Mainly a merged class
        if ($registry->hasClass($reference)) {
            if (!($class = $registry->getClass($reference)) instanceof ClassGuess) {
                throw new RuntimeException(\sprintf('Class for reference %s could not be found', $reference));
            }

            if (!($schema = $registry->getSchema($reference)) instanceof \LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Registry\Schema) {
                throw new RuntimeException(\sprintf('Schema for reference %s could not be found', $reference));
            }

            return new ObjectType($object, $class->getName(), $schema->getNamespace());
        }

        foreach ($object->getAllOf() ?? [] as $allOfIndex => $allOf) {
            $allOfSchema    = $allOf;
            $allOfReference = $reference . '/allOf/' . $allOfIndex;

            if ($allOfSchema instanceof Reference) {
                $mergedUri = $allOfSchema->getMergedUri();

                $allOfReference           = $mergedUri->__toString();
                $allOfReferenceNoFragment = $mergedUri->withFragment('')->__toString();
                if ($allOfReference === $allOfReferenceNoFragment) {
                    $allOfReference .= '#';
                }

                $allOfSchema = $this->resolve($allOfSchema, $this->getSchemaClass());
            }

            if (!$allOfSchema instanceof SchemaInterface) {
                continue;
            }

            if (null !== $allOfSchema->getType()) {
                if ($type instanceof Type && $allOfType !== $allOfSchema->getType()) {
                    throw new RuntimeException('an allOf instruction with 2 or more types is strictly impossible, check your schema');
                }

                $allOfType = $allOfSchema->getType();
                $type      = $this->chainGuesser->guessType($allOf, $name, $allOfReference, $registry);
            }
        }

        if (!$type instanceof Type) {
            return new Type($object, 'mixed');
        }

        return $type;
    }

    public function supportObject(mixed $object): bool
    {
        if (!$object instanceof SchemaInterface) {
            return false;
        }

        $allOf = $object->getAllOf();

        return \is_array($allOf) && [] !== $allOf;
    }

    public function guessProperties(mixed $object, string $name, string $reference, Registry $registry): array
    {
        if (!$object instanceof SchemaInterface) {
            throw new LogicException('Expected SchemaInterface, got ' . get_debug_type($object));
        }

        $properties = [];
        foreach ($object->getAllOf() ?? [] as $allOfIndex => $allOfSchema) {
            $allOfReference = $reference . '/allOf/' . $allOfIndex;

            if ($allOfSchema instanceof Reference) {
                $mergedUri = $allOfSchema->getMergedUri();

                $allOfReference           = $mergedUri->__toString();
                $allOfReferenceNoFragment = $mergedUri->withFragment('')->__toString();
                if ($allOfReference === $allOfReferenceNoFragment) {
                    $allOfReference .= '#';
                }

                $allOfSchema = $this->resolve($allOfSchema, $this->getSchemaClass());
            }

            if (!\is_object($allOfSchema)) {
                continue;
            }

            $properties = array_merge($properties, $this->chainGuesser->guessProperties($allOfSchema, $name, $allOfReference, $registry));
        }

        return $properties;
    }

    protected function isObjectSchema(mixed $allOf): bool
    {
        return $allOf instanceof SchemaInterface && 'object' === $allOf->getType();
    }

    /** @return array<string, mixed>|null */
    protected function getAllOfSchemaProperties(mixed $allOf): ?array
    {
        if (!$allOf instanceof SchemaInterface) {
            return null;
        }

        return $allOf->getProperties();
    }

    protected function getSchemaClass(): string
    {
        return JsonSchema::class;
    }

    /**
     * @param array<string, mixed> $extensions
     */
    protected function createClassGuess(object $object, string $reference, string $name, array $extensions): ClassGuess
    {
        return new ClassGuess($object, $reference, $this->naming->getClassName($name), $extensions);
    }
}
