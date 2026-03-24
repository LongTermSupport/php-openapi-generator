<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApi3\Generator;

use LongTermSupport\OpenApiGenerator\Component\OpenApi3\Generator\Client\ServerPluginGenerator;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Generator\ClientGenerator as BaseClientGenerator;

class ClientGenerator extends BaseClientGenerator
{
    use ServerPluginGenerator;
}
