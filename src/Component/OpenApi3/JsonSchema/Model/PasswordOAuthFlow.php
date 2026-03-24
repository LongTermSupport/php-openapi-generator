<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model;

use ArrayObject;

/** @extends ArrayObject<string, mixed> */
class PasswordOAuthFlow extends ArrayObject
{
    /**
     * @var array<string, bool>
     */
    protected $initialized = [];

    protected ?string $tokenUrl = null;

    protected ?string $refreshUrl = null;

    /**
     * @var array<string, string>|null
     */
    protected $scopes;

    public function isInitialized(string $property): bool
    {
        return \array_key_exists($property, $this->initialized);
    }

    public function getTokenUrl(): ?string
    {
        return $this->tokenUrl;
    }

    public function setTokenUrl(?string $tokenUrl): self
    {
        $this->initialized['tokenUrl'] = true;
        $this->tokenUrl                = $tokenUrl;

        return $this;
    }

    public function getRefreshUrl(): ?string
    {
        return $this->refreshUrl;
    }

    public function setRefreshUrl(?string $refreshUrl): self
    {
        $this->initialized['refreshUrl'] = true;
        $this->refreshUrl                = $refreshUrl;

        return $this;
    }

    /**
     * @return array<string, string>|null
     */
    public function getScopes(): ?iterable
    {
        return $this->scopes;
    }

    /**
     * @param array<string, string>|null $scopes
     */
    public function setScopes(?iterable $scopes): self
    {
        $this->initialized['scopes'] = true;
        $this->scopes                = $scopes;

        return $this;
    }
}
