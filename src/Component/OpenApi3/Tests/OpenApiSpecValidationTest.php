<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApi3\Tests;

use LongTermSupport\StrictOpenApiValidator\Exception\InvalidSpecVersionException;
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
        try {
            Spec::createFromFile($specPath);
        } catch (InvalidSpecVersionException $invalidSpecVersionException) {
            $this->markTestIncomplete(
                \sprintf(
                    'Spec at %s is not OpenAPI 3.1.x (version error: %s). Needs upgrading.',
                    $specPath,
                    $invalidSpecVersionException->getMessage()
                )
            );
        }

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
