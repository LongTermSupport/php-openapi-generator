<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model;

use ArrayObject;

/** @extends ArrayObject<string, mixed> */
class Response extends ArrayObject
{
    /**
     * @var array<string, bool>
     */
    protected $initialized = [];

    protected ?string $description = null;

    /**
     * @var array<string, Header|Reference>|null
     */
    protected $headers;

    /**
     * @var array<string, MediaType>|null
     */
    protected $content;

    /**
     * @var array<string, Link|Reference>|null
     */
    protected $links;

    public function isInitialized(string $property): bool
    {
        return \array_key_exists($property, $this->initialized);
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
     * @return array<string, Header|Reference>|null
     */
    public function getHeaders(): ?iterable
    {
        return $this->headers;
    }

    /**
     * @param array<string, Header|Reference>|null $headers
     */
    public function setHeaders(?iterable $headers): self
    {
        $this->initialized['headers'] = true;
        $this->headers                = $headers;

        return $this;
    }

    /**
     * @return array<string, MediaType>|null
     */
    public function getContent(): ?iterable
    {
        return $this->content;
    }

    /**
     * @param array<string, MediaType>|null $content
     */
    public function setContent(?iterable $content): self
    {
        $this->initialized['content'] = true;
        $this->content                = $content;

        return $this;
    }

    /**
     * @return array<string, Link|Reference>|null
     */
    public function getLinks(): ?iterable
    {
        return $this->links;
    }

    /**
     * @param array<string, Link|Reference>|null $links
     */
    public function setLinks(?iterable $links): self
    {
        $this->initialized['links'] = true;
        $this->links                = $links;

        return $this;
    }
}
