<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApi3\Generator;

use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Generator\Context\Context;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\MediaType;
use PhpParser\Node;

interface RequestBodyContentGeneratorInterface
{
    /** @return array<mixed> */
    public function getTypes(MediaType $content, string $reference, Context $context): array;

    public function getTypeCondition(MediaType $content, string $reference, Context $context): Node;

    /** @return array<mixed> */
    public function getSerializeStatements(MediaType $content, string $contentType, string $reference, Context $context): array;
}
