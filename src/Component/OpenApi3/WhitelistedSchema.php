<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApi3;

use LogicException;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\MediaType;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Operation;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Parameter;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\RequestBody;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Response;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Schema as SchemaModel;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Contracts\WhitelistFetchInterface;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Guesser\Guess\OperationGuess;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Guesser\GuessClass;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Naming\ChainOperationNaming;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Naming\OperationIdNaming;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Naming\OperationNamingInterface;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Naming\OperationUrlNaming;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Registry\Registry;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Registry\Schema;
use LongTermSupport\OpenApiGenerator\Component\OpenApiRuntime\Reference;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class WhitelistedSchema implements WhitelistFetchInterface
{
    private readonly OperationNamingInterface $naming;

    private readonly GuessClass $guessClass;

    public function __construct(
        private readonly Schema $schema,
        DenormalizerInterface $denormalizer,
    ) {
        $this->naming = new ChainOperationNaming([
            new OperationIdNaming(),
            new OperationUrlNaming(),
        ]);
        $this->guessClass = new GuessClass(SchemaModel::class, $denormalizer);
    }

    public function addOperationRelations(OperationGuess $operationGuess, Registry $registry): void
    {
        $baseOperation = $this->naming->getEndpointName($operationGuess);
        if ($this->schema->relationExists($baseOperation)) {
            return;
        }

        $operation = $operationGuess->getOperation();
        if (!$operation instanceof Operation) {
            throw new LogicException('Expected Operation, got ' . get_debug_type($operation));
        }

        $requestBody = $operation->getRequestBody();
        if ($requestBody instanceof Reference) {
            $requestBody = null; // References not resolved here
        }

        if ($requestBody instanceof RequestBody && (null !== $requestBody->getContent() && is_iterable($requestBody->getContent()))) {
            foreach ($requestBody->getContent() as $contentType => $content) {
                if (!$content instanceof MediaType) {
                    throw new LogicException('Expected MediaType, got ' . get_debug_type($content));
                }

                if (\in_array($contentType, ['application/json', 'application/x-www-form-urlencoded'], true) || str_ends_with($contentType, '+json')) {
                    $contentReference = $operationGuess->getReference() . '/content/' . $contentType . '/schema';
                    $schema           = $content->getSchema();
                    $classGuess       = $this->guessClass->guessClass($schema, $contentReference, $registry);
                    if ($classGuess instanceof \LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Guess\ClassGuess) {
                        $this->schema->addOperationRelation($baseOperation, $classGuess->getName());
                    }
                }
            }
        }

        $responses = $operation->getResponses();
        if ($responses instanceof JsonSchema\Model\Responses && \count($responses) > 0) {
            foreach ($responses as $response) {
                if ($response instanceof Reference) {
                    [$_, $response] = $this->guessClass->resolve($response, Response::class);
                }

                if (!$response instanceof Response) {
                    continue;
                }

                if (null === $response->getContent()) {
                    $schema     = null;
                    $classGuess = $this->guessClass->guessClass($schema, $operationGuess->getReference(), $registry);
                    if ($classGuess instanceof \LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Guess\ClassGuess) {
                        $this->schema->addOperationRelation($baseOperation, $classGuess->getName());
                    }
                }

                if (null !== $response->getContent() && is_iterable($response->getContent())) {
                    foreach ($response->getContent() as $contentType => $content) {
                        if (!$content instanceof MediaType) {
                            throw new LogicException('Expected MediaType, got ' . get_debug_type($content));
                        }

                        if ('application/json' === $contentType || str_ends_with($contentType, '+json')) {
                            $contentReference = $operationGuess->getReference() . '/content/' . $contentType . '/schema';
                            $schema           = $content->getSchema();
                            $classGuess       = $this->guessClass->guessClass($schema, $contentReference, $registry);
                            if ($classGuess instanceof \LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Guess\ClassGuess) {
                                $this->schema->addOperationRelation($baseOperation, $classGuess->getName());
                            }
                        }
                    }
                }
            }
        }

        $parameters = $operation->getParameters();
        if (null !== $parameters && [] !== $parameters) {
            foreach ($parameters as $key => $parameter) {
                if ($parameter instanceof Parameter && 'body' === $parameter->getIn()) {
                    $reference  = $operationGuess->getReference() . '/parameters/' . $key;
                    $schema     = $parameter->getSchema();
                    $classGuess = $this->guessClass->guessClass($schema, $reference, $registry);
                    if ($classGuess instanceof \LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Guess\ClassGuess) {
                        $this->schema->addOperationRelation($baseOperation, $classGuess->getName());
                    }
                }
            }
        }
    }
}
