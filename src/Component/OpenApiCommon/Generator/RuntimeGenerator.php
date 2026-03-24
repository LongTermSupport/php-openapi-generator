<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Generator;

use Generator;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Generator\RuntimeGenerator as BaseRuntimeGenerator;

class RuntimeGenerator extends BaseRuntimeGenerator
{
    protected function directories(): Generator
    {
        foreach (parent::directories() as $directory) {
            yield $directory;
        }

        yield __DIR__ . '/Runtime/data';
    }
}
