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

        if ($includeVar) {
            $description[] = \sprintf(' * @var %s', $docTypeHint);
        }

        $description[] = ' */';

        return new Doc(implode("\n", $description));
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
}
