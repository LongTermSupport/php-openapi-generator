<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Runtime\Normalizer;

use UnexpectedValueException;

final class TypeValidator
{
    public static function assertString(mixed $value, string $field): string
    {
        if (!\is_string($value)) {
            throw new UnexpectedValueException(
                \sprintf('Field "%s" expected string, got %s', $field, get_debug_type($value))
            );
        }

        return $value;
    }

    public static function assertNullableString(mixed $value, string $field): ?string
    {
        if (null === $value) {
            return null;
        }

        return self::assertString($value, $field);
    }

    public static function assertInt(mixed $value, string $field): int
    {
        if (!\is_int($value)) {
            throw new UnexpectedValueException(
                \sprintf('Field "%s" expected int, got %s', $field, get_debug_type($value))
            );
        }

        return $value;
    }

    public static function assertNullableInt(mixed $value, string $field): ?int
    {
        if (null === $value) {
            return null;
        }

        return self::assertInt($value, $field);
    }

    public static function assertFloat(mixed $value, string $field): float
    {
        if (\is_int($value)) {
            return (float)$value;
        }

        if (!\is_float($value)) {
            throw new UnexpectedValueException(
                \sprintf('Field "%s" expected float, got %s', $field, get_debug_type($value))
            );
        }

        return $value;
    }

    public static function assertNullableFloat(mixed $value, string $field): ?float
    {
        if (null === $value) {
            return null;
        }

        return self::assertFloat($value, $field);
    }

    public static function assertBool(mixed $value, string $field): bool
    {
        if (\is_int($value) && (0 === $value || 1 === $value)) {
            return 1 === $value;
        }

        if (!\is_bool($value)) {
            throw new UnexpectedValueException(
                \sprintf('Field "%s" expected bool, got %s', $field, get_debug_type($value))
            );
        }

        return $value;
    }

    public static function assertNullableBool(mixed $value, string $field): ?bool
    {
        if (null === $value) {
            return null;
        }

        return self::assertBool($value, $field);
    }
}
