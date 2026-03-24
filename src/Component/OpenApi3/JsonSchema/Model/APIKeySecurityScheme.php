<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model;

use ArrayObject;

/**
 * @extends ArrayObject<string, mixed>
 */
class APIKeySecurityScheme extends ArrayObject
{
    /**
     * @var array<string, bool>
     */
    protected $initialized = [];

    protected ?string $type = null;

    protected ?string $name = null;

    protected ?string $in = null;

    protected ?string $description = null;

    public function isInitialized(string $property): bool
    {
        return \array_key_exists($property, $this->initialized);
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->initialized['type'] = true;
        $this->type                = $type;

        return $this;
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

    public function getIn(): ?string
    {
        return $this->in;
    }

    public function setIn(?string $in): self
    {
        $this->initialized['in'] = true;
        $this->in                = $in;

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
