<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Guesser\OpenApiSchema;

use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\JsonSchema\SimpleTypeGuesser as BaseSimpleTypeGuesser;

class SimpleTypeGuesser extends BaseSimpleTypeGuesser
{
    use SchemaClassTrait;

    /** @param array<string>|null $typesSupported */
    public function __construct(string $schemaClass, ?array $typesSupported = null)
    {
        $this->schemaClass = $schemaClass;
        if (null !== $typesSupported) {
            $this->typesSupported = $typesSupported;
        }
    }
}
