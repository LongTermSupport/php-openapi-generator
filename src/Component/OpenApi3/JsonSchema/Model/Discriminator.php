<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model;

class Discriminator
{
    /**
     * @var array<string, bool>
     */
    protected $initialized = [];

    protected ?string $propertyName = null;

    /**
     * @var array<string, string>|null
     */
    protected $mapping;

    public function isInitialized(string $property): bool
    {
        return \array_key_exists($property, $this->initialized);
    }

    public function getPropertyName(): ?string
    {
        return $this->propertyName;
    }

    public function setPropertyName(?string $propertyName): self
    {
        $this->initialized['propertyName'] = true;
        $this->propertyName                = $propertyName;

        return $this;
    }

    /**
     * @return array<string, string>|null
     */
    public function getMapping(): ?iterable
    {
        return $this->mapping;
    }

    /**
     * @param array<string, string>|null $mapping
     */
    public function setMapping(?iterable $mapping): self
    {
        $this->initialized['mapping'] = true;
        $this->mapping                = $mapping;

        return $this;
    }
}
