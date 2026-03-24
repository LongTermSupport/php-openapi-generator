<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Validator\Any;

use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Guess\ClassGuess;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Guess\Property;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Validator\ObjectCheckTrait;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Validator\ValidatorGuess;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Validator\ValidatorInterface;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\JsonSchema\Model\JsonSchema;
use Symfony\Component\Validator\Constraints\NotNull;

class NotNullValidator implements ValidatorInterface
{
    use ObjectCheckTrait;

    public function supports(mixed $object): bool
    {
        if (!\is_object($object)) {
            return false;
        }

        if ($object instanceof JsonSchema) {
            return \is_array($object->getType()) ? !\in_array('null', $object->getType(), true) : 'null' !== $object->getType();
        }

        return false;
    }

    public function guess(mixed $object, string $name, ClassGuess|Property $guess): void
    {
        $guess->addValidatorGuess(new ValidatorGuess(NotNull::class, [
            'message' => 'This value should not be null.',
        ]));
    }
}
