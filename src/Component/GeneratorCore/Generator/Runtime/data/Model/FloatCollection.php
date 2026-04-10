<?php

declare(strict_types=1);

final class FloatCollection
{
    /** @var list<float> */
    public private(set) array $items;

    public function __construct(float ...$items)
    {
        $this->items = \array_values($items);
    }
}
