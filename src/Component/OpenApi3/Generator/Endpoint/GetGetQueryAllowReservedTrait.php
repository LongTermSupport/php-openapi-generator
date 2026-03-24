<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApi3\Generator\Endpoint;

use LongTermSupport\OpenApiGenerator\Component\OpenApi3\Generator\EndpointGenerator;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\Guesser\GuessClass;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Parameter;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Guesser\Guess\OperationGuess;
use LongTermSupport\OpenApiGenerator\Component\OpenApiRuntime\Reference;
use PhpParser\Comment\Doc;
use PhpParser\Modifiers;
use PhpParser\Node\Expr;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar;
use PhpParser\Node\Stmt;

trait GetGetQueryAllowReservedTrait
{
    public function getQueryAllowReservedMethod(OperationGuess $operation, string $methodName, GuessClass $guessClass): ?Stmt\ClassMethod
    {
        $queryAllowReservedParameters = [];
        foreach ($operation->getParameters() as $parameter) {
            if ($parameter instanceof Reference) {
                $parameter = $guessClass->resolveParameter($parameter);
            }

            if ($parameter instanceof Parameter && EndpointGenerator::IN_QUERY === $parameter->getIn() && true === $parameter->getAllowReserved()) {
                $queryAllowReservedParameters[] = $parameter->getName();
            }
        }

        if ([] === $queryAllowReservedParameters) {
            return null;
        }

        $items = [];
        foreach ($queryAllowReservedParameters as $parameter) {
            $items[] = new Expr\ArrayItem(new Scalar\String_((string)$parameter));
        }

        return new Stmt\ClassMethod($methodName, [
            'flags'      => Modifiers::PROTECTED,
            'stmts'      => [
                new Stmt\Return_(new Expr\Array_($items)),
            ],
            'returnType' => new Name('array'),
        ], [
            'comments' => [new Doc("/**\n * @return list<string>\n */")],
        ]);
    }
}
