<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Guesser\OpenApiSchema;

use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\ReferenceGuesser as BaseReferenceGuesser;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class ReferenceGuesser extends BaseReferenceGuesser
{
    use SchemaClassTrait;

    public function __construct(DenormalizerInterface $denormalizer, string $schemaClass)
    {
        parent::__construct($denormalizer);
        $this->schemaClass = $schemaClass;
    }
}
