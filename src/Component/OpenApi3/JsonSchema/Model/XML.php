<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model;

use ArrayObject;

/** @extends ArrayObject<string, mixed> */
class XML extends ArrayObject
{
    /**
     * @var array<string, bool>
     */
    protected $initialized = [];

    protected ?string $name = null;

    protected ?string $namespace = null;

    protected ?string $prefix = null;

    protected ?bool $attribute = false;

    protected ?bool $wrapped = false;

    public function isInitialized(string $property): bool
    {
        return \array_key_exists($property, $this->initialized);
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->initialized['name'] = true;
        $this->name                = $name;

        return $this;
    }

    public function getNamespace(): ?string
    {
        return $this->namespace;
    }

    public function setNamespace(?string $namespace): self
    {
        $this->initialized['namespace'] = true;
        $this->namespace                = $namespace;

        return $this;
    }

    public function getPrefix(): ?string
    {
        return $this->prefix;
    }

    public function setPrefix(?string $prefix): self
    {
        $this->initialized['prefix'] = true;
        $this->prefix                = $prefix;

        return $this;
    }

    public function getAttribute(): ?bool
    {
        return $this->attribute;
    }

    public function setAttribute(?bool $attribute): self
    {
        $this->initialized['attribute'] = true;
        $this->attribute                = $attribute;

        return $this;
    }

    public function getWrapped(): ?bool
    {
        return $this->wrapped;
    }

    public function setWrapped(?bool $wrapped): self
    {
        $this->initialized['wrapped'] = true;
        $this->wrapped                = $wrapped;

        return $this;
    }
}
