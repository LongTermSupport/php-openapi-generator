<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Naming;

use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Tools\InflectorTrait;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Guesser\Guess\OperationGuess;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\String\Slugger\SluggerInterface;

class OperationIdNaming implements OperationNamingInterface
{
    use InflectorTrait;

    private SluggerInterface $slugger;

    public function __construct()
    {
        $this->slugger = new AsciiSlugger();
    }

    public function getFunctionName(OperationGuess $operation): string
    {
        $operationId = $this->resolveOperationId($operation);

        return $this->getInflector()->camelize($this->slugger->slug($operationId)->toString());
    }

    public function getEndpointName(OperationGuess $operation): string
    {
        $operationId = $this->slugger->slug($this->resolveOperationId($operation), '-')->toString();

        return $this->getInflector()->classify($operationId);
    }

    private function resolveOperationId(OperationGuess $operation): string
    {
        $op          = $operation->getOperation();
        $operationId = method_exists($op, 'getOperationId') ? $op->getOperationId() : null;
        if (\is_string($operationId) && '' !== $operationId) {
            return $operationId;
        }

        // Fallback: derive an id from method + path when operationId is absent
        return strtolower($operation->getMethod()) . ' ' . $operation->getPath();
    }
}
