<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Generator\Authentication;

use LogicException;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\HTTPSecurityScheme;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Guesser\Guess\SecuritySchemeGuess;
use PhpParser\Modifiers;
use PhpParser\Node\Expr;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt;

trait ConstructGenerator
{
    /** @return array<int, Stmt> */
    protected function createConstruct(SecuritySchemeGuess $securityScheme): array
    {
        $needs = [];
        switch ($securityScheme->getType()) {
            case SecuritySchemeGuess::TYPE_HTTP:
                $object = $securityScheme->getObject();
                if (!$object instanceof HTTPSecurityScheme) {
                    throw new LogicException('Expected HTTPSecurityScheme, got ' . get_debug_type($object));
                }

                $scheme = $object->getScheme() ?? 'Bearer';
                $scheme = ucfirst(mb_strtolower($scheme));

                switch ($scheme) {
                    case 'Bearer':
                        $needs['token'] = new Name('string');
                        break;
                    case 'Basic':
                        $needs['username'] = new Name('string');
                        $needs['password'] = new Name('string');
                        break;
                }

                break;
            case SecuritySchemeGuess::TYPE_API_KEY:
                $needs['apiKey'] = new Name('string');
                break;
        }

        $constructStmts  = [];
        $constructParams = [];
        $statements      = [];
        foreach ($needs as $field => $type) {
            $prop              = new Stmt\Property(Modifiers::PRIVATE, [new Stmt\PropertyProperty($field)]);
            $prop->type        = $type;
            $statements[]      = $prop;
            $constructParams[] = new Param(new Expr\Variable($field), null, $type);
            $constructStmts[]  = new Stmt\Expression(new Expr\Assign(new Expr\PropertyFetch(new Expr\Variable('this'), $field), new Expr\Variable($field)));
        }

        $statements[] = new Stmt\ClassMethod('__construct', [
            'flags'  => Modifiers::PUBLIC,
            'stmts'  => $constructStmts,
            'params' => $constructParams,
        ]);

        return $statements;
    }
}
