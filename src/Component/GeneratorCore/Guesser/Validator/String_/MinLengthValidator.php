<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Validator\String_;

use LogicException;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Guess\ClassGuess;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Guess\Property;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Validator\ObjectCheckTrait;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Validator\ValidatorGuess;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Validator\ValidatorInterface;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\JsonSchema\Model\JsonSchema;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class MinLengthValidator implements ValidatorInterface
{
    use ObjectCheckTrait;

    public function supports(mixed $object): bool
    {
        return $object instanceof JsonSchema && ((\is_array($object->getType()) ? \in_array('string', $object->getType(), true) : 'string' === $object->getType()) || null === $object->getType()) && null !== $object->getMinLength() && $object->getMinLength() > 0;
    }

    public function guess(mixed $object, string $name, ClassGuess|Property $guess): void
    {
        if (!$object instanceof JsonSchema) {
            throw new LogicException('Expected JsonSchema, got ' . get_debug_type($object));
        }

        $guess->addValidatorGuess(new ValidatorGuess(Length::class, [
            'min'        => $object->getMinLength(),
            'minMessage' => 'This value is too short. It should have {{ limit }} characters or more.',
        ]));
        if ($object->getMinLength() > 0) {
            $nullable = \is_array($object->getType()) ? \in_array('null', $object->getType(), true) : 'null' === $object->getType();

            $options = [];
            if ($nullable) {
                // Using an integer as a replacement boolean value is most likely to break as soon as
                // \Symfony\Component\Validator\Constraints\NotBlank::$allowNull is strongly typed.
                // Currently we can not use 'bool' here, because \LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Generator\ValidatorGenerator::generateConstraint()
                // does not handle them. This seems to be an issue with nikic/php-parser not being able to provide support
                // for it.
                $options = ['allowNull' => 1];
            }

            $guess->addValidatorGuess(new ValidatorGuess(NotBlank::class, $options));
        }
    }
}
