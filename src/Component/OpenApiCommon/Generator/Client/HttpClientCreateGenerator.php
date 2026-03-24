<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Generator\Client;

use Http\Client\Common\PluginClient;
use Http\Discovery\Psr18ClientDiscovery;
use LogicException;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Generator\Context\Context;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\OpenApi;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;

trait HttpClientCreateGenerator
{
    use ServerPluginGenerator;

    protected function getHttpClientCreateExpr(Context $context): array
    {
        $openApi = $context->getCurrentSchema()->getParsed();
        if (!$openApi instanceof OpenApi) {
            throw new LogicException('Expected OpenApi, got ' . get_debug_type($openApi));
        }

        $statements = [
            new Stmt\Expression(new Expr\Assign(
                new Expr\Variable('httpClient'),
                new Expr\StaticCall(
                    new Name\FullyQualified(Psr18ClientDiscovery::class),
                    'find'
                )
            )),
        ];

        $needsServerPlugins = $this->needsServerPlugins($openApi);

        $statements[] = new Stmt\Expression(new Expr\Assign(
            new Expr\Variable('plugins'),
            new Expr\Array_()
        ));

        if ($needsServerPlugins) {
            $statements = array_merge($statements, $this->getServerPluginsStatements($openApi));
        }

        $statements[] = new Stmt\If_(
            new Expr\BinaryOp\Greater(
                new Expr\FuncCall(new Name('count'), [new Node\Arg(new Expr\Variable('additionalPlugins'))]),
                new Expr\ConstFetch(new Name('0'))
            ),
            [
                'stmts' => [
                    new Stmt\Expression(new Expr\Assign(
                        new Expr\Variable('plugins'),
                        new Expr\FuncCall(new Name('array_merge'), [
                            new Node\Arg(new Expr\Variable('plugins')),
                            new Node\Arg(new Expr\Variable('additionalPlugins')),
                        ])
                    )),
                ],
            ]
        );

        $statements[] = new Stmt\Expression(new Expr\Assign(
            new Expr\Variable('httpClient'),
            new Expr\New_(
                new Name\FullyQualified(PluginClient::class),
                [
                    new Node\Arg(new Expr\Variable('httpClient')),
                    new Node\Arg(new Expr\Variable('plugins')),
                ]
            )
        ));

        return $statements;
    }
}
