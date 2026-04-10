<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Generator;

use LogicException;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Generator\Context\Context;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Generator\File;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Guesser\Guess\ClassGuess;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Naming\ExceptionNaming;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Registry\Registry;
use PhpParser\Comment\Doc;
use PhpParser\Modifiers;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar;
use PhpParser\Node\Stmt;

class ExceptionGenerator
{
    private const array BANNED_VARIABLES = ['message', 'code', 'file', 'line'];

    /** @var array<int, string> */
    public static array $statusTexts = [
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Content Too Large',                                           // RFC-ietf-httpbis-semantics
        414 => 'URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => "I'm a teapot",                                               // RFC2324
        421 => 'Misdirected Request',                                         // RFC7540
        422 => 'Unprocessable Content',                                       // RFC-ietf-httpbis-semantics
        423 => 'Locked',                                                      // RFC4918
        424 => 'Failed Dependency',                                           // RFC4918
        425 => 'Too Early',                                                   // RFC-ietf-httpbis-replay-04
        426 => 'Upgrade Required',                                            // RFC2817
        428 => 'Precondition Required',                                       // RFC6585
        429 => 'Too Many Requests',                                           // RFC6585
        431 => 'Request Header Fields Too Large',                             // RFC6585
        451 => 'Unavailable For Legal Reasons',                               // RFC7725
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',                                     // RFC2295
        507 => 'Insufficient Storage',                                        // RFC4918
        508 => 'Loop Detected',                                               // RFC5842
        510 => 'Not Extended',                                                // RFC2774
        511 => 'Network Authentication Required',                             // RFC6585
    ];

    private readonly ExceptionNaming $exceptionNaming;

    /** @var array<string, array<int|string, bool>> */
    private array $initialized = [];

    /**
     * Tracks model FQDNs per generated exception so that when multiple content
     * types (e.g. application/json + application/scim+json) produce different
     * model classes for the same HTTP status code, the constructor parameter
     * can use a union type.
     *
     * @var array<string, array{propertyName: string, realPropertyName: string, fqdns: list<string>}>
     */
    private array $generatedExceptions = [];

    public function __construct()
    {
        $this->exceptionNaming = new ExceptionNaming();
    }

    public function generate(string $functionName, int $status, Context $context, ?ClassGuess $classGuess, bool $isArray, ?string $classFqdn, ?string $description): ?string
    {
        if ($status < 400) {
            return null;
        }

        if ((null === $description || '' === $description) && \array_key_exists($status, self::$statusTexts)) {
            $description = self::$statusTexts[$status];
        }

        $schema = $context->getCurrentSchema();
        $this->createBaseExceptions($context);

        $highLevelExceptionName = $this->createHighLevelException($context, $status);
        $exceptionName          = $this->exceptionNaming->generateExceptionName($status, $functionName);

        if ($classGuess instanceof ClassGuess) {
            $realPropertyName = lcfirst($classGuess->getName());
            $propertyName     = $realPropertyName;
            if ($isArray) {
                $propertyName .= 'List';
                $realPropertyName = $propertyName;
            }

            if (\in_array($propertyName, self::BANNED_VARIABLES, true)) {
                $propertyName = \sprintf('%sObject', $propertyName);
            }

            // Track models per exception to support union types when multiple content
            // types produce different model classes for the same HTTP status code.
            $exceptionKey = $schema->getDirectory() . '/' . $exceptionName;
            if (!isset($this->generatedExceptions[$exceptionKey])) {
                $this->generatedExceptions[$exceptionKey] = [
                    'propertyName'     => $propertyName,
                    'realPropertyName' => $realPropertyName,
                    'fqdns'            => [],
                ];
            } else {
                // Reuse the first model's property name for consistency
                $propertyName     = $this->generatedExceptions[$exceptionKey]['propertyName'];
                $realPropertyName = $this->generatedExceptions[$exceptionKey]['realPropertyName'];
            }

            if (null !== $classFqdn && !\in_array($classFqdn, $this->generatedExceptions[$exceptionKey]['fqdns'], true)) {
                $this->generatedExceptions[$exceptionKey]['fqdns'][] = $classFqdn;
            }

            $allFqdns  = $this->generatedExceptions[$exceptionKey]['fqdns'];
            $multiType = \count($allFqdns) > 1
                ? new Node\UnionType(array_map(static fn (string $fqdn): Name => new Name('\\' . $fqdn), $allFqdns))
                : new Name('\\' . $classFqdn);
            $modelType = $isArray ? new Name('array') : $multiType;

            $methodName         = 'get' . ucfirst($propertyName);
            $propertyAttributes = $isArray ? ['comments' => [new Doc(\sprintf("/**\n * @var %s[]\n */", '\\' . $classFqdn))]] : [];
            $exception          = new Stmt\Namespace_(new Name($schema->getNamespace() . '\Exception'), [
                new Stmt\Class_(
                    $exceptionName,
                    [
                        'extends' => new Name($highLevelExceptionName),
                        'stmts'   => [
                            new Stmt\Property(Modifiers::PRIVATE, [
                                new Stmt\PropertyProperty($propertyName),
                            ], $propertyAttributes, $modelType),
                            new Stmt\Property(Modifiers::PRIVATE, [
                                new Stmt\PropertyProperty('response'),
                            ], [], new Name\FullyQualified(\Psr\Http\Message\ResponseInterface::class)),
                            new Stmt\ClassMethod('__construct', [
                                'flags'  => Modifiers::PUBLIC,
                                'params' => [
                                    new Param(new Expr\Variable($realPropertyName), null, $modelType),
                                    new Param(new Expr\Variable('response'), null, new Name\FullyQualified(\Psr\Http\Message\ResponseInterface::class)),
                                ],
                                'stmts'  => [
                                    new Stmt\Expression(new Expr\StaticCall(new Name('parent'), '__construct', [new Node\Arg(new Scalar\String_($description ?? ''))])),
                                    new Stmt\Expression(new Expr\Assign(
                                        new Expr\PropertyFetch(
                                            new Expr\Variable('this'),
                                            $propertyName
                                        ),
                                        new Expr\Variable($realPropertyName)
                                    )),
                                    new Stmt\Expression(new Expr\Assign(
                                        new Expr\PropertyFetch(
                                            new Expr\Variable('this'),
                                            'response'
                                        ),
                                        new Expr\Variable('response')
                                    )),
                                ],
                            ]),
                            new Stmt\ClassMethod($methodName, [
                                'flags'      => Modifiers::PUBLIC,
                                'stmts'      => [
                                    new Stmt\Return_(
                                        new Expr\PropertyFetch(
                                            new Expr\Variable('this'),
                                            $propertyName
                                        )
                                    ),
                                ],
                                'returnType' => $modelType,
                            ], $isArray ? [
                                'comments' => [new Doc(\sprintf("/**\n * @return %s[]\n */", '\\' . $classFqdn))],
                            ] : []),
                            new Stmt\ClassMethod('getResponse', [
                                'flags'      => Modifiers::PUBLIC,
                                'stmts'      => [
                                    new Stmt\Return_(
                                        new Expr\PropertyFetch(
                                            new Expr\Variable('this'),
                                            'response'
                                        )
                                    ),
                                ],
                                'returnType' => new Name\FullyQualified(\Psr\Http\Message\ResponseInterface::class),
                            ]),
                        ],
                    ]
                ),
            ]);
            $schema->addFile(new File($schema->getDirectory() . '/Exception/' . $exceptionName . '.php', $exception, 'Exception'));

            return $exceptionName;
        }

        $exception = new Stmt\Namespace_(new Name($schema->getNamespace() . '\Exception'), [
            new Stmt\Class_(
                $exceptionName,
                [
                    'extends' => new Name($highLevelExceptionName),
                    'stmts'   => [
                        new Stmt\Property(Modifiers::PRIVATE, [
                            new Stmt\PropertyProperty('response'),
                        ], [], new Node\NullableType(new Name\FullyQualified(\Psr\Http\Message\ResponseInterface::class))),
                        new Stmt\ClassMethod('__construct', [
                            'flags'  => Modifiers::PUBLIC,
                            'params' => [
                                new Param(new Expr\Variable('response'), new Expr\ConstFetch(new Name('null')), new Node\NullableType(new Name\FullyQualified(\Psr\Http\Message\ResponseInterface::class))),
                            ],
                            'stmts'  => [
                                new Stmt\Expression(new Expr\StaticCall(new Name('parent'), '__construct', [
                                    new Node\Arg(new Scalar\String_($description ?? '')),
                                ])),
                                new Stmt\Expression(new Expr\Assign(
                                    new Expr\PropertyFetch(
                                        new Expr\Variable('this'),
                                        'response'
                                    ),
                                    new Expr\Variable('response')
                                )),
                            ],
                        ]),
                        new Stmt\ClassMethod('getResponse', [
                            'flags'      => Modifiers::PUBLIC,
                            'stmts'      => [
                                new Stmt\Return_(
                                    new Expr\PropertyFetch(
                                        new Expr\Variable('this'),
                                        'response'
                                    )
                                ),
                            ],
                            'returnType' => new Node\NullableType(new Name\FullyQualified(\Psr\Http\Message\ResponseInterface::class)),
                        ]),
                    ],
                ]
            ),
        ]);

        $schema->addFile(new File($schema->getDirectory() . '/Exception/' . $exceptionName . '.php', $exception, 'Exception'));

        return $exceptionName;
    }

    public function createBaseExceptions(Context $context): void
    {
        $schema   = $context->getCurrentSchema();
        $registry = $context->getRegistry();
        if (!$registry instanceof Registry) {
            throw new LogicException('Expected OpenApiCommon Registry, got ' . get_debug_type($registry));
        }

        $unique = $schema->getRootName() . $schema->getDirectory();
        if (\array_key_exists($unique, $this->initialized) && ($this->initialized[$unique]['base'] ?? false)) {
            return;
        }

        if (!\array_key_exists($unique, $this->initialized)) {
            $this->initialized[$unique] = [];
        }

        $this->initialized[$unique]['base'] = true;

        $apiException = new Stmt\Namespace_(new Name($schema->getNamespace() . '\Exception'), [
            new Stmt\Interface_(
                'ApiException',
                [
                    'extends' => [
                        new Name('\Throwable'),
                    ],
                ]
            ),
        ]);

        $clientException = new Stmt\Namespace_(new Name($schema->getNamespace() . '\Exception'), [
            new Stmt\Interface_(
                'ClientException',
                [
                    'extends' => [
                        new Name('ApiException'),
                    ],
                ]
            ),
        ]);

        $serverException = new Stmt\Namespace_(new Name($schema->getNamespace() . '\Exception'), [
            new Stmt\Interface_(
                'ServerException',
                [
                    'extends' => [
                        new Name('ApiException'),
                    ],
                ]
            ),
        ]);

        $schema->addFile(new File($schema->getDirectory() . '/Exception/ApiException.php', $apiException, 'Exception'));
        $schema->addFile(new File($schema->getDirectory() . '/Exception/ClientException.php', $clientException, 'Exception'));
        $schema->addFile(new File($schema->getDirectory() . '/Exception/ServerException.php', $serverException, 'Exception'));

        if ($registry->getThrowUnexpectedStatusCode()) {
            $unexpectedStatusCodeException = new Stmt\Namespace_(new Name($schema->getNamespace() . '\Exception'), [
                new Stmt\Class_(
                    'UnexpectedStatusCodeException',
                    [
                        'implements' => [
                            new Name('ClientException'),
                        ],
                        'extends'    => new Name('\RuntimeException'),
                        'flags'      => Modifiers::FINAL,
                        'stmts'      => [
                            new Stmt\ClassMethod('__construct', [
                                'flags'  => Modifiers::PUBLIC,
                                'params' => [
                                    new Param(new Expr\Variable('status'), null, new Name('int')),
                                    new Param(new Expr\Variable('message'), new Scalar\String_(''), new Name('string')),
                                ],
                                'stmts'  => [
                                    new Stmt\Expression(new Expr\StaticCall(new Name('parent'), '__construct', [
                                        new Node\Arg(new Expr\Variable('message')),
                                        new Node\Arg(new Expr\Variable('status')),
                                    ])),
                                ],
                            ]),
                        ],
                    ]
                ),
            ]);

            $schema->addFile(new File($schema->getDirectory() . '/Exception/UnexpectedStatusCodeException.php', $unexpectedStatusCodeException, 'Exception'));
        }
    }

    private function createHighLevelException(Context $context, int $code): string
    {
        $schema                 = $context->getCurrentSchema();
        $highLevelExceptionName = $this->exceptionNaming->generateExceptionName($code);
        $unique                 = $schema->getRootName() . $schema->getDirectory();

        if (\array_key_exists($unique, $this->initialized) && ($this->initialized[$unique][$code] ?? false)) {
            return $highLevelExceptionName;
        }

        if (!\array_key_exists($unique, $this->initialized)) {
            $this->initialized[$unique] = [];
        }

        $this->initialized[$unique][$code] = true;

        $highLevelException = new Stmt\Namespace_(new Name($schema->getNamespace() . '\Exception'), [
            new Stmt\Class_(
                $highLevelExceptionName,
                [
                    'extends'    => new Name('\RuntimeException'),
                    'implements' => [new Name($code >= 500 ? 'ServerException' : 'ClientException')],
                    'stmts'      => [
                        new Stmt\ClassMethod('__construct', [
                            'flags'  => Modifiers::PUBLIC,
                            'params' => [
                                new Param(new Expr\Variable('message'), null, new Name('string')),
                            ],
                            'stmts'  => [
                                new Stmt\Expression(new Expr\StaticCall(new Name('parent'), '__construct', [
                                    new Node\Arg(new Expr\Variable('message')),
                                    new Node\Arg(new Scalar\LNumber($code)),
                                ])),
                            ],
                        ]),
                    ],
                ]
            ),
        ]);

        $schema->addFile(new File(\sprintf('%s/Exception/%s.php', $schema->getDirectory(), $highLevelExceptionName), $highLevelException, 'Exception'));

        return $highLevelExceptionName;
    }
}
