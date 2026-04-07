<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Generator\Normalizer;

use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Generator\Context\Context;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Generator\Naming;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Guess\ArrayType;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Guess\ClassGuess;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Guess\MultipleType;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Guess\Property;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Guess\Type;
use PhpParser\Comment\Doc;
use PhpParser\Modifiers;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar;
use PhpParser\Node\Stmt;
use PhpParser\Node\UnionType;

trait NormalizerGenerator
{
    /**
     * The naming service.
     */
    abstract protected function getNaming(): Naming;

    /**
     * @param array<int, Stmt> $methods
     */
    protected function createNormalizerClass(string $name, array $methods, bool $useCacheableSupportsMethod = false): Stmt\Class_
    {
        $traits = [
            new Stmt\TraitUse([new Name('DenormalizerAwareTrait')]),
            new Stmt\TraitUse([new Name('NormalizerAwareTrait')]),
            new Stmt\TraitUse([new Name('CheckArray')]),
            new Stmt\TraitUse([new Name('ValidatorTrait')]),
        ];

        $implements = [
            new Name('DenormalizerInterface'),
            new Name('NormalizerInterface'),
            new Name('DenormalizerAwareInterface'),
            new Name('NormalizerAwareInterface'),
        ];

        if ($useCacheableSupportsMethod) {
            $implements[] = new Name('CacheableSupportsMethodInterface');
        }

        return new Stmt\Class_(
            $this->getNaming()->getClassName($name),
            [
                'stmts'      => array_merge($traits, $methods),
                'implements' => $implements,
            ]
        );
    }

    /**
     * Create a method to check if denormalization is supported.
     *
     * @param string $modelFqdn Fully Qualified name of the model class denormalized
     */
    protected function createSupportsNormalizationMethod(string $modelFqdn): Stmt\ClassMethod
    {
        return new Stmt\ClassMethod('supportsNormalization', [
            'flags'      => Modifiers::PUBLIC,
            'returnType' => new Identifier('bool'),
            'params'     => [
                new Param(new Expr\Variable('data'), type: new Identifier('mixed')),
                new Param(new Expr\Variable('format'), new Expr\ConstFetch(new Name('null')), new Identifier('?string')),
                new Param(new Expr\Variable('context'), new Expr\Array_(), new Identifier('array')),
            ],
            'stmts'      => [new Stmt\Return_(
                new Expr\Instanceof_(
                    new Expr\Variable('data'),
                    new Name('\\' . $modelFqdn)
                ),
            )],
        ]);
    }

    /**
     * Create the normalization method.
     */
    protected function createNormalizeMethod(string $modelFqdn, Context $context, ClassGuess $classGuess, bool $skipNullValues = true, bool $skipRequiredFields = false, bool $includeNullValue = true): Stmt\ClassMethod
    {
        $context->refreshScope();
        $dataVariable   = new Expr\Variable('dataArray');
        $objectVariable = new Expr\Variable('data');

        // Type guard: narrow $data from mixed to the model class.
        // This must come first so that all subsequent method calls on $data are
        // recognized by PHPStan as valid (fixes method.nonObject and foreach.nonIterable).
        $typeGuard = new Stmt\If_(
            new Expr\BooleanNot(new Expr\Instanceof_(
                $objectVariable,
                new Name('\\' . $modelFqdn)
            )),
            ['stmts' => [
                new Stmt\Expression(new Expr\Throw_(new Expr\New_(
                    new Name('\LogicException'),
                    [new Arg(new Expr\BinaryOp\Concat(
                        new Scalar\String_('Expected ' . $modelFqdn . ', got '),
                        new Expr\FuncCall(new Name('get_debug_type'), [new Arg($objectVariable)])
                    ))]
                ))),
            ]]
        );

        $statements = array_merge(
            [$typeGuard],
            $this->normalizeMethodStatements($dataVariable, $classGuess, $context)
        );

        foreach ($classGuess->getProperties() as $property) {
            if (!$property->isReadOnly()) {
                $propertyVar = new Expr\MethodCall($objectVariable, $this->getNaming()->getPrefixedMethodName('get', $property->getAccessorName()));

                // PHPStan doesn't narrow method-call return types through conditions.
                // For nullable properties, assign the getter to a local variable so that
                // null-check conditions can narrow the type for the if-body. This fixes
                // method.nonObject on DateTime, foreach.nonIterable on arrays, etc.
                $normalizationInput = $propertyVar;
                $localVarAssign     = null;
                if ($property->isNullable()) {
                    if ($property->getType() instanceof ArrayType && $property->isRequired()) {
                        // Required nullable array: use ?? [] to give foreach a non-null array
                        $normalizationInput = new Expr\BinaryOp\Coalesce($propertyVar, new Expr\Array_());
                    } else {
                        // Assign getter to local variable for PHPStan type narrowing
                        $localVarName       = $context->getUniqueVariableName('val');
                        $localVar           = new Expr\Variable($localVarName);
                        $localVarAssign     = new Stmt\Expression(new Expr\Assign($localVar, $propertyVar));
                        $normalizationInput = $localVar;
                    }
                }

                /** @var array{array<int, Stmt>, Expr} $normResult */
                $normResult = $property->getType()->createNormalizationStatement($context, $normalizationInput);
                /** @var array<int, Stmt> $normalizationStatements */
                $normalizationStatements = $normResult[0];
                $outputVar               = $normResult[1];

                $normalizationStatements[] = new Stmt\Expression(new Expr\Assign(new Expr\ArrayDimFetch($dataVariable, new Scalar\String_($property->getName())), $outputVar));

                // Use local var in null conditions when available (enables PHPStan narrowing)
                $conditionVar = $localVarAssign instanceof Stmt\Expression ? $normalizationInput : $propertyVar;

                // ---- Required property path ----
                if (!$skipRequiredFields && $property->isRequired()) {
                    if ($localVarAssign instanceof Stmt\Expression) {
                        // Required nullable: local var + null guard for PHPStan narrowing
                        $statements[] = $localVarAssign;
                        $statements[] = new Stmt\If_(
                            new Expr\BinaryOp\NotIdentical(new Expr\ConstFetch(new Name('null')), $normalizationInput),
                            [
                                'stmts' => $normalizationStatements,
                                'else'  => new Stmt\Else_([
                                    new Stmt\Expression(new Expr\Assign(
                                        new Expr\ArrayDimFetch($dataVariable, new Scalar\String_($property->getName())),
                                        new Expr\ConstFetch(new Name('null'))
                                    )),
                                ]),
                            ]
                        );
                    } else {
                        $statements = array_merge($statements, $normalizationStatements);
                    }

                    continue;
                }

                // ---- Non-required property paths ----
                // Emit local var assignment before any condition for PHPStan narrowing
                if ($localVarAssign instanceof Stmt\Expression) {
                    $statements[] = $localVarAssign;
                }

                if (!$includeNullValue) {
                    if (!$property->isRequired()) {
                        $condition = new Expr\MethodCall($objectVariable, 'isInitialized', [new Arg(new Scalar\String_($property->getPhpName()))]);
                        // When the property is nullable (local var assigned), add null guard
                        // so PHPStan can narrow the type (fixes method.nonObject on DateTime|null)
                        if ($localVarAssign instanceof Stmt\Expression) {
                            $condition = new Expr\BinaryOp\BooleanAnd($condition, new Expr\BinaryOp\NotIdentical(new Expr\ConstFetch(new Name('null')), $conditionVar));
                        }

                        $statements[] = new Stmt\If_($condition, ['stmts' => $normalizationStatements]);
                    } else {
                        $statements[] = new Stmt\If_(
                            new Expr\BinaryOp\NotIdentical(new Expr\ConstFetch(new Name('null')), $conditionVar),
                            ['stmts' => $normalizationStatements]
                        );
                    }

                    continue;
                }

                if (!$property->isRequired()) {
                    if ($property->isNullable()) {
                        $statements[] = new Stmt\If_(
                            new Expr\BinaryOp\BooleanAnd(
                                new Expr\MethodCall($objectVariable, 'isInitialized', [new Arg(new Scalar\String_($property->getPhpName()))]),
                                new Expr\BinaryOp\NotIdentical(new Expr\ConstFetch(new Name('null')), $conditionVar)
                            ),
                            ['stmts' => $normalizationStatements]
                        );
                    } else {
                        $statements[] = new Stmt\If_(
                            new Expr\MethodCall($objectVariable, 'isInitialized', [new Arg(new Scalar\String_($property->getPhpName()))]),
                            ['stmts' => $normalizationStatements]
                        );
                    }
                } else {
                    $statements[] = new Stmt\If_(
                        new Expr\BinaryOp\NotIdentical(new Expr\ConstFetch(new Name('null')), $conditionVar),
                        ['stmts' => $normalizationStatements]
                    );
                }

                if ((!$context->isStrict() || $property->isNullable()
                                           || ($property->getType() instanceof MultipleType && 1 === \count(array_intersect([Type::TYPE_NULL], $property->getType()->getTypes())))
                                           || (Type::TYPE_NULL === $property->getType()->getName())) && !$skipNullValues) {
                    $statements[] = new Stmt\Else_(
                        [new Stmt\Expression(new Expr\Assign(new Expr\ArrayDimFetch($dataVariable, new Scalar\String_($property->getName())), new Expr\ConstFetch(new Name('null'))))]
                    );
                }
            }
        }

        $patternCondition = [];
        $loopKeyVar       = new Expr\Variable($context->getUniqueVariableName('key'));
        $loopValueVar     = new Expr\Variable($context->getUniqueVariableName('value'));

        foreach ($classGuess->getExtensionsType() as $pattern => $type) {
            /** @var array{array<int, Stmt>, Expr} $normResult */
            $normResult = $type->createNormalizationStatement($context, $loopValueVar);
            /** @var array<int, Stmt> $denormalizationStatements */
            $denormalizationStatements = $normResult[0];
            $outputVar                 = $normResult[1];

            // This loop is over the typed model object, which extends
            // `\ArrayObject<string, mixed>` (guarded by `willExtendArrayObject()`
            // below). PHPStan narrows `$key` to `string` from the @template,
            // but PHP coerces numeric-string keys to `int` when stored in
            // ArrayObject — so at runtime `$key` may actually be `int`.
            //
            // For the `preg_match` argument we therefore emit `strval($key)`:
            //
            //   1. Runtime safe: converts `int` keys back to `string`, preventing
            //      TypeError under strict_types where preg_match's `$subject`
            //      parameter requires `string`.
            //   2. PHPStan clean: `strval()` is a function call, not the
            //      `(string)` cast operator, so it is NOT reported as
            //      `cast.useless` even when the input is already `string`.
            //
            // For the array-key assignment we emit the bare `$key`. Even if it
            // is `int` at runtime, PHP coerces it back to the appropriate key
            // type when stored in `$dataArray`, so an explicit cast there is
            // both useless (PHPStan) and unnecessary (runtime).
            $patternCondition[] = new Stmt\If_(
                new Expr\BinaryOp\Identical(
                    new Expr\FuncCall(new Name('preg_match'), [
                        new Arg(new Expr\ConstFetch(new Name("'/" . str_replace('/', '\/', $pattern) . "/'"))),
                        new Arg(new Expr\FuncCall(new Name('strval'), [new Arg($loopKeyVar)])),
                    ]),
                    new Scalar\Int_(1)
                ),
                [
                    'stmts' => array_merge($denormalizationStatements, [
                        new Stmt\Expression(new Expr\Assign(new Expr\ArrayDimFetch($dataVariable, $loopKeyVar), $outputVar)),
                    ]),
                ]
            );
        }

        if ([] !== $patternCondition && $classGuess->willExtendArrayObject()) {
            $statements[] = new Stmt\Foreach_($objectVariable, $loopValueVar, [
                'keyVar' => $loopKeyVar,
                'stmts'  => $patternCondition,
            ]);
        }

        if ($this->validation) {
            $schema          = $context->getCurrentSchema();
            $contextVariable = new Expr\Variable('context');
            $constraintFqdn  = $schema->getNamespace() . '\Validator\\' . $this->naming->getConstraintName($classGuess->getName());

            $statements[] = new Stmt\If_(new Expr\BooleanNot(new Expr\Cast\Bool_(new Expr\BinaryOp\Coalesce(new Expr\ArrayDimFetch($contextVariable, new Scalar\String_('skip_validation')), new Expr\ConstFetch(new Name('false'))))), ['stmts' => [
                new Stmt\Expression(new Expr\MethodCall(new Expr\Variable('this'), 'validate', [
                    new Arg($dataVariable), new Arg(new Expr\New_(new Name('\\' . $constraintFqdn))),
                ])),
            ]]);
        }

        $statements[] = new Stmt\Return_($dataVariable);

        return new Stmt\ClassMethod('normalize', [
            'flags'      => Modifiers::PUBLIC,
            'returnType' => new UnionType([new Identifier('array'), new Identifier('string'), new Identifier('int'), new Identifier('float'), new Identifier('bool'), new Name('\ArrayObject'), new Identifier('null')]),
            'params'     => [
                new Param($objectVariable, type: new Identifier('mixed')),
                new Param(new Expr\Variable('format'), new Expr\ConstFetch(new Name('null')), new Identifier('?string')),
                new Param(new Expr\Variable('context'), new Expr\Array_(), new Identifier('array')),
            ],
            'stmts'      => $statements,
        ], [
            'comments' => [new Doc("/**\n * @return array<string, mixed>|string|int|float|bool|\\ArrayObject<string, mixed>|null\n * @phpstan-return array<int|string, mixed>|string|int|float|bool|\\ArrayObject<int|string, mixed>|null\n */")],
        ]);
    }

    /**
     * Create a method to say that hasCacheableSupportsMethod is supported.
     */
    protected function createHasCacheableSupportsMethod(): Stmt\ClassMethod
    {
        return new Stmt\ClassMethod('hasCacheableSupportsMethod', [
            'flags'      => Modifiers::PUBLIC,
            'returnType' => new Identifier('bool'),
            'stmts'      => [
                new Stmt\Return_(new Expr\ConstFetch(new Name('true'))),
            ],
        ]);
    }

    /** @return array<int, Stmt> */
    protected function normalizeMethodStatements(Expr\Variable $dataVariable, ClassGuess $classGuess, Context $context): array
    {
        return [
            new Stmt\Expression(new Expr\Assign($dataVariable, new Expr\Array_())),
        ];
    }
}
