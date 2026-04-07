<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Generator\Normalizer;

use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Generator\Context\Context;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Generator\Naming;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Guess\ClassGuess;
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

trait DenormalizerGenerator
{
    /**
     * The naming service.
     */
    abstract protected function getNaming(): Naming;

    /**
     * Create a method to check if denormalization is supported.
     *
     * @param string $modelFqdn Fully Qualified name of the model class denormalized
     */
    protected function createSupportsDenormalizationMethod(string $modelFqdn): Stmt\ClassMethod
    {
        return new Stmt\ClassMethod('supportsDenormalization', [
            'flags'      => Modifiers::PUBLIC,
            'returnType' => new Identifier('bool'),
            'params'     => [
                new Param(new Expr\Variable('data'), type: new Identifier('mixed')),
                new Param(new Expr\Variable('type'), type: new Identifier('string')),
                new Param(
                    new Expr\Variable('format'),
                    new Expr\ConstFetch(new Name('null')),
                    new Identifier('?string')
                ),
                new Param(new Expr\Variable('context'), new Expr\Array_(), new Identifier('array')),
            ],
            'stmts'      => [new Stmt\Return_(new Expr\BinaryOp\Identical(
                new Expr\Variable('type'),
                new Expr\ClassConstFetch(new Name\FullyQualified($modelFqdn), new Identifier('class'))
            ))],
        ]);
    }

    protected function createDenormalizeMethod(string $modelFqdn, Context $context, ClassGuess $classGuess): Stmt\ClassMethod
    {
        $context->refreshScope();
        $objectVariable = new Expr\Variable('object');
        $dataVariable   = new Expr\Variable('data');
        /** @var array<int, Stmt> $statements */
        $statements = [];

        // 1. Create the model object first (needed for early return in guard).
        $statements[] = new Stmt\Expression(new Expr\Assign(
            $objectVariable,
            new Expr\New_(new Name\FullyQualified($modelFqdn)),
        ));

        // 2. Guard: early return if $data is not an array.
        // After this guard, PHPStan knows $data is array — all subsequent
        // array access ($data['key']) is type-safe.
        $statements[] = new Stmt\If_(new Expr\BinaryOp\BooleanOr(new Expr\BinaryOp\Identical(new Expr\ConstFetch(new Name('null')), $dataVariable), new Expr\BinaryOp\Identical(new Expr\ConstFetch(new Name('false')), new Expr\FuncCall(new Name('\is_array'), [new Arg($dataVariable)]))), [
            'stmts' => [new Stmt\Return_($objectVariable)],
        ]);

        // 3. Discriminator / subclass dispatch (from denormalizeMethodStatements).
        // These access $data as array, so they must come after the guard.
        $denormalizeMethodStatements = $this->denormalizeMethodStatements($classGuess, $context);
        foreach ($denormalizeMethodStatements as $stmt) {
            $statements[] = $stmt;
        }

        // 4. $ref / $recursiveRef handling (JSON Schema references).
        if ($this->useReference) {
            $contextOrigin = new Expr\StaticCall(
                new Name('TypeValidator'),
                'assertString',
                [
                    new Arg(new Expr\ArrayDimFetch(new Expr\Variable('context'), new Scalar\String_('document-origin'))),
                    new Arg(new Scalar\String_('context.document-origin')),
                ]
            );

            $statements[] = new Stmt\If_(
                new Expr\Isset_([new Expr\ArrayDimFetch($dataVariable, new Scalar\String_('$ref'))]),
                [
                    'stmts' => [
                        new Stmt\Return_(new Expr\New_(new Name('Reference'), [
                            new Arg(new Expr\StaticCall(
                                new Name('TypeValidator'),
                                'assertString',
                                [
                                    new Arg(new Expr\ArrayDimFetch($dataVariable, new Scalar\String_('$ref'))),
                                    new Arg(new Scalar\String_('$ref')),
                                ]
                            )),
                            new Arg($contextOrigin),
                        ])),
                    ],
                ]
            );
            $statements[] = new Stmt\If_(
                new Expr\Isset_([new Expr\ArrayDimFetch($dataVariable, new Scalar\String_('$recursiveRef'))]),
                [
                    'stmts' => [
                        new Stmt\Return_(new Expr\New_(new Name('Reference'), [
                            new Arg(new Expr\StaticCall(
                                new Name('TypeValidator'),
                                'assertString',
                                [
                                    new Arg(new Expr\ArrayDimFetch($dataVariable, new Scalar\String_('$recursiveRef'))),
                                    new Arg(new Scalar\String_('$recursiveRef')),
                                ]
                            )),
                            new Arg($contextOrigin),
                        ])),
                    ],
                ]
            );
        }

        // 5. Validation (if enabled).
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

        $unset = [] !== $classGuess->getExtensionsType();

        $primitiveTypes = [Type::TYPE_STRING, Type::TYPE_INTEGER, Type::TYPE_FLOAT, Type::TYPE_BOOLEAN];

        foreach ($classGuess->getProperties() as $property) {
            $propertyVar = new Expr\ArrayDimFetch($dataVariable, new Scalar\String_($property->getName()));

            $baseCondition = new Expr\FuncCall(new Name('\array_key_exists'), [
                new Arg(new Scalar\String_($property->getName())),
                new Arg($dataVariable),
            ]);

            if (\in_array($property->getType()->getName(), $primitiveTypes, true)) {
                // Primitive types: use TypeValidator for clean type assertion with clear error messages.
                // TypeValidator handles null internally (assertNullable* returns null, assert* throws).
                $isNullable = !$context->isStrict() || $property->isNullable();
                $methodName = ($isNullable ? 'assertNullable' : 'assert') . ucfirst($property->getType()->getName());

                $validatedExpr = new Expr\StaticCall(
                    new Name('TypeValidator'),
                    $methodName,
                    [
                        new Arg($propertyVar),
                        new Arg(new Scalar\String_($property->getName())),
                    ]
                );

                $mutatorStmt = [
                    new Stmt\Expression(new Expr\MethodCall(
                        $objectVariable,
                        $this->getNaming()->getPrefixedMethodName('set', $property->getAccessorName()),
                        [new Arg($validatedExpr)],
                    )),
                ];

                if ($unset) {
                    $mutatorStmt[] = new Stmt\Unset_([$propertyVar]);
                }

                $statements[] = new Stmt\If_($baseCondition, [
                    'stmts' => $mutatorStmt,
                ]);
            } else {
                // Non-primitive types: use normalizer-based denormalization (objects, arrays, etc.)
                /** @var array{array<int, Stmt>, Expr} $denormResult */
                $denormResult = $property->getType()->createDenormalizationStatement($context, $propertyVar);
                /** @var array<int, Stmt> $denormalizationStatements */
                $denormalizationStatements = $denormResult[0];
                $outputVar                 = $denormResult[1];

                $fullCondition = $baseCondition;

                $mutatorStmt = array_merge($denormalizationStatements, [
                    new Stmt\Expression(new Expr\MethodCall(
                        $objectVariable,
                        $this->getNaming()->getPrefixedMethodName('set', $property->getAccessorName()),
                        [new Arg($outputVar)],
                    )),
                ], $unset ? [new Stmt\Unset_([$propertyVar])] : []);

                if (!$context->isStrict() || $property->isNullable()) {
                    $fullCondition = new Expr\BinaryOp\BooleanAnd(
                        $baseCondition,
                        new Expr\BinaryOp\NotIdentical(
                            $propertyVar,
                            new Expr\ConstFetch(new Name('null'))
                        )
                    );
                }

                $statements[] = new Stmt\If_($fullCondition, [
                    'stmts' => $mutatorStmt,
                ]);

                if (!$context->isStrict() || $property->isNullable()) {
                    $invertCondition = new Expr\BinaryOp\BooleanAnd(
                        $baseCondition,
                        new Expr\BinaryOp\Identical(
                            $propertyVar,
                            new Expr\ConstFetch(new Name('null'))
                        )
                    );

                    $statements[] = new Stmt\ElseIf_($invertCondition, [
                        new Stmt\Expression(new Expr\MethodCall(
                            $objectVariable,
                            $this->getNaming()->getPrefixedMethodName('set', $property->getAccessorName()),
                            [new Arg(new Expr\ConstFetch(new Name('null')))],
                        )),
                    ]);
                }
            }
        }

        $patternCondition = [];
        $loopKeyVar       = new Expr\Variable($context->getUniqueVariableName('key'));
        $loopValueVar     = new Expr\Variable($context->getUniqueVariableName('value'));

        foreach ($classGuess->getExtensionsType() as $pattern => $type) {
            /** @var array{array<int, Stmt>, Expr} $denormResult */
            $denormResult = $type->createDenormalizationStatement($context, $loopValueVar);
            /** @var array<int, Stmt> $denormalizationStatements */
            $denormalizationStatements = $denormResult[0];
            $outputVar                 = $denormResult[1];

            $patternCondition[] = new Stmt\If_(
                new Expr\BinaryOp\Identical(
                    new Expr\FuncCall(new Name('preg_match'), [
                        new Arg(new Expr\ConstFetch(new Name("'/" . str_replace('/', '\/', $pattern) . "/'"))),
                        new Arg(new Expr\Cast\String_($loopKeyVar)),
                    ]),
                    new Scalar\Int_(1)
                ),
                [
                    'stmts' => array_merge($denormalizationStatements, [
                        new Stmt\Expression(new Expr\Assign(new Expr\ArrayDimFetch($objectVariable, $loopKeyVar), $outputVar)),
                    ]),
                ]
            );
        }

        if ([] !== $patternCondition && $classGuess->willExtendArrayObject()) {
            $statements[] = new Stmt\Foreach_($dataVariable, $loopValueVar, [
                'keyVar' => $loopKeyVar,
                'stmts'  => $patternCondition,
            ]);
        }

        $statements[] = new Stmt\Return_($objectVariable);

        // Native return type MUST be `mixed` and there must be NO `@return`
        // phpdoc, so that PHPStan's method.childReturnType covariance check
        // passes against Symfony's DenormalizerInterface::denormalize(), whose
        // return type is the conditional
        //   `($type is class-string<TObject> ? TObject : mixed)`.
        //
        // A narrower return (e.g. `Bar|Reference`) is NOT covariant with the
        // parent's conditional, because in the true branch the parent expects
        // `TObject` (= `Bar` for this normalizer) and `Reference` is not a
        // subtype of `Bar`. The only correct return type is `mixed`.
        //
        // Callers recover the concrete type via `TypeValidator::assertInstanceOf`
        // in the generated `transformResponseBody`, so no type information is
        // lost at the boundary.
        $modelDocFqdn = '\\' . ltrim($modelFqdn, '\\');
        $returnDoc    = $this->useReference
            ? "/**\n * The denormalized result is either a " . $modelDocFqdn . " or a Reference.\n * Native return type is `mixed` for Symfony interface covariance — callers\n * must narrow via TypeValidator::assertInstanceOf.\n */"
            : "/**\n * The denormalized result is a " . $modelDocFqdn . ".\n * Native return type is `mixed` for Symfony interface covariance — callers\n * must narrow via TypeValidator::assertInstanceOf.\n */";

        return new Stmt\ClassMethod('denormalize', [
            'flags'      => Modifiers::PUBLIC,
            'returnType' => new Identifier('mixed'),
            'params'     => [
                new Param($dataVariable, type: new Identifier('mixed')),
                new Param(new Expr\Variable('type'), type: new Identifier('string')),
                new Param(new Expr\Variable('format'), new Expr\ConstFetch(new Name('null')), new Identifier('?string')),
                new Param(new Expr\Variable('context'), new Expr\Array_(), new Identifier('array')),
            ],
            'stmts'      => $statements,
        ], [
            'comments' => [new Doc($returnDoc)],
        ]);
    }

    /** @return array<int, Stmt> */
    protected function denormalizeMethodStatements(ClassGuess $classGuess, Context $context): array
    {
        return [];
    }
}
