<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Validator;

use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\JsonSchema\Model\JsonSchema;

trait ObjectCheckTrait
{
    public function checkObject(object $object): bool
    {
        return $object instanceof JsonSchema;
    }
}
