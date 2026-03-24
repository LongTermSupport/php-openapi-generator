<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model;

use ArrayObject;

/** @extends ArrayObject<string, mixed> */
class Operation extends ArrayObject
{
    /**
     * @var array<string, bool>
     */
    protected $initialized = [];

    /**
     * @var array<string>|null
     */
    protected ?array $tags = null;

    protected ?string $summary = null;

    protected ?string $description = null;

    protected ?ExternalDocumentation $externalDocs = null;

    protected ?string $operationId = null;

    /**
     * @var array<Parameter|Reference>|null
     */
    protected ?array $parameters = null;

    protected RequestBody|Reference|\LongTermSupport\OpenApiGenerator\Component\OpenApiRuntime\Reference|null $requestBody = null;

    protected ?Responses $responses = null;

    /**
     * @var array<string, mixed[]|Reference>|null
     */
    protected $callbacks;

    protected ?bool $deprecated = false;

    /**
     * @var array<string, string[]>[]|null
     */
    protected $security;

    /**
     * @var array<Server>|null
     */
    protected ?array $servers = null;

    public function isInitialized(string $property): bool
    {
        return \array_key_exists($property, $this->initialized);
    }

    /**
     * @return string[]|null
     */
    public function getTags(): ?array
    {
        return $this->tags;
    }

    /**
     * @param string[]|null $tags
     */
    public function setTags(?array $tags): self
    {
        $this->initialized['tags'] = true;
        $this->tags                = $tags;

        return $this;
    }

    public function getSummary(): ?string
    {
        return $this->summary;
    }

    public function setSummary(?string $summary): self
    {
        $this->initialized['summary'] = true;
        $this->summary                = $summary;

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

    public function getExternalDocs(): ?ExternalDocumentation
    {
        return $this->externalDocs;
    }

    public function setExternalDocs(?ExternalDocumentation $externalDocs): self
    {
        $this->initialized['externalDocs'] = true;
        $this->externalDocs                = $externalDocs;

        return $this;
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

    /**
     * @return Parameter[]|Reference[]|null
     */
    public function getParameters(): ?array
    {
        return $this->parameters;
    }

    /**
     * @param Parameter[]|Reference[]|null $parameters
     */
    public function setParameters(?array $parameters): self
    {
        $this->initialized['parameters'] = true;
        $this->parameters                = $parameters;

        return $this;
    }

    /**
     * @return RequestBody|Reference|null
     */
    public function getRequestBody(): RequestBody|Reference|\LongTermSupport\OpenApiGenerator\Component\OpenApiRuntime\Reference|null
    {
        return $this->requestBody;
    }

    /**
     * @param RequestBody|Reference|null $requestBody
     */
    public function setRequestBody(RequestBody|Reference|\LongTermSupport\OpenApiGenerator\Component\OpenApiRuntime\Reference|null $requestBody): self
    {
        $this->initialized['requestBody'] = true;
        $this->requestBody                = $requestBody;

        return $this;
    }

    public function getResponses(): ?Responses
    {
        return $this->responses;
    }

    public function setResponses(?Responses $responses): self
    {
        $this->initialized['responses'] = true;
        $this->responses                = $responses;

        return $this;
    }

    /**
     * @return array<string, mixed[]|Reference>|null
     */
    public function getCallbacks(): ?iterable
    {
        return $this->callbacks;
    }

    /**
     * @param array<string, mixed[]|Reference>|null $callbacks
     */
    public function setCallbacks(?iterable $callbacks): self
    {
        $this->initialized['callbacks'] = true;
        $this->callbacks                = $callbacks;

        return $this;
    }

    public function getDeprecated(): ?bool
    {
        return $this->deprecated;
    }

    public function setDeprecated(?bool $deprecated): self
    {
        $this->initialized['deprecated'] = true;
        $this->deprecated                = $deprecated;

        return $this;
    }

    /**
     * @return array<string, string[]>[]|null
     */
    public function getSecurity(): ?array
    {
        return $this->security;
    }

    /**
     * @param array<string, string[]>[]|null $security
     */
    public function setSecurity(?array $security): self
    {
        $this->initialized['security'] = true;
        $this->security                = $security;

        return $this;
    }

    /**
     * @return Server[]|null
     */
    public function getServers(): ?array
    {
        return $this->servers;
    }

    /**
     * @param Server[]|null $servers
     */
    public function setServers(?array $servers): self
    {
        $this->initialized['servers'] = true;
        $this->servers                = $servers;

        return $this;
    }
}
