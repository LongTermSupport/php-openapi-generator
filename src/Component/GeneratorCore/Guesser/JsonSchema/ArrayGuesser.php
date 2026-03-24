<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\JsonSchema;

use LogicException;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\ChainGuesserAwareInterface;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\ChainGuesserAwareTrait;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\ClassGuesserInterface;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Guess\ArrayType;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Guess\MultipleType;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Guess\Type;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\GuesserInterface;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\TypeGuesserInterface;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\JsonSchema\Model\SchemaInterface;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Registry\Registry;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Registry\Schema;

class ArrayGuesser implements GuesserInterface, TypeGuesserInterface, ChainGuesserAwareInterface, ClassGuesserInterface
{
    use ChainGuesserAwareTrait;

    /** @var array<string, int> */
    protected array $refGuessLevel = [];

    public function guessClass(mixed $object, string $name, string $reference, Registry $registry): void
    {
        if (!$object instanceof SchemaInterface) {
            throw new LogicException('Expected SchemaInterface, got ' . get_debug_type($object));
        }

        $items = $object->getItems();
        if ($items instanceof SchemaInterface) {
            $this->chainGuesser->guessClass($items, $name . 'Item', $reference . '/items', $registry);
        }
    }

    public function supportObject(mixed $object): bool
    {
        return ($object instanceof SchemaInterface) && 'array' === $object->getType();
    }

    public function guessType(mixed $object, string $name, string $reference, Registry $registry): Type
    {
        if (!$object instanceof SchemaInterface) {
            throw new LogicException('Expected SchemaInterface, got ' . get_debug_type($object));
        }

        $this->refGuessLevel[$reference] = ($this->refGuessLevel[$reference] ?? 0) + 1;

        if ($this->refGuessLevel[$reference] > 20) {
            return new ArrayType($object, new Type($object, 'mixed'));
        }

        $items = $object->getItems();

        if (null === $items || (\is_array($items) && [] === $items)) {
            return new ArrayType($object, new Type($object, 'mixed'));
        }

        if (!\is_array($items)) {
            return new ArrayType($object, $this->chainGuesser->guessType($items, $name . 'Item', $reference . '/items', $registry));
        }

        $type = new MultipleType($object);

        foreach ($items as $key => $item) {
            $type->addType(new ArrayType($object, $this->chainGuesser->guessType($item, $name . 'Item', $reference . '/items/' . $key, $registry)));
        }

        return $type;
    }

    protected function getSchemaClass(): string
    {
        return Schema::class;
    }
}
