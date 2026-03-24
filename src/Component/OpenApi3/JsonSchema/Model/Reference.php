<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model;

class Reference
{
    /**
     * @var array<string, bool>
     */
    protected $initialized = [];

    protected ?string $dollarRef = null;

    public function isInitialized(string $property): bool
    {
        return \array_key_exists($property, $this->initialized);
    }

    public function getDollarRef(): ?string
    {
        return $this->dollarRef;
    }

    public function setDollarRef(?string $dollarRef): self
    {
        $this->initialized['dollarRef'] = true;
        $this->dollarRef                = $dollarRef;

        return $this;
    }
}
