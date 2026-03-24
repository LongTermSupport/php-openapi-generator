<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Guesser\OpenApiSchema;

use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\JsonSchema\AdditionalPropertiesGuesser as BaseAdditionalPropertiesGuesser;

class AdditionalPropertiesGuesser extends BaseAdditionalPropertiesGuesser
{
    use SchemaClassTrait;
}
