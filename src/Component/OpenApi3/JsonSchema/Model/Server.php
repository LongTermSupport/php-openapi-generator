<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model;

use ArrayObject;

/** @extends ArrayObject<string, mixed> */
class Server extends ArrayObject
{
    /**
     * @var array<string, bool>
     */
    protected $initialized = [];

    protected ?string $url = null;

    protected ?string $description = null;

    /**
     * @var array<string, ServerVariable>|null
     */
    protected $variables;

    public function isInitialized(string $property): bool
    {
        return \array_key_exists($property, $this->initialized);
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): self
    {
        $this->initialized['url'] = true;
        $this->url                = $url;

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

    /**
     * @return array<string, ServerVariable>|null
     */
    public function getVariables(): ?iterable
    {
        return $this->variables;
    }

    /**
     * @param array<string, ServerVariable>|null $variables
     */
    public function setVariables(?iterable $variables): self
    {
        $this->initialized['variables'] = true;
        $this->variables                = $variables;

        return $this;
    }
}
