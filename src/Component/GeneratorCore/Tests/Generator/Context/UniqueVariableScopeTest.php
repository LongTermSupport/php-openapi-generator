<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Generator\Context\Tests;

use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Generator\Context\UniqueVariableScope;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class UniqueVariableScopeTest extends TestCase
{
    public function testUniqueVariable(): void
    {
        $uniqueVariableScope = new UniqueVariableScope();

        $name = $uniqueVariableScope->getUniqueName('name');
        self::assertSame('name', $name);

        $name = $uniqueVariableScope->getUniqueName('name');
        self::assertSame('name_1', $name);

        $name = $uniqueVariableScope->getUniqueName('name');
        self::assertSame('name_2', $name);
    }
}
