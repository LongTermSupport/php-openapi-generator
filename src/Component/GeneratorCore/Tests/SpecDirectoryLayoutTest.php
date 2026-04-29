<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Tests;

use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\SpecDirectoryLayout;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/** @internal */
final class SpecDirectoryLayoutTest extends TestCase
{
    #[Test]
    public function origDirectoryName(): void
    {
        self::assertSame('orig', \constant(SpecDirectoryLayout::class . '::ORIG'));
    }

    #[Test]
    public function patchesDirectoryName(): void
    {
        self::assertSame('patches', \constant(SpecDirectoryLayout::class . '::PATCHES'));
    }

    #[Test]
    public function patchedDirectoryName(): void
    {
        self::assertSame('patched', \constant(SpecDirectoryLayout::class . '::PATCHED'));
    }
}
