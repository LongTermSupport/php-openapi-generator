<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Validator\Object_;

use LogicException;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Generator\Naming;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Guess\ClassGuess;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Guess\Property;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\GuesserResolverTrait;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Validator\ObjectCheckTrait;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Validator\ValidatorGuess;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Validator\ValidatorInterface;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\JsonSchema\Model\JsonSchema;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Registry\Registry;
use LongTermSupport\OpenApiGenerator\Component\OpenApiRuntime\Reference;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class SubObjectValidator implements ValidatorInterface
{
    use GuesserResolverTrait;
    use ObjectCheckTrait;

    public function __construct(
        DenormalizerInterface $denormalizer,
        private readonly Naming $naming,
        private readonly Registry $registry,
    ) {
        $this->denormalizer = $denormalizer;
    }

    public function supports(mixed $object): bool
    {
        return $object instanceof JsonSchema && (\is_array($object->getType()) ? \in_array('object', $object->getType(), true) : 'object' === $object->getType());
    }

    public function guess(mixed $object, string $name, ClassGuess|Property $guess): void
    {
        if (!$object instanceof JsonSchema) {
            throw new LogicException('Expected JsonSchema, got ' . get_debug_type($object));
        }

        if (str_contains($guess->getReference(), 'properties')) {
            return; // we don't want to guess on properties here, only on classes
        }

        foreach ($object->getProperties() ?? [] as $localName => $property) {
            $reference   = null;
            $className   = null;
            $propertyObj = null;
            if ($property instanceof Reference) {
                $reference   = $property->getMergedUri()->__toString();
                $resolved    = $this->resolve($property, $object::class);
                $propertyObj = ($resolved instanceof JsonSchema) ? $resolved : null;
                $classGuess  = $this->registry->getClass($reference);
                if ($classGuess instanceof ClassGuess) {
                    $className = $classGuess->getName();
                }
            } else {
                $schema         = $this->registry->getFirstSchema();
                $found          = $schema->findPropertyClass($name, $localName);
                $classGuess     = $found[0] ?? null;
                $localReference = $found[1] ?? null;

                $propertyObj = $property instanceof JsonSchema ? $property : null;
                if (null !== $classGuess) {
                    $className = $classGuess->getName();
                    $reference = \is_string($localReference) ? $localReference : null;
                }
            }

            if (null !== $className && $propertyObj instanceof JsonSchema && (\is_array($propertyObj->getType()) ? \in_array('object', $propertyObj->getType(), true) : 'object' === $propertyObj->getType())) {
                $localNameStr = \is_string($localName) ? $localName : (string)$localName;
                $referenceStr = \is_string($reference) ? $reference : null;
                $guess->addValidatorGuess(new ValidatorGuess($this->naming->getConstraintName($className), [], $localNameStr, $referenceStr));
            }
        }
    }
}
