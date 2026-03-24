<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Generator\Traits;

use LogicException;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Generator\Context\Context;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Guesser\Guess\OperationGuess;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Registry\Registry;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Scalar;

trait OptionResolverNormalizationTrait
{
    /**
     * @return array{0: array<string, mixed>, 1: array<string, mixed>}
     */
    private function customOptionResolvers(OperationGuess $operation, Context $context): array
    {
        $registry = $context->getRegistry();
        if (!$registry instanceof Registry) {
            throw new LogicException('Expected OpenApiCommon Registry, got ' . get_debug_type($registry));
        }

        $customQueryResolver = $registry->getCustomQueryResolver();
        /** @var array<string, mixed> $genericCustomQueryResolver */
        $genericCustomQueryResolver = [];
        /** @var array<string, mixed> $operationCustomQueryResolver */
        $operationCustomQueryResolver = [];
        if (\array_key_exists('__type', $customQueryResolver)) {
            /** @var array<string, mixed> $genericCustomQueryResolver */
            $genericCustomQueryResolver = $customQueryResolver['__type'];
        }

        $pathResolver = $customQueryResolver[$operation->getPath()] ?? null;
        if (\array_key_exists($operation->getPath(), $customQueryResolver)
            && \is_array($pathResolver)
            && \array_key_exists(mb_strtolower($operation->getMethod()), $pathResolver)) {
            /** @var array<string, mixed> $operationCustomQueryResolver */
            $operationCustomQueryResolver = $pathResolver[mb_strtolower($operation->getMethod())];
        }

        return [$genericCustomQueryResolver, $operationCustomQueryResolver];
    }

    private function generateOptionResolverNormalizationStatement(string $optionName, string $class): Node\Stmt\Expression
    {
        return new Node\Stmt\Expression(
            new Expr\MethodCall(
                new Expr\Variable('optionsResolver'),
                'setNormalizer',
                [
                    new Node\Arg(new Scalar\String_($optionName)),
                    new Node\Arg(new Expr\StaticCall(new Node\Name('\Closure'), 'fromCallable', [
                        new Node\Arg(new Expr\Array_([
                            new Expr\ArrayItem(new Expr\New_(new Node\Name($class))),
                            new Expr\ArrayItem(new Scalar\String_('__invoke')),
                        ])),
                    ])),
                ]
            )
        );
    }
}
