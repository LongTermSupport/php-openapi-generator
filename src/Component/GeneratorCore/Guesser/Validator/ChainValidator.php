<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Validator;

use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Guess\ClassGuess;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Guess\Property;

class ChainValidator implements ValidatorInterface
{
    /** @var array<ValidatorInterface> */
    private array $validators = [];

    public function addValidator(ValidatorInterface $validator): void
    {
        $this->validators[] = $validator;
    }

    public function supports(mixed $object): bool
    {
        return false;
    }

    public function guess(mixed $object, string $name, ClassGuess|Property $guess): void
    {
        foreach ($this->validators as $validator) {
            if ($validator->supports($object)) {
                $validator->guess($object, $name, $guess);
            }
        }
    }
}
