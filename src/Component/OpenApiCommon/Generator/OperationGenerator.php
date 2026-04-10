<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Generator;

use LogicException;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Generator\Context\Context;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Guesser\Guess\OperationGuess;
use PhpParser\Comment;
use PhpParser\Modifiers;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar;
use PhpParser\Node\Stmt;
use PhpParser\Node\UnionType;

class OperationGenerator
{
    public function __construct(
        protected EndpointGeneratorInterface $endpointGenerator,
    ) {
    }

    public function createOperation(string $name, OperationGuess $operation, Context $context): Stmt\ClassMethod
    {
        /** @var array{0: string, 1: Param[], 2: string, 3: array<string>, 4: array<string>} $endpointClassData */
        $endpointClassData                                                    = $this->endpointGenerator->createEndpointClass($operation, $context);
        [$endpointName, $methodParams, $methodDoc, $returnTypes, $throwTypes] = $endpointClassData;
        $endpointArgs                                                         = [];

        // Make sure the $fetch param is in front of $accept header for backwards compatibility.
        $lastMethodParam = '';
        foreach ($methodParams as $param) {
            $endpointArgs[] = new Arg($param->var);
            if ($param->var instanceof Expr\Variable) {
                $lastMethodParam = $param->var->name;
            }
        }

        if (str_ends_with($methodDoc, '*/')) {
            $methodDoc = substr($methodDoc, 0, -2); // remove trailing */ from base method docs
        }

        $methodDocSplit    = explode("\n", $methodDoc);
        $methodDocPosition = 'accept' === $lastMethodParam ? \count($methodDocSplit) - 1 : \count($methodDocSplit);
        array_splice($methodDocSplit, $methodDocPosition, 0, [
            ' * @param string $fetch Fetch mode to use (can be OBJECT or RESPONSE)',
        ]);
        $methodDocSplit[] = $this->getReturnDoc($throwTypes);
        $methodDocSplit[] = ' */';
        $documentation    = implode("\n", $methodDocSplit);
        $paramsPosition   = 'accept' === $lastMethodParam ? \count($methodParams) - 1 : \count($methodParams);
        array_splice($methodParams, $paramsPosition, 0, [new Param(new Expr\Variable('fetch'), new Expr\ClassConstFetch(new Name('self'), 'FETCH_OBJECT'), new Name('string'))]);

        $executeCall = new Expr\MethodCall(new Expr\Variable('this'), 'executeEndpoint', [
            new Arg(new Expr\New_(new Name\FullyQualified($endpointName), $endpointArgs)),
            new Arg(new Expr\Variable('fetch')),
        ]);

        // `mixed` cannot be part of a union type and `instanceof mixed` is invalid PHP.
        // When any return type is `mixed`, fall through to the bare-mixed path.
        if ([] !== $returnTypes && !\in_array('mixed', $returnTypes, true)) {
            // Always include ResponseInterface — FETCH_RESPONSE mode returns the raw PSR-7 response.
            $allReturnTypes = array_unique([...$returnTypes, \Psr\Http\Message\ResponseInterface::class]);

            // Build native PHP return type.
            // Array types (e.g. Foo[]) are not valid native PHP types — they collapse to `array`.
            // Deduplicate so multiple Foo[]/Bar[] don't produce duplicate `array` entries.
            $typeNodes    = [];
            $hasArrayType = false;
            foreach ($allReturnTypes as $type) {
                if ('null' === $type) {
                    $typeNodes[] = new Identifier('null');
                } elseif (str_ends_with($type, '[]')) {
                    if (!$hasArrayType) {
                        $typeNodes[]  = new Identifier('array');
                        $hasArrayType = true;
                    }
                } else {
                    $typeNodes[] = new Name\FullyQualified(ltrim($type, '\\'));
                }
            }

            $nativeReturnType = 1 === \count($typeNodes)
                ? $typeNodes[0]
                : new UnionType($typeNodes);

            // Build runtime type-guard so PHPStan can verify the native return type.
            // Array types use is_array(); object types use instanceof; null uses === null.
            $resultVar        = new Expr\Variable('result');
            $conditions       = [];
            $hasIsArrayGuard  = false;
            foreach ($allReturnTypes as $type) {
                if ('null' === $type) {
                    $conditions[] = new Expr\BinaryOp\Identical($resultVar, new Expr\ConstFetch(new Name('null')));
                } elseif (str_ends_with($type, '[]')) {
                    // Only add is_array() once even if multiple array return types exist.
                    if (!$hasIsArrayGuard) {
                        $conditions[]    = new Expr\FuncCall(new Name('is_array'), [new Arg($resultVar)]);
                        $hasIsArrayGuard = true;
                    }
                } else {
                    $conditions[] = new Expr\Instanceof_($resultVar, new Name\FullyQualified(ltrim($type, '\\')));
                }
            }

            $condition = array_shift($conditions);
            if (null === $condition) {
                throw new LogicException('Expected at least one type condition — $allReturnTypes was empty');
            }

            foreach ($conditions as $cond) {
                $condition = new Expr\BinaryOp\BooleanOr($condition, $cond);
            }

            $stmts = [
                new Stmt\Expression(new Expr\Assign($resultVar, $executeCall)),
                new Stmt\If_($condition, ['stmts' => [new Stmt\Return_($resultVar)]]),
                new Stmt\Expression(new Expr\Throw_(new Expr\New_(
                    new Name\FullyQualified('LogicException'),
                    [new Arg(new Expr\FuncCall(
                        new Name\FullyQualified('sprintf'),
                        [
                            new Arg(new Scalar\String_('Unexpected response type from executeEndpoint: %s')),
                            new Arg(new Expr\FuncCall(new Name\FullyQualified('get_debug_type'), [new Arg($resultVar)])),
                        ],
                    ))],
                ))),
            ];
        } else {
            $nativeReturnType = new Name('mixed');
            $stmts            = [new Stmt\Return_($executeCall)];
        }

        return new Stmt\ClassMethod($name, [
            'flags'      => Modifiers::PUBLIC,
            'params'     => $methodParams,
            'returnType' => $nativeReturnType,
            'stmts'      => $stmts,
        ], [
            'comments' => [new Comment\Doc($documentation)],
        ]);
    }

    /**
     * @param array<string> $throwTypes
     */
    /**
     * @param array<string> $throwTypes
     */
    protected function getReturnDoc(array $throwTypes): string
    {
        // `@return` is intentionally omitted: the native PHP union return type (including
        // typed collections) already expresses the full return type precisely.
        // PHPDoc `@return` would just restate the signature — noise, not information.
        return implode('', array_map(static fn (string $value): string => ' * @throws ' . $value . "\n", $throwTypes));
    }
}
