<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\JsonSchema;

use LogicException;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Guess\Type;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\GuesserInterface;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\TypeGuesserInterface;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\JsonSchema\Model\JsonSchema;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\JsonSchema\Model\SchemaInterface;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Registry\Registry;

class SimpleTypeGuesser implements GuesserInterface, TypeGuesserInterface
{
    /** @var array<string> */
    protected array $typesSupported = [
        'boolean',
        'integer',
        'number',
        'string',
        'null',
    ];

    /** @var array<string, string> */
    protected array $phpTypesMapping = [
        'boolean' => 'bool',
        'integer' => 'int',
        'number'  => 'float',
        'string'  => 'string',
        'null'    => 'null',
    ];

    /** @var array<string, array<string>> */
    protected array $excludeFormat = [
        'string' => [
            'date-time',
        ],
    ];

    public function supportObject(mixed $object): bool
    {
        if (!$object instanceof SchemaInterface) {
            return false;
        }

        $type = $object->getType();
        if (!\in_array($type, $this->typesSupported, true)) {
            return false;
        }

        return !(\array_key_exists($type, $this->excludeFormat) && \in_array($object->getFormat(), $this->excludeFormat[$type], true));
    }

    public function guessType(mixed $object, string $name, string $reference, Registry $registry): Type
    {
        if (!$object instanceof SchemaInterface) {
            throw new LogicException('Expected SchemaInterface, got ' . get_debug_type($object));
        }

        $type = $object->getType();
        if (!\is_string($type)) {
            throw new LogicException('Expected string, got ' . get_debug_type($type));
        }

        return new Type($object, $this->phpTypesMapping[$type]);
    }

    protected function getSchemaClass(): string
    {
        return JsonSchema::class;
    }
}
