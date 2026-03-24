<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser;

use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Registry\Registry;

interface ClassGuesserInterface
{
    /**
     * Guess model.
     *
     * This guesser should create a Model and the associated File
     * The file must be inject into the context
     *
     * @internal
     */
    public function guessClass(mixed $object, string $name, string $reference, Registry $registry): void;
}
