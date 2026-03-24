# Coding Standards and QA

## Safe Function Variants (Mandatory)

**Rector enforces `\Safe\` function variants.** Never revert to non-safe versions — Rector will undo it on the next pass.

`\Safe\` functions throw exceptions instead of returning `false`/`null` on failure. PHPStan sees their return types as unions (e.g. `\Safe\preg_replace` returns `string|array<string>`). Narrow with type guards:

```php
// CORRECT: type guard with LogicException
$result = \Safe\preg_replace('/pattern/', 'replacement', $input);
if (!\is_string($result)) {
    throw new LogicException('Expected string from preg_replace, got ' . get_debug_type($result));
}
// PHPStan now knows $result is string

// WRONG: reverting to non-safe (Rector will undo)
$result = preg_replace('/pattern/', 'replacement', $input);

// WRONG: casting (PHPStan rejects cast on union)
$result = (string) \Safe\preg_replace('/pattern/', 'replacement', $input);

// WRONG: silent fallback (hides unexpected types)
$result = \is_string($replaced) ? $replaced : $fallback;
```

**Only exception**: `json_decode` in code that intentionally tests if content is valid JSON and falls back to another format (e.g. YAML). In this case, non-safe `json_decode` returning `null` is the expected behaviour. Document with a comment.

## Type Guards Over Annotations

**`@var` is banned** except for array shapes. Use runtime type guards instead.

```php
// CORRECT: type guard narrows type, PHPStan verifies
$value = $container->get('service');
if (!$value instanceof MyService) {
    throw new LogicException('Expected MyService, got ' . get_debug_type($value));
}
// PHPStan now knows $value is MyService

// BANNED: @var suppresses type checking
/** @var MyService $value */
$value = $container->get('service');
```

### Allowed `@var` usage — array shapes only

PHP cannot express generic array types natively:

```php
/** @var array<string, Foo> */
private array $items;

/** @var list<Bar> */
private array $bars;
```

Bare `array` properties without `@var` shape documentation are also banned.

### Enforced by

- `ForbidInlineVarAnnotationRule` — custom PHPStan rule in `qaConfig/PHPStan/Rules/`
- `ForbidAssertCallRule` — bans `assert()` calls (use type guards with exceptions)
- `ForbidGeneratedAssertRule` — bans `assert()` in generated code output

## No `assert()` Calls

`assert()` can be disabled at runtime. Use explicit type guards:

```php
// CORRECT
if (!$node instanceof ClassMethod) {
    throw new LogicException('Expected ClassMethod, got ' . get_debug_type($node));
}

// BANNED
assert($node instanceof ClassMethod);
```

This applies to both source code and generator output (code the generators emit).

## Strict Comparisons

Use `===`/`!==` everywhere. Loose comparisons (`==`/`!=`) are banned.

Enforced by `RequireStrictComparisonRule` in `qaConfig/PHPStan/Rules/`.

## PHPStan Suppression Policy

**No suppressions. Fix the code.**

- No `@phpstan-ignore-next-line`
- No `@phpstan-ignore identifier`
- No `ignoreErrors` entries in `phpstan.neon`
- No baseline files

If PHPStan reports an error, the code is wrong. Fix it with type guards, proper typing, or refactoring. If PHPStan genuinely can't understand the code, restructure the code so it can.

Enforced by `ForbidInlinePhpstanIgnoreRule` (php-qa-ci rule) for inline suppression. The no-neon-suppression policy is enforced by code review.

### Legacy `ignoreErrors` in phpstan.neon

The current `qaConfig/phpstan.neon` has existing `ignoreErrors` entries. These are technical debt to be eliminated, not a pattern to follow. Each entry represents code that needs fixing. Do not add new entries.

## declare(strict_types=1)

Every PHP file must have `declare(strict_types=1)`. Enforced by `RequireDeclareStrictTypesRule`.

## Running QA Tools

**All QA tools MUST be run via `vendor/bin/qa`** — never invoke PHPStan, PHPUnit, Rector, or CS Fixer binaries directly.

```bash
vendor/bin/qa -t phpstan           # static analysis (max level)
vendor/bin/qa -t phpunit           # full test suite with timeouts
vendor/bin/qa -t rector            # automated refactoring
vendor/bin/qa -t fixer             # code style fixing
vendor/bin/qa -t phpstan -p <path> # analyse specific path only
vendor/bin/qa                      # full pipeline (all tools)
```

### Tool exclusion configuration

| Path | PHPStan | Rector | CS Fixer |
|------|---------|--------|----------|
| `src/` (source code) | Scanned | Scanned | Scanned |
| `tests/` (test code) | Scanned | Scanned | Scanned |
| `fixtures/*/expected/` (golden snapshots) | **Scanned** | Excluded | Excluded |
| `var/test-generated/` (runtime test output) | Not visible | Not visible | Not visible |

**expected/ MUST be scanned by PHPStan.** The whole point of this project is generating PHPStan-max-level code from OpenAPI specs. expected/ is the proof. If expected/ has errors, the generator is broken — fix the generator, regenerate expected/. Rector and CS Fixer are excluded because they would reformat and break the diff comparison.

Configuration files:
- Rector exclusion: `qaConfig/rector.php` + `qaConfig/qaConfig.inc.bash`
- CS Fixer exclusion: `qaConfig/php_cs_finder.php`
- PHPStan exclusion: `qaConfig/phpstan.neon` (`excludePaths`)

## Fixture Snapshot Workflow

When generator output changes intentionally:

```bash
# 1. Delete old expected/
rm -rf src/Component/OpenApi3/Tests/fixtures/<name>/expected/

# 2. Run generation (outputs to var/test-generated/<name>/)
php vendor/bin/phpunit --filter='testResources.*<name>'

# 3. Copy generated to expected
cp -r var/test-generated/<name>/ src/Component/OpenApi3/Tests/fixtures/<name>/expected/

# 4. Run tests to verify match
vendor/bin/qa -t phpunit
```

## PHPUnit 11

Use PHP attributes, never docblock annotations:

```php
#[DataProvider('provideData')]   // not @dataProvider
#[Large]                         // not @large
#[Medium]                        // not @medium
#[Small]                         // not @small (default)
#[Group('integration')]          // not @group
```

### Test sizes

- **Small** (1s): Default. Unit tests, no I/O.
- **Medium** (10s): Tests with filesystem, database, or network.
- **Large** (300s): Code generation over large OpenAPI specs.

Keep tests as small as possible. Only use `#[Large]` when genuinely required.

## Custom PHPStan Rules

Located in `qaConfig/PHPStan/Rules/`, registered in `qaConfig/phpstan.neon`:

| Rule | Identifier | Purpose |
|------|-----------|---------|
| `ForbidInlineVarAnnotationRule` | `forbidInlineVar.found` | Bans `@var` except array shapes |
| `ForbidAssertCallRule` | `forbidAssertCall.found` | Bans `assert()` in source |
| `ForbidGeneratedAssertRule` | `forbidGeneratedAssert.found` | Bans `assert()` in generated output |
| `RequireStrictComparisonRule` | `requireStrictComparison.found` | Bans `==`/`!=` |

## php-qa-ci Rules (from vendor)

Key rules from `lts/php-qa-ci`:

| Rule | Purpose |
|------|---------|
| `ForbidDangerousFunctionsRule` | Blocks unsafe functions |
| `ForbidEmptyCatchBlockRule` | No empty catch blocks |
| `ForbidSilentCatchRule` | No catch blocks that swallow without logging |
| `ForbidEmptyLanguageConstructRule` | No `empty()` — use explicit checks |
| `ForbidDeprecatedSerializableRule` | PHP 8.4 compatibility |
| `ForbidNewDateTimeRule` | Use DateTimeImmutable |
| `ForbidInlinePhpstanIgnoreRule` | No inline PHPStan suppression |
| `RequireDeclareStrictTypesRule` | Every file needs strict_types |
| `phpqaci.interfaceSuffix` | Interface naming (globally suppressed) |
| `phpqaci.traitSuffix` | Trait naming (globally suppressed) |
