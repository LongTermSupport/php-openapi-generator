# Plan: Eliminate `getSchemaClass()` Polymorphic Pattern

## Context
PHPStan cannot narrow types through `instanceof $stringVariable`. The `getSchemaClass()` pattern in 10 base guessers causes ~15 ignoreErrors in phpstan.neon. Fix: create a `SchemaInterface` both schema classes implement, then use `instanceof SchemaInterface` (a concrete type PHPStan can narrow).

## Critical Finding
- `OpenApi3\JsonSchema\Model\Schema` extends `ArrayObject` — does NOT extend `GeneratorCore\JsonSchema\Model\JsonSchema`
- They share method names but are separate hierarchies
- A common interface bridges them

## Steps

### 1. Create SchemaInterface
**File**: `src/Component/GeneratorCore/JsonSchema/Model/SchemaInterface.php`

Methods needed (audit of what base guessers call after instanceof):
- `getType(): mixed`, `getProperties(): ?iterable`, `getFormat(): ?string`
- `getRequired(): ?array`, `getAdditionalProperties(): object|bool|null`
- `getItems(): mixed`, `getAllOf(): ?array`, `getOneOf(): ?array`, `getAnyOf(): ?array`
- `getDescription(): ?string`, `getDefault(): mixed`, `getReadOnly(): ?bool`
- `getDeprecated(): ?bool`, `getEnum(): ?array`

NOT included: `setType()` (contravariant param issue), `getPatternProperties()` (Schema lacks it)

### 2. Implement interface on both models
- `JsonSchema.php` — add `implements SchemaInterface`
- `Schema.php` — add `implements SchemaInterface`
- PHP 8.4 covariant returns handle the signature differences

### 3. Update 10 base guessers
Replace `instanceof $this->getSchemaClass()` → `instanceof SchemaInterface` in:
- `ObjectGuesser.php`, `AllOfGuesser.php`, `SimpleTypeGuesser.php`
- `DateGuesser.php`, `DateTimeGuesser.php`, `MultipleGuesser.php`
- `ArrayGuesser.php`, `ItemsGuesser.php`, `AdditionalPropertiesGuesser.php`
- `CustomStringFormatGuesser.php`

Keep `getSchemaClass()` for `resolve()` calls (denormalizer needs class name).

### 4. Handle edge cases
- `ObjectGuesser::buildPatternExtensions()`: widen param to `SchemaInterface`, add `method_exists` guard for `getPatternProperties()`
- `MultipleGuesser::guessType()`: `setType()` not in interface — use concrete `instanceof JsonSchema` secondary check, or override in OpenApiCommon layer

### 5. Update OpenApiCommon overrides as needed
- Files in `src/Component/OpenApiCommon/Guesser/OpenApiSchema/` may need signature updates

### 6. Remove phpstan.neon ignoreErrors
Remove these entries targeting `GeneratorCore/Guesser/JsonSchema/*`:
- `method.notFound`, `argument.type`, `foreach.nonIterable`
- `binaryOp.invalid`, `cast.string`, `offsetAccess.invalidOffset`
- `return.type` (AllOfGuesser)

### 7. Verify
- `vendor/bin/qa -t phpstan` — zero errors
- `vendor/bin/qa -t phpunit` — all tests pass
