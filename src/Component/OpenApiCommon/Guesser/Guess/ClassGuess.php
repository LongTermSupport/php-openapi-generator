<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Guesser\Guess;

use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Guess\ClassGuess as BaseClassGuess;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Guess\Property;

class ClassGuess extends BaseClassGuess
{
    private ?ParentClass $parentClass = null;

    public function getParentClass(): ?ParentClass
    {
        return $this->parentClass;
    }

    public function setParentClass(?ParentClass $parentClass): void
    {
        $this->parentClass = $parentClass;
    }

    public function willExtendArrayObject(): bool
    {
        return [] !== $this->getExtensionsType() && !$this->parentClass instanceof ParentClass;
    }

    public function getLocalProperties(): array
    {
        if (!$this->parentClass instanceof ParentClass) {
            return $this->getProperties();
        }

        $parentClass = $this->parentClass;

        return array_filter( // return only those properties which not present in parent class
            $this->getProperties(),
            static fn (Property $property): bool => !$parentClass->getProperty($property->getName()) instanceof Property
        );
    }
}
