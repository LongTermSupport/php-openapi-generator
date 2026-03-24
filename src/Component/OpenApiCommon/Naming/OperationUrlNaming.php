<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Naming;

use ArrayIterator;
use ArrayObject;
use LogicException;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Tools\InflectorTrait;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Response as OA3Response;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Schema as OA3Schema;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Guesser\Guess\OperationGuess;

class OperationUrlNaming implements OperationNamingInterface
{
    use InflectorTrait;

    public const FORBIDDEN_EXTENSIONS = [
        '.json',
        '.php',
        '.asp',
    ];

    public function getFunctionName(OperationGuess $operation): string
    {
        return $this->getInflector()->camelize($this->getUniqueName($operation));
    }

    public function getEndpointName(OperationGuess $operation): string
    {
        return $this->getInflector()->classify($this->getUniqueName($operation));
    }

    protected function getUniqueName(OperationGuess $operation): string
    {
        $prefix            = strtolower($operation->getMethod());
        $shouldSingularize = true;
        $op                = $operation->getOperation();
        $responses         = method_exists($op, 'getResponses') ? $op->getResponses() : null;

        if ($responses instanceof ArrayObject && isset($responses[200])) {
            $response = $responses[200];

            if (class_exists(OA3Response::class) && $response instanceof OA3Response && null !== $response->getContent() && [] !== $response->getContent()) {
                $firstContent = new ArrayIterator($response->getContent())->current();

                if ($firstContent->getSchema() instanceof OA3Schema && 'array' === $firstContent->getSchema()->getType()) {
                    $shouldSingularize = false;
                }
            }
        }

        $rawMatches = [];
        \Safe\preg_match_all('/(?<separator>[^a-zA-Z0-9_{}])+(?<part>[a-zA-Z0-9_{}]*)/', $operation->getPath(), $rawMatches);
        /** @var list<string> $fullMatches */
        $fullMatches = $rawMatches[0] ?? [];
        /** @var list<string> $separators */
        $separators = $rawMatches['separator'] ?? [];
        /** @var list<string> $parts */
        $parts = $rawMatches['part'] ?? [];

        $methodNameParts           = [];
        $lastNonParameterPartIndex = 0;

        foreach ($fullMatches as $index => $match) {
            if ('.' === $separators[$index] && \in_array(mb_strtolower($match), self::FORBIDDEN_EXTENSIONS, true)) {
                continue;
            }

            $part = $parts[$index];

            $rawParamMatches = [];
            if (\Safe\preg_match_all('/{(?P<parameter>[^{}]+)}/', $part, $rawParamMatches) > 0) {
                /** @var list<string> $paramNames */
                $paramNames = $rawParamMatches['parameter'] ?? [];
                foreach ($paramNames as $paramName) {
                    $withoutSnakes = \Safe\preg_replace_callback(
                        '/(^|_|\.)+(.)/',
                        static function (array $match): string {
                            $sep  = $match[1];
                            $char = $match[2];
                            if (!\is_string($sep) || !\is_string($char)) {
                                throw new LogicException('Expected string match groups, got ' . get_debug_type($sep) . ' and ' . get_debug_type($char));
                            }

                            return ('.' === $sep ? '_' : '') . strtoupper($char);
                        },
                        $paramName,
                    );
                    if (!\is_string($withoutSnakes)) {
                        throw new LogicException('Expected string, got ' . get_debug_type($withoutSnakes));
                    }

                    $methodNameParts[] = 'By' . ucfirst($withoutSnakes);
                }
            } else {
                $methodNameParts[]         = ucfirst($part);
                $lastNonParameterPartIndex = \count($methodNameParts) - 1;
            }
        }

        if ($shouldSingularize && [] !== $methodNameParts) {
            $methodNameParts[$lastNonParameterPartIndex] = $this->getInflector()->singularize($methodNameParts[$lastNonParameterPartIndex]);
        }

        return $prefix . ucfirst(implode('', $methodNameParts));
    }
}
