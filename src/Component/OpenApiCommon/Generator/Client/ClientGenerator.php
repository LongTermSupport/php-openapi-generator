<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Generator\Client;

use Http\Discovery\Psr17FactoryDiscovery;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Generator\Context\Context;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Generator\Naming;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Registry\Schema;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Registry\Schema as BaseSchema;
use PhpParser\Comment\Doc;
use PhpParser\Modifiers;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Scalar;
use PhpParser\Node\Stmt;
use Psr\Http\Client\ClientInterface;
use Symfony\Component\Serializer\Encoder\JsonDecode;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;

trait ClientGenerator
{
    /**
     * @return list<Stmt>
     */
    abstract protected function getHttpClientCreateExpr(Context $context): array;

    protected function getSuffix(): string
    {
        return '';
    }

    protected function createResourceClass(Schema $schema, string $name): Stmt\Class_
    {
        $naming = new Naming();

        return new Stmt\Class_($name, [
            'extends' => new Name\FullyQualified($naming->getRuntimeClassFQCN($schema->getNamespace(), ['Client'], 'Client')),
        ]);
    }

    protected function getFactoryMethod(BaseSchema $schema, Context $context): Stmt
    {
        $params = [
            new Node\Param(new Expr\Variable('httpClient'), new Expr\ConstFetch(new Name('null')), new NullableType(new Name\FullyQualified(ClientInterface::class))),
            new Node\Param(new Expr\Variable('additionalPlugins'), new Expr\Array_(), new Node\Identifier('array')),
            new Node\Param(new Expr\Variable('additionalNormalizers'), new Expr\Array_(), new Node\Identifier('array')),
        ];

        return new Stmt\ClassMethod(
            'create',
            [
                'flags'      => Modifiers::STATIC | Modifiers::PUBLIC,
                'params'     => $params,
                'returnType' => new Name('self'),
                'stmts'      => [
                    new Stmt\If_(
                        new Expr\BinaryOp\Identical(new Expr\ConstFetch(new Name('null')), new Expr\Variable('httpClient')),
                        [
                            'stmts' => $this->getHttpClientCreateExpr($context),
                        ]
                    ),
                    new Stmt\Expression(new Expr\Assign(
                        new Expr\Variable('requestFactory'),
                        new Expr\StaticCall(
                            new Name\FullyQualified(Psr17FactoryDiscovery::class),
                            'findRequestFactory'
                        )
                    )),
                    new Stmt\Expression(new Expr\Assign(
                        new Expr\Variable('streamFactory'),
                        new Expr\StaticCall(
                            new Name\FullyQualified(Psr17FactoryDiscovery::class),
                            'findStreamFactory'
                        )
                    )),
                    new Stmt\Expression(new Expr\Assign(
                        new Expr\Variable('normalizers'),
                        new Expr\Array_([
                            new Expr\ArrayItem(new Expr\New_(new Name\FullyQualified(\Symfony\Component\Serializer\Normalizer\ArrayDenormalizer::class))),
                            new Expr\ArrayItem(new Expr\New_(new Name('\\' . $context->getCurrentSchema()->getNamespace() . '\Normalizer\JaneObjectNormalizer'))),
                        ])
                    )),
                    new Stmt\If_(
                        new Expr\BinaryOp\NotIdentical(
                            new Expr\Variable('additionalNormalizers'),
                            new Expr\Array_()
                        ),
                        [
                            'stmts' => [
                                new Stmt\Expression(new Expr\Assign(
                                    new Expr\Variable('normalizers'),
                                    new Expr\FuncCall(new Name('array_merge'), [
                                        new Node\Arg(new Expr\Variable('normalizers')),
                                        new Node\Arg(new Expr\Variable('additionalNormalizers')),
                                    ])
                                )),
                            ],
                        ]
                    ),
                    new Stmt\Expression(new Expr\Assign(
                        new Expr\Variable('serializer'),
                        new Expr\New_(
                            new Name\FullyQualified(Serializer::class),
                            [
                                new Node\Arg(new Expr\Variable('normalizers')),
                                new Node\Arg(
                                    new Expr\Array_([
                                        new Expr\ArrayItem(
                                            new Expr\New_(new Name\FullyQualified(JsonEncoder::class), [
                                                new Node\Arg(new Expr\New_(new Name\FullyQualified(JsonEncode::class))),
                                                new Node\Arg(new Expr\New_(new Name\FullyQualified(JsonDecode::class), [
                                                    new Node\Arg(new Expr\Array_([
                                                        new Expr\ArrayItem(new Expr\ConstFetch(new Name('true')), new Scalar\String_('json_decode_associative')),
                                                    ])),
                                                ])),
                                            ])
                                        ),
                                    ])
                                ),
                            ]
                        )
                    )),
                    new Stmt\Return_(
                        new Expr\New_(
                            new Name('self'),
                            [
                                new Node\Arg(new Expr\Variable('httpClient')),
                                new Node\Arg(new Expr\Variable('requestFactory')),
                                new Node\Arg(new Expr\Variable('serializer')),
                                new Node\Arg(new Expr\Variable('streamFactory')),
                            ]
                        )
                    ),
                ],
            ],
            [
                'comments' => [
                    new Doc("/**\n * @param list<\\Http\\Client\\Common\\Plugin> \$additionalPlugins\n * @param list<\\Symfony\\Component\\Serializer\\Normalizer\\NormalizerInterface> \$additionalNormalizers\n */"),
                ],
            ]
        );
    }
}
