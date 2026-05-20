<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Guesser\OpenApiSchema;

use LogicException;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Guess\FileUploadType;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Guess\Type;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\GuesserInterface;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\TypeGuesserInterface;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\JsonSchema\Model\SchemaInterface;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Registry\Registry;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Registry\Schema;

/**
 * Routes `type: string, format: binary` schema properties to FileUploadType so
 * generated multipart bodies expose a typed file-upload value object rather
 * than a raw string.
 *
 * Must be registered BEFORE SimpleTypeGuesser in the chain — SimpleTypeGuesser
 * matches plain `type: string` and would otherwise win first.
 */
class BinaryFormatGuesser implements GuesserInterface, TypeGuesserInterface
{
    public function __construct(private readonly string $schemaClass)
    {
    }

    public function supportObject(mixed $object): bool
    {
        if (!$object instanceof $this->schemaClass) {
            return false;
        }

        if (!$object instanceof SchemaInterface) {
            return false;
        }

        return 'string' === $object->getType() && 'binary' === $object->getFormat();
    }

    public function guessType(mixed $object, string $name, string $reference, Registry $registry): Type
    {
        if (!$object instanceof SchemaInterface) {
            throw new LogicException('Expected SchemaInterface, got ' . get_debug_type($object));
        }

        $schema = $registry->getSchema($reference);
        if (!$schema instanceof Schema) {
            throw new LogicException(\sprintf(
                'Failed to resolve Schema for binary-format reference "%s"; the chain guesser invoked BinaryFormatGuesser without a registered schema for this reference.',
                $reference
            ));
        }

        $runtimeFqcn = \sprintf('\%s\Runtime\Client\FileUpload', $schema->getNamespace());

        return new FileUploadType($object, $runtimeFqcn);
    }
}
