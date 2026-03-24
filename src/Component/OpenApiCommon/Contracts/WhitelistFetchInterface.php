<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Contracts;

use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Guesser\Guess\OperationGuess;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Registry\Registry;

interface WhitelistFetchInterface
{
    public function addOperationRelations(OperationGuess $operationGuess, Registry $registry): void;
}
