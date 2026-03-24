<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Console\Loader;

interface ConfigLoaderInterface
{
    public function fileKey(): string;

    /** @return array<string, mixed> */
    public function load(string $path): array;
}
