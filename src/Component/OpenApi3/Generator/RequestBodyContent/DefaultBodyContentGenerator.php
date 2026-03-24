<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApi3\Generator\RequestBodyContent;

use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Generator\Context\Context;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\MediaType;
use PhpParser\Node\Expr;
use PhpParser\Node\Scalar;
use PhpParser\Node\Stmt;

class DefaultBodyContentGenerator extends AbstractBodyContentGenerator
{
    /**
     * @return array<mixed>
     */
    public function getSerializeStatements(MediaType $content, string $contentType, string $reference, Context $context): array
    {
        return [new Stmt\Return_(new Expr\Array_([
            new Expr\ArrayItem(new Expr\Array_([
                new Expr\ArrayItem(
                    new Expr\Array_([new Expr\ArrayItem(new Scalar\String_($contentType))]),
                    new Scalar\String_('Content-Type')
                ),
            ])),
            new Expr\ArrayItem(new Expr\PropertyFetch(new Expr\Variable('this'), 'body')),
        ]))];
    }
}
