<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Generator\Authentication;

use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Generator\Naming;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;

trait ClassGenerator
{
    abstract protected function getNaming(): Naming;

    /** @param array<int, Stmt> $statements */
    protected function createClass(string $name, array $statements, string $schemaNamespace): Stmt\Class_
    {
        $authPluginFqcn = $this->getNaming()->getRuntimeClassFQCN($schemaNamespace, ['Client'], 'AuthenticationPlugin');

        return new Stmt\Class_($name, [
            'stmts'      => $statements,
            'implements' => [new Name\FullyQualified($authPluginFqcn)],
        ]);
    }
}
