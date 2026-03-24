<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Guess;

use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Generator\Context\Context;
use Override;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;

class ArrayType extends Type
{
    public function __construct(object $object, protected Type $itemType, string $type = 'array')
    {
        parent::__construct($object, $type);
    }

    public function getItemType(): Type
    {
        return $this->itemType;
    }

    #[Override]
    public function getDocTypeHint(string $namespace): string|Name|null
    {
        if ($this->itemType instanceof MultipleType) {
            $typesString = [];

            foreach ($this->itemType->getTypes() as $type) {
                $itemDoc       = (string)$type->getDocTypeHint($namespace);
                $typesString[] = \sprintf('list<%s>', '' !== $itemDoc ? $itemDoc : 'mixed');
            }

            return implode('|', $typesString);
        }

        $itemDoc = (string)$this->itemType->getDocTypeHint($namespace);

        return \sprintf('list<%s>', '' !== $itemDoc ? $itemDoc : 'mixed');
    }

    #[Override]
    public function createDenormalizationStatement(Context $context, Expr $input, bool $normalizerFromObject = true): array
    {
        $valuesVar = new Expr\Variable($context->getUniqueVariableName('values'));

        $statements = [
            // $values = [];
            new Stmt\Expression(new Expr\Assign($valuesVar, $this->createArrayValueStatement())),
        ];

        $loopValueVar = new Expr\Variable($context->getUniqueVariableName('value'));
        $loopKeyVar   = $this->createLoopKeyStatement($context);

        [$subStatements, $outputExpr] = $this->itemType->createDenormalizationStatement($context, $loopValueVar, $normalizerFromObject);

        $loopStatements = array_merge($subStatements, [
            new Stmt\Expression(new Expr\Assign($this->createLoopOutputAssignement($valuesVar, $loopKeyVar), $outputExpr)),
        ]);

        // Guard: wrap foreach in is_array() check since $input may be mixed
        // (e.g. $data['field'] where $data is array<string, mixed>).
        $statements[] = new Stmt\If_(
            new Expr\FuncCall(new Name('\is_array'), [new Arg($input)]),
            [
                'stmts' => [
                    new Stmt\Foreach_($input, $loopValueVar, [
                        'keyVar' => $loopKeyVar,
                        'stmts'  => $loopStatements,
                    ]),
                ],
            ]
        );

        return [$statements, $valuesVar];
    }

    #[Override]
    public function createConditionStatement(Expr $input): Expr
    {
        return new Expr\BinaryOp\BooleanAnd(
            parent::createConditionStatement($input),
            new Expr\MethodCall(new Expr\Variable('this'), 'isOnlyNumericKeys', [
                new Arg($input),
            ])
        );
    }

    #[Override]
    public function createNormalizationStatement(Context $context, Expr $input, bool $normalizerFromObject = true): array
    {
        $valuesVar  = new Expr\Variable($context->getUniqueVariableName('values'));
        $statements = [
            // $values = [];
            new Stmt\Expression(new Expr\Assign($valuesVar, $this->createNormalizationArrayValueStatement())),
        ];

        $loopValueVar = new Expr\Variable($context->getUniqueVariableName('value'));
        $loopKeyVar   = $this->createLoopKeyStatement($context);

        [$subStatements, $outputExpr] = $this->itemType->createNormalizationStatement($context, $loopValueVar, $normalizerFromObject);

        $loopStatements = array_merge($subStatements, [
            new Stmt\Expression(new Expr\Assign($this->createNormalizationLoopOutputAssignement($valuesVar, $loopKeyVar), $outputExpr)),
        ]);

        $statements[] = new Stmt\Foreach_($input, $loopValueVar, [
            'keyVar' => $loopKeyVar,
            'stmts'  => $loopStatements,
        ]);

        return [$statements, $valuesVar];
    }

    #[Override]
    public function getTypeHint(string $namespace): Node\Identifier
    {
        return new Node\Identifier('array');
    }

    protected function createArrayValueStatement(): Expr
    {
        return new Expr\Array_();
    }

    protected function createNormalizationArrayValueStatement(): Expr
    {
        return new Expr\Array_();
    }

    protected function createLoopKeyStatement(Context $context): ?Expr
    {
        return null;
    }

    protected function createLoopOutputAssignement(Expr $valuesVar, ?Expr $loopKeyVar): Expr
    {
        return new Expr\ArrayDimFetch($valuesVar);
    }

    protected function createNormalizationLoopOutputAssignement(Expr $valuesVar, ?Expr $loopKeyVar): Expr
    {
        return new Expr\ArrayDimFetch($valuesVar);
    }
}
