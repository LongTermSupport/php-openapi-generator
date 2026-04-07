<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApi3\Tests;

use LongTermSupport\OpenApiGenerator\Component\OpenApi3\Tests\Expected\TestNullable\Model\Model;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\Tests\Expected\TestNullable\Normalizer\JaneObjectNormalizer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Encoder\JsonDecode;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * Runtime tests for the generated normalizer's handling of uninitialized typed properties.
 *
 * Bug being prevented:
 *   Generated models declare nullable typed properties without `= null` defaults
 *   (e.g. `protected ?string $foo;`). The generated normalizer reads the property
 *   eagerly via the getter BEFORE checking `isInitialized()`. PHP throws
 *   `Error: Typed property must not be accessed before initialization` when reading
 *   a typed property that has never been written.
 *
 * Fix policy:
 *   Default ALL nullable typed model properties to `null` so the read is always safe.
 *   `isInitialized()` continues to track the explicit set/unset state independently
 *   of the underlying value.
 *
 * @internal
 */
final class NullablePropertyNormalizationTest extends TestCase
{
    public function testNormalizingFreshModelDoesNotThrowOnUninitializedNullableProperties(): void
    {
        // Create a model and only set the required field. Nullable properties
        // (`foo`, `date`) are intentionally left uninitialized.
        $model = new Model();
        $model->setBar('hello');

        $serializer = new Serializer(
            [new ArrayDenormalizer(), new JaneObjectNormalizer()],
            [new JsonEncoder(new JsonEncode(), new JsonDecode(['json_decode_associative' => true]))]
        );

        // Before the fix: this throws Error("Typed property ...::$foo must not be accessed before initialization").
        $result = $serializer->normalize($model);

        self::assertIsArray($result);
        self::assertSame(['bar' => 'hello'], $result);
    }

    public function testFreshModelGetterReturnsNullForNullableProperty(): void
    {
        // Reading a nullable property on a freshly-constructed model must not throw.
        // It should return null because the property defaults to null.
        $model = new Model();

        self::assertNull($model->getFoo());
        self::assertNull($model->getDate());
    }

    public function testIsInitializedRemainsFalseForUnsetNullableProperty(): void
    {
        // The fix MUST NOT break the isInitialized tracking. The default-to-null
        // is purely a backing-storage initialization; the explicit set-tracking
        // (initialized array) must remain independent of the value.
        $model = new Model();

        self::assertFalse($model->isInitialized('foo'));
        self::assertFalse($model->isInitialized('date'));
    }

    public function testExplicitlySetNullIsRoundTripped(): void
    {
        // When a nullable property is explicitly set to null, isInitialized should be true.
        // The normalizer's existing logic skips uninitialized properties, so a nullable
        // property explicitly set to null should NOT appear in the output (because the
        // current normalize logic checks `isInitialized && null !== $val`).
        $model = new Model();
        $model->setBar('hello');
        $model->setFoo(null);

        self::assertTrue($model->isInitialized('foo'));

        $serializer = new Serializer(
            [new ArrayDenormalizer(), new JaneObjectNormalizer()],
            [new JsonEncoder(new JsonEncode(), new JsonDecode(['json_decode_associative' => true]))]
        );

        $result = $serializer->normalize($model);
        self::assertIsArray($result);
        // Existing normalizer behaviour: skip-null-values is on by default, so foo=null is skipped.
        self::assertSame(['bar' => 'hello'], $result);
    }
}
