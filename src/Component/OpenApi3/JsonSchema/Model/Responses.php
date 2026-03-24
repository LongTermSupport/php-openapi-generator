<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model;

use ArrayObject;

/** @extends ArrayObject<string, mixed> */
class Responses extends ArrayObject
{
    /**
     * @var array<string, bool>
     */
    protected $initialized = [];

    protected Response|Reference|\LongTermSupport\OpenApiGenerator\Component\OpenApiRuntime\Reference|null $default = null;

    public function isInitialized(string $property): bool
    {
        return \array_key_exists($property, $this->initialized);
    }

    /**
     * @return Response|Reference|null
     */
    public function getDefault(): Response|Reference|\LongTermSupport\OpenApiGenerator\Component\OpenApiRuntime\Reference|null
    {
        return $this->default;
    }

    /**
     * @param Response|Reference|null $default
     */
    public function setDefault(Response|Reference|\LongTermSupport\OpenApiGenerator\Component\OpenApiRuntime\Reference|null $default): self
    {
        $this->initialized['default'] = true;
        $this->default                = $default;

        return $this;
    }
}
