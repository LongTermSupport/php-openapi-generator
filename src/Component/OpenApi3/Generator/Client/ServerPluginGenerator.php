<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApi3\Generator\Client;

use Http\Client\Common\Plugin\AddHostPlugin;
use Http\Client\Common\Plugin\AddPathPlugin;
use LogicException;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\JsonSchema\Model\OpenApi;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Generator\Client\ServerPluginGenerator as BaseServerPluginGenerator;

trait ServerPluginGenerator
{
    use BaseServerPluginGenerator;

    /** @return array{0: string, 1: list<string>} */
    protected function discoverServer(mixed $openApi): array
    {
        if (!$openApi instanceof OpenApi) {
            throw new LogicException('Expected OpenApi, got ' . get_debug_type($openApi));
        }

        $servers = $openApi->getServers();
        $server  = (null !== $servers && isset($servers[0])) ? $servers[0] : null;

        if (null !== $server) {
            $serverUrl = $server->getUrl();
            $url       = null !== $serverUrl ? \Safe\parse_url($serverUrl) : false;
            if (!\is_array($url)) {
                return ['', []];
            }

            $baseUri = '';
            $plugins = [];

            if (\array_key_exists('host', $url)) {
                $scheme    = (string)($url['scheme'] ?? 'https');
                $baseUri   = $scheme . '://' . trim((string)$url['host'], '/');
                $plugins[] = AddHostPlugin::class;
            }

            $variables = $server->getVariables();

            if (null !== $variables
                && isset($variables['port'])
                && null !== $variables['port']->getDefault()
            ) {
                $baseUri .= ':' . $variables['port']->getDefault();
            }

            if (\array_key_exists('path', $url) && null !== $url['path']) {
                $baseUri .= '/' . trim($url['path'], '/');
                $plugins[] = AddPathPlugin::class;
            }

            return [$baseUri, $plugins];
        }

        return ['', []];
    }
}
