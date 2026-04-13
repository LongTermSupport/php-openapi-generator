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
        self::assertSame('orig', SpecDirectoryLayout::ORIG);
    }

    #[Test]
    public function patchesDirectoryName(): void
    {
        self::assertSame('patches', SpecDirectoryLayout::PATCHES);
    }

    #[Test]
    public function patchedDirectoryName(): void
    {
        self::assertSame('patched', SpecDirectoryLayout::PATCHED);
    }
}
