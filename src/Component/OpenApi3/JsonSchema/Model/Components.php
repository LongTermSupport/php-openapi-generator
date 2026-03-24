<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model;

use ArrayObject;

/**
 * @extends ArrayObject<string, mixed>
 */
class Components extends ArrayObject
{
    /**
     * @var array<string, bool>
     */
    protected $initialized = [];

    /**
     * @phpstan-var \ArrayObject<string, Reference|Schema>|null
     */
    protected ?ArrayObject $schemas = null;

    /**
     * @phpstan-var \ArrayObject<string, Reference|Response>|null
     */
    protected ?ArrayObject $responses = null;

    /**
     * @phpstan-var \ArrayObject<string, Parameter|Reference>|null
     */
    protected ?ArrayObject $parameters = null;

    /**
     * @phpstan-var \ArrayObject<string, Example|Reference>|null
     */
    protected ?ArrayObject $examples = null;

    /**
     * @phpstan-var \ArrayObject<string, Reference|RequestBody>|null
     */
    protected ?ArrayObject $requestBodies = null;

    /**
     * @phpstan-var \ArrayObject<string, Header|Reference>|null
     */
    protected ?ArrayObject $headers = null;

    /**
     * @phpstan-var \ArrayObject<string, APIKeySecurityScheme|HTTPSecurityScheme|OAuth2SecurityScheme|OpenIdConnectSecurityScheme|Reference>|null
     */
    protected ?ArrayObject $securitySchemes = null;

    /**
     * @phpstan-var \ArrayObject<string, Link|Reference>|null
     */
    protected ?ArrayObject $links = null;

    /**
     * @var array<string, array<mixed>|Reference>|null
     */
    protected $callbacks;

    public function isInitialized(string $property): bool
    {
        return \array_key_exists($property, $this->initialized);
    }

    /**
     * @return ArrayObject<string, Reference|Schema>|null
     */
    public function getSchemas(): ?ArrayObject
    {
        return $this->schemas;
    }

    /**
     * @param ArrayObject<string, Reference|Schema>|null $schemas
     */
    public function setSchemas(?ArrayObject $schemas): self
    {
        $this->initialized['schemas'] = true;
        $this->schemas                = $schemas;

        return $this;
    }

    /**
     * @return ArrayObject<string, Reference|Response>|null
     */
    public function getResponses(): ?ArrayObject
    {
        return $this->responses;
    }

    /**
     * @param ArrayObject<string, Reference|Response>|null $responses
     */
    public function setResponses(?ArrayObject $responses): self
    {
        $this->initialized['responses'] = true;
        $this->responses                = $responses;

        return $this;
    }

    /**
     * @return ArrayObject<string, Parameter|Reference>|null
     */
    public function getParameters(): ?ArrayObject
    {
        return $this->parameters;
    }

    /**
     * @param ArrayObject<string, Parameter|Reference>|null $parameters
     */
    public function setParameters(?ArrayObject $parameters): self
    {
        $this->initialized['parameters'] = true;
        $this->parameters                = $parameters;

        return $this;
    }

    /**
     * @return ArrayObject<string, Example|Reference>|null
     */
    public function getExamples(): ?ArrayObject
    {
        return $this->examples;
    }

    /**
     * @param ArrayObject<string, Example|Reference>|null $examples
     */
    public function setExamples(?ArrayObject $examples): self
    {
        $this->initialized['examples'] = true;
        $this->examples                = $examples;

        return $this;
    }

    /**
     * @return ArrayObject<string, Reference|RequestBody>|null
     */
    public function getRequestBodies(): ?ArrayObject
    {
        return $this->requestBodies;
    }

    /**
     * @param ArrayObject<string, Reference|RequestBody>|null $requestBodies
     */
    public function setRequestBodies(?ArrayObject $requestBodies): self
    {
        $this->initialized['requestBodies'] = true;
        $this->requestBodies                = $requestBodies;

        return $this;
    }

    /**
     * @return ArrayObject<string, Header|Reference>|null
     */
    public function getHeaders(): ?ArrayObject
    {
        return $this->headers;
    }

    /**
     * @param ArrayObject<string, Header|Reference>|null $headers
     */
    public function setHeaders(?ArrayObject $headers): self
    {
        $this->initialized['headers'] = true;
        $this->headers                = $headers;

        return $this;
    }

    /**
     * @return ArrayObject<string, APIKeySecurityScheme|HTTPSecurityScheme|OAuth2SecurityScheme|OpenIdConnectSecurityScheme|Reference>|null
     */
    public function getSecuritySchemes(): ?ArrayObject
    {
        return $this->securitySchemes;
    }

    /**
     * @param ArrayObject<string, APIKeySecurityScheme|HTTPSecurityScheme|OAuth2SecurityScheme|OpenIdConnectSecurityScheme|Reference>|null $securitySchemes
     */
    public function setSecuritySchemes(?ArrayObject $securitySchemes): self
    {
        $this->initialized['securitySchemes'] = true;
        $this->securitySchemes                = $securitySchemes;

        return $this;
    }

    /**
     * @return ArrayObject<string, Link|Reference>|null
     */
    public function getLinks(): ?ArrayObject
    {
        return $this->links;
    }

    /**
     * @param ArrayObject<string, Link|Reference>|null $links
     */
    public function setLinks(?ArrayObject $links): self
    {
        $this->initialized['links'] = true;
        $this->links                = $links;

        return $this;
    }

    /**
     * @return array<string, array<mixed>|Reference>|null
     */
    public function getCallbacks(): ?array
    {
        return $this->callbacks;
    }

    /**
     * @param array<string, array<mixed>|Reference>|null $callbacks
     */
    public function setCallbacks(?array $callbacks): self
    {
        $this->initialized['callbacks'] = true;
        $this->callbacks                = $callbacks;

        return $this;
    }
}
