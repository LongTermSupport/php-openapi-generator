<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model;

use ArrayObject;

/** @extends ArrayObject<string, mixed> */
class OpenApi extends ArrayObject
{
    /**
     * @var array<string, bool>
     */
    protected $initialized = [];

    protected ?string $openapi = null;

    protected ?Info $info = null;

    protected ?ExternalDocumentation $externalDocs = null;

    /**
     * @var array<Server>|null
     */
    protected ?array $servers = null;

    /**
     * @var array<string, string[]>[]|null
     */
    protected $security;

    /**
     * @var array<Tag>|null
     */
    protected ?array $tags = null;

    /**
     * @var array<PathItem|mixed>|null
     */
    protected ?array $paths = null;

    protected ?Components $components = null;

    public function isInitialized(string $property): bool
    {
        return \array_key_exists($property, $this->initialized);
    }

    public function getOpenapi(): ?string
    {
        return $this->openapi;
    }

    public function setOpenapi(?string $openapi): self
    {
        $this->initialized['openapi'] = true;
        $this->openapi                = $openapi;

        return $this;
    }

    public function getInfo(): ?Info
    {
        return $this->info;
    }

    public function setInfo(?Info $info): self
    {
        $this->initialized['info'] = true;
        $this->info                = $info;

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
     * @return Tag[]|null
     */
    public function getTags(): ?array
    {
        return $this->tags;
    }

    /**
     * @param Tag[]|null $tags
     */
    public function setTags(?array $tags): self
    {
        $this->initialized['tags'] = true;
        $this->tags                = $tags;

        return $this;
    }

    /**
     * @return array<mixed>
     */
    public function getPaths(): array
    {
        return $this->paths ?? [];
    }

    /**
     * @param array<mixed> $paths
     */
    public function setPaths(?array $paths): self
    {
        $this->initialized['paths'] = true;
        $this->paths                = $paths;

        return $this;
    }

    public function getComponents(): ?Components
    {
        return $this->components;
    }

    public function setComponents(?Components $components): self
    {
        $this->initialized['components'] = true;
        $this->components                = $components;

        return $this;
    }
}
