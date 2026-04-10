<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApi3\Generator\Endpoint;

use LogicException;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Generator\Context\Context;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Tools\InflectorTrait;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\Generator\EndpointGenerator;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\Generator\Parameter\NonBodyParameterGenerator;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\Generator\RequestBodyGenerator;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\Guesser\GuessClass;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Parameter;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\RequestBody;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Schema;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Guesser\Guess\OperationGuess;
use LongTermSupport\OpenApiGenerator\Component\OpenApiRuntime\Reference;
use PhpParser\Comment\Doc;
use PhpParser\Modifiers;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;

trait GetConstructorTrait
{
    use GetResponseContentTrait;
    use InflectorTrait;

    /** @return array{0: Stmt\ClassMethod|null, 1: array<mixed>, 2: string, 3: array<Stmt>} */
    public function getConstructor(OperationGuess $operation, Context $context, GuessClass $guessClass, NonBodyParameterGenerator $nonBodyParameterGenerator, RequestBodyGenerator $requestBodyGenerator): array
    {
        $pathParams                    = [];
        $pathParamsDoc                 = [];
        $pathParamsWithDefaultValue    = [];
        $pathParamsWithDefaultValueDoc = [];
        $queryParamsDoc                = [];
        $headerParamsDoc               = [];
        $methodStatements              = [];
        $pathProperties                = [];
        $bodyParam                     = null;
        $bodyDoc                       = null;
        $bodyAssign                    = null;
        $contentTypes                  = $this->getContentTypes($operation, $guessClass);

        foreach ($operation->getParameters() as $key => $parameter) {
            if ($parameter instanceof Reference) {
                $parameter = $guessClass->resolveParameter($parameter);
            }

            if ($parameter instanceof Parameter && $parameter->getSchema() instanceof Reference) {
                [, $resolvedSchema] = $guessClass->resolve($parameter->getSchema(), Schema::class);
                if ($resolvedSchema instanceof Schema) {
                    $parameter->setSchema($resolvedSchema);
                } elseif (null === $resolvedSchema) {
                    $parameter->setSchema(null);
                }
            }

            if ($parameter instanceof Parameter && EndpointGenerator::IN_PATH === $parameter->getIn()) {
                $paramSchema = $parameter->getSchema();
                if (null === ($paramSchema instanceof Schema ? $paramSchema->getDefault() : null)) {
                    $pathParams[]    = $nonBodyParameterGenerator->generateMethodParameter($parameter, $context, $operation->getReference() . '/parameters/' . $key);
                    $pathParamsDoc[] = $nonBodyParameterGenerator->generateMethodDocParameter($parameter, $context, $operation->getReference() . '/parameters/' . $key);
                } else {
                    $pathParamsWithDefaultValue[]    = $nonBodyParameterGenerator->generateMethodParameter($parameter, $context, $operation->getReference() . '/parameters/' . $key);
                    $pathParamsWithDefaultValueDoc[] = $nonBodyParameterGenerator->generateMethodDocParameter($parameter, $context, $operation->getReference() . '/parameters/' . $key);
                }

                $paramName = $parameter->getName() ?? '';

                // When anyOf is present or the schema type is an OAS 3.1 array of types,
                // the constructor parameter will be a union type (e.g. string|int|null).
                // Path parameters always become URL strings, so use strval() to safely
                // convert to string, avoiding assign.propertyType errors.
                $schemaAnyOf  = $paramSchema instanceof Schema ? $paramSchema->getAnyOf() : null;
                $hasAnyOf     = null !== $schemaAnyOf && [] !== $schemaAnyOf;
                $schemaType   = $paramSchema instanceof Schema ? $paramSchema->getType() : null;
                $hasMultiType = \is_array($schemaType);

                $assignValue = ($hasAnyOf || $hasMultiType)
                    ? new Expr\FuncCall(new Name('strval'), [new Node\Arg(new Expr\Variable($this->getInflector()->camelize($paramName)))])
                    : new Expr\Variable($this->getInflector()->camelize($paramName));

                $methodStatements[] = new Stmt\Expression(new Expr\Assign(new Expr\PropertyFetch(new Expr\Variable('this'), $paramName), $assignValue));

                $paramNativeType = match ($paramSchema instanceof Schema ? $paramSchema->getType() : null) {
                    'string'  => new Node\Identifier('string'),
                    'integer' => new Node\Identifier('int'),
                    'number'  => new Node\Identifier('float'),
                    'boolean' => new Node\Identifier('bool'),
                    'array'   => new Node\Identifier('array'),
                    default   => new Node\Identifier('string'),
                };
                $propertyAttrs = [];
                if ('array' === ($paramSchema instanceof Schema ? $paramSchema->getType() : null)) {
                    $itemsType = 'mixed';
                    if ($paramSchema instanceof Schema) {
                        $items = $paramSchema->getItems();
                        if ($items instanceof Schema) {
                            $itemsType = match ($items->getType()) {
                                'string'  => 'string',
                                'integer' => 'int',
                                'number'  => 'float',
                                'boolean' => 'bool',
                                default   => 'mixed',
                            };
                        }
                    }

                    $propertyAttrs = ['comments' => [new Doc("/**\n * @var list<" . $itemsType . ">\n */")]];
                }

                $pathProperties[] = new Stmt\Property(Modifiers::PROTECTED, [
                    new Stmt\PropertyProperty($paramName),
                ], $propertyAttrs, $paramNativeType);
            }

            if ($parameter instanceof Parameter && EndpointGenerator::IN_QUERY === $parameter->getIn()) {
                $queryParamsDoc[] = $nonBodyParameterGenerator->generateOptionDocParameter($parameter);
            }

            if ($parameter instanceof Parameter && EndpointGenerator::IN_HEADER === $parameter->getIn()) {
                $headerParamsDoc[] = $nonBodyParameterGenerator->generateOptionDocParameter($parameter);
            }
        }

        $op = $operation->getOperation();
        if (!$op instanceof \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Operation) {
            throw new LogicException('Expected Operation, got ' . get_debug_type($op));
        }

        if (($requestBody = $op->getRequestBody()) instanceof RequestBody && null !== $requestBody->getContent()) {
            $bodyParam  = $requestBodyGenerator->generateMethodParameter($requestBody, $operation->getReference() . '/requestBody', $context);
            $bodyDoc    = $requestBodyGenerator->generateMethodDocParameter($requestBody, $operation->getReference() . '/requestBody', $context);
            $bodyAssign = new Stmt\Expression(new Expr\Assign(new Expr\PropertyFetch(new Expr\Variable('this'), 'body'), new Expr\Variable('requestBody')));
        }

        if (\count($contentTypes) > 1) {
            $pathProperties[] = new Stmt\Property(Modifiers::PROTECTED, [new Stmt\PropertyProperty('accept')], [
                'comments' => [new Doc("/**\n * @var list<string>\n */")],
            ], new Node\Identifier('array'));
        }

        $methodStatements = array_merge(
            $methodStatements,
            $bodyAssign instanceof Stmt\Expression ? [$bodyAssign] : [],
            [] !== $queryParamsDoc ? [new Stmt\Expression(new Expr\Assign(new Expr\PropertyFetch(new Expr\Variable('this'), 'queryParameters'), new Expr\Variable('queryParameters')))] : [],
            [] !== $headerParamsDoc ? [new Stmt\Expression(new Expr\Assign(new Expr\PropertyFetch(new Expr\Variable('this'), 'headerParameters'), new Expr\Variable('headerParameters')))] : [],
            \count($contentTypes) > 1 ? [new Stmt\Expression(new Expr\Assign(new Expr\PropertyFetch(new Expr\Variable('this'), 'accept'), new Expr\Variable('accept')))] : []
        );

        if ([] === $methodStatements) {
            return [null, [], '/**', []];
        }

        $methodParams = array_merge(
            $pathParams,
            $pathParamsWithDefaultValue,
            $bodyParam instanceof Node\Param ? [$bodyParam] : [],
            [] !== $queryParamsDoc ? [new Node\Param(new Expr\Variable('queryParameters'), new Expr\Array_(), new Name('array'))] : [],
            [] !== $headerParamsDoc ? [new Node\Param(new Expr\Variable('headerParameters'), new Expr\Array_(), new Name('array'))] : [],
            \count($contentTypes) > 1 ? [new Node\Param(new Expr\Variable('accept'), new Expr\Array_(), new Name('array'))] : []
        );

        $methodDocumentations = array_merge(
            $pathParamsDoc,
            $pathParamsWithDefaultValueDoc,
            null !== $bodyDoc ? [$bodyDoc] : [],
            []   !== $queryParamsDoc ? [\sprintf(" * @param array{\n%s\n * } \$queryParameters", implode("\n", $queryParamsDoc))] : [],
            []   !== $headerParamsDoc ? [\sprintf(" * @param array{\n%s\n * } \$headerParameters", implode("\n", $headerParamsDoc))] : [],
            \count($contentTypes) > 1 ? [' * @param list<string> $accept Accept content header ' .
                str_replace('*/', '*\/', implode('|', $this->getContentTypes($operation, $guessClass)))] : []
        );

        $methodParamsDoc = ['/**'];
        $description     = $op->getDescription();
        if (null !== $description && '' !== $description) {
            foreach (explode("\n", $description) as $line) {
                $methodParamsDoc[] = rtrim(' * ' . $line);
            }
        }

        $methodParamsDoc[] = implode("\n", $methodDocumentations);
        $methodParamsDoc[] = ' */';

        $methodParamsDoc = implode("\n", $methodParamsDoc);

        /** @var array<Node\Param> $filteredMethodParams */
        $filteredMethodParams = array_values(array_filter($methodParams, static fn (?Node\Param $p): bool => $p instanceof Node\Param));

        return [
            new Stmt\ClassMethod(
                '__construct',
                [
                    'flags'  => Modifiers::PUBLIC,
                    'params' => $filteredMethodParams,
                    'stmts'  => $methodStatements,
                ],
                [
                    'comments' => [
                        new Doc($methodParamsDoc),
                    ],
                ]
            ),
            $methodParams,
            $methodParamsDoc,
            $pathProperties,
        ];
    }
}
