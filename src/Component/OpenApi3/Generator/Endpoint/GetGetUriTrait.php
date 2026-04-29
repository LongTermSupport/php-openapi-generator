<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApi3\Generator\Endpoint;

use LogicException;
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

            $paramName = $parameter->getName();
            if (!\is_string($paramName)) {
                throw new LogicException('Expected string parameter name, got ' . get_debug_type($paramName));
            }

            $names[] = $paramName;
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
                    new Arg(new Expr\Array_(array_map(static function ($type, int|string $name): ArrayItem {
                        $propertyFetch = new Expr\PropertyFetch(new Expr\Variable('this'), $name);

                        // 'array' → implode(',', $this->param) — array property joined with comma
                        if ('array' === $type) {
                            return new ArrayItem(new Expr\FuncCall(new Name('implode'), [
                                new Arg(new Scalar\String_(',')),
                                new Arg($propertyFetch),
                            ]));
                        }

                        // 'string', null (untyped), or array (OAS 3.1 multi-type) → no cast needed.
                        // For array types (e.g. ["string", "integer"]), GetConstructorTrait emits
                        // `protected string $param` and assigns via strval(), so the property is
                        // already `string`. Casting `string` to `string` triggers PHPStan `cast.useless`.
                        if ('string' === $type || null === $type || \is_array($type)) {
                            return new ArrayItem($propertyFetch);
                        }

                        // Other scalar types (integer → int, number → float, boolean → bool)
                        // need an explicit cast to string for str_replace.
                        return new ArrayItem(new Expr\Cast\String_($propertyFetch));
                    }, $types, $names))),
                    new Arg(new Scalar\String_($operation->getPath())),
                ])),
            ],
            'returnType' => new Name('string'),
        ]);
    }
}
