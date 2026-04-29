<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\GeneratorCore;

/**
 * Canonical directory names for the three-folder spec architecture.
 *
 * Convention:
 *   {projectRoot}/specs/{product}/{ORIG}/    ← original third-party specs (READ ONLY)
 *   {projectRoot}/specs/{product}/{PATCHES}/ ← RFC 6902 patch files (.json.patch)
 *   {projectRoot}/specs/{product}/{PATCHED}/ ← patch-applied specs (generator input)
 */
final readonly class SpecDirectoryLayout
{
    /** Original (unmodified) OpenAPI spec files synced from the upstream source. */
    public const string ORIG = 'orig';

    /** RFC 6902 JSON Patch files that correct divergences between the spec and the real API. */
    public const string PATCHES = 'patches';

    /** Patch-applied specs — the output of applying PATCHES to ORIG; used as generator input. */
    public const string PATCHED = 'patched';
}
