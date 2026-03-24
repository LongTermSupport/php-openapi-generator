<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Generator;

use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Generator\Context\Context;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Generator\File;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Generator\GeneratorInterface;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Generator\Naming;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Registry\Schema as BaseSchema;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Generator\Authentication\AuthenticationGenerator as AuthenticationMethodGenerator;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Generator\Authentication\ClassGenerator;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Generator\Authentication\ConstructGenerator;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Generator\Authentication\GetScopeGenerator;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Registry\Schema;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;

class AuthenticationGenerator implements GeneratorInterface
{
    use AuthenticationMethodGenerator;
    use ClassGenerator;
    use ConstructGenerator;
    use GetScopeGenerator;

    protected const REFERENCE = 'Authentication';

    protected const FILE_TYPE_AUTH = 'auth';

    protected Naming $naming;

    public function __construct()
    {
        $this->naming = new Naming();
    }

    public function generate(BaseSchema $schema, string $className, Context $context): void
    {
        if ($schema instanceof Schema) {
            $baseNamespace = \sprintf('%s\%s', $schema->getNamespace(), self::REFERENCE);

            $securitySchemes = $schema->getSecuritySchemes();
            foreach ($securitySchemes as $securityScheme) {
                $className = $this->getNaming()->getAuthName($securityScheme->getName());

                $statements     = $this->createConstruct($securityScheme);
                $statements[]   = $this->createAuthentication($securityScheme);
                $statements[]   = $this->createGetScope($securityScheme);
                $authentication = $this->createClass($className, $statements, $schema->getNamespace());

                $namespace = new Stmt\Namespace_(new Name($baseNamespace), [$authentication]);

                $schema->addFile(new File(\sprintf('%s/%s/%s.php', $schema->getDirectory(), self::REFERENCE, $className), $namespace, self::FILE_TYPE_AUTH));
            }
        }
    }

    protected function getNaming(): Naming
    {
        return $this->naming;
    }
}
