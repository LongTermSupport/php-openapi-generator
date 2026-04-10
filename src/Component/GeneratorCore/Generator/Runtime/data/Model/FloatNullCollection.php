<?php

declare(strict_types=1);

final class FloatNullCollection
{
    /** @var list<float|null> */
    public private(set) array $items;

    public function __construct(float|null ...$items)
    {
        $this->items = \array_values($items);
    }
}
