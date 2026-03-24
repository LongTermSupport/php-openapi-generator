<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApi3\Tests\Expected\AllBooleanQueryResolver;

use LongTermSupport\OpenApiGenerator\Component\OpenApiRuntime\Client\CustomQueryResolver;
use Symfony\Component\OptionsResolver\Options;

class BooleanCustomQueryResolver implements CustomQueryResolver
{
    public function __invoke(Options $options, mixed $value): string
    {
        return ((bool)$value) ? 'true' : 'false';
    }
}
