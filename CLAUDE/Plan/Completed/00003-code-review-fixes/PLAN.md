# Plan: Fix Code Review Findings

## Context

A thorough code review of the recent Rector + PHPStan commit identified 9 actionable findings ranging from a critical runtime bug to low-priority code smells. This plan addresses all findings in a single pass, ordered to minimize fixture regeneration overhead.

## Phase 1: Generator output-affecting fixes (do before fixture regen)

### 1a. CRITICAL — Fix inverted `isNullable()` logic

**File:** `src/Component/GeneratorCore/Guesser/Guess/CheckNullableTrait.php:15`

Remove `!` from `!\in_array(...)` and change `!==` to `===`:
```php
// Before:
return \is_array($schema->getType()) ? !\in_array('null', ...) : 'null' !== $schema->getType();
// After:
return \is_array($schema->getType()) ? \in_array('null', ...) : 'null' === $schema->getType();
```

**Why:** Currently nullable date fields generate non-nullsafe calls (`->format()`) causing NPE at runtime.

### 1b. CRITICAL — Replace `assert()` with `if/throw` in generated code

Replace `assert()` AST emissions with proper type guards in 4 files:

| File | Lines | Pattern |
|------|-------|---------|
| `Guesser/Guess/ObjectType.php` | 38-43 | `assert($val instanceof Class)` -> `if (!$val instanceof Class) throw` |
| `Guesser/Guess/CustomObjectType.php` | 37-42 | Same pattern |
| `Generator/Normalizer/NormalizerGenerator.php` | 96-110 | `assert($data instanceof Model)` -> `if/throw` |
| `Generator/Normalizer/JaneObjectNormalizerGenerator.php` | 79, 174, 219 | Three `assert(is_object(...))` -> `if/throw` |

All under `src/Component/GeneratorCore/`. Use `new Expr\Throw_(new Expr\New_(Name\FullyQualified('LogicException'), [...]))` pattern.

### 1c. Remove dead `$classFlags` variable

**File:** `src/Component/GeneratorCore/Generator/Model/ClassGenerator.php:45`

Remove `$classFlags = 0`, pass `0` directly with a comment explaining why models aren't final.

### 1d. Fix ValidatorGenerator issues

**File:** `src/Component/GeneratorCore/Generator/ValidatorGenerator.php`

- **Line 95:** Replace `method_exists($classObject, 'getAdditionalProperties')` with `$classObject instanceof JsonSchema` (import from GeneratorCore's own JsonSchema model — no cross-component dependency)
- **Lines 58-75:** Capture `$subProperty = $classGuess->getSubProperty()` once before the if/else, remove redundant null check
- **Line 143:** Replace `assert(is_string($item))` with `if/throw LogicException`

### 1e. Remove dead `$isSimple` variable

**File:** `src/Component/GeneratorCore/Guesser/JsonSchema/ObjectGuesser.php:164-169`

Delete the `$isSimple` variable and its loop (assigned but never read).

### 1f. Fix MapType/ArrayType parameter types

- `src/Component/GeneratorCore/Guesser/Guess/ArrayType.php`: Change `mixed $loopKeyVar` to `?Expr $loopKeyVar` on `createLoopOutputAssignement` and `createNormalizationLoopOutputAssignement`
- `src/Component/GeneratorCore/Guesser/Guess/MapType.php`: Match signatures, replace `assert($loopKeyVar instanceof Expr)` with `if/throw LogicException`

## Phase 2: Regenerate ALL fixture expected/ directories

Run PHPUnit (which generates into `generated/`), then copy `generated/` -> `expected/` for all fixtures. Single regen pass covers both the nullable logic fix and the assert->if/throw change.

```bash
for dir in src/Component/OpenApi3/Tests/fixtures/*/; do
  cp -r "$dir/generated/"* "$dir/expected/"
done
```

## Phase 3: Non-output-affecting source fixes

### 3a. Scope over-broad PHPStan suppressions

**File:** `qaConfig/phpstan.neon`

After Phase 1+2, run PHPStan to see which global suppressions are still hit. Then scope:

| Identifier | Scope to |
|-----------|----------|
| `function.alreadyNarrowedType` | `src/Component/OpenApi3/Guesser/OpenApiSchema/` + `src/Component/GeneratorCore/Guesser/JsonSchema/` |
| `instanceof.alwaysTrue` | Same paths |
| `function.impossibleType` | `src/Component/OpenApi3/JsonSchema/Normalizer/` |

Remove any that are no longer matched after the assert->if/throw changes.

### 3b. Fix @var laundering by adding @return annotations

**Root cause:** `Type::createDenormalizationStatement()` has `@return array{array<int, Stmt>, Expr}` on the base class, but PHPStan may not propagate to child overrides.

1. Add `@return array{array<int, Stmt>, Expr}` to all overrides of `createDenormalizationStatement` and `createNormalizationStatement` across the Type hierarchy (~10 methods)
2. Remove `@var` annotations from callers in `DenormalizerGenerator.php` (lines 60, 189, 191, 244, 246) and `NormalizerGenerator.php` (lines 146, 148, 213, 215)
3. Fix `GenerateCommand.php:52` — replace `@var` with runtime type guard

### 3c. Make GenerateCommand abstract

**File:** `src/Component/GeneratorCore/Console/Command/GenerateCommand.php`

Mark class `abstract`, make `execute()` abstract. Update `JaneBaseTest.php` to use the concrete `OpenApiCommon\Console\Command\GenerateCommand` subclass.

## Verification

1. `vendor/bin/qa -t phpstan` -> 0 errors
2. `php vendor/bin/phpunit` -> all tests pass
3. Spot-check: generated fixture normalizers use `if/throw` not `assert()`
4. Spot-check: nullable date fixtures use `?->format()` (nullsafe)
5. Commit with descriptive message
