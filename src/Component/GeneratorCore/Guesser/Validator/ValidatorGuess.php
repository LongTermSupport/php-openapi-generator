<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Validator;

class ValidatorGuess
{
    /**
     * @param array<string, mixed> $arguments
     */
    public function __construct(
        private string $constraintClass,
        private readonly array $arguments = [],
        private readonly ?string $subProperty = null,
        private readonly ?string $classReference = null,
    ) {
    }

    public function getClassReference(): ?string
    {
        return $this->classReference;
    }

    public function getConstraintClass(): string
    {
        return $this->constraintClass;
    }

    public function setConstraintClass(string $constraintClass): void
    {
        $this->constraintClass = $constraintClass;
    }

    /** @return array<string, mixed> */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    public function getSubProperty(): ?string
    {
        return $this->subProperty;
    }
}
