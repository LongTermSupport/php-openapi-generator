<?php

declare(strict_types=1);

use Psr\Http\Message\RequestInterface;

/**
 * @internal
 */
interface AuthenticationPlugin
{
    public function authentication(RequestInterface $request): RequestInterface;

    public function getScope(): string;
}
