<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model;

use ArrayObject;

/** @extends ArrayObject<string, mixed> */
class Example extends ArrayObject
{
    /**
     * @var array<string, bool>
     */
    protected $initialized = [];

    protected ?string $summary = null;

    protected ?string $description = null;

    protected mixed $value;

    protected ?string $externalValue = null;

    public function isInitialized(string $property): bool
    {
        return \array_key_exists($property, $this->initialized);
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

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function setValue(mixed $value): self
    {
        $this->initialized['value'] = true;
        $this->value                = $value;

        return $this;
    }

    public function getExternalValue(): ?string
    {
        return $this->externalValue;
    }

    public function setExternalValue(?string $externalValue): self
    {
        $this->initialized['externalValue'] = true;
        $this->externalValue                = $externalValue;

        return $this;
    }
}
