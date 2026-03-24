<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model;

use ArrayObject;

/** @extends ArrayObject<string, mixed> */
class Header extends ArrayObject
{
    /**
     * @var array<string, bool>
     */
    protected $initialized = [];

    protected ?string $description = null;

    protected ?bool $required = false;

    protected ?bool $deprecated = false;

    protected ?bool $allowEmptyValue = false;

    protected ?string $style = 'simple';

    protected ?bool $explode = null;

    protected ?bool $allowReserved = false;

    protected Schema|Reference|\LongTermSupport\OpenApiGenerator\Component\OpenApiRuntime\Reference|null $schema = null;

    /**
     * @var array<string, MediaType>|null
     */
    protected $content;

    protected mixed $example;

    /**
     * @var array<string, Example|Reference>|null
     */
    protected $examples;

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

    public function getRequired(): ?bool
    {
        return $this->required;
    }

    public function setRequired(?bool $required): self
    {
        $this->initialized['required'] = true;
        $this->required                = $required;

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

    public function getAllowEmptyValue(): ?bool
    {
        return $this->allowEmptyValue;
    }

    public function setAllowEmptyValue(?bool $allowEmptyValue): self
    {
        $this->initialized['allowEmptyValue'] = true;
        $this->allowEmptyValue                = $allowEmptyValue;

        return $this;
    }

    public function getStyle(): ?string
    {
        return $this->style;
    }

    public function setStyle(?string $style): self
    {
        $this->initialized['style'] = true;
        $this->style                = $style;

        return $this;
    }

    public function getExplode(): ?bool
    {
        return $this->explode;
    }

    public function setExplode(?bool $explode): self
    {
        $this->initialized['explode'] = true;
        $this->explode                = $explode;

        return $this;
    }

    public function getAllowReserved(): ?bool
    {
        return $this->allowReserved;
    }

    public function setAllowReserved(?bool $allowReserved): self
    {
        $this->initialized['allowReserved'] = true;
        $this->allowReserved                = $allowReserved;

        return $this;
    }

    /**
     * @return Schema|Reference|null
     */
    public function getSchema(): Schema|Reference|\LongTermSupport\OpenApiGenerator\Component\OpenApiRuntime\Reference|null
    {
        return $this->schema;
    }

    /**
     * @param Schema|Reference|null $schema
     */
    public function setSchema(Schema|Reference|\LongTermSupport\OpenApiGenerator\Component\OpenApiRuntime\Reference|null $schema): self
    {
        $this->initialized['schema'] = true;
        $this->schema                = $schema;

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

    public function getExample(): mixed
    {
        return $this->example;
    }

    public function setExample(mixed $example): self
    {
        $this->initialized['example'] = true;
        $this->example                = $example;

        return $this;
    }

    /**
     * @return array<string, Example|Reference>|null
     */
    public function getExamples(): ?iterable
    {
        return $this->examples;
    }

    /**
     * @param array<string, Example|Reference>|null $examples
     */
    public function setExamples(?iterable $examples): self
    {
        $this->initialized['examples'] = true;
        $this->examples                = $examples;

        return $this;
    }
}
