<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApi3\Generator\Endpoint;

use LogicException;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Generator\Context\Context;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\Generator\RequestBodyGenerator;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\Guesser\GuessClass;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\RequestBody;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Guesser\Guess\OperationGuess;
use LongTermSupport\OpenApiGenerator\Component\OpenApiRuntime\Reference;
use PhpParser\Comment\Doc;
use PhpParser\Modifiers;
use PhpParser\Node\Expr;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt;
use Psr\Http\Message\StreamFactoryInterface;
use Symfony\Component\Serializer\SerializerInterface;

trait GetGetBodyTrait
{
    public function getGetBody(OperationGuess $operation, Context $context, GuessClass $guessClass, RequestBodyGenerator $requestBodyGenerator): Stmt\ClassMethod
    {
        $opRef = $operation->getReference() . '/requestBody';
        $op    = $operation->getOperation();
        if (!$op instanceof \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Operation) {
            throw new LogicException('Expected Operation, got ' . get_debug_type($op));
        }

        $requestBody = $op->getRequestBody();

        if ($requestBody instanceof Reference) {
            [, $resolvedBody] = $guessClass->resolve($requestBody, RequestBody::class);
            $requestBody      = $resolvedBody instanceof RequestBody ? $resolvedBody : null;
        }

        /** @var array<Stmt> $serializeStmts */
        $serializeStmts = $requestBodyGenerator->getSerializeStatements($requestBody instanceof RequestBody ? $requestBody : null, $opRef, $context);

        return new Stmt\ClassMethod('getBody', [
            'flags'      => Modifiers::PUBLIC,
            'params'     => [
                new Param(new Expr\Variable('serializer'), null, new Name\FullyQualified(SerializerInterface::class)),
                new Param(new Expr\Variable('streamFactory'), new Expr\ConstFetch(new Name('null')), new NullableType(new Name\FullyQualified(StreamFactoryInterface::class))),
            ],
            'returnType' => new Name('array'),
            'stmts'      => $serializeStmts,
        ], [
            'comments' => [new Doc("/**\n * @return array<int, mixed>\n */")],
        ]);
    }
}
