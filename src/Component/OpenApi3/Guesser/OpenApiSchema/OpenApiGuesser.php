<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApi3\Guesser\OpenApiSchema;

use ArrayObject;
use LogicException;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\ChainGuesserAwareInterface;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\ChainGuesserAwareTrait;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\ClassGuesserInterface;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\GuesserInterface;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\GuesserResolverTrait;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Registry\Registry;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Components;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\OpenApi;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Operation;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Parameter;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\PathItem;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\RequestBody;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Response;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Schema;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Guesser\Guess\OperationGuess;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Naming\ChainOperationNaming;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Naming\OperationIdNaming;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Naming\OperationNamingInterface;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Naming\OperationUrlNaming;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Registry\Registry as OpenApiRegistry;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Registry\Schema as OpenApiRegistrySchema;
use LongTermSupport\OpenApiGenerator\Component\OpenApiRuntime\Reference;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\String\Slugger\SluggerInterface;

class OpenApiGuesser implements GuesserInterface, ClassGuesserInterface, ChainGuesserAwareInterface
{
    use ChainGuesserAwareTrait;
    use GuesserResolverTrait;

    private const string IN_BODY = 'body';

    private SluggerInterface $slugger;

    private OperationNamingInterface $naming;

    public function __construct(DenormalizerInterface $denormalizer)
    {
        $this->denormalizer = $denormalizer;
        $this->slugger      = new AsciiSlugger();
        $this->naming       = new ChainOperationNaming([
            new OperationIdNaming(),
            new OperationUrlNaming(),
        ]);
    }

    public function supportObject(mixed $object): bool
    {
        return $object instanceof OpenApi;
    }

    public function guessClass(mixed $object, string $name, string $reference, Registry $registry): void
    {
        if (!$object instanceof OpenApi) {
            throw new LogicException('Expected OpenApi, got ' . get_debug_type($object));
        }

        if (!$registry instanceof OpenApiRegistry) {
            throw new LogicException('Expected OpenApiRegistry, got ' . get_debug_type($registry));
        }

        if ($object->getComponents() instanceof Components) {
            $schemas = $object->getComponents()->getSchemas();
            if ($schemas instanceof ArrayObject) {
                foreach ($schemas as $key => $definition) {
                    $this->chainGuesser->guessClass($definition, $key, $reference . '/components/schemas/' . $key, $registry);
                }
            }

            $securitySchemes = $object->getComponents()->getSecuritySchemes();
            if ($securitySchemes instanceof ArrayObject) {
                foreach ($securitySchemes as $key => $definition) {
                    $this->chainGuesser->guessClass($definition, $key, $reference . '/components/securitySchemes/' . $key, $registry);
                }
            }

            $responses = $object->getComponents()->getResponses();
            if ($responses instanceof ArrayObject) {
                foreach ($responses as $responseName => $response) {
                    if (!$response instanceof Response) {
                        continue;
                    }

                    $responseContent = $response->getContent();
                    if (null !== $responseContent) {
                        foreach ($responseContent as $contentType => $content) {
                            if ('application/problem+json' === $contentType && null === $content->getSchema()) {
                                $content->setSchema($this->getApplicationProblemJsonDefaultSchema());
                            }

                            $this->chainGuesser->guessClass($content->getSchema(), 'Response' . ucfirst((string)$responseName), $reference . '/components/responses/' . $responseName . '/content/' . $contentType . '/schema', $registry);
                        }
                    }
                }
            }
        }

        $paths                 = $object->getPaths();
        $whitelistedPaths      = $registry->getWhitelistedPaths();
        $checkWhitelistedPaths = [] !== $whitelistedPaths;
        /** @var array<string> $globalSecurityScopes */
        $globalSecurityScopes = [];
        foreach ($object->getSecurity() ?? [] as $securityItem) {
            foreach ($securityItem as $scope => $_) {
                $globalSecurityScopes[] = $scope;
            }
        }

        foreach ($paths as $pathName => $path) {
            if ($checkWhitelistedPaths && null === ($allowedMethods = $this->isWhitelisted((string)$pathName, $whitelistedPaths))) {
                continue;
            }

            if ($path instanceof PathItem) {
                if ($checkWhitelistedPaths) {
                    if (\in_array(OperationGuess::DELETE, $allowedMethods, true)) {
                        $this->guessClassFromOperation($path, $path->getDelete(), (string)$pathName, OperationGuess::DELETE, $reference, $globalSecurityScopes, $registry);
                    }

                    if (\in_array(OperationGuess::GET, $allowedMethods, true)) {
                        $this->guessClassFromOperation($path, $path->getGet(), (string)$pathName, OperationGuess::GET, $reference, $globalSecurityScopes, $registry);
                    }

                    if (\in_array(OperationGuess::HEAD, $allowedMethods, true)) {
                        $this->guessClassFromOperation($path, $path->getHead(), (string)$pathName, OperationGuess::HEAD, $reference, $globalSecurityScopes, $registry);
                    }

                    if (\in_array(OperationGuess::OPTIONS, $allowedMethods, true)) {
                        $this->guessClassFromOperation($path, $path->getOptions(), (string)$pathName, OperationGuess::OPTIONS, $reference, $globalSecurityScopes, $registry);
                    }

                    if (\in_array(OperationGuess::PATCH, $allowedMethods, true)) {
                        $this->guessClassFromOperation($path, $path->getPatch(), (string)$pathName, OperationGuess::PATCH, $reference, $globalSecurityScopes, $registry);
                    }

                    if (\in_array(OperationGuess::POST, $allowedMethods, true)) {
                        $this->guessClassFromOperation($path, $path->getPost(), (string)$pathName, OperationGuess::POST, $reference, $globalSecurityScopes, $registry);
                    }

                    if (\in_array(OperationGuess::PUT, $allowedMethods, true)) {
                        $this->guessClassFromOperation($path, $path->getPut(), (string)$pathName, OperationGuess::PUT, $reference, $globalSecurityScopes, $registry);
                    }
                } else {
                    $this->guessClassFromOperation($path, $path->getDelete(), (string)$pathName, OperationGuess::DELETE, $reference, $globalSecurityScopes, $registry);
                    $this->guessClassFromOperation($path, $path->getGet(), (string)$pathName, OperationGuess::GET, $reference, $globalSecurityScopes, $registry);
                    $this->guessClassFromOperation($path, $path->getHead(), (string)$pathName, OperationGuess::HEAD, $reference, $globalSecurityScopes, $registry);
                    $this->guessClassFromOperation($path, $path->getOptions(), (string)$pathName, OperationGuess::OPTIONS, $reference, $globalSecurityScopes, $registry);
                    $this->guessClassFromOperation($path, $path->getPatch(), (string)$pathName, OperationGuess::PATCH, $reference, $globalSecurityScopes, $registry);
                    $this->guessClassFromOperation($path, $path->getPost(), (string)$pathName, OperationGuess::POST, $reference, $globalSecurityScopes, $registry);
                    $this->guessClassFromOperation($path, $path->getPut(), (string)$pathName, OperationGuess::PUT, $reference, $globalSecurityScopes, $registry);
                }

                $pathParameters = $path->getParameters();
                if (null !== $pathParameters) {
                    foreach ($pathParameters as $key => $parameter) {
                        if ($parameter instanceof Parameter && self::IN_BODY === $parameter->getIn()) {
                            $this->chainGuesser->guessClass($parameter->getSchema(), $pathName . 'Body' . $key, $reference . '/' . $pathName . '/parameters/' . $key, $registry);
                        }
                    }
                }
            }
        }

        if ($object->getComponents() instanceof Components) {
            $componentParams = $object->getComponents()->getParameters();
            if ($componentParams instanceof ArrayObject) {
                foreach ($componentParams as $parameterName => $parameter) {
                    if ($parameter instanceof Parameter && self::IN_BODY === $parameter->getIn()) {
                        $this->chainGuesser->guessClass($parameter->getSchema(), (string)$parameterName, $reference . '/parameters/' . $parameterName, $registry);
                    }
                }
            }
        }
    }

    /**
     * @param array<string> $globalSecurityScopes
     */
    protected function guessClassFromOperation(PathItem $pathItem, ?Operation $operation, string $path, string $operationType, string $reference, array $globalSecurityScopes, OpenApiRegistry $registry): void
    {
        if (!$operation instanceof Operation) {
            return;
        }

        $securityScopes = $globalSecurityScopes;
        foreach ($operation->getSecurity() ?? [] as $securityItem) {
            foreach ($securityItem as $scope => $_) {
                $securityScopes[] = $scope;
            }
        }

        $securityScopes = array_unique($securityScopes);

        $name           = $path . ucfirst(strtolower($operationType));
        $reference      = $reference . '/' . $path . '/' . strtolower($operationType);
        $operationGuess = new OperationGuess($pathItem, $operation, $path, $operationType, $reference, $securityScopes);
        $operationName  = $this->naming->getEndpointName($operationGuess);

        $schema = $registry->getSchema($reference);
        if (!$schema instanceof OpenApiRegistrySchema) {
            throw new LogicException('Expected OpenApiRegistrySchema for reference ' . $reference . ', got ' . get_debug_type($schema));
        }

        $schema->addOperation($reference, $operationGuess);
        $schema->initOperationRelations($operationName);

        if (null !== $operation->getParameters() && [] !== $operation->getParameters()) {
            foreach ($operation->getParameters() as $key => $parameter) {
                if ($parameter instanceof Parameter && self::IN_BODY === $parameter->getIn()) {
                    $subReference = $reference . '/parameters/' . $key;
                    $this->chainGuesser->guessClass($parameter->getSchema(), $name . 'Body', $subReference, $registry);
                    if (($guessClass = $schema->getClass($subReference)) instanceof \LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Guess\ClassGuess) {
                        $schema->addOperationRelation($operationName, $guessClass->getName());
                    }
                }
            }
        }

        $requestBody = $operation->getRequestBody();
        // PHPDoc says Reference|RequestBody|null but runtime Reference is OpenApiRuntime\Reference
        if ($requestBody instanceof Reference) {
            $resolved = $this->resolve($requestBody, RequestBody::class);
            if ($resolved instanceof RequestBody) {
                $operation->setRequestBody($resolved);
            }
        }

        $requestBodyObj = $operation->getRequestBody();
        if ($requestBodyObj instanceof RequestBody && null !== $requestBodyObj->getContent()) {
            foreach ($requestBodyObj->getContent() as $contentType => $content) {
                $subReference = $reference . '/requestBody/content/' . $contentType . '/schema';
                $this->chainGuesser->guessClass($content->getSchema(), $name . 'Body', $subReference, $registry);
                if (($guessClass = $schema->getClass($subReference)) instanceof \LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Guess\ClassGuess) {
                    $schema->addOperationRelation($operationName, $guessClass->getName());
                }
            }
        }

        $operationResponses = $operation->getResponses();
        if ($operationResponses instanceof \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Responses) {
            foreach ($operationResponses as $status => $response) {
                if ($response instanceof Response) {
                    $responseContent = $response->getContent();
                    if (null !== $responseContent) {
                        $contentCount = \count($responseContent);
                        foreach ($responseContent as $contentType => $content) {
                            // Make sure the response class names are unique when we have multiple response types.
                            $responseName = $contentCount > 1
                                ? $name . $this->slugContentType((string)$contentType) . 'Response' . $status
                                : $name . 'Response' . $status;
                            $subReference = $reference . '/responses/' . $status . '/content/' . $contentType . '/schema';
                            $this->chainGuesser->guessClass($content->getSchema(), $responseName, $subReference, $registry);
                            if (($guessClass = $schema->getClass($subReference)) instanceof \LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Guess\ClassGuess) {
                                $schema->addOperationRelation($operationName, $guessClass->getName());
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * @param array<mixed> $whitelistedPaths
     *
     * @return array<string>|null
     */
    private function isWhitelisted(string $path, array $whitelistedPaths): ?array
    {
        foreach ($whitelistedPaths as $data) {
            $whitelistedPath = $data;
            /** @var array<string> $whitelistedMethods */
            $whitelistedMethods = [];
            if (\is_string($data) || (\is_array($data) && 1 === \count($data))) {
                $whitelistedMethods = [
                    OperationGuess::DELETE,
                    OperationGuess::GET,
                    OperationGuess::HEAD,
                    OperationGuess::OPTIONS,
                    OperationGuess::PATCH,
                    OperationGuess::POST,
                    OperationGuess::PUT,
                ];
            } elseif (\is_array($data) && 2 === \count($data)) {
                $methodsValue = $data[1];
                if (\is_string($methodsValue)) {
                    $whitelistedMethods = [$methodsValue];
                } elseif (\is_array($methodsValue)) {
                    foreach ($methodsValue as $m) {
                        if (\is_string($m)) {
                            $whitelistedMethods[] = $m;
                        }
                    }
                }
            }

            if (\is_array($data)) {
                $whitelistedPath = $data[0];
            }

            if (!\is_string($whitelistedPath)) {
                continue;
            }

            if (1 === \Safe\preg_match(\sprintf('#%s#', $whitelistedPath), $path)) {
                return $whitelistedMethods;
            }
        }

        return null;
    }

    private function slugContentType(string $contentType): string
    {
        return ucfirst(str_replace('application', '', $this->slugger->slug($contentType, '')->toString()));
    }

    private function getApplicationProblemJsonDefaultSchema(): Schema
    {
        return new Schema()
            ->setType('object')
            ->setProperties([
                'status' => new Schema()
                    ->setType('integer'),
                'title'  => new Schema()
                    ->setType('string'),
                'type'   => new Schema()
                    ->setType('string')
                    ->setDefault('about:blank'),
                'detail' => new Schema()
                    ->setType('string'),
            ])
            ->setAdditionalProperties(true)
            ->setRequired(['type'])
        ;
    }
}
