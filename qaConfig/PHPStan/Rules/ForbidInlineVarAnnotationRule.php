<?php

declare(strict_types=1);

namespace QaConfig\PHPStan\Rules;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Enforces two rules about @var annotations:
 *
 * 1. BANS inline @var that force object/scalar types — use type guards instead.
 * 2. REQUIRES @var on properties typed as `array` — PHP can't express generics
 *    natively, so every `array` property must have a @var documenting its shape.
 *
 * Exceptions:
 *   - Property @var with array generics (array<...>, list<...>, array{...}, Type[]) — allowed
 *   - JsonSchema/Normalizer and JsonSchema/Model files — auto-generated, excluded
 *
 * @implements Rule<Node\Stmt>
 */
final class ForbidInlineVarAnnotationRule implements Rule
{
    public function getNodeType(): string
    {
        return Node\Stmt::class;
    }

    /**
     * @return list<\PHPStan\Rules\IdentifierRuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        // For class properties: two complementary rules.
        if ($node instanceof Node\Stmt\Property) {
            $docComment = $node->getDocComment();
            $hasVar = $docComment !== null && preg_match('/@var\b/', $docComment->getText()) === 1;
            $hasArrayGenericVar = $hasVar && $this->containsArrayGeneric($docComment->getText());

            // Rule 1: If @var exists, it must contain array generics — no scalar/object @var.
            if ($hasVar && !$hasArrayGenericVar) {
                return [
                    RuleErrorBuilder::message(
                        'Property @var annotation is forbidden for non-array types — use native PHP type declarations instead. '
                        . 'Array shape @var (array{...}, array<...>, list<...>, Type[]) is allowed on properties.'
                    )
                        ->identifier('forbidInlineVar.property')
                        ->build(),
                ];
            }

            // Rule 2: If property has `array` in its native type, it MUST have a @var
            // documenting the array shape. Bare `array` hides what it contains.
            if (!$hasArrayGenericVar && $this->propertyHasArrayType($node)) {
                return [
                    RuleErrorBuilder::message(
                        'Property typed as `array` must have a @var annotation documenting its shape '
                        . '(e.g. array<string, mixed>, array{key: Type}, list<Type>). '
                        . 'Bare `array` without @var hides the structure from static analysis.'
                    )
                        ->identifier('forbidInlineVar.missingArrayShape')
                        ->build(),
                ];
            }

            return [];
        }

        // Exclude bootstrap normalizer files — auto-generated infrastructure code that
        // parses JSON Schema and OpenAPI3 specs. These will be regenerated once the
        // generator itself is fully migrated.
        $file = $scope->getFile();
        if (str_contains($file, 'JsonSchema/Normalizer/') || str_contains($file, 'JsonSchema/Model/')) {
            return [];
        }

        $docComment = $node->getDocComment();
        if ($docComment === null) {
            return [];
        }

        if (preg_match('/@var\b/', $docComment->getText()) !== 1) {
            return [];
        }

        // Allow @var when any type in the union is an array generic — these document
        // structure not expressible in native PHP types.
        if ($this->containsArrayGeneric($docComment->getText())) {
            return [];
        }

        return [
            RuleErrorBuilder::message(
                'Inline @var annotation is forbidden — it forces types without runtime verification. '
                . 'Use a type guard instead: if (!$x instanceof Expected) { throw new \LogicException(...); }. '
                . 'Array shape @var (array{...}, array<...>, list<...>) is allowed.'
            )
                ->identifier('forbidInlineVar.found')
                ->build(),
        ];
    }

    /**
     * Check if a property's native PHP type includes `array`.
     *
     * Handles: `array`, `?array`, `array|null`, `string|array|null`, etc.
     */
    private function propertyHasArrayType(Node\Stmt\Property $node): bool
    {
        $type = $node->type;
        if ($type === null) {
            return false;
        }
        return $this->typeContainsArray($type);
    }

    private function typeContainsArray(Node $type): bool
    {
        if ($type instanceof Node\Identifier) {
            return $type->toString() === 'array';
        }
        if ($type instanceof Node\Name) {
            return false;
        }
        if ($type instanceof Node\NullableType) {
            return $this->typeContainsArray($type->type);
        }
        if ($type instanceof Node\UnionType || $type instanceof Node\IntersectionType) {
            foreach ($type->types as $subType) {
                if ($this->typeContainsArray($subType)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Check if a docblock @var contains any array generic type anywhere in the union.
     *
     * Matches: array<...>, list<...>, array{...}, Type[]
     */
    private function containsArrayGeneric(string $docText): bool
    {
        // array<...> or list<...>
        if (preg_match('/@var\b.*(?:array|list)\s*[<{]/', $docText) === 1) {
            return true;
        }
        // Type[] notation
        if (preg_match('/@var\b.*\w\[\]/', $docText) === 1) {
            return true;
        }
        return false;
    }
}
