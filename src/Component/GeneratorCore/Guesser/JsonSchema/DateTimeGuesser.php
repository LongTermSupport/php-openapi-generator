<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\JsonSchema;

use DateTime;
use LogicException;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Guess\DateTimeType;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Guess\Type;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\GuesserInterface;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\TypeGuesserInterface;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\JsonSchema\Model\JsonSchema;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\JsonSchema\Model\SchemaInterface;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Registry\Registry;

class DateTimeGuesser implements GuesserInterface, TypeGuesserInterface
{
    /**
     * @param string      $outputDateFormat Format of date to use when normalized
     * @param string|null $inputDateFormat  Format of date to use when denormalized
     */
    public function __construct(
        private readonly string $outputDateFormat = DateTime::RFC3339,
        private readonly ?string $inputDateFormat = null,
        private readonly ?bool $preferInterface = null,
    ) {
    }

    public function supportObject(mixed $object): bool
    {
        return ($object instanceof SchemaInterface) && 'string' === $object->getType() && 'date-time' === $object->getFormat();
    }

    public function guessType(mixed $object, string $name, string $reference, Registry $registry): Type
    {
        if (!$object instanceof SchemaInterface) {
            throw new LogicException('Expected SchemaInterface, got ' . get_debug_type($object));
        }

        return new DateTimeType($object, $this->outputDateFormat, $this->inputDateFormat, true === $this->preferInterface);
    }

    protected function getSchemaClass(): string
    {
        return JsonSchema::class;
    }
}
