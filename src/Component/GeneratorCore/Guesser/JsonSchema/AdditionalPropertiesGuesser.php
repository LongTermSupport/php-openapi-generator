<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\JsonSchema;

use LogicException;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\ChainGuesserAwareInterface;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\ChainGuesserAwareTrait;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\ClassGuesserInterface;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Guess\MapType;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Guess\Type;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\GuesserInterface;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\TypeGuesserInterface;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\JsonSchema\Model\JsonSchema;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\JsonSchema\Model\SchemaInterface;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Registry\Registry;

class AdditionalPropertiesGuesser implements GuesserInterface, TypeGuesserInterface, ChainGuesserAwareInterface, ClassGuesserInterface
{
    use ChainGuesserAwareTrait;

    public function guessClass(mixed $object, string $name, string $reference, Registry $registry): void
    {
        if (!$object instanceof SchemaInterface) {
            throw new LogicException('Expected SchemaInterface, got ' . get_debug_type($object));
        }

        $additionalProperties = $object->getAdditionalProperties();
        if ($additionalProperties instanceof SchemaInterface) {
            $this->chainGuesser->guessClass($additionalProperties, $name . 'Item', $reference . '/additionalProperties', $registry);
        }
    }

    public function supportObject(mixed $object): bool
    {
        if (!$object instanceof SchemaInterface) {
            return false;
        }

        if ('object' !== $object->getType()) {
            return false;
        }

        if (true !== $object->getAdditionalProperties() && !\is_object($object->getAdditionalProperties())) {
            return false;
        }

        return true;
    }

    public function guessType(mixed $object, string $name, string $reference, Registry $registry): Type
    {
        if (!$object instanceof SchemaInterface) {
            throw new LogicException('Expected SchemaInterface, got ' . get_debug_type($object));
        }

        if (true === $object->getAdditionalProperties()) {
            return new MapType($object, new Type($object, 'mixed'));
        }

        return new MapType($object, $this->chainGuesser->guessType($object->getAdditionalProperties(), $name . 'Item', $reference . '/additionalProperties', $registry));
    }

    protected function getSchemaClass(): string
    {
        return JsonSchema::class;
    }
}
