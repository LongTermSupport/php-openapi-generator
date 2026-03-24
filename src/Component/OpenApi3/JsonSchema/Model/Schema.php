<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model;

use ArrayObject;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\JsonSchema\Model\SchemaInterface;

/** @extends ArrayObject<string, mixed> */
class Schema extends ArrayObject implements SchemaInterface
{
    /**
     * @var array<string, bool>
     */
    protected $initialized = [];

    protected ?string $title = null;

    protected ?float $multipleOf = null;

    protected ?float $maximum = null;

    protected ?bool $exclusiveMaximum = false;

    protected ?float $minimum = null;

    protected ?bool $exclusiveMinimum = false;

    protected ?int $maxLength = null;

    protected ?int $minLength = 0;

    protected ?string $pattern = null;

    protected ?int $maxItems = null;

    protected ?int $minItems = 0;

    protected ?bool $uniqueItems = false;

    protected ?int $maxProperties = null;

    protected ?int $minProperties = 0;

    /**
     * @var array<string>|null
     */
    protected ?array $required = null;

    /**
     * @var array<mixed>|null
     */
    protected ?array $enum = null;

    protected ?string $type = null;

    protected Schema|Reference|\LongTermSupport\OpenApiGenerator\Component\OpenApiRuntime\Reference|null $not = null;

    /**
     * @var array<Schema|Reference>|null
     */
    protected ?array $allOf = null;

    /**
     * @var array<Schema|Reference>|null
     */
    protected ?array $oneOf = null;

    /**
     * @var array<Schema|Reference>|null
     */
    protected ?array $anyOf = null;

    protected Schema|Reference|\LongTermSupport\OpenApiGenerator\Component\OpenApiRuntime\Reference|null $items = null;

    /**
     * @var array<string, Schema|Reference>|null
     */
    protected $properties;

    protected Schema|Reference|\LongTermSupport\OpenApiGenerator\Component\OpenApiRuntime\Reference|bool|null $additionalProperties = true;

    protected ?string $description = null;

    protected ?string $format = null;

    /** @var string|int|float|bool|array<mixed>|null */
    protected string|int|float|bool|array|null $default = null;

    protected ?bool $nullable = false;

    protected ?Discriminator $discriminator = null;

    protected ?bool $readOnly = false;

    protected ?bool $writeOnly = false;

    protected mixed $example;

    protected ?ExternalDocumentation $externalDocs = null;

    protected ?bool $deprecated = false;

    protected ?XML $xml = null;

    public function isInitialized(string $property): bool
    {
        return \array_key_exists($property, $this->initialized);
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->initialized['title'] = true;
        $this->title                = $title;

        return $this;
    }

    public function getMultipleOf(): ?float
    {
        return $this->multipleOf;
    }

    public function setMultipleOf(?float $multipleOf): self
    {
        $this->initialized['multipleOf'] = true;
        $this->multipleOf                = $multipleOf;

        return $this;
    }

    public function getMaximum(): ?float
    {
        return $this->maximum;
    }

    public function setMaximum(?float $maximum): self
    {
        $this->initialized['maximum'] = true;
        $this->maximum                = $maximum;

        return $this;
    }

    public function getExclusiveMaximum(): ?bool
    {
        return $this->exclusiveMaximum;
    }

    public function setExclusiveMaximum(?bool $exclusiveMaximum): self
    {
        $this->initialized['exclusiveMaximum'] = true;
        $this->exclusiveMaximum                = $exclusiveMaximum;

        return $this;
    }

    public function getMinimum(): ?float
    {
        return $this->minimum;
    }

    public function setMinimum(?float $minimum): self
    {
        $this->initialized['minimum'] = true;
        $this->minimum                = $minimum;

        return $this;
    }

    public function getExclusiveMinimum(): ?bool
    {
        return $this->exclusiveMinimum;
    }

    public function setExclusiveMinimum(?bool $exclusiveMinimum): self
    {
        $this->initialized['exclusiveMinimum'] = true;
        $this->exclusiveMinimum                = $exclusiveMinimum;

        return $this;
    }

    public function getMaxLength(): ?int
    {
        return $this->maxLength;
    }

    public function setMaxLength(?int $maxLength): self
    {
        $this->initialized['maxLength'] = true;
        $this->maxLength                = $maxLength;

        return $this;
    }

    public function getMinLength(): ?int
    {
        return $this->minLength;
    }

    public function setMinLength(?int $minLength): self
    {
        $this->initialized['minLength'] = true;
        $this->minLength                = $minLength;

        return $this;
    }

    public function getPattern(): ?string
    {
        return $this->pattern;
    }

    public function setPattern(?string $pattern): self
    {
        $this->initialized['pattern'] = true;
        $this->pattern                = $pattern;

        return $this;
    }

    public function getMaxItems(): ?int
    {
        return $this->maxItems;
    }

    public function setMaxItems(?int $maxItems): self
    {
        $this->initialized['maxItems'] = true;
        $this->maxItems                = $maxItems;

        return $this;
    }

    public function getMinItems(): ?int
    {
        return $this->minItems;
    }

    public function setMinItems(?int $minItems): self
    {
        $this->initialized['minItems'] = true;
        $this->minItems                = $minItems;

        return $this;
    }

    public function getUniqueItems(): ?bool
    {
        return $this->uniqueItems;
    }

    public function setUniqueItems(?bool $uniqueItems): self
    {
        $this->initialized['uniqueItems'] = true;
        $this->uniqueItems                = $uniqueItems;

        return $this;
    }

    public function getMaxProperties(): ?int
    {
        return $this->maxProperties;
    }

    public function setMaxProperties(?int $maxProperties): self
    {
        $this->initialized['maxProperties'] = true;
        $this->maxProperties                = $maxProperties;

        return $this;
    }

    public function getMinProperties(): ?int
    {
        return $this->minProperties;
    }

    public function setMinProperties(?int $minProperties): self
    {
        $this->initialized['minProperties'] = true;
        $this->minProperties                = $minProperties;

        return $this;
    }

    /**
     * @return string[]|null
     */
    public function getRequired(): ?array
    {
        return $this->required;
    }

    /**
     * @param string[]|null $required
     */
    public function setRequired(?array $required): self
    {
        $this->initialized['required'] = true;
        $this->required                = $required;

        return $this;
    }

    /**
     * @return mixed[]|null
     */
    public function getEnum(): ?array
    {
        return $this->enum;
    }

    /**
     * @param mixed[]|null $enum
     */
    public function setEnum(?array $enum): self
    {
        $this->initialized['enum'] = true;
        $this->enum                = $enum;

        return $this;
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

    /**
     * @return Schema|Reference|null
     */
    public function getNot(): self|Reference|\LongTermSupport\OpenApiGenerator\Component\OpenApiRuntime\Reference|null
    {
        return $this->not;
    }

    /**
     * @param Schema|Reference|null $not
     */
    public function setNot(self|Reference|\LongTermSupport\OpenApiGenerator\Component\OpenApiRuntime\Reference|null $not): self
    {
        $this->initialized['not'] = true;
        $this->not                = $not;

        return $this;
    }

    /**
     * @return Schema[]|Reference[]|null
     */
    public function getAllOf(): ?array
    {
        return $this->allOf;
    }

    /**
     * @param Schema[]|Reference[]|null $allOf
     */
    public function setAllOf(?array $allOf): self
    {
        $this->initialized['allOf'] = true;
        $this->allOf                = $allOf;

        return $this;
    }

    /**
     * @return Schema[]|Reference[]|null
     */
    public function getOneOf(): ?array
    {
        return $this->oneOf;
    }

    /**
     * @param Schema[]|Reference[]|null $oneOf
     */
    public function setOneOf(?array $oneOf): self
    {
        $this->initialized['oneOf'] = true;
        $this->oneOf                = $oneOf;

        return $this;
    }

    /**
     * @return Schema[]|Reference[]|null
     */
    public function getAnyOf(): ?array
    {
        return $this->anyOf;
    }

    /**
     * @param Schema[]|Reference[]|null $anyOf
     */
    public function setAnyOf(?array $anyOf): self
    {
        $this->initialized['anyOf'] = true;
        $this->anyOf                = $anyOf;

        return $this;
    }

    /**
     * @return Schema|Reference|null
     */
    public function getItems(): self|Reference|\LongTermSupport\OpenApiGenerator\Component\OpenApiRuntime\Reference|null
    {
        return $this->items;
    }

    /**
     * @param Schema|Reference|null $items
     */
    public function setItems(self|Reference|\LongTermSupport\OpenApiGenerator\Component\OpenApiRuntime\Reference|null $items): self
    {
        $this->initialized['items'] = true;
        $this->items                = $items;

        return $this;
    }

    /**
     * @return array<string, Schema|Reference>|null
     */
    public function getProperties(): ?array
    {
        return $this->properties;
    }

    /**
     * @param array<string, Schema|Reference>|null $properties
     */
    public function setProperties(?iterable $properties): self
    {
        $this->initialized['properties'] = true;
        $this->properties                = $properties instanceof ArrayObject ? $properties->getArrayCopy() : $properties;

        return $this;
    }

    /**
     * @return Schema|Reference|bool|null
     */
    public function getAdditionalProperties(): self|Reference|\LongTermSupport\OpenApiGenerator\Component\OpenApiRuntime\Reference|bool|null
    {
        return $this->additionalProperties;
    }

    /**
     * @param Schema|Reference|bool|null $additionalProperties
     */
    public function setAdditionalProperties(self|Reference|\LongTermSupport\OpenApiGenerator\Component\OpenApiRuntime\Reference|bool|null $additionalProperties): self
    {
        $this->initialized['additionalProperties'] = true;
        $this->additionalProperties                = $additionalProperties;

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

    public function getFormat(): ?string
    {
        return $this->format;
    }

    public function setFormat(?string $format): self
    {
        $this->initialized['format'] = true;
        $this->format                = $format;

        return $this;
    }

    /** @return string|int|float|bool|array<mixed>|null */
    public function getDefault(): string|int|float|bool|array|null
    {
        return $this->default;
    }

    /** @param string|int|float|bool|array<mixed>|null $default */
    public function setDefault(string|int|float|bool|array|null $default): self
    {
        $this->initialized['default'] = true;
        $this->default                = $default;

        return $this;
    }

    public function getNullable(): ?bool
    {
        return $this->nullable;
    }

    public function setNullable(?bool $nullable): self
    {
        $this->initialized['nullable'] = true;
        $this->nullable                = $nullable;

        return $this;
    }

    public function getDiscriminator(): ?Discriminator
    {
        return $this->discriminator;
    }

    public function setDiscriminator(?Discriminator $discriminator): self
    {
        $this->initialized['discriminator'] = true;
        $this->discriminator                = $discriminator;

        return $this;
    }

    public function getReadOnly(): ?bool
    {
        return $this->readOnly;
    }

    public function setReadOnly(?bool $readOnly): self
    {
        $this->initialized['readOnly'] = true;
        $this->readOnly                = $readOnly;

        return $this;
    }

    public function getWriteOnly(): ?bool
    {
        return $this->writeOnly;
    }

    public function setWriteOnly(?bool $writeOnly): self
    {
        $this->initialized['writeOnly'] = true;
        $this->writeOnly                = $writeOnly;

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

    public function getXml(): ?XML
    {
        return $this->xml;
    }

    public function setXml(?XML $xml): self
    {
        $this->initialized['xml'] = true;
        $this->xml                = $xml;

        return $this;
    }
}
