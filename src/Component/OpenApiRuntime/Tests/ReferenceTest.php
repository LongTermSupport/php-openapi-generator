<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApiRuntime\Tests;

use LongTermSupport\OpenApiGenerator\Component\OpenApiRuntime\Reference;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class ReferenceTest extends TestCase
{
    #[DataProvider('resolveProvider')]
    public function testResolve(string $reference, string $origin, mixed $expected, ?callable $denormalizerCallback): void
    {
        $ref = new Reference($reference, $origin);

        self::assertEquals($expected, $ref->resolve($denormalizerCallback));
    }

    /** @return array<int, array{string, string, mixed, callable|null}> */
    public static function resolveProvider(): array
    {
        $schemaContents = \Safe\file_get_contents(__DIR__ . '/schema.json');

        return [
            ['#', __DIR__ . '/schema.json', \Safe\json_decode($schemaContents, true), null],
            [
                'http://json-schema.org/draft-04/schema#/id',
                __DIR__ . '/schema.json',
                'http://json-schema.org/draft-04/schema#',
                null,
            ],
        ];
    }
}
