<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model;

class HTTPSecuritySchemeSub
{
    /**
     * @var array<string, bool>
     */
    protected $initialized = [];

    protected mixed $scheme;

    public function isInitialized(string $property): bool
    {
        return \array_key_exists($property, $this->initialized);
    }

    public function getScheme(): mixed
    {
        return $this->scheme;
    }

    public function setScheme(mixed $scheme): self
    {
        $this->initialized['scheme'] = true;
        $this->scheme                = $scheme;

        return $this;
    }
}
