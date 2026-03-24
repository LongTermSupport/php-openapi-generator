<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApiRuntime;

use League\Uri\Http;
use League\Uri\UriString;
use LogicException;
use Psr\Http\Message\UriInterface;
use Rs\Json\Pointer;
use RuntimeException;
use Symfony\Component\Yaml\Yaml;
use Throwable;

/**
 * Deal with a JSON Reference.
 */
class Reference
{
    /** @var array<string, string> */
    private static array $fileCache = [];

    /** @var array<string, Pointer> */
    private static array $pointerCache = [];

    /** @var array<string, mixed> */
    private static array $arrayCache = [];

    /** @var string|array<mixed>|null */
    private string|array|null $resolved = null;

    private readonly Http $referenceUri;

    private readonly Http $originUri;

    private readonly Http $mergedUri;

    public function __construct(string $reference, string $origin)
    {
        $reference      = $this->fixPath($reference);
        $origin         = $this->fixPath($origin);
        $originParts    = UriString::parse($origin);
        $referenceParts = \Safe\parse_url($reference);
        if (!\is_array($referenceParts)) {
            $referenceParts = [];
        }

        /** @var array{scheme?: string|null, user?: string|null, pass?: string|null, host?: string|null, port?: int|null, path?: string|null, query?: string|null, fragment?: string|null} $mergedParts */
        $mergedParts = array_merge($originParts, $referenceParts);

        if (\array_key_exists('path', $referenceParts)) {
            $mergedParts['path'] = $this->joinPath(\dirname((string)($originParts['path'] ?? '')), (string)$referenceParts['path']);
        }

        $this->referenceUri = Http::new($reference);
        $this->originUri    = Http::new($origin);
        $this->mergedUri    = Http::fromComponents($mergedParts);
    }

    /**
     * Resolve a JSON Reference.
     *
     * @return mixed Return the json value (deserialized) referenced
     */
    public function resolve(?callable $deserializeCallback = null)
    {
        if (null === $deserializeCallback) {
            $deserializeCallback = (static fn ($data) => $data);
        }

        if (null === $this->resolved) {
            $this->resolved = $this->doResolve();
        }

        return $deserializeCallback($this->resolved);
    }

    /**
     * Return true if reference and origin are in the same document.
     */
    public function isInCurrentDocument(): bool
    {
        return
            $this->mergedUri->getScheme()   === $this->originUri->getScheme()
            && $this->mergedUri->getHost()  === $this->originUri->getHost()
            && $this->mergedUri->getPort()  === $this->originUri->getPort()
            && $this->mergedUri->getPath()  === $this->originUri->getPath()
            && $this->mergedUri->getQuery() === $this->originUri->getQuery();
    }

    public function getMergedUri(): UriInterface
    {
        return $this->mergedUri;
    }

    public function getReferenceUri(): UriInterface
    {
        return $this->referenceUri;
    }

    public function getOriginUri(): UriInterface
    {
        return $this->originUri;
    }

    /**
     * Resolve a JSON Reference for a Schema.
     *
     * @return string|array<mixed> Return the json value referenced
     */
    protected function doResolve(): string|array
    {
        $fragment  = $this->mergedUri->withFragment('')->__toString();
        $reference = \sprintf('%s_%s', $fragment, $this->mergedUri->getFragment());

        if (!\array_key_exists($fragment, self::$fileCache)) {
            $contents = \Safe\file_get_contents($fragment);

            try {
                \Safe\json_decode($contents, true);
            } catch (\Safe\Exceptions\JsonException $jsonException) {
                // Content is not valid JSON — fall back to YAML parsing
                try {
                    $decoded = Yaml::parse(
                        $contents,
                        Yaml::PARSE_OBJECT | Yaml::PARSE_OBJECT_FOR_MAP | Yaml::PARSE_DATETIME | Yaml::PARSE_EXCEPTION_ON_INVALID_TYPE
                    );
                    $contents = \Safe\json_encode($decoded, \JSON_THROW_ON_ERROR);
                } catch (Throwable $yamlException) {
                    throw new RuntimeException(
                        'Content is neither valid JSON nor YAML: ' . $jsonException->getMessage(),
                        0,
                        $yamlException
                    );
                }
            }

            self::$fileCache[$fragment] = $contents;
        }

        if (!\array_key_exists($reference, self::$arrayCache)) {
            if ('' === $this->mergedUri->getFragment()) {
                $array = \Safe\json_decode(self::$fileCache[$fragment], true);
            } else {
                if (!\array_key_exists($fragment, self::$pointerCache)) {
                    self::$pointerCache[$fragment] = new Pointer(self::$fileCache[$fragment]);
                }

                $pointer        = self::$pointerCache[$fragment];
                $encodedPointer = \Safe\json_encode($pointer->get($this->mergedUri->getFragment()));
                $array          = \Safe\json_decode($encodedPointer, true);
            }

            self::$arrayCache[$reference] = $array;
        }

        $result = self::$arrayCache[$reference];
        if (!\is_array($result) && !\is_string($result)) {
            throw new LogicException('Expected array or string, got ' . get_debug_type($result));
        }

        return $result;
    }

    /**
     * Join path like unix path join :.
     *
     *   a/b + c => a/b/c
     *   a/b + /c => /c
     *   a/b/c + .././d => a/b/d
     */
    private function joinPath(string ...$paths): string
    {
        $resultPath = '';

        foreach ($paths as $path) {
            $resultPath = '' === $resultPath || '' !== $path && '/' === $path[0] ? $path : $resultPath . '/' . $path;
        }

        $replaced = \Safe\preg_replace('~/{2,}~', '/', $resultPath);
        if (!\is_string($replaced)) {
            throw new LogicException('Expected string, got ' . get_debug_type($replaced));
        }

        $resultPath = $replaced;

        if ('/' === $resultPath) {
            return '/';
        }

        $resultPathParts = [];
        foreach (explode('/', rtrim($resultPath, '/')) as $part) {
            if ('.' === $part) {
                continue;
            }

            if ('..' === $part && [] !== $resultPathParts) {
                array_pop($resultPathParts);
                continue;
            }

            $resultPathParts[] = $part;
        }

        return implode('/', $resultPathParts);
    }

    private function fixPath(string $path): string
    {
        if ('\\' === \DIRECTORY_SEPARATOR) {
            return lcfirst(str_replace(\DIRECTORY_SEPARATOR, '/', $path));
        }

        return $path;
    }
}
