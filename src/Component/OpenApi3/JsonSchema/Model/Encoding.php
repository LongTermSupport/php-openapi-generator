<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model;

class Encoding
{
    /**
     * @var array<string, bool>
     */
    protected $initialized = [];

    protected ?string $contentType = null;

    /**
     * @var array<string, Header>|null
     */
    protected $headers;

    protected ?string $style = null;

    protected ?bool $explode = null;

    protected ?bool $allowReserved = false;

    public function isInitialized(string $property): bool
    {
        return \array_key_exists($property, $this->initialized);
    }

    public function getContentType(): ?string
    {
        return $this->contentType;
    }

    public function setContentType(?string $contentType): self
    {
        $this->initialized['contentType'] = true;
        $this->contentType                = $contentType;

        return $this;
    }

    /**
     * @return array<string, Header>|null
     */
    public function getHeaders(): ?iterable
    {
        return $this->headers;
    }

    /**
     * @param array<string, Header>|null $headers
     */
    public function setHeaders(?iterable $headers): self
    {
        $this->initialized['headers'] = true;
        $this->headers                = $headers;

        return $this;
    }

    public function getStyle(): ?string
    {
        return $this->style;
    }

    public function setStyle(?string $style): self
    {
        $this->initialized['style'] = true;
        $this->style                = $style;

        return $this;
    }

    public function getExplode(): ?bool
    {
        return $this->explode;
    }

    public function setExplode(?bool $explode): self
    {
        $this->initialized['explode'] = true;
        $this->explode                = $explode;

        return $this;
    }

    public function getAllowReserved(): ?bool
    {
        return $this->allowReserved;
    }

    public function setAllowReserved(?bool $allowReserved): self
    {
        $this->initialized['allowReserved'] = true;
        $this->allowReserved                = $allowReserved;

        return $this;
    }
}
