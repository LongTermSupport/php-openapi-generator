<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Generator\Model;

use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Generator\Naming;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Guess\ArrayType;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Guess\MapType;
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
        $propertyType = $property->getType();

        // Build the standard (non-variadic) param first — always well-defined
        $setType = $propertyType->getTypeHint($namespace);
        if (null === $setType) {
            $setType = new \PhpParser\Node\Identifier('mixed');
        } elseif (!$strict || $property->isNullable()) {
            $setType = Type::makeNullable($setType);
        }
        $param = new Param(new Expr\Variable($property->getPhpName()), null, $setType);

        // List type: ArrayType but NOT MapType (map has string keys, must preserve them).
        // All non-nullable strict list setters use variadic syntax so the normalizer can spread
        // the array. When the item type is known (e.g. string), use that as the type hint.
        // When unknown (mixed), use explicit 'mixed' to satisfy missingType.parameter and
        // phpqaci.arrayListShouldBeVariadic.
        if ($propertyType instanceof ArrayType && !($propertyType instanceof MapType) && $strict && !$property->isNullable()) {
            $itemTypeHint = $propertyType->getItemType()->getTypeHint($namespace) ?? new \PhpParser\Node\Identifier('mixed');
            $param        = new Param(new Expr\Variable($property->getPhpName()), null, $itemTypeHint, variadic: true);
        }

        // Use array_values() for all non-nullable strict list setters (variadic or plain array).
        // PHPStan sees variadic T ...$x as array<int|string, T> in the body; plain array is also unordered.
        // array_values() normalises both to list<T> to match the property's @var list<T> PHPDoc.
        // MapType properties must NOT use array_values() — it would destroy string keys.
        $useArrayValues = $propertyType instanceof ArrayType
            && !($propertyType instanceof MapType)
            && $strict
            && !$property->isNullable();
        $assignValue    = $useArrayValues
            ? new Expr\FuncCall(new Name('array_values'), [new \PhpParser\Node\Arg(new Expr\Variable($property->getPhpName()))])
            : new Expr\Variable($property->getPhpName());

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
                    $assignValue
                )
            ),
        ];

        if ($fluent) {
            $stmts[] = new Stmt\Return_(new Expr\Variable('this'));
        }

        $methodName = $this->getNaming()->getPrefixedMethodName('set', $property->getAccessorName());
        $methodName = $this->getNaming()->getReservedSafeMethodName($methodName, $extendsArrayObject);

        $attributes = [];
        $doc        = $this->createSetterDoc($property, $namespace, $strict);
        if (null !== $doc) {
            $attributes['comments'] = [$doc];
        }

        return new Stmt\ClassMethod(
            $methodName,
            [
                'flags'      => Modifiers::PUBLIC,
                'params'     => [$param],
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

        $sections = [];
        if ($hasDescription) {
            $descriptionLines = [];
            foreach (array_map(rtrim(...), explode("\n", $property->getDescription())) as $line) {
                $descriptionLines[] = ' * ' . $line;
            }

            $sections[] = $descriptionLines;
        }

        if ($isDeprecated) {
            $sections[] = [' * @deprecated'];
        }

        if ($docAddsTypeInfo) {
            $sections[] = [\sprintf(
                ' * @return %s',
                $this->getDocType($property, $namespace, $strict)
            )];
        }

        return new Doc($this->formatDocBlock(...$sections));
    }

    protected function createSetterDoc(Property $property, string $namespace, bool $strict): ?Doc
    {
        $hasDescription  = (bool)$property->getDescription();
        $isDeprecated    = $property->isDeprecated();
        $docAddsTypeInfo = $this->docTypeAddsInfo($property, $namespace, $strict);
        $propType        = $property->getType();
        $isVariadicList  = $propType instanceof ArrayType
            && !($propType instanceof MapType)
            && $strict
            && !$property->isNullable();

        // For variadic list setters, @param must use the item type (not the full list type)
        // with "..." notation. Emit it when the item doc type is more specific than its native type.
        // Example: list<array<string, mixed>> — native item type is `array`, doc is `array<string, mixed>`.
        // The @param narrows $steps to list<array<string, mixed>> in the body so array_values() passes.
        $variadicItemDocType = null;
        if ($isVariadicList && $propType instanceof ArrayType) {
            $itemType       = $propType->getItemType();
            $itemNativeHint = $itemType->getTypeHint($namespace);
            $itemDocHint    = (string) $itemType->getDocTypeHint($namespace);
            $itemNativeStr  = Type::typeHintToString($itemNativeHint);
            if ('' !== $itemDocHint && $itemDocHint !== $itemNativeStr) {
                $variadicItemDocType = $itemDocHint;
            }
        }

        $emitsParam = ($docAddsTypeInfo && !$isVariadicList) || null !== $variadicItemDocType;

        if (!$hasDescription && !$isDeprecated && !$emitsParam) {
            return null;
        }

        $sections = [];
        if ($hasDescription) {
            $descriptionLines = [];
            foreach (array_map(rtrim(...), explode("\n", $property->getDescription())) as $line) {
                $descriptionLines[] = ' * ' . $line;
            }

            $sections[] = $descriptionLines;
        }

        if ($emitsParam) {
            if (null !== $variadicItemDocType) {
                // Variadic list setter: annotate the item type with ... so PHPStan narrows the body
                $sections[] = [\sprintf(' * @param %s ...%s', $variadicItemDocType, '$' . $property->getPhpName())];
            } else {
                $sections[] = [\sprintf(' * @param %s %s', $this->getDocType($property, $namespace, $strict), '$' . $property->getPhpName())];
            }
        }

        if ($isDeprecated) {
            $sections[] = [' * @deprecated'];
        }

        // NOTE: no `@return self` — the native `: self` return type expresses this already.
        // PHPDoc `@return` should only appear when it adds type info beyond the native hint.

        return new Doc($this->formatDocBlock(...$sections));
    }

    /**
     * Assemble docblock sections into a PHPDoc comment, separating sections with a single empty ` *` line.
     *
     * @param list<string> ...$sections
     */
    private function formatDocBlock(array ...$sections): string
    {
        $lines = ['/**'];
        foreach ($sections as $index => $section) {
            if ($index > 0) {
                $lines[] = ' *';
            }

            foreach ($section as $line) {
                $lines[] = $line;
            }
        }

        $lines[] = ' */';

        return implode("\n", $lines);
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
