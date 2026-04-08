<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Generator\Model;

use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Generator\Naming;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Guess\MultipleType;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Guess\Property;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Guess\Type;
use PhpParser\Comment\Doc;
use PhpParser\Modifiers;
use PhpParser\Node\Expr;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar;
use PhpParser\Node\Stmt;

trait GetterSetterGenerator
{
    /**
     * The naming service.
     */
    abstract protected function getNaming(): Naming;

    protected function createGetter(Property $property, string $namespace, bool $strict, bool $extendsArrayObject = false): Stmt\ClassMethod
    {
        $returnType = $property->getType()->getTypeHint($namespace);

        if (null === $returnType) {
            $returnType = new \PhpParser\Node\Identifier('mixed');
        } elseif (!$strict || $property->isNullable()) {
            $returnType = Type::makeNullable($returnType);
        }

        $methodName = $this->getNaming()->getPrefixedMethodName('get', $property->getAccessorName());
        $methodName = $this->getNaming()->getReservedSafeMethodName($methodName, $extendsArrayObject);

        $attributes = [];
        $doc        = $this->createGetterDoc($property, $namespace, $strict);
        if (null !== $doc) {
            $attributes['comments'] = [$doc];
        }

        return new Stmt\ClassMethod(
            $methodName,
            [
                'flags'      => Modifiers::PUBLIC,
                'stmts'      => [
                    new Stmt\Return_(
                        new Expr\PropertyFetch(new Expr\Variable('this'), $property->getPhpName())
                    ),
                ],
                'returnType' => $returnType,
            ],
            $attributes
        );
    }

    protected function createSetter(Property $property, string $namespace, bool $strict, bool $fluent = true, bool $extendsArrayObject = false): Stmt\ClassMethod
    {
        $setType = $property->getType()->getTypeHint($namespace);

        if (null === $setType) {
            $setType = new \PhpParser\Node\Identifier('mixed');
        } elseif (!$strict || $property->isNullable()) {
            $setType = Type::makeNullable($setType);
        }

        $stmts = [
            new Stmt\Expression(new Expr\Assign(
                new Expr\ArrayDimFetch(new Expr\PropertyFetch(new Expr\Variable('this'), 'initialized'), new Scalar\String_($property->getPhpName())),
                new Expr\ConstFetch(new Name('true'))
            )),
            new Stmt\Expression(
                new Expr\Assign(
                    new Expr\PropertyFetch(
                        new Expr\Variable('this'),
                        $property->getPhpName()
                    ),
                    new Expr\Variable($property->getPhpName())
                )
            ),
        ];

        if ($fluent) {
            $stmts[] = new Stmt\Return_(new Expr\Variable('this'));
        }

        $methodName = $this->getNaming()->getPrefixedMethodName('set', $property->getAccessorName());
        $methodName = $this->getNaming()->getReservedSafeMethodName($methodName, $extendsArrayObject);

        $attributes = [];
        $doc        = $this->createSetterDoc($property, $namespace, $strict, $fluent);
        if (null !== $doc) {
            $attributes['comments'] = [$doc];
        }

        return new Stmt\ClassMethod(
            $methodName,
            [
                'flags'      => Modifiers::PUBLIC,
                'params'     => [
                    new Param(
                        new Expr\Variable($property->getPhpName()),
                        null,
                        $setType
                    ),
                ],
                'stmts'      => $stmts,
                'returnType' => $fluent ? new Name('self') : new \PhpParser\Node\Identifier('void'),
            ],
            $attributes
        );
    }

    protected function createGetterDoc(Property $property, string $namespace, bool $strict): ?Doc
    {
        $hasDescription  = (bool)$property->getDescription();
        $isDeprecated    = $property->isDeprecated();
        $docAddsTypeInfo = $this->docTypeAddsInfo($property, $namespace, $strict);

        if (!$hasDescription && !$isDeprecated && !$docAddsTypeInfo) {
            return null;
        }

        $description = ['/**'];
        if ($hasDescription) {
            foreach (array_map(rtrim(...), explode("\n", $property->getDescription())) as $line) {
                $description[] = ' * ' . $line;
            }

            $description[] = ' *';
        }

        if ($isDeprecated) {
            $description[] = ' * @deprecated';
            $description[] = ' *';
        }

        if ($docAddsTypeInfo) {
            $description[] = \sprintf(
                ' * @return %s',
                $this->getDocType($property, $namespace, $strict)
            );
        }

        $description[] = ' */';

        return new Doc(implode("\n", $description));
    }

    protected function createSetterDoc(Property $property, string $namespace, bool $strict, bool $fluent): ?Doc
    {
        $hasDescription  = (bool)$property->getDescription();
        $isDeprecated    = $property->isDeprecated();
        $docAddsTypeInfo = $this->docTypeAddsInfo($property, $namespace, $strict);

        if (!$hasDescription && !$isDeprecated && !$docAddsTypeInfo) {
            return null;
        }

        $description = ['/**'];
        if ($hasDescription) {
            $description[] = ' * ' . $property->getDescription();
            $description[] = ' *';
        }

        if ($docAddsTypeInfo) {
            $description[] = \sprintf(' * @param %s %s', $this->getDocType($property, $namespace, $strict), '$' . $property->getPhpName());
        }

        if ($isDeprecated) {
            $description[] = ' *';
            $description[] = ' * @deprecated';
        }

        if ($fluent && ($docAddsTypeInfo || $hasDescription || $isDeprecated)) {
            $description[] = ' *';
            $description[] = ' * @return self';
        }

        $description[] = ' */';

        return new Doc(implode("\n", $description));
    }

    /**
     * Check whether the PHPDoc type adds information beyond what the native type expresses.
     */
    private function docTypeAddsInfo(Property $property, string $namespace, bool $strict): bool
    {
        $docTypeStr = $this->getDocType($property, $namespace, $strict);

        // Empty doc type adds nothing
        if ('' === $docTypeStr || '|null' === $docTypeStr) {
            return false;
        }

        $nativeType = $property->getType()->getTypeHint($namespace);
        if (null === $nativeType) {
            // Native type is mixed — doc type always adds info here (non-empty after early return)
            return true;
        }

        $nativeTypeStr = Type::typeHintToString($nativeType);

        // Strip null from doc type for comparison — native handles nullability via ?Type
        $docTypeStrWithoutNull = str_replace('|null', '', $docTypeStr);
        $docTypeStrWithoutNull = str_replace('null|', '', $docTypeStrWithoutNull);

        return $docTypeStrWithoutNull !== $nativeTypeStr;
    }

    private function getDocType(Property $property, string $namespace, bool $strict): string
    {
        $returnType     = $property->getType();
        $returnTypeHint = $returnType->getDocTypeHint($namespace);

        // When doc type is empty but native type is array, use array<mixed> to satisfy missingType.iterableValue
        if ('' === (string)$returnTypeHint) {
            $nativeType = $returnType->getTypeHint($namespace);
            if (null !== $nativeType && 'array' === Type::typeHintToString($nativeType)) {
                $returnTypeHint = 'array<mixed>';
            }
        }

        if ($strict && !$property->isNullable()) {
            return (string)$returnTypeHint;
        }

        $returnTypes = [$returnType];
        if ($returnType instanceof MultipleType) {
            $returnTypes = $returnType->getTypes();
        }

        if ('' !== (string)$returnTypeHint && [] === array_intersect([Type::TYPE_MIXED, Type::TYPE_NULL], $returnTypes)) {
            $returnTypeHint .= '|' . Type::TYPE_NULL;
        }

        return (string)$returnTypeHint;
    }
}
