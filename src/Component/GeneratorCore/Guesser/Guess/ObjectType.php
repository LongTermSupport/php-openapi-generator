<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Guess;

use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Generator\Context\Context;
use Override;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar;
use PhpParser\Node\Stmt;

class ObjectType extends Type
{
    /**
     * @param array<string, array<mixed>|null> $discriminants
     */
    public function __construct(
        object $object,
        private readonly string $className,
        private readonly string $namespace,
        private readonly array $discriminants = [],
    ) {
        parent::__construct($object, 'object');
    }

    #[Override]
    public function createDenormalizationStatement(Context $context, Expr $input, bool $normalizerFromObject = true): array
    {
        $tmpVar          = new Expr\Variable($context->getUniqueVariableName('value'));
        $denormalizeCall = $this->createDenormalizationValueStatement($context, $input, $normalizerFromObject);

        // Wrap the denormalize call in TypeValidator::assertInstanceOf so the
        // generated code:
        //
        //   1. Preserves runtime type validation (assertInstanceOf throws on
        //      a mismatch, exactly as the previous explicit `if (!$x instanceof Y)`
        //      guard did).
        //
        //   2. Avoids PHPStan `instanceof.alwaysTrue`. The Symfony parent
        //      DenormalizerInterface::denormalize() has the conditional return
        //      type `($type is class-string<TObject> ? TObject : mixed)`, so
        //      PHPStan already narrows the call result to the requested class
        //      when called with a class-string literal. An explicit `instanceof`
        //      check on that narrowed value is reported as redundant under
        //      `treatPhpDocTypesAsCertain: true` (the PHPStan default).
        //      `TypeValidator::assertInstanceOf` accepts `mixed`, so no
        //      `instanceof.alwaysTrue` is reported regardless of the consuming
        //      project's `treatPhpDocTypesAsCertain` setting.
        //
        //   3. Reduces 5 lines of generated boilerplate (assign + if + throw)
        //      to a single expression.
        $assertCall = new Expr\StaticCall(
            new Name('TypeValidator'),
            'assertInstanceOf',
            [
                new Arg($denormalizeCall),
                new Arg(new ClassConstFetch(
                    new FullyQualified($this->getFqdn(false)),
                    new Identifier('class')
                )),
                new Arg(new Scalar\String_($this->className)),
            ]
        );

        return [
            [
                new Stmt\Expression(new Expr\Assign($tmpVar, $assertCall)),
            ],
            $tmpVar,
        ];
    }

    #[Override]
    public function createConditionStatement(Expr $input): Expr
    {
        $conditionStatement = parent::createConditionStatement($input);

        foreach ($this->discriminants as $key => $values) {
            $issetCondition = new Expr\FuncCall(
                new Name('isset'),
                [
                    new Arg(new Expr\ArrayDimFetch($input, new Scalar\String_($key))),
                ]
            );

            $logicalOr = null;

            if (null !== $values) {
                foreach ($values as $value) {
                    if (!\is_string($value)) {
                        continue;
                    }

                    if (null === $logicalOr) {
                        $logicalOr = new Expr\BinaryOp\Identical(
                            new Expr\ArrayDimFetch($input, new Scalar\String_($key)),
                            new Scalar\String_($value)
                        );
                    } else {
                        $logicalOr = new Expr\BinaryOp\LogicalOr(
                            $logicalOr,
                            new Expr\BinaryOp\Identical(
                                new Expr\ArrayDimFetch($input, new Scalar\String_($key)),
                                new Scalar\String_($value)
                            )
                        );
                    }
                }
            }

            if (null !== $logicalOr) {
                $conditionStatement = new Expr\BinaryOp\LogicalAnd($conditionStatement, new Expr\BinaryOp\LogicalAnd($issetCondition, $logicalOr));
            } else {
                $conditionStatement = new Expr\BinaryOp\LogicalAnd($conditionStatement, $issetCondition);
            }
        }

        return $conditionStatement;
    }

    #[Override]
    public function createNormalizationConditionStatement(Expr $input): Expr
    {
        return new Expr\Instanceof_($input, new FullyQualified($this->getFqdn(false)));
    }

    #[Override]
    public function getTypeHint(string $namespace): Name
    {
        if ('\\' . $namespace . '\\' . $this->className === $this->getFqdn()) {
            return new Name($this->className);
        }

        return new Name($this->getFqdn());
    }

    #[Override]
    public function getDocTypeHint(string $namespace): Name
    {
        return $this->getTypeHint($namespace);
    }

    public function getClassName(): string
    {
        return $this->className;
    }

    #[Override]
    protected function createDenormalizationValueStatement(Context $context, Expr $input, bool $normalizerFromObject = true): Expr
    {
        $denormalizerVar = new Expr\PropertyFetch(new Expr\Variable('this'), 'denormalizer');
        if (!$normalizerFromObject) {
            $denormalizerVar = new Expr\Variable('denormalizer');
        }

        return new Expr\MethodCall($denormalizerVar, 'denormalize', [
            new Arg($input),
            new Arg(new ClassConstFetch(
                new FullyQualified($this->getFqdn(false)),
                new Identifier('class')
            )),
            new Arg(new Scalar\String_('json')),
            new Arg(new Expr\Variable('context')),
        ]);
    }

    #[Override]
    protected function createNormalizationValueStatement(Context $context, Expr $input, bool $normalizerFromObject = true): Expr
    {
        $normalizerVar = new Expr\PropertyFetch(new Expr\Variable('this'), 'normalizer');
        if (!$normalizerFromObject) {
            $normalizerVar = new Expr\Variable('normalizer');
        }

        return new Expr\MethodCall($normalizerVar, 'normalize', [
            new Arg($input),
            new Arg(new Scalar\String_('json')),
            new Arg(new Expr\Variable('context')),
        ]);
    }

    private function getFqdn(bool $withRoot = true): string
    {
        if ($withRoot) {
            return '\\' . $this->namespace . '\Model\\' . $this->className;
        }

        return $this->namespace . '\Model\\' . $this->className;
    }
}
