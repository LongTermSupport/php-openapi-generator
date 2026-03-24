<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Guess;

use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Validator\ValidatorGuess;

trait ValidatorGuessTrait
{
    /** @var array<ValidatorGuess> */
    private array $validators = [];

    public function addValidatorGuess(ValidatorGuess $validatorGuess): void
    {
        $this->validators[] = $validatorGuess;
    }

    /** @return array<ValidatorGuess> */
    public function getValidatorGuesses(): array
    {
        return $this->validators;
    }
}
