<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApi3\Generator\Endpoint;

use LongTermSupport\OpenApiGenerator\Component\OpenApi3\Guesser\GuessClass;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Guesser\Guess\OperationGuess;
use PhpParser\Comment\Doc;
use PhpParser\Modifiers;
use PhpParser\Node\Expr;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar;
use PhpParser\Node\Stmt;

trait GetGetExtraHeadersTrait
{
    public function getExtraHeadersMethod(OperationGuess $operation, GuessClass $guessClass): ?Stmt\ClassMethod
    {
        $headers  = [];
        $produces = $this->getContentTypes($operation, $guessClass);

        if (0 === \count($produces)) {
            return null;
        }

        // Add all content types except text/html as default Accept content types.
        $items = [];
        foreach ($produces as $contentType) {
            if ('text/html' === $contentType) {
                continue;
            }

            $items[] = new Expr\ArrayItem(new Scalar\String_($contentType));
        }

        $headers[] = new Expr\ArrayItem(
            new Expr\Array_($items),
            new Scalar\String_('Accept')
        );

        if (\count($produces) <= 1) {
            return new Stmt\ClassMethod('getExtraHeaders', [
                'flags'      => Modifiers::PUBLIC,
                'stmts'      => [new Stmt\Return_(new Expr\Array_($headers))],
                'returnType' => new Name('array'),
            ], [
                'comments' => [new Doc("/**\n * @return array<string, list<string>>\n */")],
            ]);
        }

        $returnDefault = new Stmt\If_(
            new Expr\BinaryOp\Identical(
                new Expr\Array_(),
                new Expr\PropertyFetch(new Expr\Variable('this'), 'accept'),
            ),
            [
                'stmts' => [
                    new Stmt\Return_(new Expr\Array_($headers)),
                ],
            ]
        );

        $returnAccept = new Stmt\Return_(new Expr\Array_([
            new Expr\ArrayItem(
                new Expr\PropertyFetch(new Expr\Variable('this'), 'accept'),
                new Scalar\String_('Accept')
            ),
        ]));

        return new Stmt\ClassMethod('getExtraHeaders', [
            'flags'      => Modifiers::PUBLIC,
            'stmts'      => [$returnDefault, $returnAccept],
            'returnType' => new Name('array'),
        ], [
            'comments' => [new Doc("/**\n * @return array<string, list<string>>\n */")],
        ]);
    }
}
