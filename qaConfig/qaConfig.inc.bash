#!/usr/bin/env bash
# shellcheck disable=SC2154 # projectRoot is set by php-qa-ci before sourcing this file

# Exclude generated fixture snapshots from all QA tool passes.
# expected/ contains approved golden files — not hand-written source code.
# NOTE: Rector's skip() does NOT expand globs — must expand to literal paths.
for _fixtureExpectedDir in "$projectRoot"/src/Component/OpenApi3/Tests/fixtures/*/expected; do
    [[ -d "$_fixtureExpectedDir" ]] && pathsToIgnore+=("$_fixtureExpectedDir")
done
# Also exclude client/expected and client/generated (test harness output)
for _clientDir in "$projectRoot"/src/Component/OpenApi3/Tests/client/expected \
                  "$projectRoot"/src/Component/OpenApi3/Tests/client/generated; do
    [[ -d "$_clientDir" ]] && pathsToIgnore+=("$_clientDir")
done
# Runtime templates — copied verbatim into generated code, must keep \-prefixed FQCNs
pathsToIgnore+=("$projectRoot/src/Component/GeneratorCore/Generator/Runtime/data")
pathsToIgnore+=("$projectRoot/src/Component/OpenApiCommon/Generator/Runtime/data")
unset _fixtureExpectedDir _clientDir
