<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Guesser\Guess;

use LogicException;

class OperationGuess
{
    public const DELETE = 'DELETE';

    public const GET = 'GET';

    public const POST = 'POST';

    public const PUT = 'PUT';

    public const PATCH = 'PATCH';

    public const OPTIONS = 'OPTIONS';

    public const HEAD = 'HEAD';

    private readonly string $path;

    /** @var array<mixed> */
    private readonly array $parameters;

    /**
     * @param array<string> $securityScopes
     */
    public function __construct(
        object $pathItem,
        private readonly object $operation,
        string $path,
        private readonly string $method,
        private readonly string $reference,
        private readonly array $securityScopes = [],
    ) {
        $result = \Safe\preg_replace('#^/+#', '/', $path);
        if (!\is_string($result)) {
            throw new LogicException('Expected string, got ' . get_debug_type($result));
        }

        $this->path       = $result;
        $pathItemParams   = method_exists($pathItem, 'getParameters') ? $pathItem->getParameters() : null;
        $operationParams  = method_exists($operation, 'getParameters') ? $operation->getParameters() : null;
        $this->parameters = array_merge(
            \is_array($pathItemParams) ? $pathItemParams : [],
            \is_array($operationParams) ? $operationParams : []
        );
    }

    /** @return array<mixed> */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getOperation(): object
    {
        return $this->operation;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getReference(): string
    {
        return $this->reference;
    }

    /** @return array<string> */
    public function getSecurityScopes(): array
    {
        return $this->securityScopes;
    }
}
