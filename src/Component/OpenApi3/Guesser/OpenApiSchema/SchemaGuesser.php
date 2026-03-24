<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApi3\Guesser\OpenApiSchema;

use LogicException;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Guess\ClassGuess as BaseClassGuess;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\JsonSchema\ObjectGuesser;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Discriminator;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Schema;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Guesser\Guess\ClassGuess;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Guesser\Guess\ParentClass;
use Override;

class SchemaGuesser extends ObjectGuesser
{
    #[Override]
    public function supportObject(mixed $object): bool
    {
        return ($object instanceof Schema) && ('object' === $object->getType() || null === $object->getType()) && null !== $object->getProperties();
    }

    #[Override]
    protected function isPropertyNullable(mixed $property): bool
    {
        if (!$property instanceof Schema) {
            throw new LogicException('Expected Schema, got ' . get_debug_type($property));
        }

        if (parent::isPropertyNullable($property)) {
            return true;
        }

        return $property->getNullable() ?? false;
    }

    /**
     * @param array<string, mixed> $extensions
     */
    #[Override]
    protected function createClassGuess(mixed $object, string $reference, string $name, array $extensions): BaseClassGuess
    {
        if (!$object instanceof Schema) {
            throw new LogicException('Expected Schema, got ' . get_debug_type($object));
        }

        $classGuess = new ClassGuess($object, $reference, $this->naming->getClassName($name), $extensions, $object->getDeprecated() ?? false);

        $discriminator = $object->getDiscriminator();
        if ($discriminator instanceof Discriminator
            && is_countable($discriminator->getMapping()) && \count($discriminator->getMapping()) > 0) {
            $discriminatorPropertyName = $discriminator->getPropertyName() ?? '';
            $classGuess                = new ParentClass($classGuess, $discriminatorPropertyName);

            foreach ($discriminator->getMapping() as $discriminatorValue => $entryReference) {
                $subClassName = str_replace('#/components/schemas/', '', (string)$entryReference);
                $childRef     = \Safe\preg_replace(
                    '#components/schemas\/.+$#',
                    \sprintf('components/schemas/%s', $subClassName),
                    $reference
                );
                if (!\is_string($childRef)) {
                    throw new LogicException('Expected string, got ' . get_debug_type($childRef));
                }

                $classGuess->addChildEntry($subClassName, $childRef, (string)$discriminatorValue);
            }

            return $classGuess;
        }

        if ($object->getDiscriminator() instanceof Discriminator
            && \is_array($object->getEnum()) && [] !== $object->getEnum()) {
            $discriminatorPropertyName = $object->getDiscriminator()->getPropertyName() ?? '';
            $classGuess                = new ParentClass($classGuess, $discriminatorPropertyName);

            foreach ($object->getEnum() as $subClassName) {
                if (!\is_string($subClassName)) {
                    throw new LogicException('Expected string, got ' . get_debug_type($subClassName));
                }

                $childRef = \Safe\preg_replace(
                    '#components/schemas\/.+$#',
                    \sprintf('components/schemas/%s', $subClassName),
                    $reference
                );
                if (!\is_string($childRef)) {
                    throw new LogicException('Expected string, got ' . get_debug_type($childRef));
                }

                $classGuess->addChildEntry($subClassName, $childRef);
            }

            return $classGuess;
        }

        return $classGuess;
    }

    /**
     * @return array<string, mixed>
     */
    #[Override]
    protected function buildPatternExtensions(mixed $object, string $reference): array
    {
        // OpenAPI 3.x Schema patternProperties are not represented via getPatternProperties()
        // on the Schema model — extensions are handled via additionalProperties only.
        return [];
    }

    #[Override]
    protected function getSchemaClass(): string
    {
        return Schema::class;
    }
}
