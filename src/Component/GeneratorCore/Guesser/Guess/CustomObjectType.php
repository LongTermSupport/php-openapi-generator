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

class CustomObjectType extends Type
{
    /**
     * @param array<string, list<mixed>|null> $discriminants
     */
    public function __construct(
        object $object,
        private readonly string $className,
        private readonly array $discriminants = [],
    ) {
        parent::__construct($object, 'object');
    }

    #[Override]
    public function createDenormalizationStatement(Context $context, Expr $input, bool $normalizerFromObject = true): array
    {
        $tmpVar          = new Expr\Variable($context->getUniqueVariableName('value'));
        $denormalizeCall = $this->createDenormalizationValueStatement($context, $input, $normalizerFromObject);

        return [
            [
                new Stmt\Expression(new Expr\Assign($tmpVar, $denormalizeCall)),
                new Stmt\If_(
                    new Expr\BooleanNot(new Expr\Instanceof_($tmpVar, new FullyQualified($this->getFqdn(false)))),
                    ['stmts' => [
                        new Stmt\Expression(new Expr\Throw_(new Expr\New_(
                            new FullyQualified('LogicException'),
                            [new Arg(new Expr\BinaryOp\Concat(
                                new Scalar\String_('Expected ' . $this->getFqdn(false) . ', got '),
                                new Expr\FuncCall(new Name('get_debug_type'), [new Arg($tmpVar)])
                            ))]
                        ))),
                    ]]
                ),
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

            if (\is_array($values)) {
                foreach ($values as $value) {
                    $valueStr = \is_scalar($value) ? (string)$value : '';
                    if (null === $logicalOr) {
                        $logicalOr = new Expr\BinaryOp\Identical(
                            new Expr\ArrayDimFetch($input, new Scalar\String_($key)),
                            new Scalar\String_($valueStr)
                        );
                    } else {
                        $logicalOr = new Expr\BinaryOp\BooleanOr(
                            $logicalOr,
                            new Expr\BinaryOp\Identical(
                                new Expr\ArrayDimFetch($input, new Scalar\String_($key)),
                                new Scalar\String_($valueStr)
                            )
                        );
                    }
                }
            }

            if (null !== $logicalOr) {
                $conditionStatement = new Expr\BinaryOp\BooleanAnd($conditionStatement, new Expr\BinaryOp\BooleanAnd($issetCondition, $logicalOr));
            } else {
                $conditionStatement = new Expr\BinaryOp\BooleanAnd($conditionStatement, $issetCondition);
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
    public function getDocTypeHint(string $namespace): string|Name|null
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
            return '\\' . $this->className;
        }

        return $this->className;
    }
}
