<?php

declare(strict_types=1);

use Psr\Http\Message\RequestInterface;

interface AuthenticationPlugin
{
    public function authentication(RequestInterface $request): RequestInterface;

    public function getScope(): string;
}
