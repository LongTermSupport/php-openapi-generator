<?php

declare(strict_types=1);

final class BoolCollection
{
    /** @var list<bool> */
    public private(set) array $items;

    public function __construct(bool ...$items)
    {
        $this->items = \array_values($items);
    }
}
