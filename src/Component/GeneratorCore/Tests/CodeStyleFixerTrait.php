<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Tests;

use RuntimeException;
use Symfony\Component\Process\Process;

/**
 * Normalises generated PHP files through php-qa-ci before fixture comparison.
 *
 * Delegates to `vendor/bin/qa -t fixer -p {path}` — the same pipeline used in
 * production QA runs — guaranteeing identical formatting between test normalisation
 * and the real pipeline. Never calls tool PHARs or configs directly.
 */
trait CodeStyleFixerTrait
{
    /**
     * Fix code style of a directory via the php-qa-ci pipeline.
     *
     * Runs: vendor/bin/qa -t fixer -p {path}
     *
     * CI=true prevents interactive prompts. Exit code 0 means clean; any non-zero
     * exit from the qa binary is a hard failure — the pipeline should not silently skip.
     */
    private function fixCodeStyle(string $path): void
    {
        if (!is_dir($path)) {
            return;
        }

        // Project root is 4 levels up from this file's directory:
        // src/Component/GeneratorCore/Tests/ -> src/Component/GeneratorCore/ -> src/Component/ -> src/ -> /
        $projectRoot = \dirname(__DIR__, 4);
        $qaBin       = $projectRoot . '/vendor/bin/qa';

        if (!file_exists($qaBin)) {
            throw new RuntimeException('php-qa-ci binary not found at: ' . $qaBin);
        }

        $process = new Process(
            [$qaBin, '-t', 'fixer', '-p', $path],
            $projectRoot,
            ['CI' => 'true'],
        );

        $process->run();

        // Exit 0: nothing to fix. Exit 1: CS Fixer applied changes (success).
        // Any other exit code is a genuine failure.
        $exitCode = $process->getExitCode();
        if (0 !== $exitCode && 1 !== $exitCode) {
            throw new RuntimeException(
                'vendor/bin/qa -t fixer failed (exit ' . $exitCode . "):\n" . $process->getOutput(),
            );
        }
    }
}
