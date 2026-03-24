<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model;

use ArrayObject;

/** @extends ArrayObject<string, mixed> */
class HTTPSecurityScheme extends ArrayObject
{
    /**
     * @var array<string, bool>
     */
    protected $initialized = [];

    protected ?string $scheme = null;

    protected ?string $bearerFormat = null;

    protected ?string $description = null;

    protected ?string $type = null;

    public function isInitialized(string $property): bool
    {
        return \array_key_exists($property, $this->initialized);
    }

    public function getScheme(): ?string
    {
        return $this->scheme;
    }

    public function setScheme(?string $scheme): self
    {
        $this->initialized['scheme'] = true;
        $this->scheme                = $scheme;

        return $this;
    }

    public function getBearerFormat(): ?string
    {
        return $this->bearerFormat;
    }

    public function setBearerFormat(?string $bearerFormat): self
    {
        $this->initialized['bearerFormat'] = true;
        $this->bearerFormat                = $bearerFormat;

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
}
