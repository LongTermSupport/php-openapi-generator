<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApi3\Tests\Fixtures\CustomEndpointGenerator;

use LongTermSupport\OpenApiGenerator\Component\OpenApi3\Generator\EndpointGenerator;

class CustomEndpointGenerator extends EndpointGenerator
{
    /** @return list<string> */
    protected function getInterface(): array
    {
        return ['Endpoint'];
    }

    /** @return list<string> */
    protected function getTrait(): array
    {
        return [CustomEndpointTrait::class];
    }
}
