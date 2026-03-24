<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Console\Loader;

use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Registry\Schema;

interface SchemaLoaderInterface
{
    /** @param array<string, mixed> $options */
    public function resolve(string $schema, array $options = []): Schema;
}
