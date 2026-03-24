<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Generator;

use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Generator\Context\Context;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Guesser\Guess\OperationGuess;
use PhpParser\Comment;
use PhpParser\Modifiers;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt;

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
        $methodDocSplit[] = $this->getReturnDoc($returnTypes, $throwTypes);
        $methodDocSplit[] = ' */';
        $documentation    = implode("\n", $methodDocSplit);
        $paramsPosition   = 'accept' === $lastMethodParam ? \count($methodParams) - 1 : \count($methodParams);
        array_splice($methodParams, $paramsPosition, 0, [new Param(new Expr\Variable('fetch'), new Expr\ClassConstFetch(new Name('self'), 'FETCH_OBJECT'), new Name('string'))]);

        return new Stmt\ClassMethod($name, [
            'flags'      => Modifiers::PUBLIC,
            'params'     => $methodParams,
            'returnType' => new Name('mixed'),
            'stmts'      => [
                new Stmt\Return_(new Expr\MethodCall(new Expr\Variable('this'), 'executeEndpoint', [
                    new Arg(new Expr\New_(new Name\FullyQualified($endpointName), $endpointArgs)),
                    new Arg(new Expr\Variable('fetch')),
                ])),
            ],
        ], [
            'comments' => [new Comment\Doc($documentation)],
        ]);
    }

    /**
     * @param array<string> $returnTypes
     * @param array<string> $throwTypes
     */
    protected function getReturnDoc(array $returnTypes, array $throwTypes): string
    {
        return implode('', array_map(static fn (string $value): string => ' * @throws ' . $value . "\n", $throwTypes));
    }
}
