<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Validator\Numeric;

use LogicException;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Guess\ClassGuess;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Guess\Property;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Validator\ObjectCheckTrait;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Validator\ValidatorGuess;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Validator\ValidatorInterface;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\JsonSchema\Model\JsonSchema;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;

class MinimumValidator implements ValidatorInterface
{
    use ObjectCheckTrait;

    public function supports(mixed $object): bool
    {
        return $object instanceof JsonSchema && (\is_array($object->getType()) ? (\in_array('integer', $object->getType(), true) || \in_array('number', $object->getType(), true)) : ('integer' === $object->getType() || 'number' === $object->getType())) && is_numeric($object->getMinimum());
    }

    public function guess(mixed $object, string $name, ClassGuess|Property $guess): void
    {
        if (!$object instanceof JsonSchema) {
            throw new LogicException('Expected JsonSchema, got ' . get_debug_type($object));
        }

        $guess->addValidatorGuess(new ValidatorGuess(GreaterThanOrEqual::class, [
            'value' => $object->getMinimum(),
        ]));
    }
}
