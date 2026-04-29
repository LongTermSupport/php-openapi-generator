<?php

declare(strict_types=1);

namespace QaConfig\PHPStan\Rules;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Asserts every generated SDK file (any fixture's expected/ output) imports
 * only standalone runtime dependencies — never a generator-internal namespace.
 *
 * The whole point of this generator is to produce a fully self-contained SDK
 * that runs in consumer projects without requiring `lts/php-openapi-generator`
 * at runtime. Per-consumer Runtime classes (CheckArray, TypeValidator,
 * CustomQueryResolver, AuthenticationPlugin, etc.) are emitted into the
 * consumer's chosen root namespace, never imported back from
 * `LongTermSupport\OpenApiGenerator\Component\OpenApiRuntime\…` or any other
 * generator-internal namespace.
 *
 * Allowed in expected/ output: any import OUTSIDE the generator's own
 * namespace (PSR, Symfony, php-http, native PHP), OR an intra-fixture import
 * starting with `LongTermSupport\OpenApiGenerator\Component\OpenApi3\Tests\Expected\`
 * (each fixture's own root namespace — analogous to a real consumer's
 * `Acme\MyClient\…`).
 *
 * Forbidden: any import starting with `LongTermSupport\OpenApiGenerator\` that
 * is NOT under the fixture-Expected sub-tree. Such an import would mean the
 * generator is emitting code coupled to its own runtime — exactly the
 * regression this rule prevents.
 *
 * @implements Rule<Node\Stmt\Use_>
 */
final class ForbidGeneratorImportInOutputRule implements Rule
{
    private const string GENERATOR_NS = 'LongTermSupport\\OpenApiGenerator\\';

    private const string FIXTURE_OUTPUT_NS = 'LongTermSupport\\OpenApiGenerator\\Component\\OpenApi3\\Tests\\Expected\\';

    public function getNodeType(): string
    {
        return Node\Stmt\Use_::class;
    }

    /**
     * @return list<\PHPStan\Rules\IdentifierRuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (!$this->isFixtureExpectedOutput($scope->getFile())) {
            return [];
        }

        $errors = [];
        foreach ($node->uses as $use) {
            $fqn = $use->name->toString();
            if (!str_starts_with($fqn, self::GENERATOR_NS)) {
                continue;
            }
            if (str_starts_with($fqn, self::FIXTURE_OUTPUT_NS)) {
                continue;
            }
            $errors[] = RuleErrorBuilder::message(sprintf(
                "Generated SDK file imports generator-internal class '%s'. Generated code must be standalone — fix the generator template/printer to emit a per-consumer copy under the consumer's root namespace, then regenerate the affected fixtures.",
                $fqn,
            ))
                ->identifier('forbidGeneratorImport.found')
                ->build();
        }

        return $errors;
    }

    private function isFixtureExpectedOutput(string $file): bool
    {
        return str_contains($file, '/Tests/fixtures/') && str_contains($file, '/expected/');
    }
}
