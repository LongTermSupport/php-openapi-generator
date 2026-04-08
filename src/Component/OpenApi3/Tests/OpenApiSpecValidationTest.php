<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApi3\Tests;

use LongTermSupport\StrictOpenApiValidator\Spec;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\Finder;

/**
 * Validates OpenAPI spec files in test fixtures using strict-openapi-validator.
 *
 * Fixtures at 3.0.x are marked incomplete (they need upgrading to 3.1.x).
 * Fixtures at 3.1.x are strictly validated — any failures are real bugs.
 *
 * @see https://github.com/LongTermSupport/strict-openapi-validator
 *
 * @internal
 */
class OpenApiSpecValidationTest extends TestCase
{
    #[DataProvider('jsonSpecProvider')]
    public function testSpecIsValid(string $specPath): void
    {
        // Pre-check version to skip 3.0.x specs cleanly — the validator throws
        // InvalidSpecVersionException on 3.0.x, but we don't want to rely on catching it:
        // doing the version check up-front is clearer and more explicit.
        $raw    = \Safe\file_get_contents($specPath);
        $parsed = \Safe\json_decode($raw, true);
        if (!\is_array($parsed)) {
            self::fail(\sprintf('Spec at %s did not decode to an array', $specPath));
        }

        $version = $parsed['openapi'] ?? null;
        if (\is_string($version) && !str_starts_with($version, '3.1.')) {
            self::markTestIncomplete(
                \sprintf('Spec at %s is OpenAPI %s, not 3.1.x. Needs upgrading.', $specPath, $version)
            );
        }

        Spec::createFromFile($specPath);

        // If no exception thrown, spec is valid — assertion for PHPUnit
        $this->addToAssertionCount(1);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function jsonSpecProvider(): array
    {
        $finder = new Finder();
        $finder->files()
            ->name('*.json')
            ->in(__DIR__ . '/fixtures')
            ->exclude(['generated', 'expected', 'fixture-boilerplate', 'client'])
        ;

        $data = [];
        foreach ($finder as $file) {
            $relativePath        = $file->getRelativePathname();
            $data[$relativePath] = [$file->getRealPath()];
        }

        return $data;
    }
}
