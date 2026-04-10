<?php

declare(strict_types=1);

final class NullStringCollection
{
    /** @var list<string|null> */
    public private(set) array $items;

    public function __construct(string|null ...$items)
    {
        $this->items = \array_values($items);
    }
}
