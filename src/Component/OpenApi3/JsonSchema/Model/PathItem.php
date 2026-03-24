<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model;

use ArrayObject;

/** @extends ArrayObject<string, mixed> */
class PathItem extends ArrayObject
{
    /**
     * @var array<string, bool>
     */
    protected $initialized = [];

    protected ?string $dollarRef = null;

    protected ?string $summary = null;

    protected ?string $description = null;

    protected ?Operation $get = null;

    protected ?Operation $put = null;

    protected ?Operation $post = null;

    protected ?Operation $delete = null;

    protected ?Operation $options = null;

    protected ?Operation $head = null;

    protected ?Operation $patch = null;

    protected ?Operation $trace = null;

    /**
     * @var array<Server>|null
     */
    protected ?array $servers = null;

    /**
     * @var array<Parameter|Reference>|null
     */
    protected ?array $parameters = null;

    public function isInitialized(string $property): bool
    {
        return \array_key_exists($property, $this->initialized);
    }

    public function getDollarRef(): ?string
    {
        return $this->dollarRef;
    }

    public function setDollarRef(?string $dollarRef): self
    {
        $this->initialized['dollarRef'] = true;
        $this->dollarRef                = $dollarRef;

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

    public function getGet(): ?Operation
    {
        return $this->get;
    }

    public function setGet(?Operation $get): self
    {
        $this->initialized['get'] = true;
        $this->get                = $get;

        return $this;
    }

    public function getPut(): ?Operation
    {
        return $this->put;
    }

    public function setPut(?Operation $put): self
    {
        $this->initialized['put'] = true;
        $this->put                = $put;

        return $this;
    }

    public function getPost(): ?Operation
    {
        return $this->post;
    }

    public function setPost(?Operation $post): self
    {
        $this->initialized['post'] = true;
        $this->post                = $post;

        return $this;
    }

    public function getDelete(): ?Operation
    {
        return $this->delete;
    }

    public function setDelete(?Operation $delete): self
    {
        $this->initialized['delete'] = true;
        $this->delete                = $delete;

        return $this;
    }

    public function getOptions(): ?Operation
    {
        return $this->options;
    }

    public function setOptions(?Operation $options): self
    {
        $this->initialized['options'] = true;
        $this->options                = $options;

        return $this;
    }

    public function getHead(): ?Operation
    {
        return $this->head;
    }

    public function setHead(?Operation $head): self
    {
        $this->initialized['head'] = true;
        $this->head                = $head;

        return $this;
    }

    public function getPatch(): ?Operation
    {
        return $this->patch;
    }

    public function setPatch(?Operation $patch): self
    {
        $this->initialized['patch'] = true;
        $this->patch                = $patch;

        return $this;
    }

    public function getTrace(): ?Operation
    {
        return $this->trace;
    }

    public function setTrace(?Operation $trace): self
    {
        $this->initialized['trace'] = true;
        $this->trace                = $trace;

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
}
