<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Validator;

use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Guess\ClassGuess;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Guess\Property;

interface ValidatorInterface
{
    public function supports(mixed $object): bool;

    public function guess(mixed $object, string $name, ClassGuess|Property $guess): void;
}
