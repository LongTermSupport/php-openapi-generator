<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApi3\Guesser;

use LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Parameter;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Guesser\GuessClass as BaseGuessClass;
use LongTermSupport\OpenApiGenerator\Component\OpenApiRuntime\Reference;

class GuessClass extends BaseGuessClass
{
    public function resolveParameter(Reference $parameter): mixed
    {
        $result = $parameter;

        return $parameter->resolve(fn ($value): mixed => $this->denormalizer->denormalize($value, Parameter::class, 'json', [
            'document-origin' => $result->getMergedUri()->withFragment('')->__toString(),
        ]));
    }
}
