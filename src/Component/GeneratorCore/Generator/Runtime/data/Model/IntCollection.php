<?php

declare(strict_types=1);

final class IntCollection
{
    /** @var list<int> */
    public private(set) array $items;

    public function __construct(int ...$items)
    {
        $this->items = \array_values($items);
    }
}
