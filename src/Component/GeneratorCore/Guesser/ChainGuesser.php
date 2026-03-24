<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser;

use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Guess\Type;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Registry\Registry;

class ChainGuesser implements TypeGuesserInterface, PropertiesGuesserInterface, ClassGuesserInterface
{
    /** @var array<GuesserInterface> */
    private array $guessers = [];

    public function addGuesser(GuesserInterface $guesser): void
    {
        if ($guesser instanceof ChainGuesserAwareInterface) {
            $guesser->setChainGuesser($this);
        }

        $this->guessers[] = $guesser;
    }

    public function guessClass(mixed $object, string $name, string $reference, Registry $registry): void
    {
        foreach ($this->guessers as $guesser) {
            if (!$guesser instanceof ClassGuesserInterface) {
                continue;
            }

            if ($guesser->supportObject($object)) {
                $guesser->guessClass($object, $name, $reference, $registry);
            }
        }
    }

    public function guessType(mixed $object, string $name, string $reference, Registry $registry): Type
    {
        foreach ($this->guessers as $guesser) {
            if (!$guesser instanceof TypeGuesserInterface) {
                continue;
            }

            if ($guesser->supportObject($object)) {
                return $guesser->guessType($object, $name, $reference, $registry);
            }
        }

        return new Type(\is_object($object) ? $object : null, 'mixed');
    }

    public function guessProperties(mixed $object, string $name, string $reference, Registry $registry): array
    {
        $properties = [];

        foreach ($this->guessers as $guesser) {
            if (!$guesser instanceof PropertiesGuesserInterface) {
                continue;
            }

            if ($guesser->supportObject($object)) {
                $properties = array_merge($properties, $guesser->guessProperties($object, $name, $reference, $registry));
            }
        }

        return $properties;
    }
}
