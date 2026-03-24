<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Guesser\OpenApiSchema;

use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\JsonSchema\ArrayGuesser as BaseArrayGuesser;

class ArrayGuesser extends BaseArrayGuesser
{
    use SchemaClassTrait;

    public function supportObject(mixed $object): bool
    {
        $class = $this->getSchemaClass();

        // PHPStan cannot narrow from a variable class-string instanceof; use method_exists guard
        return ($object instanceof $class) && method_exists($object, 'getType') && 'array' === $object->getType();
    }
}
