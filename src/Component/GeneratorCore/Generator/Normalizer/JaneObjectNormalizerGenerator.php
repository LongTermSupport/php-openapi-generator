<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Generator\Normalizer;

use PhpParser\Comment\Doc;
use PhpParser\Modifiers;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar;
use PhpParser\Node\Stmt;
use PhpParser\Node\UnionType;

trait JaneObjectNormalizerGenerator
{
    protected function createBaseNormalizerSupportsDenormalizationMethod(): Stmt\ClassMethod
    {
        return new Stmt\ClassMethod('supportsDenormalization', [
            'flags'      => Modifiers::PUBLIC,
            'returnType' => new Identifier('bool'),
            'params'     => [
                new Param(new Expr\Variable('data'), type: new Identifier('mixed')),
                new Param(new Expr\Variable('type'), type: new Identifier('string')),
                new Param(
                    new Expr\Variable('format'),
                    new Expr\ConstFetch(new Name('null')),
                    new Identifier('?string')
                ),
                new Param(new Expr\Variable('context'), new Expr\Array_(), new Identifier('array')),
            ],
            'stmts'      => [new Stmt\Return_(new Expr\FuncCall(new Name('array_key_exists'), [
                new Arg(new Expr\Variable('type')),
                new Arg(new Expr\PropertyFetch(new Expr\Variable('this'), 'normalizers')),
            ]))],
        ]);
    }

    protected function createBaseNormalizerSupportsNormalizationMethod(): Stmt\ClassMethod
    {
        return new Stmt\ClassMethod('supportsNormalization', [
            'flags'      => Modifiers::PUBLIC,
            'returnType' => new Identifier('bool'),
            'params'     => [
                new Param(new Expr\Variable('data'), type: new Identifier('mixed')),
                new Param(
                    new Expr\Variable('format'),
                    new Expr\ConstFetch(new Name('null')),
                    new Identifier('?string')
                ),
                new Param(new Expr\Variable('context'), new Expr\Array_(), new Identifier('array')),
            ],
            'stmts'      => [new Stmt\Return_(
                new Expr\BinaryOp\BooleanAnd(
                    new Expr\FuncCall(new Name('is_object'), [new Arg(new Expr\Variable('data'))]),
                    new Expr\FuncCall(new Name('array_key_exists'), [
                        new Arg(new Expr\ClassConstFetch(new Expr\Variable('data'), new Identifier('class'))),
                        new Arg(new Expr\PropertyFetch(new Expr\Variable('this'), 'normalizers')),
                    ])
                )
            )],
        ]);
    }

    protected function createBaseNormalizerNormalizeMethod(): Stmt\ClassMethod
    {
        return new Stmt\ClassMethod('normalize', [
            'flags'      => Modifiers::PUBLIC,
            'returnType' => new UnionType([
                new Identifier('array'), new Identifier('string'), new Identifier('int'), new Identifier('float'), new Identifier('bool'), new Name('\ArrayObject'), new Identifier('null'), ]),
            'params'     => [
                new Param(new Expr\Variable('data'), type: new Identifier('mixed')),
                new Param(new Expr\Variable('format'), new Expr\ConstFetch(new Name('null')), new Identifier('?string')),
                new Param(new Expr\Variable('context'), new Expr\Array_(), new Identifier('array')),
            ],
            'stmts'      => [
                new Stmt\If_(
                    new Expr\BooleanNot(new Expr\FuncCall(new Name('is_object'), [new Arg(new Expr\Variable('data'))])),
                    ['stmts' => [
                        new Stmt\Expression(new Expr\Throw_(new Expr\New_(
                            new Name('\LogicException'),
                            [new Arg(new Expr\BinaryOp\Concat(
                                new Scalar\String_('Expected object, got '),
                                new Expr\FuncCall(new Name('get_debug_type'), [new Arg(new Expr\Variable('data'))])
                            ))]
                        ))),
                    ]]
                ),
                new Stmt\Expression(new Expr\Assign(
                    new Expr\Variable('normalizerClass'),
                    new Expr\ArrayDimFetch(
                        new Expr\PropertyFetch(new Expr\Variable('this'), 'normalizers'),
                        new Expr\ClassConstFetch(new Expr\Variable('data'), new Identifier('class'))
                    )
                )),
                new Stmt\Expression(new Expr\Assign(
                    new Expr\Variable('normalizer'),
                    new Expr\MethodCall(new Expr\Variable('this'), 'getNormalizer', [
                        new Arg(new Expr\Variable('normalizerClass')),
                    ])
                )),
                new Stmt\Expression(new Expr\Assign(
                    new Expr\Variable('result'),
                    new Expr\MethodCall(new Expr\Variable('normalizer'), 'normalize', [
                        new Arg(new Expr\Variable('data')),
                        new Arg(new Expr\Variable('format')),
                        new Arg(new Expr\Variable('context')),
                    ])
                )),
                new Stmt\Return_(new Expr\Variable('result')),
            ],
        ], [
            'comments' => [new Doc("/**\n * @return array<string, mixed>|\\ArrayObject<string, mixed>|bool|float|int|string|null\n * @phpstan-return array<int|string, mixed>|string|int|float|bool|\\ArrayObject<int|string, mixed>|null\n */")],
        ]);
    }

    protected function createBaseNormalizerDenormalizeMethod(): Stmt\ClassMethod
    {
        // Generic dispatcher implementing Symfony's DenormalizerInterface. The
        // signature is fixed by the interface: `(mixed, string, ?string, array): mixed`.
        // Type safety lives in two places:
        //   1. Per-class normalizers (e.g. AgentResponseNormalizer) — emit a
        //      compound `\Foo|Reference` return type that callers can rely on.
        //   2. Endpoint `transformResponseBody()` — wraps `serializer->deserialize()`
        //      with type guards and exposes a precise union of response types.
        // This method should never be called from user code directly; consumers
        // see only the typed wrappers above.
        return new Stmt\ClassMethod('denormalize', [
            'flags'      => Modifiers::PUBLIC,
            'returnType' => new Identifier('mixed'),
            'params'     => [
                new Param(new Expr\Variable('data'), type: new Identifier('mixed')),
                new Param(new Expr\Variable('type'), type: new Identifier('string')),
                new Param(new Expr\Variable('format'), new Expr\ConstFetch(new Name('null')), new Identifier('?string')),
                new Param(new Expr\Variable('context'), new Expr\Array_(), new Identifier('array')),
            ],
            'stmts'      => [
                new Stmt\Expression(new Expr\Assign(
                    new Expr\Variable('denormalizerClass'),
                    new Expr\ArrayDimFetch(
                        new Expr\PropertyFetch(new Expr\Variable('this'), 'normalizers'),
                        new Expr\Variable('type')
                    )
                )),
                new Stmt\Expression(new Expr\Assign(
                    new Expr\Variable('denormalizer'),
                    new Expr\MethodCall(new Expr\Variable('this'), 'getNormalizer', [
                        new Arg(new Expr\Variable('denormalizerClass')),
                    ])
                )),
                new Stmt\Expression(new Expr\Assign(
                    new Expr\Variable('result'),
                    new Expr\MethodCall(new Expr\Variable('denormalizer'), 'denormalize', [
                        new Arg(new Expr\Variable('data')),
                        new Arg(new Expr\Variable('type')),
                        new Arg(new Expr\Variable('format')),
                        new Arg(new Expr\Variable('context')),
                    ])
                )),
                new Stmt\If_(
                    new Expr\BooleanNot(new Expr\FuncCall(new Name('is_object'), [new Arg(new Expr\Variable('result'))])),
                    ['stmts' => [
                        new Stmt\Expression(new Expr\Throw_(new Expr\New_(
                            new Name('\LogicException'),
                            [new Arg(new Expr\BinaryOp\Concat(
                                new Scalar\String_('Expected object from denormalize, got '),
                                new Expr\FuncCall(new Name('get_debug_type'), [new Arg(new Expr\Variable('result'))])
                            ))]
                        ))),
                    ]]
                ),
                new Stmt\Return_(new Expr\Variable('result')),
            ],
        ]);
    }

    protected function createBaseNormalizerGetNormalizer(): Stmt\ClassMethod
    {
        return new Stmt\ClassMethod('getNormalizer', [
            'flags'  => Modifiers::PRIVATE,
            'params' => [
                new Param(new Expr\Variable('normalizerClass'), null, new Identifier('string')),
            ],
            'stmts'  => [
                new Stmt\Return_(new Expr\BinaryOp\Coalesce(
                    new Expr\ArrayDimFetch(
                        new Expr\PropertyFetch(new Expr\Variable('this'), 'normalizersCache'),
                        new Expr\Variable('normalizerClass')
                    ),
                    new Expr\MethodCall(new Expr\Variable('this'), 'initNormalizer', [
                        new Arg(new Expr\Variable('normalizerClass')),
                    ])
                )),
            ],
        ], [
            'comments' => [new Doc("/**\n * @param class-string \$normalizerClass\n * @return NormalizerInterface&DenormalizerInterface&NormalizerAwareInterface&DenormalizerAwareInterface\n */")],
        ]);
    }

    protected function createBaseNormalizerInitNormalizerMethod(): Stmt\ClassMethod
    {
        return new Stmt\ClassMethod('initNormalizer', [
            'flags'  => Modifiers::PRIVATE,
            'params' => [
                new Param(new Expr\Variable('normalizerClass'), null, new Identifier('string')),
            ],
            'stmts'  => [
                new Stmt\Expression(new Expr\Assign(
                    new Expr\Variable('normalizer'),
                    new Expr\New_(new Expr\Variable('normalizerClass'))
                )),
                new Stmt\If_(
                    new Expr\BooleanNot(new Expr\BinaryOp\BooleanAnd(
                        new Expr\Instanceof_(new Expr\Variable('normalizer'), new Name('NormalizerInterface')),
                        new Expr\BinaryOp\BooleanAnd(
                            new Expr\Instanceof_(new Expr\Variable('normalizer'), new Name('DenormalizerInterface')),
                            new Expr\BinaryOp\BooleanAnd(
                                new Expr\Instanceof_(new Expr\Variable('normalizer'), new Name('NormalizerAwareInterface')),
                                new Expr\Instanceof_(new Expr\Variable('normalizer'), new Name('DenormalizerAwareInterface'))
                            )
                        )
                    )),
                    ['stmts' => [
                        new Stmt\Expression(new Expr\Throw_(new Expr\New_(
                            new Name('\LogicException'),
                            [new Arg(new Expr\BinaryOp\Concat(
                                new Scalar\String_('Normalizer class must implement NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface and DenormalizerAwareInterface, got '),
                                new Expr\FuncCall(new Name('get_debug_type'), [new Arg(new Expr\Variable('normalizer'))])
                            ))]
                        ))),
                    ]]
                ),
                new Stmt\Expression(new Expr\MethodCall(new Expr\Variable('normalizer'), 'setNormalizer', [
                    new Arg(new Expr\PropertyFetch(new Expr\Variable('this'), 'normalizer')),
                ])),
                new Stmt\Expression(new Expr\MethodCall(new Expr\Variable('normalizer'), 'setDenormalizer', [
                    new Arg(new Expr\PropertyFetch(new Expr\Variable('this'), 'denormalizer')),
                ])),
                new Stmt\Expression(new Expr\Assign(
                    new Expr\ArrayDimFetch(
                        new Expr\PropertyFetch(new Expr\Variable('this'), 'normalizersCache'),
                        new Expr\Variable('normalizerClass')
                    ),
                    new Expr\Variable('normalizer')
                )),
                new Stmt\Return_(new Expr\Variable('normalizer')),
            ],
        ], [
            'comments' => [new Doc("/**\n * @param class-string \$normalizerClass\n * @return NormalizerInterface&DenormalizerInterface&NormalizerAwareInterface&DenormalizerAwareInterface\n */")],
        ]);
    }
}
