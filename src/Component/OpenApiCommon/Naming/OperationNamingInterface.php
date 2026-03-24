<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Naming;

use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Guesser\Guess\OperationGuess;

interface OperationNamingInterface
{
    public function getFunctionName(OperationGuess $operation): string;

    public function getEndpointName(OperationGuess $operation): string;
}
