<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Guesser\OpenApiSchema;

use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\JsonSchema\ItemsGuesser as BaseItemsGuesser;

/**
 * @internal
 */
class ItemsGuesser extends BaseItemsGuesser
{
    use SchemaClassTrait;
}
