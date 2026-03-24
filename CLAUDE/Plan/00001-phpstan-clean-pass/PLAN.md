# Plan 00001: PHPStan Clean Pass + Rename Jane Artefacts

**Status**: In Progress
**Created**: 2026-03-26
**Owner**: Claude
**Priority**: High

## Overview

Make `vendor/bin/qa -t phpstan` pass with zero errors at PHPStan max level.
PHPStan must NOT exclude generated fixture code — `expected/` and `generated/` fixture dirs must pass genuinely.

All generated files must carry a "GENERATED CODE — DO NOT EDIT MANUALLY" header.
All Jane artefacts (class names, file names, comments, strings referencing Jane/janephp) must be renamed.
Fixture tests (`JaneOpenApiResourceTest`) must continue to pass — `generated/` must match `expected/` exactly.

## Goals

- [ ] PHPStan passes at max level with zero errors, no suppressions on generated fixture files
- [ ] All fixtures regenerated with typed denormalisers (no `argument.type` errors)
- [ ] All non-fixture source files clean (no `method.nonObject`, `missingType.*`, `foreach.nonIterable`, etc.)
- [ ] All Jane artefacts renamed throughout the codebase
- [ ] Generated file header comment on all generated output

## Non-Goals

- Changing the public OpenAPI generation API or config format
- Adding new features beyond what is needed for PHPStan compliance

## Tasks

### Phase 1: Typed Denormaliser Generator (fixes ~2,603 argument.type fixture errors)

- [x] **1.1** Analyse root cause: `ObjectType::createDenormalizationValueStatement()` returns `mixed` (from `denormalize()`), but setters expect typed objects
- [ ] **1.2** Override `createDenormalizationStatement()` in `ObjectType` to emit:
  ```php
  $value = $this->denormalizer->denormalize(...);
  assert($value instanceof SomeClass);
  ```
- [ ] **1.3** Same override in `CustomObjectType`
- [ ] **1.4** Run fixture tests to regenerate `generated/` dirs
- [ ] **1.5** Copy `generated/` → `expected/` for all affected fixtures
- [ ] **1.6** Verify fixture tests pass (generated matches expected)

### Phase 2: Fix remaining fixture PHPStan errors

- [ ] **2.1** Run PHPStan, identify residual fixture errors after Phase 1
- [ ] **2.2** Fix `return.type` errors in normalizer `normalize()` methods (~1,103)
- [ ] **2.3** Fix `notIdentical.alwaysTrue` errors (~362) — likely from assert() + null checks
- [ ] **2.4** Fix `offsetAccess.nonOffsetAccessible` errors (~347)
- [ ] **2.5** Fix any remaining fixture errors

### Phase 3: Fix non-fixture source PHPStan errors

- [ ] **3.1** Run PHPStan on non-fixture source, get current count after Phase 1+2
- [ ] **3.2** Fix `method.nonObject` errors (~746 originally)
- [ ] **3.3** Fix `argument.type` errors in source (~717 originally)
- [ ] **3.4** Fix `missingType.iterableValue` errors (~284)
- [ ] **3.5** Fix `foreach.nonIterable` errors (~172)
- [ ] **3.6** Fix any remaining source errors
- [ ] **3.7** PHPStan reports zero errors

### Phase 4: Rename Jane Artefacts

- [ ] **4.1** Rename `JaneOpenApiResourceTest` → `OpenApiResourceTest`
- [ ] **4.2** Rename `JaneBaseTest` → `GeneratorBaseTest`
- [ ] **4.3** Rename `JaneObjectNormalizerGenerator` → `ObjectNormalizerGenerator`
- [ ] **4.4** Rename `JaneOpenApi` class → `OpenApiGenerator`
- [ ] **4.5** Rename `JaneOpenApiBundle` → `OpenApiGeneratorBundle`
- [ ] **4.6** Rename `JaneOpenApiExtension` → `OpenApiGeneratorExtension`
- [ ] **4.7** Replace string literal `'JaneObjectNormalizer'` in `NormalizerGenerator.php`
- [ ] **4.8** Update all `use` statements, references, config keys
- [ ] **4.9** Update fixture `.php-openapi` config files if they reference Jane class names
- [ ] **4.10** Verify all tests pass after rename

### Phase 5: Generated File Header

- [ ] **5.1** Confirm `Printer.php` already prepends the "GENERATED CODE" header to all output
- [ ] **5.2** Verify fixture `expected/` files all carry the header
- [ ] **5.3** If missing anywhere, fix `Printer.php` and regenerate

### Phase 6: Final Verification

- [ ] **6.1** `vendor/bin/qa -t phpstan` → zero errors
- [ ] `vendor/bin/qa -t phpunit` → all fixture tests pass
- [ ] **6.3** No PHPStan ignores/excludes covering generated fixture paths

## Technical Decisions

### Decision 1: How to narrow `mixed` from `denormalize()`
**Context**: `DenormalizerInterface::denormalize()` returns `mixed`. Setters in models expect concrete types.
**Options**:
1. Change setters to accept `mixed` — hides type safety in models
2. Add `assert($var instanceof SomeClass)` — PHPStan understands this, zero runtime cost in production with `assert` disabled
3. Use `@var` phpdoc cast — fragile, not always respected
**Decision**: Option 2 — emit intermediate variable + `assert(instanceof)` from the generator. PHPStan narrows the type cleanly.

## Success Criteria

- [ ] `vendor/bin/qa -t phpstan` exits 0 with zero reported errors
- [ ] All fixture tests pass
- [ ] No `// @phpstan-ignore` or path exclusions covering generated code
