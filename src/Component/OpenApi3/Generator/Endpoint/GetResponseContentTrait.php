<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApi3\Generator\Endpoint;

use LogicException;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\Guesser\GuessClass;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Response;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Normalizer\ResponseNormalizer;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Guesser\Guess\OperationGuess;
use LongTermSupport\OpenApiGenerator\Component\OpenApiRuntime\Reference;

trait GetResponseContentTrait
{
    /**
     * @return string[]
     */
    public function getContentTypes(OperationGuess $operation, GuessClass $guessClass): array
    {
        $produces = [];
        $op       = $operation->getOperation();
        if (!$op instanceof \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Operation) {
            throw new LogicException('Expected Operation, got ' . get_debug_type($op));
        }

        $responses = $op->getResponses();
        if ($responses instanceof \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Responses) {
            foreach ($responses as $response) {
                if ($response instanceof Reference) {
                    [, $response] = $guessClass->resolve($response, Response::class);
                }

                if (\is_array($response)) {
                    $normalizer = new ResponseNormalizer();
                    $normalizer->setDenormalizer($this->denormalizer);
                    $response = $normalizer->denormalize($response, Response::class);
                }

                if (!$response instanceof Response) {
                    throw new LogicException('Expected Response, got ' . get_debug_type($response));
                }

                $content = $response->getContent();
                if (null !== $content) {
                    foreach ($content as $contentType => $contentItem) {
                        $trimmedContentType = trim($contentType);
                        if ('' !== $trimmedContentType && !\in_array($trimmedContentType, $produces, true)) {
                            $produces[] = $trimmedContentType;
                        }
                    }
                }
            }

            $defaultResponse = $responses->getDefault();
            if (null !== $defaultResponse) {
                $response = $defaultResponse;

                if ($response instanceof Reference) {
                    [, $response] = $guessClass->resolve($response, Response::class);
                }

                if (!$response instanceof Response) {
                    throw new LogicException('Expected Response, got ' . get_debug_type($response));
                }

                $content = $response->getContent();
                if (null !== $content) {
                    foreach ($content as $contentType => $contentItem) {
                        $trimmedContentType = trim($contentType);
                        if ('' !== $trimmedContentType && !\in_array($trimmedContentType, $produces, true)) {
                            $produces[] = $trimmedContentType;
                        }
                    }
                }
            }
        }

        return $produces;
    }
}
