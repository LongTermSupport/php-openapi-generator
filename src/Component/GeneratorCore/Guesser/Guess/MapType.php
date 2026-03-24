<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Guess;

use LogicException;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Generator\Context\Context;
use Override;
use PhpParser\Node\Expr;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;

class MapType extends ArrayType
{
    public function __construct(object $object, Type $itemType)
    {
        parent::__construct($object, $itemType, 'object');

        $this->itemType = $itemType;
    }

    #[Override]
    public function getTypeHint(string $namespace): Identifier
    {
        return new Identifier('array');
    }

    #[Override]
    public function getDocTypeHint(string $namespace): string|Name|null
    {
        $itemDoc = (string)$this->getItemType()->getDocTypeHint($namespace);

        return new Name(\sprintf('array<string, %s>', '' !== $itemDoc ? $itemDoc : 'mixed'));
    }

    #[Override]
    protected function createArrayValueStatement(): Expr
    {
        return new Expr\Array_();
    }

    #[Override]
    protected function createNormalizationArrayValueStatement(): Expr
    {
        return new Expr\Array_();
    }

    protected function createLoopKeyStatement(Context $context): Expr
    {
        return new Expr\Variable($context->getUniqueVariableName('key'));
    }

    #[Override]
    protected function createLoopOutputAssignement(Expr $valuesVar, ?Expr $loopKeyVar): Expr
    {
        if (!$loopKeyVar instanceof Expr) {
            throw new LogicException('MapType requires a loop key expression');
        }

        return new Expr\ArrayDimFetch($valuesVar, new Expr\Cast\String_($loopKeyVar));
    }

    #[Override]
    protected function createNormalizationLoopOutputAssignement(Expr $valuesVar, ?Expr $loopKeyVar): Expr
    {
        if (!$loopKeyVar instanceof Expr) {
            throw new LogicException('MapType requires a loop key expression');
        }

        return new Expr\ArrayDimFetch($valuesVar, new Expr\Cast\String_($loopKeyVar));
    }
}
