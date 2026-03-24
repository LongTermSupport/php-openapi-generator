<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\JsonSchema;

use LogicException;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Guess\CustomObjectType;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Guess\Type;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\GuesserInterface;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\TypeGuesserInterface;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\JsonSchema\Model\JsonSchema;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\JsonSchema\Model\SchemaInterface;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Registry\Registry;

class CustomStringFormatGuesser implements GuesserInterface, TypeGuesserInterface
{
    /**
     * @param array<string, string> $mapping key: format, value: classname of the normalizer
     */
    public function __construct(
        protected array $mapping,
    ) {
    }

    public function supportObject(mixed $object): bool
    {
        if (!$object instanceof SchemaInterface) {
            return false;
        }

        $format = $object->getFormat();

        return 'string' === $object->getType() && \is_string($format) && \array_key_exists($format, $this->mapping);
    }

    public function guessType(mixed $object, string $name, string $reference, Registry $registry): Type
    {
        if (!$object instanceof SchemaInterface) {
            throw new LogicException('Expected SchemaInterface, got ' . get_debug_type($object));
        }

        $format = $object->getFormat();
        if (!\is_string($format)) {
            throw new LogicException('Expected string, got ' . get_debug_type($format));
        }

        return new CustomObjectType($object, $this->mapping[$format], []);
    }

    protected function getSchemaClass(): string
    {
        return JsonSchema::class;
    }
}
