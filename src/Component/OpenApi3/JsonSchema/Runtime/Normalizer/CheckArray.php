<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Runtime\Normalizer;

trait CheckArray
{
    /**
     * @param array<mixed> $array
     */
    public function isOnlyNumericKeys(array $array): bool
    {
        return \count(array_filter($array, is_numeric(...), ARRAY_FILTER_USE_KEY)) === \count($array);
    }
}
