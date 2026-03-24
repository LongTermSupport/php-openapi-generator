<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Guesser\OpenApiSchema;

use DateTimeInterface;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\JsonSchema\DateTimeGuesser as BaseDateTimeGuesser;

class DateTimeGuesser extends BaseDateTimeGuesser
{
    use SchemaClassTrait;

    public function __construct(string $schemaClass, string $outputDateFormat = DateTimeInterface::RFC3339, ?string $inputDateFormat = null, ?bool $preferInterface = null)
    {
        parent::__construct($outputDateFormat, $inputDateFormat, $preferInterface);
        $this->schemaClass = $schemaClass;
    }
}
