<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApiRuntime\Client;

use Symfony\Component\OptionsResolver\Options;

interface CustomQueryResolver
{
    /** @param bool|int|float|string|array<mixed>|null $value */
    public function __invoke(Options $options, mixed $value): string;
}
