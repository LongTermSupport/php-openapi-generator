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
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar;
use PhpParser\Node\Stmt;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @internal
 */
class FormBodyContentGenerator extends AbstractBodyContentGenerator
{
    /**
     * @return array<mixed>
     */
    public function getSerializeStatements(MediaType $content, string $contentType, string $reference, Context $context): array
    {
        if (1 === \Safe\preg_match('/multipart\/form-data/', $contentType)) {
            $binaryFieldNames = $this->extractBinaryFieldNames($content);

            $statements = [
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
            ];

            // Binary file fields are emitted first as direct addResource calls reading
            // straight from the body DTO (which holds a typed FileUpload value object).
            // This is the ONLY way to wire the per-part Content-Type header — running
            // the field through normalize→serialize would flatten the FileUpload to its
            // contents string and lose the filename and content type.
            //
            // Per RFC 7578 each multipart part SHOULD carry Content-Type and
            // Content-Disposition with filename when uploading a file.
            foreach ($binaryFieldNames as $binaryFieldName) {
                $uploadVarName = $binaryFieldName . 'Upload';
                $uploadVar     = new Expr\Variable($uploadVarName);
                $getterName    = 'get' . ucfirst($binaryFieldName);

                $statements[] = new Stmt\Expression(new Expr\Assign(
                    $uploadVar,
                    new Expr\MethodCall(new Expr\PropertyFetch(new Expr\Variable('this'), 'body'), $getterName)
                ));

                $statements[] = new Stmt\Expression(new Expr\MethodCall(
                    new Expr\Variable('bodyBuilder'),
                    'addResource',
                    [
                        new Arg(new Scalar\String_($binaryFieldName)),
                        new Arg(new Expr\PropertyFetch($uploadVar, new Identifier('contents'))),
                        new Arg(new Expr\Array_([
                            new Expr\ArrayItem(
                                new Expr\PropertyFetch($uploadVar, new Identifier('filename')),
                                new Scalar\String_('filename')
                            ),
                            new Expr\ArrayItem(
                                new Expr\Array_([
                                    new Expr\ArrayItem(
                                        new Expr\PropertyFetch($uploadVar, new Identifier('contentType')),
                                        new Scalar\String_('Content-Type')
                                    ),
                                ]),
                                new Scalar\String_('headers')
                            ),
                        ])),
                    ]
                ));
            }

            // Normalize the body for the non-binary form fields.
            $statements[] = new Stmt\Expression(new Expr\Assign(new Expr\Variable('formParameters'), new Expr\MethodCall(new Expr\Variable('serializer'), 'normalize', [
                new Arg(new Expr\PropertyFetch(new Expr\Variable('this'), 'body')),
                new Arg(new Scalar\String_('json')),
            ])), [
                'comments' => [new Doc('/** @var array<string, mixed> $formParameters */')],
            ]);

            // Foreach body that handles non-binary form fields. Binary fields are
            // skipped via `continue` because they were emitted above with direct
            // typed access to the FileUpload value object.
            $foreachStmts = [];

            if ([] !== $binaryFieldNames) {
                $binaryFieldItems = array_map(
                    static fn (string $name): Expr\ArrayItem => new Expr\ArrayItem(new Scalar\String_($name)),
                    $binaryFieldNames,
                );
                $foreachStmts[] = new Stmt\If_(
                    new Expr\FuncCall(new Name('in_array'), [
                        new Arg(new Expr\Variable('key')),
                        new Arg(new Expr\Array_($binaryFieldItems)),
                        new Arg(new Expr\ConstFetch(new Name('true'))),
                    ]),
                    [
                        'stmts' => [
                            new Stmt\Continue_(),
                        ],
                    ]
                );
            }

            $foreachStmts[] = new Stmt\Expression(new Expr\Assign(
                new Expr\Variable('value'),
                new Expr\Ternary(
                    new Expr\FuncCall(new Name('is_int'), [new Arg(new Expr\Variable('value'))]),
                    new Expr\Cast\String_(new Expr\Variable('value')),
                    new Expr\Variable('value')
                )
            ));
            $foreachStmts[] = new Stmt\If_(
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
            );
            $foreachStmts[] = new Stmt\If_(
                new Expr\BooleanNot(new Expr\FuncCall(new Name('is_string'), [new Arg(new Expr\Variable('value'))])),
                ['stmts' => [
                    new Stmt\Expression(new Expr\Throw_(new Expr\New_(
                        new Name\FullyQualified('LogicException'),
                        [new Arg(new Scalar\String_('Expected form parameter value to be a string'))]
                    ))),
                ]]
            );
            $foreachStmts[] = new Stmt\Expression(new Expr\MethodCall(new Expr\Variable('bodyBuilder'), 'addResource', [
                new Arg(new Expr\Variable('key')),
                new Arg(new Expr\Variable('value')),
            ]));

            $statements[] = new Stmt\Foreach_(new Expr\Variable('formParameters'), new Expr\Variable('value'), [
                'keyVar' => new Expr\Variable('key'),
                'stmts'  => $foreachStmts,
            ]);

            $statements[] = new Stmt\Return_(new Expr\Array_([
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
            ]));

            return $statements;
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
     * These fields represent file uploads and are emitted as direct
     * `addResource(name, $upload->contents, ['filename' => ..., 'headers' => ...])`
     * calls so the multipart part carries the correct Content-Type per RFC 7578.
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
                $binaryFields[] = (string)$name;
            }
        }

        return $binaryFields;
    }
}
