<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Guesser\OpenApiSchema;

trait SchemaClassTrait
{
    private string $schemaClass;

    public function __construct(string $schemaClass)
    {
        $this->schemaClass = $schemaClass;
    }

    protected function getSchemaClass(): string
    {
        return $this->schemaClass;
    }
}
