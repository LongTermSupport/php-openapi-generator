<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\GeneratorCore\JsonSchema\Model;

/**
 * Common interface for schema models used by the guesser layer.
 *
 * Both JsonSchema (GeneratorCore) and Schema (OpenApi3) implement this,
 * enabling static type narrowing via `instanceof SchemaInterface` in base guessers
 * instead of the PHPStan-opaque `instanceof $stringVariable` pattern.
 */
interface SchemaInterface
{
    /**
     * @return string|string[]|null
     */
    public function getType(): string|array|null;

    /** @return array<string, mixed>|null */
    public function getProperties(): ?array;

    public function getFormat(): ?string;

    /** @return array<string>|null */
    public function getRequired(): ?array;

    public function getAdditionalProperties(): object|bool|null;

    public function getItems(): mixed;

    /** @return array<mixed>|null */
    public function getAllOf(): ?array;

    /** @return array<mixed>|null */
    public function getOneOf(): ?array;

    /** @return array<mixed>|null */
    public function getAnyOf(): ?array;

    public function getDescription(): ?string;

    public function getDefault(): mixed;

    public function getReadOnly(): ?bool;

    public function getDeprecated(): ?bool;

    /** @return array<mixed>|null */
    public function getEnum(): ?array;
}
