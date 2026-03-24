<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Registry;

use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Registry\Registry as BaseRegistry;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Registry\RegistryInterface;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Guesser\Guess\SecuritySchemeGuess;

class Registry extends BaseRegistry implements RegistryInterface
{
    private string $openApiClass;

    /** @var array<string> */
    private array $whitelistedPaths;

    /** @var array<string, mixed> */
    private array $customQueryResolver;

    private bool $throwUnexpectedStatusCode;

    public function setOpenApiClass(string $openApiClass): void
    {
        $this->openApiClass = $openApiClass;
    }

    public function getOpenApiClass(): string
    {
        return $this->openApiClass;
    }

    /** @param array<string> $whitelistedPaths */
    public function setWhitelistedPaths(array $whitelistedPaths): void
    {
        $this->whitelistedPaths = $whitelistedPaths;
    }

    /** @return array<string> */
    public function getWhitelistedPaths(): array
    {
        return $this->whitelistedPaths;
    }

    /** @param array<string, mixed> $customQueryResolver */
    public function setCustomQueryResolver(array $customQueryResolver): void
    {
        $this->customQueryResolver = $customQueryResolver;
    }

    /** @return array<string, mixed> */
    public function getCustomQueryResolver(): array
    {
        return $this->customQueryResolver;
    }

    public function setThrowUnexpectedStatusCode(bool $throwUnexpectedStatusCode): void
    {
        $this->throwUnexpectedStatusCode = $throwUnexpectedStatusCode;
    }

    public function getThrowUnexpectedStatusCode(): bool
    {
        return $this->throwUnexpectedStatusCode;
    }

    public function hasSecurityScheme(string $securitySchemeReference): bool
    {
        return $this->getClass($securitySchemeReference) instanceof \LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Guess\ClassGuess;
    }

    public function getSecurityScheme(string $securitySchemeReference): ?SecuritySchemeGuess
    {
        $schema = $this->getSchema($securitySchemeReference);

        if (!$schema instanceof Schema) {
            return null;
        }

        return $schema->getSecurityScheme($securitySchemeReference);
    }

    public function getOptionsHash(): string
    {
        $encoded = \Safe\json_encode([
            'open-api-class'               => $this->getOpenApiClass(),
            'whitelisted-paths'            => $this->getWhitelistedPaths(),
            'throw-unexpected-status-code' => $this->getThrowUnexpectedStatusCode(),
        ]);

        return md5($encoded);
    }
}
