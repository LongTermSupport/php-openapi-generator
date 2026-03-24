<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model;

use ArrayObject;

/** @extends ArrayObject<string, mixed> */
class ServerVariable extends ArrayObject
{
    /**
     * @var array<string, bool>
     */
    protected $initialized = [];

    /**
     * @var array<string>|null
     */
    protected ?array $enum = null;

    protected ?string $default = null;

    protected ?string $description = null;

    public function isInitialized(string $property): bool
    {
        return \array_key_exists($property, $this->initialized);
    }

    /**
     * @return string[]|null
     */
    public function getEnum(): ?array
    {
        return $this->enum;
    }

    /**
     * @param string[]|null $enum
     */
    public function setEnum(?array $enum): self
    {
        $this->initialized['enum'] = true;
        $this->enum                = $enum;

        return $this;
    }

    public function getDefault(): ?string
    {
        return $this->default;
    }

    public function setDefault(?string $default): self
    {
        $this->initialized['default'] = true;
        $this->default                = $default;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->initialized['description'] = true;
        $this->description                = $description;

        return $this;
    }
}
