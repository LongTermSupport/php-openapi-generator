<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model;

use ArrayObject;

/** @extends ArrayObject<string, mixed> */
class MediaType extends ArrayObject
{
    /**
     * @var array<string, bool>
     */
    protected $initialized = [];

    protected Schema|Reference|\LongTermSupport\OpenApiGenerator\Component\OpenApiRuntime\Reference|null $schema = null;

    protected mixed $example;

    /**
     * @var array<string, Example|Reference>|null
     */
    protected $examples;

    /**
     * @var array<string, Encoding>|null
     */
    protected $encoding;

    public function isInitialized(string $property): bool
    {
        return \array_key_exists($property, $this->initialized);
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

    /**
     * @return array<string, Encoding>|null
     */
    public function getEncoding(): ?iterable
    {
        return $this->encoding;
    }

    /**
     * @param array<string, Encoding>|null $encoding
     */
    public function setEncoding(?iterable $encoding): self
    {
        $this->initialized['encoding'] = true;
        $this->encoding                = $encoding;

        return $this;
    }
}
