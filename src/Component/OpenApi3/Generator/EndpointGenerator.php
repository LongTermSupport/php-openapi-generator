<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApi3\Generator;

use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Generator\Context\Context;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Generator\File;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Generator\Naming;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\Generator\Endpoint\GetConstructorTrait;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\Generator\Endpoint\GetGetBodyTrait;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\Generator\Endpoint\GetGetExtraHeadersTrait;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\Generator\Endpoint\GetGetOptionsResolverTrait;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\Generator\Endpoint\GetGetQueryAllowReservedTrait;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\Generator\Endpoint\GetGetUriTrait;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\Generator\Endpoint\GetTransformResponseBodyTrait;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\Generator\Parameter\NonBodyParameterGenerator;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\Guesser\GuessClass;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Schema;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Generator\Endpoint\GetAuthenticationScopesTrait;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Generator\Endpoint\GetGetMethodTrait;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Generator\EndpointGeneratorInterface;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Generator\ExceptionGenerator;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Generator\Traits\OptionResolverNormalizationTrait;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Guesser\Guess\OperationGuess;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Naming\OperationNamingInterface;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class EndpointGenerator implements EndpointGeneratorInterface
{
    use GetAuthenticationScopesTrait;
    use GetConstructorTrait;
    use GetGetBodyTrait;
    use GetGetExtraHeadersTrait;
    use GetGetMethodTrait;
    use GetGetOptionsResolverTrait;
    use GetGetQueryAllowReservedTrait;
    use GetGetUriTrait;
    use GetTransformResponseBodyTrait;
    use OptionResolverNormalizationTrait;

    public const IN_PATH = 'path';

    public const IN_QUERY = 'query';

    public const IN_HEADER = 'header';

    private GuessClass $guessClass;

    public function __construct(
        private readonly OperationNamingInterface $operationNaming,
        private readonly NonBodyParameterGenerator $nonBodyParameterGenerator,
        private readonly DenormalizerInterface $denormalizer,
        private readonly ExceptionGenerator $exceptionGenerator,
        private readonly RequestBodyGenerator $requestBodyGenerator,
    ) {
        $this->guessClass = new GuessClass(Schema::class, $denormalizer);
    }

    public function createEndpointClass(OperationGuess $operation, Context $context): array
    {
        $naming       = new Naming();
        $endpointName = $this->operationNaming->getEndpointName($operation);

        [$constructorMethod, $methodParams, $methodParamsDoc, $pathProperties] = $this->getConstructor($operation, $context, $this->guessClass, $this->nonBodyParameterGenerator, $this->requestBodyGenerator);
        [$transformBodyMethod, $outputTypes, $throwTypes]                      = $this->getTransformResponseBody($operation, $endpointName, $this->guessClass, $this->exceptionGenerator, $context);
        /** @var array<Stmt> $classStmts */
        $classStmts = array_merge($pathProperties, null === $constructorMethod ? [] : [$constructorMethod], [
            new Stmt\Use_([new Stmt\UseUse(new Name\FullyQualified($naming->getRuntimeClassFQCN($context->getCurrentSchema()->getNamespace(), ['Client'], 'EndpointTrait')))]),
            $this->getGetMethod($operation),
            $this->getGetUri($operation, $this->guessClass),
            $this->getGetBody($operation, $context, $this->guessClass, $this->requestBodyGenerator),
        ]);
        $class = new Stmt\Class_($endpointName, [
            'extends'    => new Name\FullyQualified($naming->getRuntimeClassFQCN($context->getCurrentSchema()->getNamespace(), ['Client'], 'BaseEndpoint')),
            'implements' => [new Name\FullyQualified($naming->getRuntimeClassFQCN($context->getCurrentSchema()->getNamespace(), ['Client'], 'Endpoint'))],
            'stmts'      => $classStmts,
        ]);

        /** @var array<string, mixed> $genericCustomQueryResolver */
        /** @var array<string, mixed> $operationCustomQueryResolver */
        [$genericCustomQueryResolver, $operationCustomQueryResolver] = $this->customOptionResolvers($operation, $context);

        $extraHeadersMethod       = $this->getExtraHeadersMethod($operation, $this->guessClass);
        $queryResolverMethod      = $this->getOptionsResolverMethod($operation, self::IN_QUERY, 'getQueryOptionsResolver', $this->guessClass, $this->nonBodyParameterGenerator, $operationCustomQueryResolver, $genericCustomQueryResolver);
        $headerResolverMethod     = $this->getOptionsResolverMethod($operation, self::IN_HEADER, 'getHeadersOptionsResolver', $this->guessClass, $this->nonBodyParameterGenerator);
        $queryAllowReservedMethod = $this->getQueryAllowReservedMethod($operation, 'getQueryAllowReserved', $this->guessClass);

        if ($extraHeadersMethod instanceof Stmt\ClassMethod) {
            $class->stmts[] = $extraHeadersMethod;
        }

        if ($queryResolverMethod instanceof Stmt\ClassMethod) {
            $class->stmts[] = $queryResolverMethod;
        }

        if ($headerResolverMethod instanceof Stmt\ClassMethod) {
            $class->stmts[] = $headerResolverMethod;
        }

        if ($queryAllowReservedMethod instanceof Stmt\ClassMethod) {
            $class->stmts[] = $queryAllowReservedMethod;
        }

        $class->stmts[] = $transformBodyMethod;
        $class->stmts[] = $this->getAuthenticationScopesMethod($operation);

        $file = new File(
            $context->getCurrentSchema()->getDirectory() . \DIRECTORY_SEPARATOR . 'Endpoint' . \DIRECTORY_SEPARATOR . $endpointName . '.php',
            new Stmt\Namespace_(
                new Name($context->getCurrentSchema()->getNamespace() . '\Endpoint'),
                [
                    $class,
                ]
            ),
            'Endpoint'
        );

        $context->getCurrentSchema()->addFile($file);

        return [$context->getCurrentSchema()->getNamespace() . '\Endpoint\\' . $endpointName, $methodParams, $methodParamsDoc, $outputTypes, $throwTypes];
    }
}
