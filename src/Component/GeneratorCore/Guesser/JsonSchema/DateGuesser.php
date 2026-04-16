<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\JsonSchema;

use LogicException;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Guess\DateType;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Guess\Type;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\GuesserInterface;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\TypeGuesserInterface;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\JsonSchema\Model\JsonSchema;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\JsonSchema\Model\SchemaInterface;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Registry\Registry;

class DateGuesser implements GuesserInterface, TypeGuesserInterface
{
    /**
     * @param string    $dateFormat      Format of date to use
     * @param bool|null $preferInterface indicator whether to use DateTime or DateTimeInterface as type hint
     */
    public function __construct(
        private readonly string $dateFormat = 'Y-m-d',
        private readonly ?bool $preferInterface = null,
    ) {
    }

    public function supportObject(mixed $object): bool
    {
        return ($object instanceof SchemaInterface) && 'string' === $object->getType() && 'date' === $object->getFormat();
    }

    public function guessType(mixed $object, string $name, string $reference, Registry $registry): Type
    {
        if (!$object instanceof SchemaInterface) {
            throw new LogicException('Expected SchemaInterface, got ' . get_debug_type($object));
        }

        return new DateType($object, $this->dateFormat, true === $this->preferInterface);
    }

    protected function getSchemaClass(): string
    {
        return JsonSchema::class;
    }
}
