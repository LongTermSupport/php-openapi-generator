<?php

declare(strict_types=1);

namespace QaConfig\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Prevents generators from emitting assert() into generated PHP code.
 *
 * Catches the AST pattern: new Expr\FuncCall(new Name('assert'), ...)
 * This means a generator is building an assert() call that will appear in
 * generated output — where it will be compiled out in production.
 *
 * The fix: emit if/throw LogicException AST nodes instead.
 *
 * @implements Rule<Node\Expr\New_>
 */
final class ForbidGeneratedAssertRule implements Rule
{
    public function getNodeType(): string
    {
        return Node\Expr\New_::class;
    }

    /**
     * @return list<\PHPStan\Rules\IdentifierRuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        // We're looking for: new Expr\FuncCall(new Name('assert'), ...)
        // But from PHPStan's perspective, we see: new Name('assert')
        // Actually, we need to match `new Name('assert')` where it's used as
        // a FuncCall name argument. But PHPStan sees `new Name(...)` as a New_ node.

        // Match: new Name('assert') or new Name\FullyQualified('assert')
        if (!$node->class instanceof Name) {
            return [];
        }

        $className = $node->class->toString();

        // Check if constructing a PhpParser Name node
        if ($className !== 'Name'
            && $className !== 'PhpParser\\Node\\Name'
            && $className !== 'Node\\Name'
            && !str_ends_with($className, '\\Name')
        ) {
            return [];
        }

        // Check if the first constructor argument is the string 'assert'
        $args = $node->getArgs();
        if ($args === []) {
            return [];
        }

        $firstArg = $args[0]->value;
        if (!$firstArg instanceof Node\Scalar\String_ || $firstArg->value !== 'assert') {
            return [];
        }

        // Allow in test files
        $file = $scope->getFile();
        if (str_contains($file, '/Tests/') || str_contains($file, '/tests/')) {
            return [];
        }

        return [
            RuleErrorBuilder::message(
                'Generator emits assert() into generated code — assert() is compiled out in production. '
                . 'Emit if/throw \LogicException instead: '
                . 'new Stmt\If_(new Expr\BooleanNot(...), [\'stmts\' => [new Stmt\Expression(new Expr\Throw_(...))]])'
            )
                ->identifier('forbidGeneratedAssert.found')
                ->build(),
        ];
    }
}
