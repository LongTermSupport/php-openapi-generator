<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Guess;

use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\JsonSchema\Model\JsonSchema;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Schema;

trait CheckNullableTrait
{
    public function isNullable(mixed $schema): bool
    {
        if ($schema instanceof JsonSchema) {
            return \is_array($schema->getType()) ? \in_array('null', $schema->getType(), true) : 'null' === $schema->getType();
        }

        if ($schema instanceof Schema) {
            return true === $schema->getNullable();
        }

        return false;
    }
}
