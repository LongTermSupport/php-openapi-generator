<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\GeneratorCore\JsonSchema\Model;

class JsonSchema implements SchemaInterface
{
    /**
     * @var array<string, bool>
     */
    protected $initialized = [];

    /**
     * @var array<string, JsonSchema|bool>|null
     */
    protected $definitions;

    /**
     * @var array<string, JsonSchema|bool|array<string>>|null
     */
    protected $dependencies;

    protected JsonSchema|bool|null $additionalItems = null;

    protected JsonSchema|bool|null $unevaluatedItems = null;

    /** @var JsonSchema|bool|array<JsonSchema>|array<bool>|null */
    protected JsonSchema|bool|array|null $items = null;

    protected JsonSchema|bool|null $contains = null;

    protected JsonSchema|bool|null $additionalProperties = null;

    /**
     * @var array<string, JsonSchema|bool>|null
     */
    protected $unevaluatedProperties;

    /**
     * @var array<string, JsonSchema|bool|\LongTermSupport\OpenApiGenerator\Component\OpenApiRuntime\Reference>|null
     */
    protected $properties;

    /**
     * @var array<string, JsonSchema|bool>|null
     */
    protected $patternProperties;

    /**
     * @var array<string, JsonSchema|bool>|null
     */
    protected $dependentSchemas;

    protected JsonSchema|bool|null $propertyNames = null;

    protected JsonSchema|bool|null $if = null;

    protected JsonSchema|bool|null $then = null;

    protected JsonSchema|bool|null $else = null;

    /**
     * @var array<JsonSchema>|array<int, bool>|null
     */
    protected $allOf;

    /**
     * @var array<JsonSchema>|array<int, bool>|null
     */
    protected $anyOf;

    /**
     * @var array<JsonSchema>|array<int, bool>|null
     */
    protected $oneOf;

    protected JsonSchema|bool|null $not = null;

    protected ?string $contentMediaType = null;

    protected ?string $contentEncoding = null;

    protected JsonSchema|bool|null $contentSchema = null;

    protected ?string $dollarId = null;

    protected ?string $dollarSchema = null;

    protected ?string $dollarAnchor = null;

    protected ?string $dollarRef = null;

    protected ?string $dollarRecursiveRef = null;

    protected ?bool $dollarRecursiveAnchor = false;

    /**
     * @var array<string, bool>|null
     */
    protected $dollarVocabulary;

    protected ?string $dollarComment = null;

    /**
     * @var array<string, JsonSchema|bool>|null
     */
    protected $dollarDefs;

    protected ?string $format = null;

    protected ?string $title = null;

    protected ?string $description = null;

    protected mixed $default;

    protected ?bool $deprecated = false;

    protected ?bool $readOnly = false;

    protected ?bool $writeOnly = false;

    /**
     * @var array<mixed>|null
     */
    protected $examples;

    protected ?float $multipleOf = null;

    protected ?float $maximum = null;

    protected ?float $exclusiveMaximum = null;

    protected ?float $minimum = null;

    protected ?float $exclusiveMinimum = null;

    protected ?int $maxLength = null;

    protected ?int $minLength = null;

    protected ?string $pattern = null;

    protected ?int $maxItems = null;

    protected ?int $minItems = null;

    protected ?bool $uniqueItems = false;

    protected ?int $maxContains = null;

    protected ?int $minContains = null;

    protected ?int $maxProperties = null;

    protected ?int $minProperties = null;

    /**
     * @var array<string>|null
     */
    protected $required = [];

    /**
     * @var array<string, array<string>>|null
     */
    protected $dependentRequired;

    protected ?string $const = null;

    /**
     * @var array<string>|null
     */
    protected $enum;

    protected mixed $type;

    public function isInitialized(string $property): bool
    {
        return \array_key_exists($property, $this->initialized);
    }

    /**
     * @return array<string, JsonSchema|bool>|null
     */
    public function getDefinitions(): ?iterable
    {
        return $this->definitions;
    }

    /**
     * @param array<string, JsonSchema|bool>|null $definitions
     */
    public function setDefinitions(?iterable $definitions): self
    {
        $this->initialized['definitions'] = true;
        $this->definitions                = $definitions;

        return $this;
    }

    /**
     * @return array<string, JsonSchema|bool|array<string>>|null
     */
    public function getDependencies(): ?iterable
    {
        return $this->dependencies;
    }

    /**
     * @param array<string, JsonSchema|bool|array<string>>|null $dependencies
     */
    public function setDependencies(?iterable $dependencies): self
    {
        $this->initialized['dependencies'] = true;
        $this->dependencies                = $dependencies;

        return $this;
    }

    public function getAdditionalItems(): self|bool|null
    {
        return $this->additionalItems;
    }

    public function setAdditionalItems(self|bool|null $additionalItems): self
    {
        $this->initialized['additionalItems'] = true;
        $this->additionalItems                = $additionalItems;

        return $this;
    }

    public function getUnevaluatedItems(): self|bool|null
    {
        return $this->unevaluatedItems;
    }

    public function setUnevaluatedItems(self|bool|null $unevaluatedItems): self
    {
        $this->initialized['unevaluatedItems'] = true;
        $this->unevaluatedItems                = $unevaluatedItems;

        return $this;
    }

    /**
     * @return JsonSchema|bool|array<JsonSchema>|array<int, bool>|null
     */
    public function getItems(): self|bool|array|null
    {
        return $this->items;
    }

    /**
     * @param JsonSchema|bool|array<JsonSchema>|array<int, bool>|null $items
     */
    public function setItems(self|bool|array|null $items): self
    {
        $this->initialized['items'] = true;
        $this->items                = $items;

        return $this;
    }

    public function getContains(): self|bool|null
    {
        return $this->contains;
    }

    public function setContains(self|bool|null $contains): self
    {
        $this->initialized['contains'] = true;
        $this->contains                = $contains;

        return $this;
    }

    public function getAdditionalProperties(): self|bool|null
    {
        return $this->additionalProperties;
    }

    public function setAdditionalProperties(self|bool|null $additionalProperties): self
    {
        $this->initialized['additionalProperties'] = true;
        $this->additionalProperties                = $additionalProperties;

        return $this;
    }

    /**
     * @return array<string, JsonSchema|bool>|null
     */
    public function getUnevaluatedProperties(): ?iterable
    {
        return $this->unevaluatedProperties;
    }

    /**
     * @param array<string, JsonSchema|bool>|null $unevaluatedProperties
     */
    public function setUnevaluatedProperties(?iterable $unevaluatedProperties): self
    {
        $this->initialized['unevaluatedProperties'] = true;
        $this->unevaluatedProperties                = $unevaluatedProperties;

        return $this;
    }

    /**
     * @return array<string, JsonSchema|bool|\LongTermSupport\OpenApiGenerator\Component\OpenApiRuntime\Reference>|null
     */
    public function getProperties(): ?array
    {
        return $this->properties;
    }

    /**
     * @param array<string, JsonSchema|bool|\LongTermSupport\OpenApiGenerator\Component\OpenApiRuntime\Reference>|null $properties
     */
    public function setProperties(?iterable $properties): self
    {
        $this->initialized['properties'] = true;
        $this->properties                = $properties;

        return $this;
    }

    /**
     * @return array<string, JsonSchema|bool>|null
     */
    public function getPatternProperties(): ?iterable
    {
        return $this->patternProperties;
    }

    /**
     * @param array<string, JsonSchema|bool>|null $patternProperties
     */
    public function setPatternProperties(?iterable $patternProperties): self
    {
        $this->initialized['patternProperties'] = true;
        $this->patternProperties                = $patternProperties;

        return $this;
    }

    /**
     * @return array<string, JsonSchema|bool>|null
     */
    public function getDependentSchemas(): ?iterable
    {
        return $this->dependentSchemas;
    }

    /**
     * @param array<string, JsonSchema|bool>|null $dependentSchemas
     */
    public function setDependentSchemas(?iterable $dependentSchemas): self
    {
        $this->initialized['dependentSchemas'] = true;
        $this->dependentSchemas                = $dependentSchemas;

        return $this;
    }

    public function getPropertyNames(): self|bool|null
    {
        return $this->propertyNames;
    }

    public function setPropertyNames(self|bool|null $propertyNames): self
    {
        $this->initialized['propertyNames'] = true;
        $this->propertyNames                = $propertyNames;

        return $this;
    }

    public function getIf(): self|bool|null
    {
        return $this->if;
    }

    public function setIf(self|bool|null $if): self
    {
        $this->initialized['if'] = true;
        $this->if                = $if;

        return $this;
    }

    public function getThen(): self|bool|null
    {
        return $this->then;
    }

    public function setThen(self|bool|null $then): self
    {
        $this->initialized['then'] = true;
        $this->then                = $then;

        return $this;
    }

    public function getElse(): self|bool|null
    {
        return $this->else;
    }

    public function setElse(self|bool|null $else): self
    {
        $this->initialized['else'] = true;
        $this->else                = $else;

        return $this;
    }

    /**
     * @return array<JsonSchema>|array<int, bool>|null
     */
    public function getAllOf(): ?array
    {
        return $this->allOf;
    }

    /**
     * @param array<JsonSchema>|array<int, bool>|null $allOf
     */
    public function setAllOf(?array $allOf): self
    {
        $this->initialized['allOf'] = true;
        $this->allOf                = $allOf;

        return $this;
    }

    /**
     * @return array<JsonSchema>|array<int, bool>|null
     */
    public function getAnyOf(): ?array
    {
        return $this->anyOf;
    }

    /**
     * @param array<JsonSchema>|array<int, bool>|null $anyOf
     */
    public function setAnyOf(?array $anyOf): self
    {
        $this->initialized['anyOf'] = true;
        $this->anyOf                = $anyOf;

        return $this;
    }

    /**
     * @return array<JsonSchema>|array<int, bool>|null
     */
    public function getOneOf(): ?array
    {
        return $this->oneOf;
    }

    /**
     * @param array<JsonSchema>|array<int, bool>|null $oneOf
     */
    public function setOneOf(?array $oneOf): self
    {
        $this->initialized['oneOf'] = true;
        $this->oneOf                = $oneOf;

        return $this;
    }

    public function getNot(): self|bool|null
    {
        return $this->not;
    }

    public function setNot(self|bool|null $not): self
    {
        $this->initialized['not'] = true;
        $this->not                = $not;

        return $this;
    }

    public function getContentMediaType(): ?string
    {
        return $this->contentMediaType;
    }

    public function setContentMediaType(?string $contentMediaType): self
    {
        $this->initialized['contentMediaType'] = true;
        $this->contentMediaType                = $contentMediaType;

        return $this;
    }

    public function getContentEncoding(): ?string
    {
        return $this->contentEncoding;
    }

    public function setContentEncoding(?string $contentEncoding): self
    {
        $this->initialized['contentEncoding'] = true;
        $this->contentEncoding                = $contentEncoding;

        return $this;
    }

    public function getContentSchema(): self|bool|null
    {
        return $this->contentSchema;
    }

    public function setContentSchema(self|bool|null $contentSchema): self
    {
        $this->initialized['contentSchema'] = true;
        $this->contentSchema                = $contentSchema;

        return $this;
    }

    public function getDollarId(): ?string
    {
        return $this->dollarId;
    }

    public function setDollarId(?string $dollarId): self
    {
        $this->initialized['dollarId'] = true;
        $this->dollarId                = $dollarId;

        return $this;
    }

    public function getDollarSchema(): ?string
    {
        return $this->dollarSchema;
    }

    public function setDollarSchema(?string $dollarSchema): self
    {
        $this->initialized['dollarSchema'] = true;
        $this->dollarSchema                = $dollarSchema;

        return $this;
    }

    public function getDollarAnchor(): ?string
    {
        return $this->dollarAnchor;
    }

    public function setDollarAnchor(?string $dollarAnchor): self
    {
        $this->initialized['dollarAnchor'] = true;
        $this->dollarAnchor                = $dollarAnchor;

        return $this;
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

    public function getDollarRecursiveRef(): ?string
    {
        return $this->dollarRecursiveRef;
    }

    public function setDollarRecursiveRef(?string $dollarRecursiveRef): self
    {
        $this->initialized['dollarRecursiveRef'] = true;
        $this->dollarRecursiveRef                = $dollarRecursiveRef;

        return $this;
    }

    public function getDollarRecursiveAnchor(): ?bool
    {
        return $this->dollarRecursiveAnchor;
    }

    public function setDollarRecursiveAnchor(?bool $dollarRecursiveAnchor): self
    {
        $this->initialized['dollarRecursiveAnchor'] = true;
        $this->dollarRecursiveAnchor                = $dollarRecursiveAnchor;

        return $this;
    }

    /**
     * @return array<string, bool>|null
     */
    public function getDollarVocabulary(): ?iterable
    {
        return $this->dollarVocabulary;
    }

    /**
     * @param array<string, bool>|null $dollarVocabulary
     */
    public function setDollarVocabulary(?iterable $dollarVocabulary): self
    {
        $this->initialized['dollarVocabulary'] = true;
        $this->dollarVocabulary                = $dollarVocabulary;

        return $this;
    }

    public function getDollarComment(): ?string
    {
        return $this->dollarComment;
    }

    public function setDollarComment(?string $dollarComment): self
    {
        $this->initialized['dollarComment'] = true;
        $this->dollarComment                = $dollarComment;

        return $this;
    }

    /**
     * @return array<string, JsonSchema|bool>|null
     */
    public function getDollarDefs(): ?iterable
    {
        return $this->dollarDefs;
    }

    /**
     * @param array<string, JsonSchema|bool>|null $dollarDefs
     */
    public function setDollarDefs(?iterable $dollarDefs): self
    {
        $this->initialized['dollarDefs'] = true;
        $this->dollarDefs                = $dollarDefs;

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

    public function getDefault(): mixed
    {
        return $this->default;
    }

    public function setDefault(mixed $default): self
    {
        $this->initialized['default'] = true;
        $this->default                = $default;

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

    /**
     * @return array<mixed>|null
     */
    public function getExamples(): ?array
    {
        return $this->examples;
    }

    /**
     * @param array<mixed>|null $examples
     */
    public function setExamples(?array $examples): self
    {
        $this->initialized['examples'] = true;
        $this->examples                = $examples;

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

    public function getExclusiveMaximum(): ?float
    {
        return $this->exclusiveMaximum;
    }

    public function setExclusiveMaximum(?float $exclusiveMaximum): self
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

    public function getExclusiveMinimum(): ?float
    {
        return $this->exclusiveMinimum;
    }

    public function setExclusiveMinimum(?float $exclusiveMinimum): self
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

    public function getMaxContains(): ?int
    {
        return $this->maxContains;
    }

    public function setMaxContains(?int $maxContains): self
    {
        $this->initialized['maxContains'] = true;
        $this->maxContains                = $maxContains;

        return $this;
    }

    public function getMinContains(): ?int
    {
        return $this->minContains;
    }

    public function setMinContains(?int $minContains): self
    {
        $this->initialized['minContains'] = true;
        $this->minContains                = $minContains;

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
     * @return array<string>|null
     */
    public function getRequired(): ?array
    {
        return $this->required;
    }

    /**
     * @param array<string>|null $required
     */
    public function setRequired(?array $required): self
    {
        $this->initialized['required'] = true;
        $this->required                = $required;

        return $this;
    }

    /**
     * @return array<string, array<string>>|null
     */
    public function getDependentRequired(): ?iterable
    {
        return $this->dependentRequired;
    }

    /**
     * @param array<string, array<string>>|null $dependentRequired
     */
    public function setDependentRequired(?iterable $dependentRequired): self
    {
        $this->initialized['dependentRequired'] = true;
        $this->dependentRequired                = $dependentRequired;

        return $this;
    }

    public function getConst(): ?string
    {
        return $this->const;
    }

    public function setConst(?string $const): self
    {
        $this->initialized['const'] = true;
        $this->const                = $const;

        return $this;
    }

    /**
     * @return array<string>|null
     */
    public function getEnum(): ?array
    {
        return $this->enum;
    }

    /**
     * @param array<string>|null $enum
     */
    public function setEnum(?array $enum): self
    {
        $this->initialized['enum'] = true;
        $this->enum                = $enum;

        return $this;
    }

    /**
     * @return mixed|array<mixed>
     */
    public function getType(): mixed
    {
        return $this->type;
    }

    /**
     * @param mixed|array<mixed> $type
     */
    public function setType($type): self
    {
        $this->initialized['type'] = true;
        $this->type                = $type;

        return $this;
    }
}
