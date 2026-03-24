<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Guess;

class ClassGuess
{
    use ValidatorGuessTrait;

    /** @var array<Property> */
    private array $properties = [];

    /** @var array<string> */
    private array $required = [];

    /** @var array<Type> */
    private array $extensionsType = [];

    /** @var array<mixed> */
    private array $constraints = [];

    /**
     * @param object       $object           Object link to the generation
     * @param string       $name             Name of the class
     * @param array<mixed> $extensionsObject
     */
    public function __construct(
        private readonly object $object,
        private readonly string $reference,
        private readonly string $name,
        private readonly array $extensionsObject = [],
        private readonly bool $deprecated = false,
    ) {
    }

    public function getObject(): object
    {
        return $this->object;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getReference(): string
    {
        return $this->reference;
    }

    /**
     * @return Property[]
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    /**
     * @return Property[]
     */
    public function getLocalProperties(): array
    {
        return $this->properties;
    }

    public function getProperty(string $name): ?Property
    {
        foreach ($this->properties as $property) {
            if ($name === $property->getName()) {
                return $property;
            }
        }

        return null;
    }

    public function isRequired(string $propertyName): bool
    {
        return \in_array($propertyName, $this->required, true);
    }

    /**
     * @param string[] $required
     */
    public function setRequired(array $required): void
    {
        $this->required = $required;
    }

    /**
     * @param array<Property> $properties
     */
    public function setProperties(array $properties): void
    {
        $this->properties = $properties;
    }

    /**
     * @return Type[]
     */
    public function getExtensionsType(): array
    {
        return $this->extensionsType;
    }

    /**
     * Whether the generated model class will extend ArrayObject.
     *
     * Models with extensions and no custom parent class extend ArrayObject directly.
     * Override in subclasses that support custom parent classes (discriminator pattern).
     */
    public function willExtendArrayObject(): bool
    {
        return [] !== $this->extensionsType;
    }

    /**
     * @param Type[] $extensionsType
     */
    public function setExtensionsType(array $extensionsType): void
    {
        $this->extensionsType = $extensionsType;
    }

    /** @return array<mixed> */
    public function getExtensionsObject(): array
    {
        return $this->extensionsObject;
    }

    /** @return array<mixed> */
    public function getConstraints(): array
    {
        return $this->constraints;
    }

    /** @param array<mixed> $constraints */
    public function setConstraints(array $constraints): void
    {
        $this->constraints = $constraints;
    }

    public function isDeprecated(): bool
    {
        return $this->deprecated;
    }

    public function hasValidatorGuesses(): bool
    {
        if ([] !== $this->getValidatorGuesses()) {
            return true;
        }

        return array_any($this->properties, static fn ($property): bool => \count($property->getValidatorGuesses()) > 0);
    }

    /**
     * @return array<string, array<\LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Validator\ValidatorGuess>>
     */
    public function getPropertyValidatorGuesses(): array
    {
        $validatorGuesses = [];
        foreach ($this->properties as $property) {
            $validatorGuesses[$property->getName()] = $property->getValidatorGuesses();
        }

        return $validatorGuesses;
    }
}
