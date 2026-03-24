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
 * Bans assert() calls in production source code.
 *
 * PHP's assert() is compiled out when zend.assertions=-1 (production default),
 * making it invisible at runtime. Type guards with if/throw LogicException are
 * production-safe and visible to PHPStan for type narrowing.
 *
 * Allowed in: test files (Tests/ directories)
 *
 * @implements Rule<FuncCall>
 */
final class ForbidAssertCallRule implements Rule
{
    public function getNodeType(): string
    {
        return FuncCall::class;
    }

    /**
     * @return list<\PHPStan\Rules\IdentifierRuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node->name instanceof Name) {
            return [];
        }

        if ($node->name->toLowerString() !== 'assert') {
            return [];
        }

        // Allow assert() in test files
        $file = $scope->getFile();
        if (str_contains($file, '/Tests/') || str_contains($file, '/tests/')) {
            return [];
        }

        return [
            RuleErrorBuilder::message(
                'assert() is forbidden in production code — it is compiled out when zend.assertions=-1. '
                . 'Use a type guard instead: if (!$x instanceof Expected) { throw new \LogicException(...); }'
            )
                ->identifier('forbidAssert.found')
                ->build(),
        ];
    }
}
