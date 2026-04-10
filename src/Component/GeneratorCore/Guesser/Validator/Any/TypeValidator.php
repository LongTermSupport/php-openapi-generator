<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Validator\Any;

use LogicException;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Guess\ClassGuess;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Guess\Property;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Validator\ObjectCheckTrait;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Validator\ValidatorGuess;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Validator\ValidatorInterface;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\JsonSchema\Model\JsonSchema;
use Symfony\Component\Validator\Constraints\Type;

class TypeValidator implements ValidatorInterface
{
    use ObjectCheckTrait;

    /** @var array<string, string> */
    private const array TYPES_MAPPING = [
        'boolean' => 'bool',
        'number'  => 'float',
    ];

    public function supports(mixed $object): bool
    {
        return $object instanceof JsonSchema && null !== $object->getType() && (\is_string($object->getType()) || \is_array($object->getType()) && null !== $object->getType()[0]);
    }

    public function guess(mixed $object, string $name, ClassGuess|Property $guess): void
    {
        if (!$object instanceof JsonSchema) {
            throw new LogicException('Expected JsonSchema, got ' . get_debug_type($object));
        }

        $rawType      = $object->getType();
        $stringAsList = \is_string($rawType) ? [$rawType] : [];
        $typeList     = \is_array($rawType) ? $rawType : $stringAsList;

        /** @var array<string, int> $types */
        $types = [];
        foreach ($typeList as $t) {
            if (\is_string($t)) {
                $types[$t] = 1;
            }
        }

        if (\array_key_exists('object', $types)) {
            return;
        }

        foreach (self::TYPES_MAPPING as $jsonSchemaType => $phpType) {
            if (\array_key_exists($jsonSchemaType, $types)) {
                unset($types[$jsonSchemaType]);
                $types[$phpType] = 1;
            }
        }

        $guess->addValidatorGuess(new ValidatorGuess(Type::class, [
            'type' => array_keys($types),
        ]));
    }
}
