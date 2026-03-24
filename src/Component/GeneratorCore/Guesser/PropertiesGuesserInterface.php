<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser;

use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Guess\Property;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Registry\Registry;

interface PropertiesGuesserInterface
{
    /**
     * Return all properties guessed.
     *
     * @internal
     *
     * @return Property[]
     */
    public function guessProperties(mixed $object, string $name, string $reference, Registry $registry): array;
}
