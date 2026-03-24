<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Generator\Endpoint;

use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Guesser\Guess\OperationGuess;
use PhpParser\Comment\Doc;
use PhpParser\Modifiers;
use PhpParser\Node\Expr;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar;
use PhpParser\Node\Stmt;

trait GetAuthenticationScopesTrait
{
    public function getAuthenticationScopesMethod(OperationGuess $operation): Stmt\ClassMethod
    {
        $securityScopes = [];
        foreach ($operation->getSecurityScopes() as $scope) {
            $securityScopes[] = new Expr\ArrayItem(new Scalar\String_($scope));
        }

        return new Stmt\ClassMethod('getAuthenticationScopes', [
            'flags'      => Modifiers::PUBLIC,
            'returnType' => new Name('array'),
            'stmts'      => [new Stmt\Return_(new Expr\Array_($securityScopes))],
        ], [
            'comments' => [new Doc("/**\n * @return list<string>\n */")],
        ]);
    }
}
