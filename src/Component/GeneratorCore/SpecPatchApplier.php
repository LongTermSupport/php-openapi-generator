<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\GeneratorCore;

use InvalidArgumentException;
use LogicException;

/**
 * Applies RFC 6902 JSON Patch files to OpenAPI spec files.
 *
 * Patch file format:
 * {
 *   "x-base-sha256": "<sha256 of orig spec>",
 *   "patches": [
 *     {"op": "add",     "path": "/paths/~1api~1v1~1layouts/get/parameters/-", "value": {...}},
 *     {"op": "replace", "path": "/info/title",   "value": "New Title"},
 *     {"op": "remove",  "path": "/info/x-internal"}
 *   ]
 * }
 *
 * Supported RFC 6902 operations: add, replace, remove.
 * RFC 6901 pointer escaping: ~1 → /, ~0 → ~.
 */
final readonly class SpecPatchApplier
{
    /**
     * Apply a JSON patch file to an original spec file.
     *
     * If no patch file exists at $patchPath, the orig spec is returned unchanged.
     *
     * @param string $origPath  Absolute path to the original spec JSON file
     * @param string $patchPath Absolute path to the .json.patch file
     *
     * @return array<string, mixed> The patched spec as an associative array
     *
     * @throws SpecBaseChangedException When orig spec hash differs from x-base-sha256
     * @throws InvalidArgumentException When patch format is invalid or an unknown op is used
     */
    public function apply(string $origPath, string $patchPath): array
    {
        $origContent = \Safe\file_get_contents($origPath);

        $decoded = \Safe\json_decode($origContent, true);
        if (!\is_array($decoded)) {
            throw new InvalidArgumentException(\sprintf(
                'Spec at %s decoded to %s, expected a JSON object',
                $origPath,
                get_debug_type($decoded),
            ));
        }

        /** @var array<string, mixed> $spec */
        $spec = $decoded;

        if (!file_exists($patchPath)) {
            return $spec;
        }

        $patchContent = \Safe\file_get_contents($patchPath);
        $patchDecoded = \Safe\json_decode($patchContent, true);
        if (!\is_array($patchDecoded)) {
            throw new InvalidArgumentException(\sprintf(
                'Patch at %s decoded to %s, expected a JSON object',
                $patchPath,
                get_debug_type($patchDecoded),
            ));
        }

        /** @var array<string, mixed> $patch */
        $patch = $patchDecoded;

        if (!\array_key_exists('x-base-sha256', $patch)) {
            throw new InvalidArgumentException(\sprintf(
                'Patch file %s is missing the required x-base-sha256 key. '
                . 'All patch files must declare the SHA-256 of the original spec they were written against.',
                $patchPath,
            ));
        }

        $expectedHash = $patch['x-base-sha256'];
        if (!\is_string($expectedHash)) {
            throw new InvalidArgumentException(\sprintf(
                'x-base-sha256 in patch file %s must be a string',
                $patchPath,
            ));
        }

        $actualHash = hash('sha256', $origContent);

        if ($expectedHash !== $actualHash) {
            $specName = basename($origPath);
            throw new SpecBaseChangedException(\sprintf(
                "Spec '%s' has changed since this patch was written.\n"
                . "Expected SHA-256 (from patch): %s\n"
                . "Actual SHA-256   (current):    %s\n"
                . "\n"
                . "Action required:\n"
                . "  1. Review what changed: git diff specs/orig/desk/%s\n"
                . "  2. Update the patch operations in: %s\n"
                . '  3. Update x-base-sha256 in the patch file to: %s',
                $specName,
                $expectedHash,
                $actualHash,
                $specName,
                $patchPath,
                $actualHash,
            ));
        }

        $rawPatches = $patch['patches'] ?? [];
        if (!\is_array($rawPatches)) {
            throw new InvalidArgumentException(\sprintf(
                "The 'patches' key in %s must be an array",
                $patchPath,
            ));
        }

        foreach ($rawPatches as $i => $op) {
            if (!\is_array($op)) {
                throw new InvalidArgumentException(\sprintf(
                    'Patch operation %d in %s must be a JSON object',
                    $i,
                    $patchPath,
                ));
            }

            /** @var array<string, mixed> $op */
            $spec = $this->applyOperation($spec, $op);
        }

        return $spec;
    }

    /**
     * @param array<string, mixed> $spec
     * @param array<string, mixed> $op
     *
     * @return array<string, mixed>
     */
    private function applyOperation(array $spec, array $op): array
    {
        $opType = $op['op']   ?? null;
        $path   = $op['path'] ?? null;

        if (!\is_string($opType)) {
            throw new InvalidArgumentException("Patch operation must have a string 'op' key");
        }

        if (!\is_string($path)) {
            throw new InvalidArgumentException("Patch operation must have a string 'path' key");
        }

        return match ($opType) {
            'add'     => $this->applyAtPath($spec, $path, $op['value'] ?? null, 'add'),
            'replace' => $this->applyAtPath($spec, $path, $op['value'] ?? null, 'replace'),
            'remove'  => $this->applyAtPath($spec, $path, null, 'remove'),
            default   => throw new InvalidArgumentException(\sprintf(
                "Unknown RFC 6902 operation '%s'. Supported operations: add, replace, remove",
                $opType,
            )),
        };
    }

    /**
     * @param array<string, mixed> $spec
     *
     * @return array<string, mixed>
     */
    private function applyAtPath(array $spec, string $pointerString, mixed $value, string $operation): array
    {
        $segments = $this->parsePointer($pointerString);

        if ([] === $segments) {
            // Root replacement (add/replace on empty pointer '')
            if ('remove' === $operation) {
                throw new InvalidArgumentException("Cannot apply RFC 6902 'remove' to the root document");
            }

            if (!\is_array($value)) {
                throw new InvalidArgumentException('Root replacement requires an array value');
            }

            /** @var array<string, mixed> $value */
            return $value;
        }

        return $this->applyAtNode($spec, $segments, $value, $operation);
    }

    /**
     * Recursively navigate to the target node and apply the operation.
     *
     * @param array<string, mixed> $spec
     * @param list<string>         $segments
     *
     * @return array<string, mixed>
     */
    private function applyAtNode(array $spec, array $segments, mixed $value, string $operation): array
    {
        $segment = array_shift($segments);
        if (null === $segment) {
            throw new LogicException('applyAtNode called with empty segments array; this is a bug');
        }

        $isFinalSegment = [] === $segments;

        if ($isFinalSegment) {
            return $this->applyFinalSegment($spec, $segment, $value, $operation);
        }

        // Not at final segment — navigate into child then recurse
        if (!\array_key_exists($segment, $spec)) {
            throw new InvalidArgumentException(\sprintf(
                "Pointer segment '%s' does not exist in spec",
                $segment,
            ));
        }

        $child = $spec[$segment];
        if (!\is_array($child)) {
            throw new InvalidArgumentException(\sprintf(
                "Cannot navigate into %s at pointer segment '%s'",
                get_debug_type($child),
                $segment,
            ));
        }

        /** @var array<string, mixed> $child */
        $spec[$segment] = $this->applyAtNode($child, $segments, $value, $operation);

        return $spec;
    }

    /**
     * Apply the operation at the final pointer segment.
     *
     * @param array<string, mixed> $node
     *
     * @return array<string, mixed>
     */
    private function applyFinalSegment(array $node, string $segment, mixed $value, string $operation): array
    {
        if ('add' === $operation) {
            if ('-' === $segment) {
                // RFC 6902: '-' appends to an array
                if (!array_is_list($node)) {
                    throw new InvalidArgumentException(
                        "RFC 6902 'add' with '-' pointer: target is not a list array",
                    );
                }

                $node[] = $value;
            } else {
                $node[$segment] = $value;
            }

            return $node;
        }

        if ('replace' === $operation) {
            if (!\array_key_exists($segment, $node)) {
                throw new InvalidArgumentException(\sprintf(
                    "RFC 6902 'replace' target '%s' does not exist",
                    $segment,
                ));
            }

            $node[$segment] = $value;

            return $node;
        }

        if ('remove' === $operation) {
            if (!\array_key_exists($segment, $node)) {
                throw new InvalidArgumentException(\sprintf(
                    "RFC 6902 'remove' target '%s' does not exist",
                    $segment,
                ));
            }

            unset($node[$segment]);

            return $node;
        }

        // Unreachable — applyOperation already validates op type
        throw new InvalidArgumentException(\sprintf("Unknown operation '%s'", $operation));
    }

    /**
     * Parse an RFC 6901 JSON Pointer string into decoded segments.
     *
     * '' (empty string) → []
     * '/foo/bar~1baz~0qux' → ['foo', 'bar/baz~qux']
     *
     * @return list<string>
     */
    private function parsePointer(string $pointer): array
    {
        if ('' === $pointer) {
            return [];
        }

        if ('/' !== $pointer[0]) {
            throw new InvalidArgumentException(\sprintf(
                "Invalid RFC 6901 JSON Pointer '%s': must start with '/'",
                $pointer,
            ));
        }

        $parts = explode('/', substr($pointer, 1));

        return array_map(
            static fn (string $part): string => str_replace(['~1', '~0'], ['/', '~'], $part),
            $parts,
        );
    }
}
