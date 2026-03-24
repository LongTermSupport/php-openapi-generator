<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApi3\Generator\Endpoint;

use LogicException;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\Generator\Parameter\NonBodyParameterGenerator;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\Guesser\GuessClass;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Parameter;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Guesser\Guess\OperationGuess;
use LongTermSupport\OpenApiGenerator\Component\OpenApiRuntime\Reference;
use PhpParser\Modifiers;
use PhpParser\Node\Expr;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;
use Symfony\Component\OptionsResolver\OptionsResolver;

trait GetGetOptionsResolverTrait
{
    /**
     * @param array<string, mixed> $customResolver
     * @param array<string, mixed> $genericResolver
     */
    public function getOptionsResolverMethod(OperationGuess $operation, string $parameterIn, string $methodName, GuessClass $guessClass, NonBodyParameterGenerator $nonBodyParameterGenerator, array $customResolver = [], array $genericResolver = []): ?Stmt\ClassMethod
    {
        $parameters                  = [];
        $queryResolverNormalizerStms = [];
        $customResolverKeys          = array_keys($customResolver);

        foreach ($operation->getParameters() as $parameter) {
            if ($parameter instanceof Reference) {
                $parameter = $guessClass->resolveParameter($parameter);
            }

            if ($parameter instanceof Parameter && $parameterIn === $parameter->getIn()) {
                if ($parameter->offsetExists('x-openapi-skip-validation') && (bool)$parameter->offsetGet('x-openapi-skip-validation')) {
                    continue;
                }

                $parameters[] = $parameter;
                $paramName    = $parameter->getName();
                if (null !== $paramName && \in_array($paramName, $customResolverKeys, true)) {
                    $resolverValue = $customResolver[$paramName];
                    if (!\is_string($resolverValue)) {
                        throw new LogicException('Expected string, got ' . get_debug_type($resolverValue));
                    }

                    $queryResolverNormalizerStms[] = $this->generateOptionResolverNormalizationStatement($paramName, $resolverValue);
                }
            }
        }

        if ([] === $parameters) {
            return null;
        }

        $optionsResolverVariable = new Expr\Variable('optionsResolver');

        /** @var array<Stmt> $resolverStmts */
        $resolverStmts = array_merge(
            [
                new Stmt\Expression(new Expr\Assign($optionsResolverVariable, new Expr\StaticCall(new Name('parent'), $methodName))),
            ],
            $nonBodyParameterGenerator->generateOptionsResolverStatements($optionsResolverVariable, $parameters, $genericResolver),
            $queryResolverNormalizerStms,
            [
                new Stmt\Return_($optionsResolverVariable),
            ]
        );

        return new Stmt\ClassMethod($methodName, [
            'flags'      => Modifiers::PROTECTED,
            'stmts'      => $resolverStmts,
            'returnType' => new Name\FullyQualified(OptionsResolver::class),
        ]);
    }
}
