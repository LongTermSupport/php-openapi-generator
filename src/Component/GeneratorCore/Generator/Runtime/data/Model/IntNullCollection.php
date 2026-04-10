<?php

declare(strict_types=1);

final class IntNullCollection
{
    /** @var list<int|null> */
    public private(set) array $items;

    public function __construct(int|null ...$items)
    {
        $this->items = \array_values($items);
    }
}
