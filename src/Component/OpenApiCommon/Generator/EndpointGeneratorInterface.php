<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Generator;

use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Generator\Context\Context;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Guesser\Guess\OperationGuess;

interface EndpointGeneratorInterface
{
    /** @return array<mixed> */
    public function createEndpointClass(OperationGuess $operation, Context $context): array;
}
