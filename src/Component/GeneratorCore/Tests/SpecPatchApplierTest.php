<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Tests;

use InvalidArgumentException;
use LogicException;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\SpecBaseChangedException;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\SpecPatchApplier;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/** @internal */
final class SpecPatchApplierTest extends TestCase
{
    private SpecPatchApplier $applier;

    private string $tempDir;

    protected function setUp(): void
    {
        $this->applier = new SpecPatchApplier();
        $this->tempDir = sys_get_temp_dir() . '/spec-patch-applier-test-' . uniqid('', true);
        \Safe\mkdir($this->tempDir, 0o755, true);
    }

    protected function tearDown(): void
    {
        $files = \Safe\glob($this->tempDir . '/*');
        foreach ($files as $file) {
            if (!\is_string($file)) {
                throw new LogicException('Expected string filename from glob');
            }

            \Safe\unlink($file);
        }

        \Safe\rmdir($this->tempDir);
    }

    // ---------------------------------------------------------------------------
    // No-patch-file path
    // ---------------------------------------------------------------------------

    #[Test]
    public function noPatchFileReturnsOrigSpecUnchanged(): void
    {
        $spec     = ['info' => ['title' => 'My API'], 'paths' => []];
        $origPath = $this->writeOrigSpec($spec);
        $noPath   = $this->tempDir . '/nonexistent.json.patch';

        $result = $this->applier->apply($origPath, $noPath);

        self::assertSame($spec, $result);
    }

    // ---------------------------------------------------------------------------
    // Hash verification
    // ---------------------------------------------------------------------------

    #[Test]
    public function appliesPatchWhenHashMatches(): void
    {
        $spec      = ['info' => ['title' => 'Original']];
        $origPath  = $this->writeOrigSpec($spec);
        $patchPath = $this->writePatch($origPath, [
            ['op' => 'replace', 'path' => '/info/title', 'value' => 'Patched'],
        ]);

        $result = $this->applier->apply($origPath, $patchPath);

        /** @var array<string, mixed> $info */
        $info = $result['info'];
        self::assertSame('Patched', $info['title']);
    }

    #[Test]
    public function throwsSpecBaseChangedExceptionWhenHashMismatches(): void
    {
        $spec     = ['info' => ['title' => 'Original']];
        $origPath = $this->writeOrigSpec($spec);

        // Write patch with a deliberately wrong hash
        $patchData = [
            'x-base-sha256' => 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
            'patches'       => [],
        ];
        $patchPath = $this->tempDir . '/spec.json.patch';
        \Safe\file_put_contents($patchPath, \Safe\json_encode($patchData));

        $this->expectException(SpecBaseChangedException::class);
        $this->expectExceptionMessageMatches('/Expected SHA-256.*aaaaaa/s');
        $this->expectExceptionMessageMatches('/Actual SHA-256/s');

        $this->applier->apply($origPath, $patchPath);
    }

    #[Test]
    public function exceptionMessageNamesTheSpecAndIncludesActionableInstructions(): void
    {
        $spec     = ['openapi' => '3.1.0'];
        $origPath = $this->writeOrigSpec($spec);

        $patchPath = $this->tempDir . '/spec.json.patch';
        \Safe\file_put_contents($patchPath, \Safe\json_encode([
            'x-base-sha256' => 'deadbeef',
            'patches'       => [],
        ]));

        try {
            $this->applier->apply($origPath, $patchPath);
            self::fail('Expected SpecBaseChangedException');
        } catch (SpecBaseChangedException $specBaseChangedException) {
            self::assertStringContainsString('spec.json', $specBaseChangedException->getMessage());
            self::assertStringContainsString('git diff', $specBaseChangedException->getMessage());
            self::assertStringContainsString('x-base-sha256', $specBaseChangedException->getMessage());
        }
    }

    #[Test]
    public function throwsInvalidArgumentExceptionWhenXBaseSha256KeyIsMissing(): void
    {
        $spec     = ['info' => ['title' => 'Test']];
        $origPath = $this->writeOrigSpec($spec);

        $patchPath = $this->tempDir . '/spec.json.patch';
        \Safe\file_put_contents($patchPath, \Safe\json_encode(['patches' => []]));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/x-base-sha256/');

        $this->applier->apply($origPath, $patchPath);
    }

    // ---------------------------------------------------------------------------
    // RFC 6902 — add operation
    // ---------------------------------------------------------------------------

    #[Test]
    public function addOperationAddsNewLeafKey(): void
    {
        $spec      = ['info' => ['title' => 'Test']];
        $origPath  = $this->writeOrigSpec($spec);
        $patchPath = $this->writePatch($origPath, [
            ['op' => 'add', 'path' => '/info/version', 'value' => '1.0.0'],
        ]);

        $result = $this->applier->apply($origPath, $patchPath);

        /** @var array<string, mixed> $info */
        $info = $result['info'];
        self::assertSame('1.0.0', $info['version']);
        self::assertSame('Test', $info['title']);
    }

    #[Test]
    public function addOperationAppendsToArrayWithDashPointer(): void
    {
        $spec = [
            'paths' => [
                '/test' => [
                    'get' => [
                        'parameters' => [
                            ['name' => 'limit', 'in' => 'query'],
                        ],
                    ],
                ],
            ],
        ];
        $origPath  = $this->writeOrigSpec($spec);
        $patchPath = $this->writePatch($origPath, [
            [
                'op'    => 'add',
                'path'  => '/paths/~1test/get/parameters/-',
                'value' => ['name' => 'status', 'in' => 'query'],
            ],
        ]);

        $result = $this->applier->apply($origPath, $patchPath);

        /** @var array<string, mixed> $paths */
        $paths = $result['paths'];
        /** @var array<string, mixed> $testPath */
        $testPath = $paths['/test'];
        /** @var array<string, mixed> $get */
        $get    = $testPath['get'];
        $params = $get['parameters'];
        self::assertIsArray($params);
        self::assertCount(2, $params);
        /** @var array<string, mixed> $second */
        $second = $params[1];
        self::assertSame('status', $second['name']);
    }

    #[Test]
    public function addOperationCreatesNestedKeyAtDepth(): void
    {
        $spec      = ['paths' => ['/test' => ['get' => ['responses' => []]]]];
        $origPath  = $this->writeOrigSpec($spec);
        $patchPath = $this->writePatch($origPath, [
            [
                'op'    => 'add',
                'path'  => '/paths/~1test/get/responses/204',
                'value' => ['description' => 'No Content'],
            ],
        ]);

        $result = $this->applier->apply($origPath, $patchPath);

        /** @var array<string, mixed> $paths */
        $paths = $result['paths'];
        /** @var array<string, mixed> $testPath */
        $testPath = $paths['/test'];
        /** @var array<string, mixed> $get */
        $get = $testPath['get'];
        /** @var array<int|string, mixed> $responses */
        $responses = $get['responses'];
        // JSON response codes like '204' are stored as integer keys in PHP
        /** @var array<string, mixed> $response204 */
        $response204 = $responses[204];
        self::assertSame('No Content', $response204['description']);
    }

    // ---------------------------------------------------------------------------
    // RFC 6902 — replace operation
    // ---------------------------------------------------------------------------

    #[Test]
    public function replaceOperationChangesExistingLeafValue(): void
    {
        $spec      = ['info' => ['title' => 'Old Title', 'version' => '1']];
        $origPath  = $this->writeOrigSpec($spec);
        $patchPath = $this->writePatch($origPath, [
            ['op' => 'replace', 'path' => '/info/title', 'value' => 'New Title'],
        ]);

        $result = $this->applier->apply($origPath, $patchPath);

        /** @var array<string, mixed> $info */
        $info = $result['info'];
        self::assertSame('New Title', $info['title']);
        self::assertSame('1', $info['version']);
    }

    #[Test]
    public function replaceOperationCanReplaceAnObject(): void
    {
        $spec      = ['paths' => ['/test' => ['get' => ['responses' => ['200' => ['description' => 'OK']]]]]];
        $origPath  = $this->writeOrigSpec($spec);
        $patchPath = $this->writePatch($origPath, [
            [
                'op'    => 'replace',
                'path'  => '/paths/~1test/get/responses/200',
                'value' => ['description' => 'Success', 'content' => []],
            ],
        ]);

        $result = $this->applier->apply($origPath, $patchPath);

        /** @var array<string, mixed> $paths */
        $paths = $result['paths'];
        /** @var array<string, mixed> $testPath */
        $testPath = $paths['/test'];
        /** @var array<string, mixed> $get */
        $get = $testPath['get'];
        /** @var array<int|string, mixed> $responses */
        $responses = $get['responses'];
        // JSON response codes like '200' are stored as integer keys in PHP
        /** @var array<string, mixed> $resp200 */
        $resp200 = $responses[200];
        self::assertSame('Success', $resp200['description']);
        self::assertArrayHasKey('content', $resp200);
    }

    // ---------------------------------------------------------------------------
    // RFC 6902 — remove operation
    // ---------------------------------------------------------------------------

    #[Test]
    public function removeOperationDeletesExistingKey(): void
    {
        $spec      = ['info' => ['title' => 'Test', 'x-internal' => true]];
        $origPath  = $this->writeOrigSpec($spec);
        $patchPath = $this->writePatch($origPath, [
            ['op' => 'remove', 'path' => '/info/x-internal'],
        ]);

        $result = $this->applier->apply($origPath, $patchPath);

        /** @var array<string, mixed> $info */
        $info = $result['info'];
        self::assertArrayNotHasKey('x-internal', $info);
        self::assertArrayHasKey('title', $info);
    }

    // ---------------------------------------------------------------------------
    // RFC 6901 — pointer escaping
    // ---------------------------------------------------------------------------

    #[Test]
    public function pointerEscapingHandlesTildeOne(): void
    {
        // Key contains a literal '/'
        $spec      = ['paths' => ['/api/v1/test' => ['description' => 'original']]];
        $origPath  = $this->writeOrigSpec($spec);
        $patchPath = $this->writePatch($origPath, [
            ['op' => 'replace', 'path' => '/paths/~1api~1v1~1test/description', 'value' => 'patched'],
        ]);

        $result = $this->applier->apply($origPath, $patchPath);

        /** @var array<string, mixed> $paths */
        $paths = $result['paths'];
        /** @var array<string, mixed> $testPath */
        $testPath = $paths['/api/v1/test'];
        self::assertSame('patched', $testPath['description']);
    }

    #[Test]
    public function pointerEscapingHandlesTildeZero(): void
    {
        // Key contains a literal '~'
        $spec      = ['x~ext' => 'original'];
        $origPath  = $this->writeOrigSpec($spec);
        $patchPath = $this->writePatch($origPath, [
            ['op' => 'replace', 'path' => '/x~0ext', 'value' => 'patched'],
        ]);

        $result = $this->applier->apply($origPath, $patchPath);

        self::assertSame('patched', $result['x~ext']);
    }

    // ---------------------------------------------------------------------------
    // Multiple patches applied in sequence
    // ---------------------------------------------------------------------------

    #[Test]
    public function multiplePatchesAreAppliedInOrder(): void
    {
        $spec      = ['info' => ['title' => 'v1', 'version' => '1']];
        $origPath  = $this->writeOrigSpec($spec);
        $patchPath = $this->writePatch($origPath, [
            ['op' => 'replace', 'path' => '/info/title', 'value' => 'v2'],
            ['op' => 'replace', 'path' => '/info/version', 'value' => '2'],
            ['op' => 'add', 'path' => '/info/description', 'value' => 'New description'],
        ]);

        $result = $this->applier->apply($origPath, $patchPath);

        /** @var array<string, mixed> $info */
        $info = $result['info'];
        self::assertSame('v2', $info['title']);
        self::assertSame('2', $info['version']);
        self::assertSame('New description', $info['description']);
    }

    // ---------------------------------------------------------------------------
    // Error cases
    // ---------------------------------------------------------------------------

    #[Test]
    public function throwsInvalidArgumentExceptionForUnknownOperation(): void
    {
        $spec      = ['info' => ['title' => 'Test']];
        $origPath  = $this->writeOrigSpec($spec);
        $patchPath = $this->writePatch($origPath, [
            ['op' => 'move', 'path' => '/info/title', 'from' => '/info/other'],
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/move/');

        $this->applier->apply($origPath, $patchPath);
    }

    // ---------------------------------------------------------------------------
    // Helper methods
    // ---------------------------------------------------------------------------

    /** @param array<string, mixed> $spec */
    private function writeOrigSpec(array $spec): string
    {
        $path = $this->tempDir . '/spec.json';
        \Safe\file_put_contents($path, \Safe\json_encode($spec));

        return $path;
    }

    /**
     * @param array<array<string, mixed>> $patches
     */
    private function writePatch(string $origPath, array $patches): string
    {
        $hash    = hash('sha256', \Safe\file_get_contents($origPath));
        $path    = $this->tempDir . '/spec.json.patch';
        \Safe\file_put_contents($path, \Safe\json_encode([
            'x-base-sha256' => $hash,
            'patches'       => $patches,
        ]));

        return $path;
    }
}
