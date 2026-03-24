<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Guesser\OpenApiSchema;

use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\JsonSchema\MultipleGuesser as BaseMultipleGuesser;

class MultipleGuesser extends BaseMultipleGuesser
{
    use SchemaClassTrait;

    /** @param array<string> $bannedTypes */
    public function __construct(string $schemaClass, array $bannedTypes = [])
    {
        $this->schemaClass = $schemaClass;
        $this->bannedTypes = $bannedTypes;
    }
}
