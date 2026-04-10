<?php

declare(strict_types=1);

final class BoolNullCollection
{
    /** @var list<bool|null> */
    public private(set) array $items;

    public function __construct(bool|null ...$items)
    {
        $this->items = \array_values($items);
    }
}
