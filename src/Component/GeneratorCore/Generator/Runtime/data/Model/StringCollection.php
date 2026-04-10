<?php

declare(strict_types=1);

final class StringCollection
{
    /** @var list<string> */
    public private(set) array $items;

    public function __construct(string ...$items)
    {
        $this->items = \array_values($items);
    }
}
