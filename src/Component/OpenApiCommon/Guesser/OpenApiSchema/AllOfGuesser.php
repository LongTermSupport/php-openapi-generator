<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Guesser\OpenApiSchema;

use LogicException;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Generator\Naming;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Guess\ClassGuess as BaseClassGuess;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\JsonSchema\AllOfGuesser as BaseAllOfGuesser;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Guesser\Guess\ClassGuess;
use LongTermSupport\OpenApiGenerator\Component\OpenApiRuntime\Reference;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class AllOfGuesser extends BaseAllOfGuesser
{
    public function __construct(
        DenormalizerInterface $denormalizer,
        Naming $naming,
        private readonly string $schemaClass,
    ) {
        parent::__construct($denormalizer, $naming);
    }

    /** @param array<string, mixed> $extensions */
    protected function createClassGuess(object $object, string $reference, string $name, array $extensions): BaseClassGuess
    {
        return new ClassGuess($object, $reference, $this->naming->getClassName($name), $extensions);
    }

    protected function isObjectSchema(mixed $allOf): bool
    {
        if (!\is_object($allOf)) {
            throw new LogicException(\sprintf('Expected object in allOf, got %s', \gettype($allOf)));
        }

        return method_exists($allOf, 'getType') && 'object' === $allOf->getType();
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function getAllOfSchemaProperties(mixed $allOf): ?array
    {
        if (!\is_object($allOf)) {
            throw new LogicException(\sprintf('Expected object in allOf, got %s', \gettype($allOf)));
        }

        // References have no inline properties; they are processed via their own guessClass() call
        if ($allOf instanceof Reference) {
            return null;
        }

        if (!method_exists($allOf, 'getProperties')) {
            throw new LogicException(\sprintf('%s does not have getProperties() method', $allOf::class));
        }

        $props = $allOf->getProperties();

        if (!\is_array($props)) {
            return null;
        }

        /** @var array<string, mixed> $props */
        return $props;
    }

    protected function getSchemaClass(): string
    {
        return $this->schemaClass;
    }
}
