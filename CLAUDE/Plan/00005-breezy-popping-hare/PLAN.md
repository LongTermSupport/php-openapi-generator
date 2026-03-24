# Plan: Move Generated Test Output Out of src/

## Context

Rector processes 28,000+ generated PHP files because `generated/` and `expected/` dirs live inside `src/`. Only ~313 files are real source code. The QA pipeline takes 10+ minutes just on Rector. Generated code has no business being scanned by Rector or any code-modification tool.

## What Changes

### 1. Move `generated/` output to `var/test-generated/<fixture-name>/`

**Why:** `generated/` is a runtime test artifact. It should not exist inside `src/`.

**Files to change:**
- **76 `.php-openapi` configs** — change `'directory' => __DIR__ . '/generated'` to point to `var/test-generated/<name>/`
  - 73 standard configs: `'directory' => __DIR__ . '/generated'`
  - 3 mapping configs (multi-specs, exception-with-no-schema, custom-string-format-mapping): multiple `'directory'` entries with subdirs like `/generated/Api1`
- **`src/Component/OpenApi3/Tests/JaneOpenApiResourceTest.php`** — update `testResources()` to find `generated/` in `var/test-generated/<name>/` instead of `$testDirectory/generated`
- **Client fixture** `src/Component/OpenApi3/Tests/client/.php-openapi` — point to `var/test-generated/client/`
- **`composer.json`** autoload-dev — update client PSR-4 mapping from `src/.../client/generated/` to `var/test-generated/client/`
- **`.gitignore`** — remove old `src/.../fixtures/*/generated/` entries (no longer needed, `var/` already gitignored)

### 2. Exclude `expected/` from Rector

**Why:** `expected/` is tracked test fixtures (golden snapshots of generated code). Rector must not modify them — they're generated code, not hand-written source.

**Files to change:**
- **`qaConfig/rector.php`** — add `*/Tests/fixtures/*/expected` to `$rectorConfig->skip()`
- **`qaConfig/qaConfig.inc.bash`** (create) — NOT needed. Rector skip is cleaner in rector.php directly.

### 3. Exclude `expected/` from PHPStan main pipeline scan

**Why:** 14,038 files of generated code slow PHPStan down massively. Generated code quality is validated by the test itself (generator runs, output matches expected). The principle "generated code must pass PHPStan" is preserved because the expected/ snapshots were validated when they were created.

**Files to change:**
- **`qaConfig/phpstan.neon`** — add `excludePaths` for `*/Tests/fixtures/*/expected` (update the header comment explaining why this specific exclusion is OK)

## Implementation Steps

### Step 1: Update `.php-openapi` configs (76 files)

Script approach — each config uses `__DIR__` for its spec file path, so we know the fixture name from the directory. Change `'directory'` to use project root + `var/test-generated/<name>`.

**Standard config** (73 fixtures):
```php
// Before
'directory' => __DIR__ . '/generated',
// After
'directory' => dirname(__DIR__, 5) . '/var/test-generated/' . basename(__DIR__),
```
`dirname(__DIR__, 5)` goes: `fixtures/<name>` → `fixtures` → `Tests` → `OpenApi3` → `Component` → project root.

**Mapping configs** (3 fixtures):
```php
// Before
'directory' => __DIR__ . '/generated/Api1',
// After
'directory' => dirname(__DIR__, 5) . '/var/test-generated/' . basename(__DIR__) . '/Api1',
```

**Client fixture:**
```php
// Before
'directory' => __DIR__ . '/generated',
// After
'directory' => dirname(__DIR__, 5) . '/var/test-generated/client',
```
Client is at `src/Component/OpenApi3/Tests/client/`, so `dirname(__DIR__, 5)` = `src/Component/OpenApi3/Tests/` → wrong. Client needs `dirname(__DIR__, 4)` (Tests → OpenApi3 → Component → src → root). Wait — let me recount:
- `src/Component/OpenApi3/Tests/client/` = `__DIR__`
- `dirname(__DIR__, 1)` = `src/Component/OpenApi3/Tests/`
- `dirname(__DIR__, 2)` = `src/Component/OpenApi3/`
- `dirname(__DIR__, 3)` = `src/Component/`
- `dirname(__DIR__, 4)` = `src/`
- `dirname(__DIR__, 5)` = project root

Wait, fixtures are at `src/Component/OpenApi3/Tests/fixtures/<name>/`:
- `dirname(__DIR__, 1)` = `src/Component/OpenApi3/Tests/fixtures/`
- `dirname(__DIR__, 2)` = `src/Component/OpenApi3/Tests/`
- `dirname(__DIR__, 3)` = `src/Component/OpenApi3/`
- `dirname(__DIR__, 4)` = `src/Component/`
- `dirname(__DIR__, 5)` = `src/`
- `dirname(__DIR__, 6)` = project root

So fixtures need `dirname(__DIR__, 6)`, client needs `dirname(__DIR__, 5)`.

### Step 2: Update test class

**`src/Component/OpenApi3/Tests/JaneOpenApiResourceTest.php`**

In `testResources()`:
- `generated/` path changes from `$testDirectory->getRealPath() . '/generated'` to project root + `/var/test-generated/` + fixture name
- `expected/` path stays as `$testDirectory->getRealPath() . '/expected'`

```php
$projectRoot = dirname(__DIR__, 4); // src/Component/OpenApi3/Tests → project root
$generatedDir = $projectRoot . '/var/test-generated/' . $name;
$expectedDir = $testDirectory->getRealPath() . DIRECTORY_SEPARATOR . 'expected';

$this->fixCodeStyle($expectedDir);
$this->fixCodeStyle($generatedDir);

$expectedFinder = new Finder();
$expectedFinder->in($expectedDir);

$generatedFinder = new Finder();
$generatedFinder->in($generatedDir);
```

In `testClient()`: same pattern, generated comes from `var/test-generated/client/`.

### Step 3: Exclude expected/ from Rector

In `qaConfig/rector.php`, add to the existing `$rectorConfig->skip()`:
```php
$rectorConfig->skip([
    // existing skips...
    PrivatizeFinalClassMethodRector::class,
    // ...
    // Generated fixture snapshots — Rector must not modify approved golden files
    __DIR__ . '/../src/Component/OpenApi3/Tests/fixtures/*/expected',
]);
```

### Step 4: Exclude expected/ from PHPStan

In `qaConfig/phpstan.neon`:
```yaml
parameters:
    excludePaths:
        - ../src/Component/OpenApi3/Tests/fixtures/*/expected
```

Update the header comment to explain this is the ONE allowed exclusion (generated snapshot code, validated at creation time).

### Step 5: Update composer.json autoload-dev

```json
"LongTermSupport\\OpenApiGenerator\\Component\\OpenApi3\\Tests\\Client\\": "var/test-generated/client/"
```

### Step 6: Update .gitignore

Remove:
```
src/Component/OpenApi3/Tests/fixtures/*/generated/
src/Component/OpenApi3/Tests/client/generated/
```
These paths no longer exist. `var/` already covers the new location.

### Step 7: Clean up old generated/ dirs

Delete all `src/Component/OpenApi3/Tests/fixtures/*/generated/` and `src/Component/OpenApi3/Tests/client/generated/` directories from disk.

## Verification

1. Run `vendor/bin/qa -t rector` — should process only ~313 real source files, not 28,000+
2. Run `vendor/bin/qa -t phpstan` — should skip expected/ dirs, analyze only real source
3. Run `vendor/bin/qa -t unit` — fixture tests should pass (generator writes to `var/test-generated/`, compares with `expected/` in fixtures)
4. Full pipeline: `vendor/bin/qa` — should complete in reasonable time

## Critical Files

| File | Change |
|------|--------|
| `src/Component/OpenApi3/Tests/JaneOpenApiResourceTest.php` | generated/ path → var/test-generated/ |
| `src/Component/OpenApi3/Tests/fixtures/*/.php-openapi` (76) | directory → var/test-generated/ |
| `src/Component/OpenApi3/Tests/client/.php-openapi` | directory → var/test-generated/client |
| `qaConfig/rector.php` | skip expected/ dirs |
| `qaConfig/phpstan.neon` | excludePaths for expected/ |
| `composer.json` | autoload-dev client path |
| `.gitignore` | remove old generated/ entries |
