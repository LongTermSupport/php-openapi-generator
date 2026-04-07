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
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Guess\Property;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Guess\Type;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\GuesserInterface;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\GuesserResolverTrait;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\PropertiesGuesserInterface;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\TypeGuesserInterface;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Validator\ChainValidatorFactory;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Validator\ValidatorInterface;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\JsonSchema\Model\JsonSchema;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\JsonSchema\Model\SchemaInterface;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Registry\Registry;
use LongTermSupport\OpenApiGenerator\Component\OpenApiRuntime\Reference;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class ObjectGuesser implements GuesserInterface, PropertiesGuesserInterface, TypeGuesserInterface, ChainGuesserAwareInterface, ClassGuesserInterface
{
    use ChainGuesserAwareTrait;
    use GuesserResolverTrait;

    protected ?ValidatorInterface $chainValidator = null;

    public function __construct(
        DenormalizerInterface $denormalizer,
        protected Naming $naming,
    ) {
        $this->denormalizer = $denormalizer;
    }

    public function supportObject(mixed $object): bool
    {
        return ($object instanceof SchemaInterface) && (\is_array($object->getType()) ? \in_array('object', $object->getType(), true) : 'object' === $object->getType()) && null !== $object->getProperties();
    }

    public function guessClass(mixed $object, string $name, string $reference, Registry $registry): void
    {
        if (!$object instanceof SchemaInterface) {
            throw new LogicException('Expected SchemaInterface, got ' . get_debug_type($object));
        }

        if (!$registry->hasClass($reference)) {
            $this->initChainValidator($registry);
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
            } else {
                $extensions = array_merge($extensions, $this->buildPatternExtensions($object, $reference));
            }

            $classGuess = $this->createClassGuess($object, $reference, $name, $extensions);
            if (null !== $object->getRequired()) {
                $classGuess->setRequired($object->getRequired());
            }

            $schema = $registry->getSchema($reference);
            if ($schema instanceof \LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Registry\Schema) {
                $schema->addClass($reference, $classGuess);
            }
        }

        foreach ($object->getProperties() ?? [] as $key => $property) {
            $this->chainGuesser->guessClass($property, $name . ucfirst((string)$key), $reference . '/properties/' . $key, $registry);
        }
    }

    public function guessProperties(mixed $object, string $name, string $reference, Registry $registry): array
    {
        if (!$object instanceof SchemaInterface) {
            throw new LogicException('Expected SchemaInterface, got ' . get_debug_type($object));
        }

        $properties = [];
        $this->initChainValidator($registry);

        foreach ($object->getProperties() ?? [] as $key => $property) {
            $resolvedSchema = $property;

            if ($resolvedSchema instanceof Reference) {
                $resolvedSchema = $this->resolve($resolvedSchema, $this->getSchemaClass());
            }

            if (!\is_object($resolvedSchema)) {
                continue;
            }

            if (!$resolvedSchema instanceof SchemaInterface) {
                throw new LogicException('Expected SchemaInterface after resolve, got ' . get_debug_type($resolvedSchema));
            }

            $nullable = $this->isPropertyNullable($resolvedSchema);

            $required = false;
            if (\is_array($object->getRequired())) {
                $required = \in_array($key, $object->getRequired(), true);
            }

            // Property::$object MUST keep the original property — when it's a Reference, the
            // chain type guesser needs to dispatch to ReferenceGuesser so it can resolve the
            // class via the merged URI. Passing the resolved schema here would route to
            // ObjectGuesser::guessType with the property path as reference, where the class
            // lookup fails (the class is registered at the merged URI, not the property path),
            // causing the property type to silently degrade to `mixed`.
            //
            // If the property is not a Reference, $property and $resolvedSchema are identical
            // and the original behaviour is preserved.
            if (!\is_object($property)) {
                throw new LogicException('Expected object property, got ' . get_debug_type($property));
            }

            $newProperty = new Property($property, (string)$key, $reference . '/properties/' . $key, $nullable, $required, null, $resolvedSchema->getDescription(), $resolvedSchema->getDefault(), $resolvedSchema->getReadOnly());
            $newProperty->setDeprecated($resolvedSchema->getDeprecated() ?? false);
            if (!$this->chainValidator instanceof ValidatorInterface) {
                throw new LogicException('Expected ValidatorInterface, got ' . get_debug_type($this->chainValidator));
            }

            $this->chainValidator->guess($resolvedSchema, $name, $newProperty);
            $properties[$key] = $newProperty;
        }

        return $properties;
    }

    public function guessType(mixed $object, string $name, string $reference, Registry $registry): Type
    {
        if (!$object instanceof SchemaInterface) {
            throw new LogicException('Expected SchemaInterface, got ' . get_debug_type($object));
        }

        $discriminants = [];
        $required      = $object->getRequired() ?? [];

        foreach ($object->getProperties() ?? [] as $key => $property) {
            if (!\in_array($key, $required, true)) {
                continue;
            }

            if ($property instanceof Reference) {
                $resolved = $this->resolve($property, $this->getSchemaClass());
                if (!$resolved instanceof SchemaInterface) {
                    throw new LogicException('Expected SchemaInterface, got ' . get_debug_type($resolved));
                }

                $property = $resolved;
            }

            if (!$property instanceof SchemaInterface) {
                throw new LogicException('Expected SchemaInterface, got ' . get_debug_type($property));
            }

            $discriminants[$key] = $property->getEnum() ?? null;
        }

        if ($registry->hasClass($reference) && ($schema = $registry->getSchema($reference)) instanceof \LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Registry\Schema) {
            $class = $registry->getClass($reference);
            if (!$class instanceof ClassGuess) {
                throw new LogicException('Expected ClassGuess, got ' . get_debug_type($class));
            }

            return new ObjectType($object, $class->getName(), $schema->getNamespace(), $discriminants);
        }

        return new Type($object, 'object');
    }

    protected function isPropertyNullable(mixed $property): bool
    {
        if (!$property instanceof SchemaInterface) {
            throw new LogicException('Expected SchemaInterface, got ' . get_debug_type($property));
        }

        $oneOf = $property->getOneOf();
        if (null !== $oneOf && [] !== $oneOf) {
            foreach ($oneOf as $oneOfProperty) {
                if (!$oneOfProperty instanceof SchemaInterface) {
                    continue;
                }

                if ($this->isPropertyNullable($oneOfProperty)) {
                    return true;
                }
            }

            return false;
        }

        $type = $property->getType();

        return 'null' === $type || (\is_array($type) && \in_array('null', $type, true));
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildPatternExtensions(SchemaInterface $object, string $reference): array
    {
        if (!method_exists($object, 'getPatternProperties')) {
            return [];
        }

        $patternProperties = $object->getPatternProperties();
        if (!\is_array($patternProperties)) {
            return [];
        }

        $extensions = [];
        foreach ($patternProperties as $pattern => $patternProperty) {
            $extensions[(string)$pattern] = [
                'object'    => $patternProperty,
                'reference' => $reference . '/patternProperties/' . $pattern,
            ];
        }

        return $extensions;
    }

    protected function getSchemaClass(): string
    {
        return JsonSchema::class;
    }

    /**
     * @param array<string, mixed> $extensions
     */
    protected function createClassGuess(mixed $object, string $reference, string $name, array $extensions): ClassGuess
    {
        if (!$object instanceof SchemaInterface) {
            throw new LogicException('Expected SchemaInterface, got ' . get_debug_type($object));
        }

        return new ClassGuess($object, $reference, $this->naming->getClassName($name), $extensions, $object->getDeprecated() ?? false);
    }

    private function initChainValidator(Registry $registry): void
    {
        if (!$this->chainValidator instanceof ValidatorInterface) {
            $this->chainValidator = ChainValidatorFactory::create($this->naming, $registry, $this->denormalizer);
        }
    }
}
