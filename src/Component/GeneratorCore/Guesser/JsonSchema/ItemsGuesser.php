<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\JsonSchema;

use LogicException;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\ChainGuesserAwareInterface;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\ChainGuesserAwareTrait;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\ClassGuesserInterface;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\GuesserInterface;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\JsonSchema\Model\JsonSchema;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\JsonSchema\Model\SchemaInterface;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Registry\Registry;

class ItemsGuesser implements GuesserInterface, ClassGuesserInterface, ChainGuesserAwareInterface
{
    use ChainGuesserAwareTrait;

    public function guessClass(mixed $object, string $name, string $reference, Registry $registry): void
    {
        if (!$object instanceof SchemaInterface) {
            throw new LogicException('Expected SchemaInterface, got ' . get_debug_type($object));
        }

        $items = $object->getItems();
        if ($items instanceof SchemaInterface) {
            $this->chainGuesser->guessClass($items, $name . 'Item', $reference . '/items', $registry);
        } elseif (\is_array($items)) {
            foreach ($items as $key => $item) {
                $this->chainGuesser->guessClass($item, $name . 'Item' . $key, $reference . '/items/' . $key, $registry);
            }
        }
    }

    public function supportObject(mixed $object): bool
    {
        if (!$object instanceof SchemaInterface) {
            return false;
        }

        $items = $object->getItems();

        return $items instanceof SchemaInterface
            || (\is_array($items) && [] !== $items);
    }

    protected function getSchemaClass(): string
    {
        return JsonSchema::class;
    }
}
