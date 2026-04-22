<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApi3\Generator\RequestBodyContent;

use Http\Message\MultipartStream\MultipartStreamBuilder;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Generator\Context\Context;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\MediaType;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\Schema;
use PhpParser\Comment\Doc;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar;
use PhpParser\Node\Stmt;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class FormBodyContentGenerator extends AbstractBodyContentGenerator
{
    /**
     * @return array<mixed>
     */
    public function getSerializeStatements(MediaType $content, string $contentType, string $reference, Context $context): array
    {
        if (1 === \Safe\preg_match('/multipart\/form-data/', $contentType)) {
            $binaryFieldNames = $this->extractBinaryFieldNames($content);

            // Build the addResource call — pass filename option for binary file fields
            // so the multipart part includes Content-Disposition: ...; filename="<name>"
            if ([] !== $binaryFieldNames) {
                $binaryFieldItems = array_map(
                    static fn (string $name): Expr\ArrayItem => new Expr\ArrayItem(new Scalar\String_($name)),
                    $binaryFieldNames,
                );
                $addResourceStatement = new Stmt\If_(
                    new Expr\FuncCall(new Name('in_array'), [
                        new Arg(new Expr\Variable('key')),
                        new Arg(new Expr\Array_($binaryFieldItems)),
                        new Arg(new Expr\ConstFetch(new Name('true'))),
                    ]),
                    [
                        'stmts' => [
                            new Stmt\Expression(new Expr\MethodCall(new Expr\Variable('bodyBuilder'), 'addResource', [
                                new Arg(new Expr\Variable('key')),
                                new Arg(new Expr\Variable('value')),
                                new Arg(new Expr\Array_([
                                    new Expr\ArrayItem(new Expr\Variable('key'), new Scalar\String_('filename')),
                                ])),
                            ])),
                        ],
                        'else' => new Stmt\Else_([
                            new Stmt\Expression(new Expr\MethodCall(new Expr\Variable('bodyBuilder'), 'addResource', [
                                new Arg(new Expr\Variable('key')),
                                new Arg(new Expr\Variable('value')),
                            ])),
                        ]),
                    ]
                );
            } else {
                $addResourceStatement = new Stmt\Expression(new Expr\MethodCall(new Expr\Variable('bodyBuilder'), 'addResource', [
                    new Arg(new Expr\Variable('key')),
                    new Arg(new Expr\Variable('value')),
                ]));
            }

            return [
                new Stmt\Expression(new Expr\Assign(new Expr\Variable('bodyBuilder'), new Expr\New_(new Name('\\' . MultipartStreamBuilder::class), [
                    new Arg(new Expr\Variable('streamFactory')),
                ]))),
                // Guard: serializer must implement NormalizerInterface (which Symfony\Serializer always does)
                new Stmt\If_(
                    new Expr\BooleanNot(new Expr\Instanceof_(
                        new Expr\Variable('serializer'),
                        new Name('\\' . NormalizerInterface::class)
                    )),
                    ['stmts' => [
                        new Stmt\Expression(new Expr\Throw_(new Expr\New_(
                            new Name\FullyQualified('LogicException'),
                            [new Arg(new Scalar\String_('Expected serializer to implement NormalizerInterface'))]
                        ))),
                    ]]
                ),
                new Stmt\Expression(new Expr\Assign(new Expr\Variable('formParameters'), new Expr\MethodCall(new Expr\Variable('serializer'), 'normalize', [
                    new Arg(new Expr\PropertyFetch(new Expr\Variable('this'), 'body')),
                    new Arg(new Scalar\String_('json')),
                ])), [
                    'comments' => [new Doc('/** @var array<string, mixed> $formParameters */')],
                ]),
                new Stmt\Foreach_(new Expr\Variable('formParameters'), new Expr\Variable('value'), [
                    'keyVar' => new Expr\Variable('key'),
                    'stmts'  => [
                        new Stmt\Expression(new Expr\Assign(
                            new Expr\Variable('value'),
                            new Expr\Ternary(
                                new Expr\FuncCall(new Name('is_int'), [new Arg(new Expr\Variable('value'))]),
                                new Expr\Cast\String_(new Expr\Variable('value')),
                                new Expr\Variable('value')
                            )
                        )),
                        new Stmt\If_(
                            new Expr\FuncCall(new Name('is_array'), [new Arg(new Expr\Variable('value'))]),
                            [
                                'stmts' => [
                                    new Stmt\Expression(new Expr\Assign(
                                        new Expr\Variable('value'),
                                        new Expr\MethodCall(new Expr\Variable('serializer'), 'serialize', [
                                            new Arg(new Expr\Variable('value')),
                                            new Arg(new Scalar\String_('json')),
                                        ])
                                    )),
                                ],
                            ]
                        ),
                        new Stmt\If_(
                            new Expr\BooleanNot(new Expr\FuncCall(new Name('is_string'), [new Arg(new Expr\Variable('value'))])),
                            ['stmts' => [
                                new Stmt\Expression(new Expr\Throw_(new Expr\New_(
                                    new Name\FullyQualified('LogicException'),
                                    [new Arg(new Scalar\String_('Expected form parameter value to be a string'))]
                                ))),
                            ]]
                        ),
                        $addResourceStatement,
                    ],
                ]),
                new Stmt\Return_(new Expr\Array_([
                    new Expr\ArrayItem(new Expr\Array_([
                        new Expr\ArrayItem(
                            new Expr\Array_([new Expr\ArrayItem(
                                new Expr\BinaryOp\Concat(
                                    new Scalar\String_('multipart/form-data; boundary="'),
                                    new Expr\BinaryOp\Concat(
                                        new Expr\MethodCall(new Expr\Variable('bodyBuilder'), 'getBoundary'),
                                        new Scalar\String_('"')
                                    )
                                )
                            )]),
                            new Scalar\String_('Content-Type')
                        ),
                    ])),
                    new Expr\ArrayItem(new Expr\MethodCall(new Expr\Variable('bodyBuilder'), 'build')),
                ])),
            ];
        }

        return [
            // Guard: serializer must implement NormalizerInterface
            new Stmt\If_(
                new Expr\BooleanNot(new Expr\Instanceof_(
                    new Expr\Variable('serializer'),
                    new Name('\\' . NormalizerInterface::class)
                )),
                ['stmts' => [
                    new Stmt\Expression(new Expr\Throw_(new Expr\New_(
                        new Name\FullyQualified('LogicException'),
                        [new Arg(new Scalar\String_('Expected serializer to implement NormalizerInterface'))]
                    ))),
                ]]
            ),
            // Normalize body and guard the result type for http_build_query()
            new Stmt\Expression(new Expr\Assign(
                new Expr\Variable('normalizedBody'),
                new Expr\MethodCall(
                    new Expr\Variable('serializer'),
                    'normalize',
                    [
                        new Arg(new Expr\PropertyFetch(new Expr\Variable('this'), 'body')),
                        new Arg(new Scalar\String_('json')),
                    ]
                )
            )),
            new Stmt\If_(
                new Expr\BooleanNot(new Expr\FuncCall(new Name('is_array'), [new Arg(new Expr\Variable('normalizedBody'))])),
                ['stmts' => [
                    new Stmt\Expression(new Expr\Throw_(new Expr\New_(
                        new Name\FullyQualified('LogicException'),
                        [new Arg(new Scalar\String_('Expected normalize() to return an array for form body'))]
                    ))),
                ]]
            ),
            new Stmt\Return_(new Expr\Array_([
                new Expr\ArrayItem(new Expr\Array_([
                    new Expr\ArrayItem(
                        new Expr\Array_([new Expr\ArrayItem(new Scalar\String_($contentType))]),
                        new Scalar\String_('Content-Type')
                    ),
                ])),
                new Expr\ArrayItem(new Expr\FuncCall(new Name('http_build_query'), [
                    new Arg(new Expr\Variable('normalizedBody')),
                ])),
            ])),
        ];
    }

    /**
     * Extract field names that have `format: binary` from the schema properties.
     *
     * These fields represent file uploads and need the `filename` option in the
     * multipart builder so that the Content-Disposition header includes `filename=`.
     *
     * @return list<string>
     */
    private function extractBinaryFieldNames(MediaType $content): array
    {
        $schema = $content->getSchema();
        if (!$schema instanceof Schema) {
            return [];
        }

        $properties = $schema->getProperties();
        if (null === $properties) {
            return [];
        }

        $binaryFields = [];
        foreach ($properties as $name => $property) {
            if ($property instanceof Schema && 'binary' === $property->getFormat()) {
                $binaryFields[] = (string) $name;
            }
        }

        return $binaryFields;
    }
}
