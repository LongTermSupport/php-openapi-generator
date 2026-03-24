<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApi3\Generator\Endpoint;

use LongTermSupport\OpenApiGenerator\Component\OpenApi3\Generator\EndpointGenerator;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\Guesser\GuessClass;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Parameter;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Schema;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Guesser\Guess\OperationGuess;
use LongTermSupport\OpenApiGenerator\Component\OpenApiRuntime\Reference;
use PhpParser\Modifiers;
use PhpParser\Node\Arg;
use PhpParser\Node\ArrayItem;
use PhpParser\Node\Expr;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar;
use PhpParser\Node\Stmt;

trait GetGetUriTrait
{
    public function getGetUri(OperationGuess $operation, GuessClass $guessClass): Stmt\ClassMethod
    {
        $names = [];
        $types = [];
        foreach ($operation->getParameters() as $parameter) {
            if ($parameter instanceof Reference) {
                $parameter = $guessClass->resolveParameter($parameter);
            }

            if (!$parameter instanceof Parameter) {
                continue;
            }

            if (EndpointGenerator::IN_PATH !== $parameter->getIn()) {
                continue;
            }

            $schema = $parameter->getSchema();
            if ($schema instanceof Reference) {
                [, $resolvedSchema] = $guessClass->resolve($schema, Schema::class);
                $schema             = $resolvedSchema instanceof Schema ? $resolvedSchema : null;
            }

            $names[] = $parameter->getName() ?? '';
            $types[] = $schema instanceof Schema ? $schema->getType() : null;
        }

        if ([] === $names) {
            return new Stmt\ClassMethod('getUri', [
                'flags'      => Modifiers::PUBLIC,
                'stmts'      => [
                    new Stmt\Return_(new Scalar\String_($operation->getPath())),
                ],
                'returnType' => new Name('string'),
            ]);
        }

        return new Stmt\ClassMethod('getUri', [
            'flags'      => Modifiers::PUBLIC,
            'stmts'      => [
                new Stmt\Return_(new Expr\FuncCall(new Name('str_replace'), [
                    new Arg(new Expr\Array_(array_map(static fn ($name): ArrayItem => new ArrayItem(new Scalar\String_('{' . $name . '}')), $names))),
                    new Arg(new Expr\Array_(array_map(static fn ($type, int|string $name): ArrayItem => 'array' === $type
                        // return str_replace(['{param}'], [implode(',', $this->param)], '/path/{param}')
                        ? new ArrayItem(new Expr\FuncCall(new Name('implode'), [new Arg(new Scalar\String_(',')), new Arg(new Expr\PropertyFetch(new Expr\Variable('this'), $name))]))
                        // return str_replace(['{param}'], [(string) $this->param], '/path/{param}')
                        : new ArrayItem(new Expr\Cast\String_(new Expr\PropertyFetch(new Expr\Variable('this'), $name))), $types, $names))),
                    new Arg(new Scalar\String_($operation->getPath())),
                ])),
            ],
            'returnType' => new Name('string'),
        ]);
    }
}
