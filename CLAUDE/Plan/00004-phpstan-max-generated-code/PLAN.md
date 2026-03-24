# Plan: PHPStan Max Level — Fix All Generated Code Errors

## Context

PHPStan at max level reports 241 errors in fixture `expected/` code (14,038 PHP files across 76 fixtures). The project rule is: **fix the GENERATOR, not the config**. No ignoreErrors for generated code. Each error traces back to a generator bug or a fixture misconfiguration.

Additionally, `generated/` test output currently lands inside the Composer classmap scan path, causing duplicate class conflicts. It should go to `var/` instead.

## Current Error Breakdown

| # | Identifier | Count | Root Cause |
|---|-----------|-------|------------|
| 1 | argument.type | 113 | ArrayObject passed to array-typed setter; github ScimError/BasicError mismatch |
| 2 | instanceof.alwaysFalse | 45 | External namespace fixtures not in autoloader |
| 3 | missingType.iterableValue | 34 | Endpoint methods return bare `array` |
| 4 | offsetAssign.valueType | 23 | ArrayObject<NEVER,NEVER> doesn't accept string |
| 5 | function.alreadyNarrowedType | 10 | is_object() on already-object union |
| 6 | cast.string | 7 | Mixed cast to string in endpoints |
| 7 | ternary.condNotBoolean | 2 | Hand-written test helper uses mixed in ternary |
| 8 | forbidInlineVar.missingArrayShape | 2 | Array property without shape annotation |
| 9 | method.nonObject | 1 | DateTime format on nullable |
| 10 | method.notFound | 1 | SerializerInterface::normalize() |
| 11 | property.notFound + return.type | 2 | Undeclared $this->accept property |
| 12 | class.notFound | 1 | Hand-written test helper references missing class |

---

## Work Items (in priority order)

### W1: MapType — ArrayObject → plain array (136 errors: 113 argument.type + 23 offsetAssign.valueType)

**File:** `src/Component/GeneratorCore/Guesser/Guess/MapType.php`

**Changes:**
- `createArrayValueStatement()` (line 38-45): Return `new Expr\Array_()` instead of `new \ArrayObject`
- `getTypeHint()` (line 24-28): Return `Identifier('array')` instead of `Identifier('iterable')`
- Remove `createLoopOutputAssignement()` override if it relies on ArrayObject semantics (check if plain array `$arr[$key] = $val` works the same)

**Verification:** Regenerate body-parameter fixture, check the normalizer no longer uses ArrayObject. Run PHPStan on that single fixture.

**Risk:** Code consuming the model may depend on ArrayObject's object semantics (pass-by-reference, ArrayAccess). Check `PatternMultipleType` and any code that does `instanceof \ArrayObject` on map values. The normalization side already uses plain array (`createNormalizationArrayValueStatement()` returns `Expr\Array_()` at line 48-51), so this is consistent.

---

### W2: External namespace fixtures → internal namespaces (45+ errors)

**Files (6 `.php-openapi` configs):**
- `fixtures/api-platform-demo/.php-openapi` → `ApiPlatform\Demo` → `...\Tests\Expected\ApiPlatformDemo`
- `fixtures/github/.php-openapi` → `Github` → `...\Tests\Expected\Github`
- `fixtures/issue-337/.php-openapi` → `CreditSafe\API` → `...\Tests\Expected\Issue337`
- `fixtures/issue-391/.php-openapi` → `Gounlaf\JanephpBug` → `...\Tests\Expected\Issue391`
- `fixtures/issue-445/.php-openapi` → `PicturePark\API` → `...\Tests\Expected\Issue445`
- `fixtures/issue-669/.php-openapi` → `Jane\Generated\DigitalOcean` → `...\Tests\Expected\Issue669`

**Why:** PHPStan can't resolve types in external namespaces via the classmap autoloader. Changing to internal namespaces means all classes are discoverable. This fixes `instanceof.alwaysFalse` (45) and likely many of the `argument.type` and `missingType.iterableValue` from these fixtures.

**After namespace change:** Regenerate all 6 fixtures. Delete old expected/, regenerate, copy new.

**Re-run PHPStan on these fixtures only to see how many errors remain.**

---

### W3: Endpoint generator — add generic array return types (remaining missingType.iterableValue)

**Files:**
- `src/Component/OpenApi3/Generator/Endpoint/GetGetBodyTrait.php:50` — add `@return array<int, mixed>` PHPDoc
- `src/Component/OpenApi3/Generator/Endpoint/GetGetExtraHeadersTrait.php:46,74` — add `@return array<string, list<string>>`
- `src/Component/OpenApi3/Generator/Endpoint/GetGetQueryAllowReservedTrait.php:48` — add `@return array<string, bool>`

**Hand-written test helpers (not generated, fix directly):**
- `fixtures/custom-endpoint-generator/CustomEndpointGenerator.php` — add `@return list<class-string>` to getInterface() and getTrait()

---

### W4: Discriminator is_object() removal (10 errors)

**File:** `src/Component/OpenApiCommon/Generator/Normalizer/DenormalizerGenerator.php:61`

The normalizer generator emits `is_object($result)` check after `$this->denormalizer->denormalize()` for anyOf/discriminator unions where ALL members are objects. PHPStan knows the result is always an object.

**Fix:** When generating the discriminator normalization, skip the `is_object()` check if all union members are object types. The denormalize call already returns the correct type.

---

### W5: cast.string on mixed in endpoint URI (7 errors)

**File:** `src/Component/OpenApi3/Generator/Endpoint/GetGetUriTrait.php:69`

Casts `$this->propertyName` (mixed) to string without narrowing.

**Fix:** Use `TypeValidator::assertString()` or add `(string)` cast only after verifying the property type is string-compatible. For path parameters that are always string, ensure the property is typed as `string` in the generated constructor.

**Note:** 6 of 7 errors are from issue-669 which may resolve after W2 namespace fix. Verify after W2.

---

### W6: DateTime nullable normalization (1 error)

**File:** `src/Component/GeneratorCore/Guesser/Guess/DateTimeType.php:101`

The normalization code calls `->format()` on a `DateTime|null` value without null guard.

**Fix:** Wrap the format() call in a null check:
```php
if ($input !== null) { $output = $input->format($format); }
```
Or use the nullable normalization path that already exists.

---

### W7: SerializerInterface::normalize() (1 error)

**File:** `src/Component/OpenApi3/Generator/RequestBodyContent/FormBodyContentGenerator.php`

Generated endpoint calls `$serializer->normalize()` but `$serializer` is typed as `SerializerInterface` which doesn't have `normalize()`. Only `NormalizerInterface` has it.

**Fix:** Add an instanceof NormalizerInterface check before calling normalize(), matching the pattern used elsewhere in generated code.

---

### W8: Undeclared $this->accept property (2 errors)

**Files:**
- `src/Component/OpenApi3/Generator/Endpoint/GetGetExtraHeadersTrait.php:55,66`
- `src/Component/OpenApi3/Generator/Endpoint/GetConstructorTrait.php:109`

The generator creates `getExtraHeaders()` referencing `$this->accept` but only declares the property when there are multiple content types. For single content type, the property is missing.

**Fix:** Always declare the `accept` property when `getExtraHeaders()` is generated. Or: when there's only one content type, hardcode the Accept header value instead of using a property.

---

### W9: Generated array properties without shape (2 errors)

**File:** `src/Component/OpenApi3/Generator/Endpoint/GetConstructorTrait.php:80`

Array-typed properties in endpoints don't have `@var` shape annotations.

**Fix:** Add `@var list<string>` or `@var array<string, mixed>` PHPDoc to generated array properties.

---

### W10: Hand-written test fixture fixes (5 errors)

**Files (not generated, edit directly):**
- `fixtures/all-boolean-query-resolver/CustomQueryResolver.php:14` — `$value ? 'true' : 'false'` → `((bool) $value) ? 'true' : 'false'`
- `fixtures/boolean-query-resolver/CustomQueryResolver.php:14` — same fix
- `fixtures/custom-endpoint-generator/CustomEndpointGenerator.php` — add `@return list<class-string>` + verify Endpoint class exists or add stub

---

### W11: github fixture — ScimError/BasicError exception mismatch (16 argument.type)

**Root cause:** When a status code (400) has multiple content types (application/json → BasicError, application/scim+json → ScimError), the exception generator picks one model for the constructor. The endpoint then throws the same exception with different model types.

**File:** `src/Component/OpenApiCommon/Generator/ExceptionGenerator.php`

**Fix:** When multiple content types produce different models for the same status code, the exception constructor should accept a union type (`BasicError|ScimError`) instead of just one.

**Note:** This may resolve after W2 (namespace change) since PHPStan currently can't resolve the Github\ types. Re-evaluate after W2.

---

### W12: Redirect generated/ output to var/ (architecture)

**Files:**
- `src/Component/OpenApi3/Tests/JaneOpenApiResourceTest.php` — modify testResources() to:
  1. Override config to generate into `var/test-fixtures/{name}/generated/`
  2. Compare from var/ path instead of fixture-local generated/
- `composer.json` — remove `"LongTermSupport\\...\\Tests\\Client\\": "src/Component/OpenApi3/Tests/client/generated/"` PSR-4 entry

**Approach:** Create a TestConfigLoader that wraps ConfigLoader and overrides the `directory` key:
```php
class TestConfigLoader extends ConfigLoader {
    public function __construct(private string $outputBase) {}
    public function load(string $path): array {
        $options = parent::load($path);
        $options['directory'] = $this->outputBase . '/' . basename(dirname($path)) . '/generated';
        return $options;
    }
}
```

---

### W13: Regenerate all 76 fixtures + final verification

1. Run JaneOpenApiResourceTest (generates all fixtures)
2. Copy generated/ → expected/ for all 76 fixtures
3. `composer dump-autoload`
4. Run PHPStan on fixtures: expect 0 errors
5. Run full PHPStan: `CI=true vendor/bin/qa -t phpstan` → expect 0 errors
6. Run PHPUnit: expect all tests pass

---

## Truly Unsolvable?

Based on analysis, I believe ALL 241 errors are solvable through generator fixes. Potential edge cases to flag for review:

- **W11 (ScimError/BasicError union exceptions):** Generating union-typed exception constructors is architecturally clean but changes the public API of generated code. May want user input on approach.
- **W4 (is_object removal):** Need to verify the discriminator logic is safe without the guard.
- **W1 (ArrayObject→array):** Most impactful change. Need to verify nothing depends on ArrayObject pass-by-reference semantics.

## Commit Strategy

One commit per work item (W1 through W13), each passing PHPStan on affected fixtures.
