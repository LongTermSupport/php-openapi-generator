<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Generator;

use LogicException;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Generator\Context\Context;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Generator\File;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Generator\GeneratorInterface;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Registry\Schema;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Generator\Client\ClientGenerator as CommonClientGenerator;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Generator\Client\HttpClientCreateGenerator;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Naming\OperationNamingInterface;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Registry\Schema as OpenApiSchema;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;

abstract class ClientGenerator implements GeneratorInterface
{
    use CommonClientGenerator;
    use HttpClientCreateGenerator;

    public function __construct(
        private readonly OperationGenerator $operationGenerator,
        private readonly OperationNamingInterface $operationNaming,
    ) {
    }

    public function generate(Schema $schema, string $className, Context $context): void
    {
        if (!$schema instanceof OpenApiSchema) {
            throw new LogicException('Expected OpenApiSchema, got ' . get_debug_type($schema));
        }

        $statements = [];

        foreach ($schema->getOperations() as $operation) {
            $operationName = $this->operationNaming->getFunctionName($operation);
            $statements[]  = $this->operationGenerator->createOperation($operationName, $operation, $context);
        }

        $client        = $this->createResourceClass($schema, 'Client' . $this->getSuffix());
        $client->stmts = array_merge(
            $statements,
            [
                $this->getFactoryMethod($schema, $context),
            ]
        );

        $node = new Stmt\Namespace_(new Name($schema->getNamespace()), [
            $client,
        ]);

        $schema->addFile(new File(
            $schema->getDirectory() . \DIRECTORY_SEPARATOR . 'Client' . $this->getSuffix() . '.php',
            $node,
            'client'
        ));
    }
}
