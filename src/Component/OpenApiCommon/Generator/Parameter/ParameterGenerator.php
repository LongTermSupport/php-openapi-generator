<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Generator\Parameter;

use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Generator\Context\Context;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Tools\InflectorTrait;
use PhpParser\Node;
use PhpParser\Parser;

abstract class ParameterGenerator
{
    use InflectorTrait;

    public function __construct(
        protected Parser $parser,
    ) {
    }

    public function generateMethodParameter(mixed $parameter, Context $context, string $reference): ?Node\Param
    {
        return null;
    }

    public function generateMethodDocParameter(mixed $parameter, Context $context, string $reference): string
    {
        return '';
    }

    /**
     * @return Node\Expr[]
     */
    protected function generateInputParamArguments(mixed $parameter): array
    {
        return [];
    }
}
