<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser;

use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Guess\Type;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Registry\Registry;

interface TypeGuesserInterface
{
    /**
     * Return all types guessed.
     *
     * @internal
     */
    public function guessType(mixed $object, string $name, string $reference, Registry $registry): Type;
}
