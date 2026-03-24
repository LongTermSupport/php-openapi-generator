# PHP OpenAPI Generator — Developer Context

## Stack

- **PHP 8.4** (strict_types everywhere)
- **PHPUnit 11** — use PHP attributes, never docblock annotations
  - `#[DataProvider('method')]` not `@dataProvider`
  - `#[Large]` / `#[Medium]` / `#[Small]` not `@large` etc.
  - `#[Group('name')]` not `@group`
- **PHPStan** at max level via `vendor/bin/qa -t phpstan`
- **lts/php-qa-ci** for QA pipeline — run with `vendor/bin/qa -t <tool>`

## Test sizes

Keep tests as small as possible. Only use `#[Large]` when test genuinely requires it (e.g. code generation over large OpenAPI specs). Timeouts enforced by qa tool:

- Small: 1s (default for unannotated)
- Medium: 10s
- Large: 300s

## Bash command output

**DO NOT pipe Bash commands to `head` or `tail`**. Instead, redirect full output to a temp file and echo only the exit code. Then use the Read tool to parse/analyse as much as needed without re-running expensive commands.

```bash
# WRONG — loses output, triggers pipe blocker, may need re-run
vendor/bin/qa -t phpstan 2>&1 | tail -50

# RIGHT — full output preserved, read what you need later
OUTFILE=$(mktemp) && vendor/bin/qa -t phpstan > "$OUTFILE" 2>&1; echo "EXIT_CODE=$?"
# Then: Read tool on $OUTFILE
```

## Running QA tools

**ALL QA tools MUST be run via `vendor/bin/qa`** — never invoke PHPStan, PHPUnit, Rector, or CS Fixer binaries directly. The `qa` wrapper configures paths, configs, and environment correctly.

```bash
vendor/bin/qa -t phpstan           # static analysis (max level)
vendor/bin/qa -t phpunit           # full test suite with timeouts
vendor/bin/qa -t phpstan -p <path> # analyse specific path only
php vendor/bin/phpunit             # direct PHPUnit (no time enforcement, use sparingly)
```

Do NOT run `php vendor/bin/phpstan` or `vendor/bin/phpstan` directly — it will not pick up the correct config.

## Namespace

All production code: `LongTermSupport\OpenApiGenerator\`

## Key packages (first-party, editable in vendor via --prefer-source)

- `lts/strict-openapi-validator` — validates OpenAPI 3.1.x specs; 3.0.x specs produce `markTestIncomplete()`
- `lts/php-qa-ci` — QA pipeline

## Coding standards

See `CLAUDE/CodingStandardsAndQa.md` for the full reference. Key rules:

- **Always use `\Safe\` function variants** — Rector enforces this, never revert to non-safe
- **Type guards with `LogicException`** to narrow Safe return types — not `@var`, not casts, not silent fallbacks
- **No `@var`** except array shapes — use `instanceof`, `is_string()`, etc.
- **No `assert()`** — use `if (!...) { throw new LogicException(...); }`
- **No PHPStan suppressions** — fix the code, never suppress errors
- **Strict comparisons** (`===`/`!==`) everywhere

## Fixture snapshots

OpenAPI3 fixture tests (`JaneOpenApiResourceTest`) run the generator then diff `generated/` vs `expected/`. When generator output changes intentionally, copy `generated/` → `expected/` for affected fixtures. All `.php-openapi` configs use namespace `LongTermSupport\OpenApiGenerator\Component\OpenApi3\Tests\Expected`.

**The entire point of this project is to generate PHPStan-max-level code from OpenAPI specs.** The `expected/` directories ARE the proof. PHPStan MUST scan expected/ — if expected/ has errors, the GENERATOR is broken and must be fixed. Never exclude expected/ from PHPStan.

### What gets scanned by what

| Path | PHPStan | Rector | CS Fixer |
|------|---------|--------|----------|
| `src/` (source code) | Scanned | Scanned | Scanned |
| `tests/` (test code) | Scanned | Scanned | Scanned |
| `fixtures/*/expected/` (golden snapshots proving generator correctness) | **Scanned** | Excluded | Excluded |
| `*/Generator/Runtime/data/` (runtime templates) | **Scanned** | Excluded | Excluded |
| `var/test-generated/` (runtime test output) | Not visible | Not visible | Not visible |

Rector and CS Fixer are excluded from expected/ because they would reformat it, breaking the diff comparison with generated/. PHPStan is NOT excluded because expected/ must pass max-level analysis — that's the whole point.

Runtime templates (`Generator/Runtime/data/`) are copied verbatim into generated code under arbitrary namespaces. They MUST use `\`-prefixed FQCNs (e.g. `\LogicException`, `\ArrayObject`). Rector/CS Fixer would strip the `\` prefix, breaking generated code.
