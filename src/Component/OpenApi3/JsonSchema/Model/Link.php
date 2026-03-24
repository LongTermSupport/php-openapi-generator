<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model;

use ArrayObject;

/** @extends ArrayObject<string, mixed> */
class Link extends ArrayObject
{
    /**
     * @var array<string, bool>
     */
    protected $initialized = [];

    protected ?string $operationId = null;

    protected ?string $operationRef = null;

    /**
     * @var array<string, mixed>|null
     */
    protected $parameters;

    protected mixed $requestBody;

    protected ?string $description = null;

    protected ?Server $server = null;

    public function isInitialized(string $property): bool
    {
        return \array_key_exists($property, $this->initialized);
    }

    public function getOperationId(): ?string
    {
        return $this->operationId;
    }

    public function setOperationId(?string $operationId): self
    {
        $this->initialized['operationId'] = true;
        $this->operationId                = $operationId;

        return $this;
    }

    public function getOperationRef(): ?string
    {
        return $this->operationRef;
    }

    public function setOperationRef(?string $operationRef): self
    {
        $this->initialized['operationRef'] = true;
        $this->operationRef                = $operationRef;

        return $this;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getParameters(): ?iterable
    {
        return $this->parameters;
    }

    /**
     * @param array<string, mixed>|null $parameters
     */
    public function setParameters(?iterable $parameters): self
    {
        $this->initialized['parameters'] = true;
        $this->parameters                = $parameters;

        return $this;
    }

    public function getRequestBody(): mixed
    {
        return $this->requestBody;
    }

    public function setRequestBody(mixed $requestBody): self
    {
        $this->initialized['requestBody'] = true;
        $this->requestBody                = $requestBody;

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

    public function getServer(): ?Server
    {
        return $this->server;
    }

    public function setServer(?Server $server): self
    {
        $this->initialized['server'] = true;
        $this->server                = $server;

        return $this;
    }
}
