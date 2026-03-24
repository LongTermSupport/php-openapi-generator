<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Validator\Array_;

use LogicException;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Guess\ClassGuess;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Guess\Property;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Validator\ObjectCheckTrait;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Validator\ValidatorGuess;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Validator\ValidatorInterface;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\JsonSchema\Model\JsonSchema;
use Symfony\Component\Validator\Constraints\Count;

class MinItemsValidator implements ValidatorInterface
{
    use ObjectCheckTrait;

    public function supports(mixed $object): bool
    {
        return $object instanceof JsonSchema && (\is_array($object->getType()) ? \in_array('array', $object->getType(), true) : 'array' === $object->getType()) && null !== $object->getMinItems();
    }

    public function guess(mixed $object, string $name, ClassGuess|Property $guess): void
    {
        if (!$object instanceof JsonSchema) {
            throw new LogicException('Expected JsonSchema, got ' . get_debug_type($object));
        }

        $guess->addValidatorGuess(new ValidatorGuess(Count::class, [
            'min'        => $object->getMinItems(),
            'minMessage' => 'This array has not enough values. It should have {{ limit }} values or more.',
        ]));
    }
}
