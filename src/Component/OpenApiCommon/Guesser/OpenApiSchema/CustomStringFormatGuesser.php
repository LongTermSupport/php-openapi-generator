<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Guesser\OpenApiSchema;

use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\JsonSchema\CustomStringFormatGuesser as BaseCustomStringFormatGuesser;

class CustomStringFormatGuesser extends BaseCustomStringFormatGuesser
{
    use SchemaClassTrait;

    public function __construct(string $schemaClass, array $mapping)
    {
        parent::__construct($mapping);
        $this->schemaClass = $schemaClass;
    }
}
