<?php

declare(strict_types=1);

use Http\Client\Common\Plugin;
use Http\Promise\Promise;
use Psr\Http\Message\RequestInterface;

class AuthenticationRegistry implements Plugin
{
    public const SCOPES_HEADER = 'X-OpenAPI-Authentication';

    /** @param AuthenticationPlugin[] $authenticationPlugins */
    public function __construct(
        private readonly array $authenticationPlugins,
    ) {
    }

    public function handleRequest(RequestInterface $request, callable $next, callable $first): Promise
    {
        $scopes = $request->getHeader(self::SCOPES_HEADER);

        foreach ($this->authenticationPlugins as $authenticationPlugin) {
            if (\in_array($authenticationPlugin->getScope(), $scopes, true)) {
                $request = $authenticationPlugin->authentication($request);
            }
        }

        // clean headers
        $request = $request->withoutHeader(self::SCOPES_HEADER);

        return $next($request);
    }
}
