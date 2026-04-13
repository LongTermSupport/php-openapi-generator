<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\GeneratorCore;

use RuntimeException;

/**
 * Thrown when a JSON patch file's x-base-sha256 does not match the current
 * hash of the original spec it was designed against.
 *
 * This means the upstream spec changed after the patch was written. The patch
 * must be reviewed and updated before it can be safely applied.
 */
final class SpecBaseChangedException extends RuntimeException
{
}
