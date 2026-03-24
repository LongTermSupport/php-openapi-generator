<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApi3\Generator;

use InvalidArgumentException;
use LogicException;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Generator\GeneratorInterface;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\Generator\Parameter\NonBodyParameterGenerator;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\Generator\RequestBodyContent\DefaultBodyContentGenerator;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\Generator\RequestBodyContent\FormBodyContentGenerator;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\Generator\RequestBodyContent\JsonBodyContentGenerator;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Generator\ExceptionGenerator;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Generator\OperationGenerator;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Naming\ChainOperationNaming;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Naming\OperationIdNaming;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Naming\OperationUrlNaming;
use PhpParser\ParserFactory;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class GeneratorFactory
{
    public static function build(DenormalizerInterface $serializer, string $endpointGeneratorClass): GeneratorInterface
    {
        $parser = new ParserFactory()->createForHostVersion();

        $nonBodyParameter   = new NonBodyParameterGenerator($serializer, $parser);
        $exceptionGenerator = new ExceptionGenerator();
        $operationNaming    = new ChainOperationNaming([
            new OperationIdNaming(),
            new OperationUrlNaming(),
        ]);

        $defaultContentGenerator = new DefaultBodyContentGenerator($serializer);
        $requestBodyGenerator    = new RequestBodyGenerator($defaultContentGenerator);
        $requestBodyGenerator->addRequestBodyGenerator(JsonBodyContentGenerator::JSON_TYPES, new JsonBodyContentGenerator($serializer));
        $requestBodyGenerator->addRequestBodyGenerator(['application/x-www-form-urlencoded', 'multipart/form-data'], new FormBodyContentGenerator($serializer));

        if (!class_exists($endpointGeneratorClass)) {
            throw new InvalidArgumentException(\sprintf('Unknown generator class %s', $endpointGeneratorClass));
        }

        $endpointGenerator = new $endpointGeneratorClass($operationNaming, $nonBodyParameter, $serializer, $exceptionGenerator, $requestBodyGenerator);
        if (!$endpointGenerator instanceof \LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Generator\EndpointGeneratorInterface) {
            throw new LogicException('Expected EndpointGeneratorInterface, got ' . get_debug_type($endpointGenerator));
        }

        $operationGenerator = new OperationGenerator($endpointGenerator);

        return new ClientGenerator($operationGenerator, $operationNaming);
    }
}
