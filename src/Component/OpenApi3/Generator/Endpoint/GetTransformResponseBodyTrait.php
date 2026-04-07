<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApi3\Generator\Endpoint;

use LogicException;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Generator\Context\Context;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\Generator\RequestBodyContent\JsonBodyContentGenerator;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\Guesser\GuessClass;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Response;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Schema;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Normalizer\ResponseNormalizer;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Generator\ExceptionGenerator;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Guesser\Guess\OperationGuess;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Registry\Registry;
use LongTermSupport\OpenApiGenerator\Component\OpenApiRuntime\Reference;
use PhpParser\Comment\Doc;
use PhpParser\Modifiers;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar;
use PhpParser\Node\Stmt;
use Symfony\Component\Serializer\SerializerInterface;

trait GetTransformResponseBodyTrait
{
    /** @return array{0: Stmt\ClassMethod, 1: array<string>, 2: array<string>} */
    public function getTransformResponseBody(OperationGuess $operation, string $endpointName, GuessClass $guessClass, ExceptionGenerator $exceptionGenerator, Context $context): array
    {
        $outputStatements = [
            new Stmt\Expression(new Expr\Assign(new Expr\Variable('status'), new Expr\MethodCall(new Expr\Variable('response'), 'getStatusCode'))),
            new Stmt\Expression(new Expr\Assign(new Expr\Variable('body'), new Expr\Cast\String_(new Expr\MethodCall(new Expr\Variable('response'), 'getBody')))),
        ];

        $registry = $context->getRegistry();
        if (!$registry instanceof Registry) {
            throw new LogicException('Expected Registry, got ' . get_debug_type($registry));
        }

        $outputTypes = $registry->getThrowUnexpectedStatusCode() ? [] : ['null'];
        $throwTypes  = [];

        $op = $operation->getOperation();
        if (!$op instanceof \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Operation) {
            throw new LogicException('Expected Operation, got ' . get_debug_type($op));
        }

        $responses = $op->getResponses();

        if ($responses instanceof \LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Responses) {
            foreach ($responses as $status => $response) {
                $reference = $operation->getReference() . '/responses/' . $status;

                if ($response instanceof Reference) {
                    [$resolvedRef, $resolvedResponse] = $guessClass->resolve($response, Response::class);
                    if (!\is_string($resolvedRef)) {
                        throw new LogicException('Expected string, got ' . get_debug_type($resolvedRef));
                    }

                    $reference = $resolvedRef;
                    $response  = $resolvedResponse;
                }

                if (\is_array($response)) {
                    $normalizer = new ResponseNormalizer();
                    $normalizer->setDenormalizer($this->denormalizer);
                    $response = $normalizer->denormalize($response, Response::class);
                }

                if (!$response instanceof Response) {
                    throw new LogicException('Expected Response, got ' . get_debug_type($response));
                }

                [$newOutputTypes, $newThrowTypes, $ifStatements] = $this->createResponseDenormalizationStatement(
                    $endpointName,
                    $status,
                    $response,
                    $context,
                    $reference,
                    $response->getDescription() ?? '',
                    $guessClass,
                    $exceptionGenerator
                );

                if (!\is_array($newOutputTypes)) {
                    throw new LogicException('Expected array, got ' . get_debug_type($newOutputTypes));
                }

                if (!\is_array($newThrowTypes)) {
                    throw new LogicException('Expected array, got ' . get_debug_type($newThrowTypes));
                }

                if (!\is_array($ifStatements)) {
                    throw new LogicException('Expected array, got ' . get_debug_type($ifStatements));
                }

                $outputTypes      = array_merge($outputTypes, $newOutputTypes);
                $throwTypes       = array_merge($throwTypes, $newThrowTypes);
                $outputStatements = array_merge($outputStatements, $ifStatements);
            }

            $defaultResponse = $responses->getDefault();
            if (null !== $defaultResponse) {
                $response  = $defaultResponse;
                $reference = $operation->getReference() . '/responses/default';

                if ($response instanceof Reference) {
                    [$resolvedRef, $resolvedResponse] = $guessClass->resolve($response, Response::class);
                    if (!\is_string($resolvedRef)) {
                        throw new LogicException('Expected string, got ' . get_debug_type($resolvedRef));
                    }

                    $reference = $resolvedRef;
                    $response  = $resolvedResponse;
                }

                if (!$response instanceof Response) {
                    throw new LogicException('Expected Response, got ' . get_debug_type($response));
                }

                [$newOutputTypes, $newThrowTypes, $ifStatements] = $this->createResponseDenormalizationStatement(
                    $endpointName,
                    'default',
                    $response,
                    $context,
                    $reference,
                    $response->getDescription() ?? '',
                    $guessClass,
                    $exceptionGenerator
                );

                if (!\is_array($newOutputTypes)) {
                    throw new LogicException('Expected array, got ' . get_debug_type($newOutputTypes));
                }

                if (!\is_array($newThrowTypes)) {
                    throw new LogicException('Expected array, got ' . get_debug_type($newThrowTypes));
                }

                if (!\is_array($ifStatements)) {
                    throw new LogicException('Expected array, got ' . get_debug_type($ifStatements));
                }

                $outputTypes      = array_merge($outputTypes, $newOutputTypes);
                $throwTypes       = array_merge($throwTypes, $newThrowTypes);
                $outputStatements = array_merge($outputStatements, $ifStatements);
            }

            $outputTypes = array_unique($outputTypes);
            $throwTypes  = array_unique($throwTypes);
        }

        if ($registry->getThrowUnexpectedStatusCode()) {
            $exceptionGenerator->createBaseExceptions($context);

            $throwType        = '\\' . $context->getCurrentSchema()->getNamespace() . '\Exception\UnexpectedStatusCodeException';
            $throwTypes[]     = $throwType;
            $outputStatements = array_merge(
                $outputStatements,
                [
                    new Stmt\Expression(new Expr\Throw_(
                        new Expr\New_(
                            new Name($throwType),
                            [
                                new Node\Arg(new Expr\Variable('status')),
                                new Node\Arg(new Expr\Variable('body')),
                            ]
                        )
                    )),
                ]
            );
        } else {
            // Add fallback return null when not throwing on unexpected status codes,
            // but only if the last statement isn't already a return (avoids deadCode.unreachable)
            $lastStmt = end($outputStatements);
            if (!$lastStmt instanceof Stmt\Return_) {
                $outputStatements[] = new Stmt\Return_(new Expr\ConstFetch(new Name('null')));
            }
        }

        $throwsDoc = implode('', array_map(static fn (string $value): string => ' * @throws ' . $value . "\n", $throwTypes));

        [$returnTypeNode, $returnDocType] = $this->buildReturnType($outputTypes);

        $methodDoc = "/**\n"
            . " * {@inheritdoc}\n"
            . " *\n"
            . ' * @return ' . $returnDocType . "\n"
            . $throwsDoc
            . ' */';

        return [new Stmt\ClassMethod('transformResponseBody', [
            'flags'      => Modifiers::PROTECTED,
            'params'     => [
                new Node\Param(new Expr\Variable('response'), null, new Name\FullyQualified(\Psr\Http\Message\ResponseInterface::class)),
                new Node\Param(new Expr\Variable('serializer'), null, new Name\FullyQualified(SerializerInterface::class)),
                new Node\Param(new Expr\Variable('contentType'), new Expr\ConstFetch(new Name('null')), new Node\NullableType(new Name('string'))),
            ],
            'returnType' => $returnTypeNode,
            'stmts'      => $outputStatements,
        ], [
            'comments' => [new Doc($methodDoc)],
        ]), $outputTypes, $throwTypes];
    }

    /** @return array{0: array<string>, 1: array<string>, 2: array<Stmt>} */
    private function createResponseDenormalizationStatement(string $name, int|string $status, Response $response, Context $context, string $reference, string $description, GuessClass $guessClass, ExceptionGenerator $exceptionGenerator): array
    {
        // No content response
        $content = $response->getContent();
        if (null === $content || [] === $content) {
            [$returnType, $throwType, $returnStatement] = $this->createContentDenormalizationStatement(
                $name,
                $status,
                null,
                $context,
                $reference,
                $description,
                $guessClass,
                $exceptionGenerator
            );

            $returnTypes = null === $returnType ? [] : [$returnType];
            $throwTypes  = null === $throwType ? [] : [$throwType];

            if (!$returnStatement instanceof Stmt) {
                throw new LogicException('Expected Stmt, got ' . get_debug_type($returnStatement));
            }

            if ('default' === $status) {
                return [$returnTypes, $throwTypes, [$returnStatement]];
            }

            return [$returnTypes, $throwTypes, [new Stmt\If_(
                new Expr\BinaryOp\Identical(
                    new Scalar\LNumber((int)$status),
                    new Expr\Variable('status')
                ),
                [
                    'stmts' => [$returnStatement],
                ]
            )]];
        }

        $returnTypes = [];
        $throwTypes  = [];
        /** @var array<Stmt\If_> $statements */
        $statements = [];

        foreach ($content as $contentType => $mediaType) {
            if (\in_array($contentType, JsonBodyContentGenerator::JSON_TYPES, true) || str_ends_with($contentType, '+json')) {
                [$returnType, $throwType, $returnStatement] = $this->createContentDenormalizationStatement(
                    $name,
                    $status,
                    $mediaType->getSchema(),
                    $context,
                    $reference . '/content/' . $contentType . '/schema',
                    $description,
                    $guessClass,
                    $exceptionGenerator
                );

                if (null !== $returnType) {
                    $returnTypes[] = $returnType;
                }

                if (null !== $throwType) {
                    $throwTypes[] = $throwType;
                }

                if (!$returnStatement instanceof Stmt) {
                    throw new LogicException('Expected Stmt, got ' . get_debug_type($returnStatement));
                }

                $statements[] = new Stmt\If_(
                    new Expr\BinaryOp\NotIdentical(
                        new Expr\FuncCall(new Name('mb_strpos'), [
                            new Node\Arg(
                                new Expr\FuncCall(new Name('strtolower'), [
                                    new Node\Arg(new Expr\Variable('contentType')),
                                ]),
                            ),
                            new Node\Arg(new Scalar\String_(strtolower($contentType))),
                        ]),
                        new Expr\ConstFetch(new Name('false'))
                    ),
                    [
                        'stmts' => [$returnStatement],
                    ]
                );
            }
        }

        if ('default' === $status) {
            if (\count($statements) > 0) {
                return [$returnTypes, $throwTypes, [new Stmt\If_(
                    new Expr\BinaryOp\Identical(
                        new Expr\FuncCall(new Name('is_null'), [
                            new Node\Arg(new Expr\Variable('contentType')),
                        ]),
                        new Expr\ConstFetch(new Name('false'))
                    ),
                    [
                        'stmts' => $statements,
                    ]
                )]];
            }

            return [$returnTypes, $throwTypes, $statements];
        }

        // Avoid useless imbrication of ifs
        if (1 === \count($statements)) {
            $firstIf = $statements[0];

            return [$returnTypes, $throwTypes, [new Stmt\If_(
                new Expr\BinaryOp\BooleanAnd(
                    new Expr\BinaryOp\Identical(
                        new Expr\FuncCall(new Name('is_null'), [
                            new Node\Arg(new Expr\Variable('contentType')),
                        ]),
                        new Expr\ConstFetch(new Name('false'))
                    ),
                    new Expr\BinaryOp\BooleanAnd(
                        new Expr\BinaryOp\Identical(
                            new Scalar\LNumber((int)$status),
                            new Expr\Variable('status')
                        ),
                        $firstIf->cond
                    )
                ),
                [
                    'stmts' => $firstIf->stmts,
                ]
            )]];
        }

        return [$returnTypes, $throwTypes, [new Stmt\If_(
            new Expr\BinaryOp\BooleanAnd(
                new Expr\BinaryOp\Identical(
                    new Expr\FuncCall(new Name('is_null'), [
                        new Node\Arg(new Expr\Variable('contentType')),
                    ]),
                    new Expr\ConstFetch(new Name('false'))
                ),
                new Expr\BinaryOp\Identical(
                    new Scalar\LNumber((int)$status),
                    new Expr\Variable('status')
                )
            ),
            [
                'stmts' => $statements,
            ]
        )]];
    }

    /** @return array{0: string|null, 1: string|null, 2: Stmt} */
    private function createContentDenormalizationStatement(string $name, int|string $status, mixed $schema, Context $context, string $reference, string $description, GuessClass $guessClass, ExceptionGenerator $exceptionGenerator): array
    {
        $registry = $context->getRegistry();
        if (!$registry instanceof Registry) {
            throw new LogicException('Expected Registry, got ' . get_debug_type($registry));
        }

        $array         = null;
        $classGuess    = $guessClass->guessClass($schema, $reference, $registry, $array);
        $isArray       = (true === $array);
        $returnType    = 'null';
        $throwType     = null;
        $serializeStmt = new Expr\ConstFetch(new Name('null'));
        $class         = null;

        if ($classGuess instanceof \LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Guess\ClassGuess) {
            $schemaObj = $context->getRegistry()->getSchema($classGuess->getReference());
            $class     = ($schemaObj instanceof \LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Registry\Schema ? $schemaObj->getNamespace() : '') . '\Model\\' . $classGuess->getName();

            if ($isArray) {
                $class .= '[]';
            }

            $returnType = '\\' . $class;

            // Symfony's serializer is type-unsafe (`deserialize()` returns `mixed`).
            // We wrap the deserialize call in a runtime type guard so this method
            // can advertise a precise union return type. The wrap costs almost
            // nothing at runtime (one instanceof) but turns the entire endpoint
            // surface from `mixed` into concrete model classes that PHPStan can
            // prove. The element class (without `[]`) is used as the assertion
            // class-string; the deserialize call still receives the full
            // `\Foo\Bar[]` type so Symfony unwraps the array itself.
            $elementClass = $isArray ? substr($class, 0, -2) : $class;

            $deserializeCall = new Expr\MethodCall(
                new Expr\Variable('serializer'),
                'deserialize',
                [
                    new Node\Arg(new Expr\Variable('body')),
                    new Node\Arg(new Scalar\String_($class)),
                    new Node\Arg(new Scalar\String_('json')),
                ]
            );

            $typeValidatorClass = new Name\FullyQualified(
                $context->getCurrentSchema()->getNamespace() . '\Runtime\Normalizer\TypeValidator'
            );

            $serializeStmt = new Expr\StaticCall(
                $typeValidatorClass,
                $isArray ? 'assertListOf' : 'assertInstanceOf',
                [
                    new Node\Arg($deserializeCall),
                    new Node\Arg(new Expr\ClassConstFetch(
                        new Name\FullyQualified(ltrim($elementClass, '\\')),
                        'class'
                    )),
                    new Node\Arg(new Scalar\String_('response body')),
                ]
            );
        } elseif ($schema instanceof Schema) {
            $returnType    = 'mixed';
            $serializeStmt = new Expr\FuncCall(new Name('json_decode'), [
                new Node\Arg(new Expr\Variable('body')),
            ]);
        }

        $contentStatement = new Stmt\Return_($serializeStmt);

        if ((int)$status >= 400) {
            $exceptionName = $exceptionGenerator->generate(
                $name,
                (int)$status,
                $context,
                $classGuess,
                $isArray,
                $class,
                $description
            );

            $returnType    = null;
            $throwType     = '\\' . $context->getCurrentSchema()->getNamespace() . '\Exception\\' . $exceptionName;
            $exceptionArgs = $classGuess instanceof \LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Guess\ClassGuess
                ? [new Node\Arg($serializeStmt), new Node\Arg(new Expr\Variable('response'))]
                : [new Node\Arg(new Expr\Variable('response'))];
            $contentStatement = new Stmt\Expression(new Expr\Throw_(new Expr\New_(new Name($throwType), $exceptionArgs)));
        }

        return [$returnType, $throwType, $contentStatement];
    }

    /**
     * Build a PHP return type node and phpdoc `@return` string from the
     * collected output type strings. Output types come in four shapes:
     *
     *   - 'null'        — endpoint may return null (default fallback)
     *   - '\Foo\Bar'    — concrete model class
     *   - '\Foo\Bar[]'  — list of model class
     *   - 'mixed'       — untyped schema (json_decode escape hatch)
     *
     * The PHP-level type is a precise UnionType wherever possible. Arrays of
     * objects degrade to PHP `array` (because PHP cannot express `list<T>` at
     * the type level) but the phpdoc keeps the precise `list<\Foo\Bar>`
     * projection so PHPStan still proves the element type. If `mixed` appears
     * anywhere in the union we collapse the entire return to `mixed`, because
     * PHP forbids `mixed` as a member of a union type.
     *
     * @param array<string> $outputTypes
     *
     * @return array{0: Node\Identifier|Name|Node\UnionType, 1: string} [phpReturnType, phpdocReturn]
     */
    private function buildReturnType(array $outputTypes): array
    {
        if (\in_array('mixed', $outputTypes, true)) {
            return [new Node\Identifier('mixed'), 'mixed'];
        }

        /** @var list<Node\Identifier|Name\FullyQualified> $phpTypes */
        $phpTypes = [];
        /** @var list<string> $docTypes */
        $docTypes = [];
        $seenPhp  = [];

        foreach (array_unique($outputTypes) as $type) {
            if ('null' === $type) {
                if (!isset($seenPhp['null'])) {
                    $phpTypes[]      = new Node\Identifier('null');
                    $seenPhp['null'] = true;
                }
                $docTypes[] = 'null';
                continue;
            }

            if (str_ends_with($type, '[]')) {
                if (!isset($seenPhp['array'])) {
                    $phpTypes[]       = new Node\Identifier('array');
                    $seenPhp['array'] = true;
                }
                $docTypes[] = 'list<' . substr($type, 0, -2) . '>';
                continue;
            }

            $fqcn = ltrim($type, '\\');
            if (!isset($seenPhp[$fqcn])) {
                $phpTypes[]     = new Name\FullyQualified($fqcn);
                $seenPhp[$fqcn] = true;
            }
            $docTypes[] = $type;
        }

        if (0 === \count($phpTypes)) {
            return [new Node\Identifier('mixed'), 'mixed'];
        }

        if (1 === \count($phpTypes)) {
            return [$phpTypes[0], implode('|', array_unique($docTypes))];
        }

        return [new Node\UnionType($phpTypes), implode('|', array_unique($docTypes))];
    }
}
