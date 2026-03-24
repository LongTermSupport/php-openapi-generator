<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model;

use ArrayObject;

/** @extends ArrayObject<string, mixed> */
class OAuth2SecurityScheme extends ArrayObject
{
    /**
     * @var array<string, bool>
     */
    protected $initialized = [];

    protected ?string $type = null;

    protected ?OAuthFlows $flows = null;

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

    public function getFlows(): ?OAuthFlows
    {
        return $this->flows;
    }

    public function setFlows(?OAuthFlows $flows): self
    {
        $this->initialized['flows'] = true;
        $this->flows                = $flows;

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
