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

# Mutation testing (Infection) is opted out. The bump turned Infection on by
# default, but (a) its default source set is `src`, which here includes the
# ~18k generated fixture snapshots under */Tests/fixtures/*/expected — Infection
# crashes parsing the runtime-template traits among them (Expected Class_, got
# Trait_) — and (b) this project has never carried a mutation-testing baseline,
# so the default 60/80 MSI floor is unmeasured. Adopting mutation testing is a
# separate quality initiative, out of scope for adopting the php-qa-ci bump.
export useInfection=0

# This is a code GENERATOR — its own runtime handles no plaintext credentials, so
# there is (correctly) no #[\SensitiveParameter] anywhere in its src. The always-on
# sensitiveParameterUsage baseline (which fails when the attribute is used nowhere)
# is therefore not applicable here; opt out. Credential redaction in the SDKs this
# tool GENERATES is a separate concern handled in the generated code itself.
export useSensitiveParameterCheck=0
