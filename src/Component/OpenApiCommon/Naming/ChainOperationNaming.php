<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Naming;

use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Guesser\Guess\OperationGuess;
use RuntimeException;

class ChainOperationNaming implements OperationNamingInterface
{
    /**
     * @param OperationNamingInterface[] $operationNamings
     */
    public function __construct(
        private readonly array $operationNamings,
    ) {
    }

    public function getFunctionName(OperationGuess $operation): string
    {
        foreach ($this->operationNamings as $operationNaming) {
            $functionName = $operationNaming->getFunctionName($operation);

            if ('' !== $functionName) {
                return $functionName;
            }
        }

        throw new RuntimeException('Cannot generate function name');
    }

    public function getEndpointName(OperationGuess $operation): string
    {
        foreach ($this->operationNamings as $operationNaming) {
            $functionName = $operationNaming->getEndpointName($operation);

            if (mb_strlen($functionName) > 0) {
                return $functionName;
            }
        }

        throw new RuntimeException('Cannot generate endpoint name');
    }
}
