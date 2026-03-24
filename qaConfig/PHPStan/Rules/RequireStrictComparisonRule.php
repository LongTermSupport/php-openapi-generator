<?php

declare(strict_types=1);

namespace QaConfig\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Bans loose comparisons (== and !=) in favour of strict (=== and !==).
 *
 * Loose comparisons cause subtle type-juggling bugs — '0' == false, '' == 0, etc.
 * With declare(strict_types=1) already enforced project-wide, strict comparisons
 * are the natural complement to complete type safety.
 *
 * @implements Rule<BinaryOp>
 */
final class RequireStrictComparisonRule implements Rule
{
    public function getNodeType(): string
    {
        return BinaryOp::class;
    }

    /**
     * @return list<\PHPStan\Rules\IdentifierRuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if ($node instanceof BinaryOp\Equal) {
            return [
                RuleErrorBuilder::message(
                    'Loose comparison (==) is forbidden — use strict comparison (===) instead. '
                    . 'Loose comparisons cause type-juggling bugs (\'0\' == false, \'\' == 0, null == 0).'
                )
                    ->identifier('strictComparison.looseEqual')
                    ->build(),
            ];
        }

        if ($node instanceof BinaryOp\NotEqual) {
            return [
                RuleErrorBuilder::message(
                    'Loose comparison (!=) is forbidden — use strict comparison (!==) instead. '
                    . 'Loose comparisons cause type-juggling bugs (\'0\' != true is false).'
                )
                    ->identifier('strictComparison.looseNotEqual')
                    ->build(),
            ];
        }

        return [];
    }
}
