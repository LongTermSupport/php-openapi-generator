# Hard Fork Master Plan: LTS OpenAPI Generator

**Status**: Planning
**Created**: 2026-03-30
**Supersedes**: `00001-phpstan-clean-pass/PLAN.md` (absorbed into this plan)

## Mission Statement

Transform the janephp/janephp hard fork into a production-grade, strictly-typed PHP OpenAPI 3.x code generator. All generated code must pass PHPStan at max level. The codebase must be self-contained, maintainable, and free of legacy complexity — no JSON Schema generator dependency, no self-hosting bootstrapping, no shared fixture namespaces hiding errors.

## Context

This project is a hard fork of `janephp/janephp`. The fork has already:
- Renamed the root namespace to `LongTermSupport\OpenApiGenerator`
- Moved to PHP 8.4 strict_types
- Set up PHPStan at max level with `treatPhpDocTypesAsCertain: false`
- Fixed several generator bugs (InvalidArgumentException FQN, str_replace type, denormalize covariance, normalize return types)
- Regenerated all 76 test fixtures with the updated generator

The remaining work falls into 6 workstreams, ordered by dependency.

---

## Completed Work (this session)

Summary of changes already made (14,429 files, 263k insertions):

1. **Namespace rename**: `Jane\*` → `LongTermSupport\OpenApiGenerator\*` across all source and fixtures
2. **Generator bug fixes** (6 files):
   - `BaseEndpoint.php`: `InvalidArgumentException` → `\InvalidArgumentException`
   - `GetGetUriTrait.php`: `str_replace` arg wrapped in `Cast\String_`
   - `GetTransformResponseBodyTrait.php`: Removed misleading `@return` PHPDoc
   - `DenormalizerGenerator.php`: Added `@return object` PHPDoc for Symfony covariance + array guard
   - `NormalizerGenerator.php`: Added full return type PHPDoc with generics + assert narrowing
   - `JaneObjectNormalizerGenerator.php`: Added `@return object` and `@return array<string,mixed>|...` PHPDocs
3. **PHPStan config**: Removed most excludePaths, added targeted ignoreErrors for legitimate cases
4. **All 76 fixtures regenerated**: `generated/` → `expected/` copied

---

## Workstream 1: Fixture Namespace Isolation (BLOCKING)

**Problem**: 68 of 76 fixtures share namespace `LongTermSupport\OpenApiGenerator\Component\OpenApi3\Tests\Expected`. PHPStan sees ~5,000+ class collision errors (duplicate `Foo`, `Bar`, `Client`, etc.) that drown real errors.

**Solution**: Give each fixture its own sub-namespace.

### Tasks

- [ ] **1.1** Update each fixture's `.php-openapi` config to use namespace `...\Tests\Expected\{FixtureName}` (68 fixtures)
- [ ] **1.2** Regenerate all fixtures (`generated/` dirs)
- [ ] **1.3** Copy `generated/` → `expected/` for all affected fixtures
- [ ] **1.4** Verify `vendor/bin/qa -t phpunit` passes (fixture snapshot tests)
- [ ] **1.5** Run `vendor/bin/qa -t phpstan` — confirm class collision errors are gone

### Key files
- `src/Component/OpenApi3/Tests/fixtures/*/.php-openapi` (68 config files)
- `src/Component/OpenApi3/Tests/JaneOpenApiResourceTest.php` (test runner)

---

## Workstream 2: PHPStan Clean Pass on Generated Code

**Problem**: Generated code must pass PHPStan max. After Workstream 1 eliminates namespace collisions, real errors will be visible. Known categories from custom-namespace fixtures:

- `argument.type` (~2,600): `denormalize()` returns `mixed`, setters expect typed objects
- `return.type` (~1,100): `normalize()` / `transformResponseBody()` return type mismatches
- `offsetAccess.nonOffsetAccessible` (~344): accessing array keys on `mixed`
- `foreach.nonIterable` (~338): iterating `mixed`
- `method.notFound` (~110): method calls on `mixed`

**Solution**: Fix the generator traits that produce the AST, then regenerate.

### Tasks

- [x] **2.1** Override `createDenormalizationStatement()` in `ObjectType` + `CustomObjectType` to emit `assert($var instanceof ...)` after `denormalize()` calls
- [x] **2.2** Fix `ArrayType` denormalization to assert array return
- [x] **2.3** Fix `MultipleType` denormalization for union type narrowing — replaced `@var` with comprehensive type guard (instanceof for objects, is_* for scalars, combined with BooleanOr + LogicException throw)
- [ ] **2.4** Fix `MapType` denormalization for map return assertion
- [x] **2.5** Ban `@var` from generated code — custom PHPStan rule `ForbidInlineVarAnnotationRule` enforces project-wide. Only array shape `@var` (`array{...}`, `array<...>`, `list<...>`, `Type[]`) is allowed.
- [x] **2.6** Remove `@var` from generators that produce it into output code — `MultipleType`, `ArrayType`, `NormalizerGenerator`, `JaneObjectNormalizerGenerator` all now emit type guards instead of `@var`
- [x] **2.7** PropertyGenerator: only generate `@var` for array shapes — all other property types use native PHP type declarations
- [x] **2.8** Normalize result type guards — `JaneObjectNormalizerGenerator` and discriminator normalizer emit `is_array || is_scalar || instanceof \ArrayObject || === null` guards
- [ ] **2.9** Regenerate all 76 fixtures, copy to expected, verify tests pass
- [ ] **2.10** Run PHPStan, identify residual errors, fix generator accordingly
- [ ] **2.11** Iterate until `vendor/bin/qa -t phpstan` reports zero errors on fixtures

### Key files
- `src/Component/GeneratorCore/Guesser/Guess/ObjectType.php`
- `src/Component/GeneratorCore/Guesser/Guess/CustomObjectType.php`
- `src/Component/GeneratorCore/Guesser/Guess/ArrayType.php`
- `src/Component/GeneratorCore/Guesser/Guess/MultipleType.php`
- `src/Component/GeneratorCore/Guesser/Guess/MapType.php`
- `src/Component/GeneratorCore/Generator/Normalizer/DenormalizerGenerator.php`
- `src/Component/GeneratorCore/Generator/Normalizer/NormalizerGenerator.php`
- `src/Component/GeneratorCore/Generator/Model/PropertyGenerator.php`
- `qaConfig/PHPStan/Rules/ForbidInlineVarAnnotationRule.php`

---

## Workstream 3: PHPStan Clean Pass on Source Code

**Problem**: Non-fixture source files also have PHPStan errors (count unknown — needs fresh run after Workstream 1).

### Tasks

- [x] **3.1** Remove inline `@var` from source code — replaced with `instanceof` + `\LogicException` type guards across all source files (GetResponseContentTrait, GetTransformResponseBodyTrait, EndpointGenerator, OpenApiGuesser, SecurityGuesser, ConstructGenerator, HttpClientCreateGenerator, ObjectGuesser, OptionResolverNormalizationTrait, ExceptionGenerator, Client runtime, GenerateCommand, Registry, WhitelistedSchema, ModelGenerator, ValidatorGenerator, PropertyGenerator)
- [x] **3.2** Remove redundant property-level `@var` — properties must use native PHP types; `@var` only for array shapes
- [x] **3.3** Exclude bootstrap normalizer files from `@var` rule — `JsonSchema/Normalizer/` and `JsonSchema/Model/` are auto-generated infrastructure (162 annotations, will be cleaned when hand-maintained in Workstream 4)
- [ ] **3.4** Convert known string constants to proper PHP enums (e.g. SecuritySchemeGuess scheme values `'Bearer'|'Basic'`, type values `'apiKey'|'http'|'oauth2'|'openIdConnect'`)
- [ ] **3.5** Fix remaining source PHPStan errors (after Workstream 1+2)
- [ ] **3.6** `vendor/bin/qa -t phpstan` → zero errors total

---

## Workstream 4: Hand-Maintain OpenAPI Spec Model (Remove Self-Hosting)

**Problem**: The OpenApi3 JsonSchema model (34 model classes, 35 normalizers) was self-generated from `version3.json` using the JSON Schema generator. The `.jane` config uses old format (`Jane\Component\OpenApi3\JsonSchema` namespace) and the generator can no longer regenerate them. This is a fragile bootstrapping dependency.

**Decision**: Hand-maintain these classes. They represent the OpenAPI 3.x spec structure which changes rarely. This removes the circular dependency on the JSON Schema generator.

### Tasks

- [ ] **4.1** Delete `.jane` config file (`src/Component/OpenApi3/.jane`)
- [ ] **4.2** Rename namespace from `Jane\Component\OpenApi3\JsonSchema` → `LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema` (already done in namespace rename, verify)
- [ ] **4.3** Audit model classes: add proper PHP 8.4 types, readonly where appropriate
- [ ] **4.4** Audit normalizer classes: ensure they pass PHPStan max
- [ ] **4.5** Add PHPDoc noting these are hand-maintained, based on OpenAPI 3.0.x spec
- [ ] **4.6** Remove `version3.json` if no longer needed for anything else
- [ ] **4.7** Verify all tests still pass

### Key files
- `src/Component/OpenApi3/.jane` (delete)
- `src/Component/OpenApi3/JsonSchema/Model/` (34 files)
- `src/Component/OpenApi3/JsonSchema/Normalizer/` (35 files)
- `src/Component/OpenApi3/version3.json`

---

## Workstream 5: Rename Jane Artefacts

**Problem**: Class names, file names, comments, and strings still reference "Jane" / "janephp" throughout.

### Tasks

- [ ] **5.1** Rename `JaneOpenApiResourceTest` → `OpenApiResourceTest`
- [ ] **5.2** Rename `JaneBaseTest` → `GeneratorBaseTest`
- [ ] **5.3** Rename `JaneObjectNormalizerGenerator` trait → `ObjectNormalizerGenerator`
- [ ] **5.4** Rename `JaneOpenApi` class → `OpenApiGenerator`
- [ ] **5.5** Rename `JaneOpenApiBundle` → `OpenApiGeneratorBundle`
- [ ] **5.6** Rename `JaneOpenApiExtension` → `OpenApiGeneratorExtension`
- [ ] **5.7** Replace string literal `'JaneObjectNormalizer'` in generators
- [ ] **5.8** Rename generated output class `JaneObjectNormalizer` → `ObjectNormalizer`
- [ ] **5.9** Update all `use` statements, config keys, service IDs
- [ ] **5.10** Regenerate fixtures (class name change affects output)
- [ ] **5.11** Verify all tests pass

### Key files
- `src/Component/OpenApi3/JaneOpenApi.php`
- `src/Component/GeneratorCore/Generator/Normalizer/JaneObjectNormalizerGenerator.php`
- `src/Component/GeneratorCore/Generator/NormalizerGenerator.php`
- `src/Bundle/OpenApiBundle/JaneOpenApiBundle.php`
- `src/Bundle/OpenApiBundle/DependencyInjection/JaneOpenApiExtension.php`
- `src/Component/OpenApi3/Tests/JaneOpenApiResourceTest.php`
- `src/Component/GeneratorCore/Tests/JaneBaseTest.php`

---

## Workstream 6: DTO Config System

**Problem**: Configuration uses PHP arrays (the `.php-openapi` files return `array`). User wants typed DTO config objects for IDE support, validation, and self-documentation.

### Tasks

- [ ] **6.1** Design `GeneratorConfig` DTO class with typed properties
- [ ] **6.2** Create config loader that hydrates DTO from array (backwards compat) or accepts DTO directly
- [ ] **6.3** Update `ConfigLoader` / `ConfigLoaderInterface` to return DTO
- [ ] **6.4** Update `Application.php` and `GenerateCommand.php` to consume DTO
- [ ] **6.5** Update all `.php-openapi` fixture configs to use new format
- [ ] **6.6** Verify all tests pass

### Key files
- `src/Component/GeneratorCore/Console/Loader/ConfigLoader.php`
- `src/Component/GeneratorCore/Console/Loader/ConfigLoaderInterface.php`
- `src/Component/GeneratorCore/Application.php`
- `src/Component/GeneratorCore/Console/Command/GenerateCommand.php`

---

## Execution Order

```
Workstream 1 (namespace isolation)
    ↓
Workstream 2 (PHPStan fixtures) ← depends on 1
    ↓
Workstream 3 (PHPStan source) ← depends on 2
    ↓
Workstream 4 (hand-maintain spec model) ← independent, but cleaner after 3
    ↓
Workstream 5 (rename Jane) ← requires fixture regeneration, best after 1-3
    ↓
Workstream 6 (DTO config) ← independent, can parallel with 4-5
```

Workstreams 4, 5, and 6 can be parallelized once 1-3 are done.

## Verification

After all workstreams:
```bash
vendor/bin/qa -t phpstan   # zero errors at max level
vendor/bin/qa -t phpunit   # all tests pass
```

No PHPStan `@phpstan-ignore`, path exclusions, or baseline entries covering generated fixture code.
