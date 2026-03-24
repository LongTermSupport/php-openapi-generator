<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Generator\Endpoint;

use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Guesser\Guess\OperationGuess;
use PhpParser\Modifiers;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar;
use PhpParser\Node\Stmt;

trait GetGetMethodTrait
{
    public function getGetMethod(OperationGuess $operation): Stmt\ClassMethod
    {
        return new Stmt\ClassMethod('getMethod', [
            'flags'      => Modifiers::PUBLIC,
            'stmts'      => [
                new Stmt\Return_(new Scalar\String_($operation->getMethod())),
            ],
            'returnType' => new Name('string'),
        ]);
    }
}
