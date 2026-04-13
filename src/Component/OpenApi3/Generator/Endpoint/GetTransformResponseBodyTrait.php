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
use PhpParser\NodeFinder;
use Symfony\Component\Serializer\SerializerInterface;

trait GetTransformResponseBodyTrait
{
    /** @return array{0: Stmt\ClassMethod, 1: array<string>, 2: array<string>} */
    public function getTransformResponseBody(OperationGuess $operation, string $endpointName, GuessClass $guessClass, ExceptionGenerator $exceptionGenerator, Context $context): array
    {
        // Locals `$status` and `$body` are prepended at the end, only if the
        // body statements actually reference them. Building the body first and
        // then walking it for references avoids emitting dead assignments like
        // `$body = (string) $response->getBody();` when nothing reads it.
        $outputStatements = [];

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

            // Strip any trailing bare `return null` statements that came from
            // `default` response handlers with no content. Those null fallbacks
            // are fully superseded by the unconditional throw below, and leaving
            // them in place causes a PHPStan `deadCode.unreachable` error.
            while ([] !== $outputStatements) {
                $last = end($outputStatements);
                if ($last instanceof Stmt\Return_ && $last->expr instanceof Expr\ConstFetch && 'null' === (string) $last->expr->name) {
                    array_pop($outputStatements);
                    // Also remove the corresponding 'null' from outputTypes since
                    // this null return is no longer emitted.
                    $outputTypes = array_values(array_filter($outputTypes, static fn (string $t): bool => 'null' !== $t));
                } else {
                    break;
                }
            }

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

        // Prepend local assignments only if referenced by the body. `$status`
        // is referenced by per-status if-conditions and by the unexpected
        // status throw; `$body` is referenced by deserialize/json_decode calls
        // and by the unexpected status throw.
        $prelude = [];
        if ($this->statementsReference($outputStatements, 'status')) {
            $prelude[] = new Stmt\Expression(new Expr\Assign(new Expr\Variable('status'), new Expr\MethodCall(new Expr\Variable('response'), 'getStatusCode')));
        }

        if ($this->statementsReference($outputStatements, 'body')) {
            $prelude[] = new Stmt\Expression(new Expr\Assign(new Expr\Variable('body'), new Expr\Cast\String_(new Expr\MethodCall(new Expr\Variable('response'), 'getBody'))));
        }

        $outputStatements = array_merge($prelude, $outputStatements);

        [$returnTypeNode, $returnDocType] = $this->buildReturnType($outputTypes);

        // `@return` only adds info when the phpdoc type is narrower than the
        // native PHP return type. The only shape that narrows is a generic
        // projection like `list<\Foo\Bar>` (PHP degrades these to plain
        // `array`). Everything else — `null`, `\Foo\Bar`, `\Foo\Bar|null`,
        // `mixed` — is expressed perfectly by the native return type and the
        // phpdoc restates nothing but noise.
        $docAddsReturnInfo = str_contains((string)$returnDocType, '<');

        $hasThrows  = [] !== $throwTypes;
        $throwsDoc  = implode('', array_map(static fn (string $value): string => ' * @throws ' . $value . "\n", $throwTypes));

        $classMethodAttributes = [];
        if ($docAddsReturnInfo || $hasThrows) {
            $docLines   = [];
            $docLines[] = '/**';
            $docLines[] = ' * {@inheritdoc}';
            $docLines[] = ' *';
            if ($docAddsReturnInfo) {
                $docLines[] = ' * @return ' . $returnDocType;
            }

            if ($hasThrows) {
                // Trim trailing newline from $throwsDoc so we can re-join cleanly.
                foreach (explode("\n", rtrim($throwsDoc, "\n")) as $throwLine) {
                    $docLines[] = $throwLine;
                }
            }

            $docLines[] = ' */';

            $classMethodAttributes = [
                'comments' => [new Doc(implode("\n", $docLines))],
            ];
        }

        return [new Stmt\ClassMethod('transformResponseBody', [
            'flags'      => Modifiers::PROTECTED,
            'params'     => [
                new Node\Param(new Expr\Variable('response'), null, new Name\FullyQualified(\Psr\Http\Message\ResponseInterface::class)),
                new Node\Param(new Expr\Variable('serializer'), null, new Name\FullyQualified(SerializerInterface::class)),
                new Node\Param(new Expr\Variable('contentType'), new Expr\ConstFetch(new Name('null')), new Node\NullableType(new Name('string'))),
            ],
            'returnType' => $returnTypeNode,
            'stmts'      => $outputStatements,
        ], $classMethodAttributes), $outputTypes, $throwTypes];
    }

    /**
     * Walk the given statement nodes and return true if any `$varName`
     * variable reference appears anywhere in the subtree. Used to decide
     * whether the generated `transformResponseBody()` needs its `$status` /
     * `$body` local assignments at all.
     *
     * @param array<Stmt> $stmts
     */
    private function statementsReference(array $stmts, string $varName): bool
    {
        $finder = new NodeFinder();

        $match = $finder->findFirst(
            $stmts,
            static fn (Node $node): bool => $node instanceof Expr\Variable
                && \is_string($node->name)
                && $varName === $node->name
        );

        return $match instanceof Node;
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
                    new Expr\FuncCall(new Name('str_contains'), [
                        new Node\Arg(
                            new Expr\FuncCall(new Name('strtolower'), [
                                new Node\Arg(new Expr\Variable('contentType')),
                            ]),
                        ),
                        new Node\Arg(new Scalar\String_(strtolower($contentType))),
                    ]),
                    [
                        'stmts' => [$returnStatement],
                    ]
                );
            }
        }

        if ('default' === $status) {
            if (\count($statements) > 0) {
                return [$returnTypes, $throwTypes, [new Stmt\If_(
                    new Expr\BinaryOp\NotIdentical(
                        new Expr\Variable('contentType'),
                        new Expr\ConstFetch(new Name('null'))
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
                    new Expr\BinaryOp\NotIdentical(
                        new Expr\Variable('contentType'),
                        new Expr\ConstFetch(new Name('null'))
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
                new Expr\BinaryOp\NotIdentical(
                    new Expr\Variable('contentType'),
                    new Expr\ConstFetch(new Name('null'))
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
            // $class is the element class — never has a [] suffix.
            // ExceptionGenerator already knows $isArray and appends [] to $class itself.
            $class = ($schemaObj instanceof \LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Registry\Schema ? $schemaObj->getNamespace() : '') . '\Model\\' . $classGuess->getName();

            if ($isArray) {
                // Array responses return a typed collection, not bare `array`.
                $collectionClass = $class . 'Collection';
                $returnType      = '\\' . $collectionClass;
            } else {
                $returnType = '\\' . $class;
            }

            // Symfony deserializer still receives `\Foo\Bar[]` to unwrap the JSON array;
            // the element class (without `[]`) is used for runtime assertListOf validation.
            $deserializeType = $isArray ? ($class . '[]') : $class;

            $deserializeCall = new Expr\MethodCall(
                new Expr\Variable('serializer'),
                'deserialize',
                [
                    new Node\Arg(new Expr\Variable('body')),
                    new Node\Arg(new Scalar\String_($deserializeType)),
                    new Node\Arg(new Scalar\String_('json')),
                ]
            );

            $typeValidatorClass = new Name\FullyQualified(
                $context->getCurrentSchema()->getNamespace() . '\Runtime\Normalizer\TypeValidator'
            );

            $assertCall = new Expr\StaticCall(
                $typeValidatorClass,
                $isArray ? 'assertListOf' : 'assertInstanceOf',
                [
                    new Node\Arg($deserializeCall),
                    new Node\Arg(new Expr\ClassConstFetch(
                        new Name\FullyQualified(ltrim($class, '\\')),
                        'class'
                    )),
                    new Node\Arg(new Scalar\String_('response body')),
                ]
            );

            if ($isArray) {
                // Wrap the validated list in a typed collection via spread constructor:
                // new BarItemCollection(...TypeValidator::assertListOf(...))
                $serializeStmt = new Expr\New_(
                    new Name\FullyQualified(ltrim($collectionClass, '\\')),
                    [new Node\Arg($assertCall, false, true)],
                );
            } else {
                $serializeStmt = $assertCall;
            }
        } elseif ($schema instanceof Schema) {
            if ($isArray) {
                $itemsSchema = $schema->getItems();
                if ($itemsSchema instanceof Schema) {
                    $scalarResult = $this->resolveScalarCollection($itemsSchema, $context);
                    if (null !== $scalarResult) {
                        [$returnType, $assertMethod] = $scalarResult;

                        $typeValidatorClass = new Name\FullyQualified(
                            $context->getCurrentSchema()->getNamespace() . '\Runtime\Normalizer\TypeValidator'
                        );

                        $assertCall = new Expr\StaticCall(
                            $typeValidatorClass,
                            $assertMethod,
                            [
                                new Node\Arg(new Expr\FuncCall(new Name\FullyQualified('json_decode'), [
                                    new Node\Arg(new Expr\Variable('body')),
                                ])),
                                new Node\Arg(new Scalar\String_('response body')),
                            ]
                        );

                        $serializeStmt = new Expr\New_(
                            new Name\FullyQualified(ltrim((string)$returnType, '\\')),
                            [new Node\Arg($assertCall, false, true)],
                        );
                    } else {
                        $returnType    = 'mixed';
                        $serializeStmt = new Expr\FuncCall(new Name('json_decode'), [
                            new Node\Arg(new Expr\Variable('body')),
                        ]);
                    }
                } else {
                    $returnType    = 'mixed';
                    $serializeStmt = new Expr\FuncCall(new Name('json_decode'), [
                        new Node\Arg(new Expr\Variable('body')),
                    ]);
                }
            } else {
                $returnType    = 'mixed';
                $serializeStmt = new Expr\FuncCall(new Name('json_decode'), [
                    new Node\Arg(new Expr\Variable('body')),
                ]);
            }
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
     * Resolves a scalar array item schema to a typed runtime collection class and its
     * corresponding TypeValidator assertion method.
     *
     * Supports the 4 JSON Schema scalar primitives (string, integer, number, boolean)
     * and their nullable counterparts. Multi-type combinations beyond T|null fall back
     * to null (which causes the caller to emit mixed/json_decode).
     *
     * Class names are derived by sorting type segments alphabetically and concatenating,
     * e.g. string|null → NullString → NullStringCollection.
     *
     * @return array{0: string, 1: string}|null ['\Ns\Runtime\Model\TypeCollection', 'assertListOfType']
     */
    private function resolveScalarCollection(Schema $itemsSchema, Context $context): ?array
    {
        $itemType = $itemsSchema->getType();

        // Normalise: a scalar type string becomes a single-element list;
        // OAS 3.1 allows an array of types (e.g. ["string", "null"]).
        if (\is_array($itemType)) {
            $types = $itemType;
        } elseif (\is_string($itemType)) {
            $types = [$itemType];
        } else {
            $types = [];
        }

        // OAS 3.0 nullable flag — treat as adding "null" to the type list.
        if (true === $itemsSchema->getNullable() && !\in_array('null', $types, true)) {
            $types[] = 'null';
        }

        // Map JSON Schema primitive types to collection class name segments.
        $segmentMap = [
            'string'  => 'String',
            'integer' => 'Int',
            'number'  => 'Float',
            'boolean' => 'Bool',
            'null'    => 'Null',
        ];

        // Supported compound names → TypeValidator method.
        $classToMethod = [
            'Bool'       => 'assertListOfBool',
            'BoolNull'   => 'assertListOfNullableBool',
            'Float'      => 'assertListOfFloat',
            'FloatNull'  => 'assertListOfNullableFloat',
            'Int'        => 'assertListOfInt',
            'IntNull'    => 'assertListOfNullableInt',
            'NullString' => 'assertListOfNullableString',
            'String'     => 'assertListOfString',
        ];

        $segments = [];
        foreach ($types as $type) {
            if (!\array_key_exists($type, $segmentMap)) {
                return null; // Unknown type — fall back to mixed
            }

            $segments[] = $segmentMap[$type];
        }

        if ([] === $segments) {
            return null;
        }

        sort($segments); // Alphabetical for consistent naming
        $segmentsKey = implode('', $segments); // e.g. 'String', 'IntNull', 'NullString'
        $className   = $segmentsKey . 'Collection';

        if (!\array_key_exists($segmentsKey, $classToMethod)) {
            return null; // Unsupported combination — fall back to mixed
        }

        $namespace = $context->getCurrentSchema()->getNamespace();
        $fqcn      = '\\' . $namespace . '\Runtime\Model\\' . $className;

        return [$fqcn, $classToMethod[$segmentsKey]];
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
            // No return statements — function only throws; PHP return type is `never`.
            return [new Node\Identifier('never'), 'never'];
        }

        if (1 === \count($phpTypes)) {
            return [$phpTypes[0], implode('|', array_unique($docTypes))];
        }

        return [new Node\UnionType($phpTypes), implode('|', array_unique($docTypes))];
    }
}
