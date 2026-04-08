<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Generator\Model;

use LogicException;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Generator\Naming;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Guess\Property;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Guess\Type;
use PhpParser\Comment\Doc;
use PhpParser\Modifiers;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;
use PhpParser\Parser;

trait PropertyGenerator
{
    /**
     * The naming service.
     */
    abstract protected function getNaming(): Naming;

    /**
     * The PHP Parser.
     */
    abstract protected function getParser(): Parser;

    protected function createProperty(Property $property, string $namespace, mixed $default = null, bool $strict = true): Stmt
    {
        $propertyName = $property->getPhpName();
        $propertyStmt = new Stmt\PropertyProperty($propertyName);

        if (null === $default) {
            $default = $property->getDefault();
        }

        if (\is_scalar($default) || (Type::TYPE_ARRAY === Type::typeHintToString($property->getType()->getTypeHint($namespace)) && \is_array($default))) {
            $propertyStmt->default = $this->getDefaultAsExpr($default)->expr;
        }

        $nativeType = $property->getType()->getTypeHint($namespace);
        if (null === $nativeType) {
            // PHP 8.0+: use `mixed` when the type system cannot determine a specific native type
            // (e.g. JSON Schema "object" without properties, union types, null-only types)
            $nativeType = new Node\Identifier('mixed');
        } elseif (!$strict || $property->isNullable()) {
            $nativeType = Type::makeNullable($nativeType);
        }

        // Default nullable typed properties to null when no other default has been set.
        // PHP requires typed properties to be explicitly initialized; reading an
        // uninitialized typed property throws "Typed property must not be accessed
        // before initialization". The generated normalizer reads properties via
        // their getter before checking isInitialized(), so the property MUST have
        // a safe default value to be readable on a freshly-constructed model.
        //
        // The isInitialized() tracking remains independent of the value: setters
        // record explicit set/unset state in the $initialized array.
        $isMixedNativeType = $nativeType instanceof Node\Identifier && 'mixed' === $nativeType->toString();
        $alreadyHasDefault = $propertyStmt->default instanceof Expr;
        $typeIsNullable    = $isMixedNativeType
            || ($nativeType instanceof Node\NullableType)
            || ($nativeType instanceof Node\UnionType && $this->unionTypeContainsNull($nativeType));
        if (!$alreadyHasDefault && $typeIsNullable) {
            $propertyStmt->default = new Expr\ConstFetch(new Name('null'));
        }

        $attributes = [];
        $isMixed    = $nativeType instanceof Node\Identifier && 'mixed' === $nativeType->toString();
        $doc        = $this->createPropertyDoc($property, $namespace, $strict, !$isMixed);
        if (null !== $doc) {
            $attributes['comments'] = [$doc];
        }

        $propertyNode = new Stmt\Property(Modifiers::PROTECTED, [
            $propertyStmt,
        ], $attributes);

        $propertyNode->type = $nativeType;

        return $propertyNode;
    }

    /**
     * Create PHPDoc for a property — only when it adds value beyond the native type.
     *
     * Returns null when native type is sufficient (no description, no deprecation,
     * and PHPDoc type matches native type exactly).
     */
    protected function createPropertyDoc(Property $property, string $namespace, bool $strict, bool $hasNativeType): ?Doc
    {
        $docTypeHint = $property->getType()->getDocTypeHint($namespace);

        // When doc type is empty but native type is array, use array<mixed> to satisfy missingType.iterableValue
        $nativeType    = $property->getType()->getTypeHint($namespace);
        $nativeTypeStr = Type::typeHintToString($nativeType);
        if ('' === (string)$docTypeHint && 'array' === $nativeTypeStr) {
            $docTypeHint = 'array<mixed>';
        }

        if ('' !== (string)$docTypeHint && (!$strict || $property->isNullable()) && !str_contains((string)$docTypeHint, 'null')) {
            $docTypeHint .= '|null';
        }

        $hasDescription = (bool)$property->getDescription();
        $isDeprecated   = $property->isDeprecated();

        // Check if PHPDoc type adds information beyond native type
        $docTypeStr = (string)$docTypeHint;

        // Strip |null for comparison — native type handles nullability via ?Type
        $docTypeStrWithoutNull = str_replace('|null', '', $docTypeStr);
        $docTypeStrWithoutNull = str_replace('null|', '', $docTypeStrWithoutNull);

        $docAddsTypeInfo = $hasNativeType && $docTypeStrWithoutNull !== $nativeTypeStr;

        // Only include @var for array shapes — PHP can't express generic array types natively.
        // All other property types must use native PHP type declarations, not @var.
        $isArrayShape = str_contains($docTypeStr, '[]')
            || 1 === \Safe\preg_match('/(?:array|list)\s*[<{(]/', $docTypeStr);
        $includeVar = $isArrayShape && (!$hasNativeType || $docAddsTypeInfo);

        // Skip doc entirely if it would contain nothing useful
        if (!$hasDescription && !$isDeprecated && !$includeVar) {
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

        if ($includeVar) {
            $sections[] = [\sprintf(' * @var %s', $docTypeHint)];
        }

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

        return new Doc(implode("\n", $lines));
    }

    private function getDefaultAsExpr(mixed $value): Stmt\Expression
    {
        $parsed     = $this->parser->parse('<?php ' . var_export($value, true) . ';');
        $expression = ($parsed ?? [])[0];
        if (!$expression instanceof Stmt\Expression) {
            throw new LogicException('Expected Stmt\Expression, got ' . get_debug_type($expression));
        }

        return $expression;
    }

    private function unionTypeContainsNull(Node\UnionType $unionType): bool
    {
        return array_any($unionType->types, static fn ($type): bool => $type instanceof Node\Identifier && 'null' === strtolower($type->toString()));
    }
}
