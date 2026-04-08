<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApiRuntime\Tests\Client\Plugin;

use Http\Promise\FulfilledPromise;
use LongTermSupport\OpenApiGenerator\Component\OpenApiRuntime\Client\AuthenticationPlugin;
use LongTermSupport\OpenApiGenerator\Component\OpenApiRuntime\Client\Plugin\AuthenticationRegistry;
use PHPUnit\Framework\MockObject\Stub\ReturnCallback;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

/**
 * @internal
 */
class AuthenticationRegistryTest extends TestCase
{
    private AuthenticationRegistry $authenticationRegistry;

    protected function setUp(): void
    {
        $plugins   = [];
        $plugins[] = new class implements AuthenticationPlugin {
            public function authentication(RequestInterface $request): RequestInterface
            {
                $request->withHeader('A', 'A');

                return $request;
            }

            public function getScope(): string
            {
                return 'A';
            }
        };

        $plugins[] = new class implements AuthenticationPlugin {
            public function authentication(RequestInterface $request): RequestInterface
            {
                $request->withHeader('B', 'B');

                return $request;
            }

            public function getScope(): string
            {
                return 'B';
            }
        };

        $plugins[] = new class implements AuthenticationPlugin {
            public function authentication(RequestInterface $request): RequestInterface
            {
                $request->withHeader('C', 'C');

                return $request;
            }

            public function getScope(): string
            {
                return 'C';
            }
        };

        $this->authenticationRegistry = new AuthenticationRegistry($plugins);
    }

    public function testNoPlugins(): void
    {
        // force no plugins
        $this->authenticationRegistry = new AuthenticationRegistry([]);

        $request = $this->createMock(RequestInterface::class);
        $request
            ->method('withHeader')
            ->willReturnSelf()
        ;
        $request
            ->method('withoutHeader')
            ->willReturnSelf()
        ;

        $fakeCallback = function (RequestInterface $request): FulfilledPromise {
            $this->addToAssertionCount(1);

            return new FulfilledPromise('ok');
        };
        $this->authenticationRegistry->handleRequest($request, $fakeCallback, $fakeCallback);
    }

    public function testOneScope(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $request
            ->method('getHeader')
            ->with(AuthenticationRegistry::SCOPES_HEADER)
            ->willReturn(['A'])
        ;
        $request
            ->method('withHeader')
            ->willReturnCallback(static function (string $name, string $value) use ($request): \PHPUnit\Framework\MockObject\MockObject {
                self::assertSame('A', $name);
                self::assertSame('A', $value);

                return $request;
            })
        ;
        $request
            ->method('withoutHeader')
            ->willReturnSelf()
        ;

        $fakeCallback = function (RequestInterface $request): FulfilledPromise {
            $this->addToAssertionCount(1);

            return new FulfilledPromise('ok');
        };
        $this->authenticationRegistry->handleRequest($request, $fakeCallback, $fakeCallback);
    }

    public function testMultipleScope(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $request
            ->method('getHeader')
            ->with(AuthenticationRegistry::SCOPES_HEADER)
            ->willReturn(['A', 'C'])
        ;
        $request
            ->method('withHeader')
            ->willReturnOnConsecutiveCalls(
                new ReturnCallback(static function (string $name, string $value) use ($request): \PHPUnit\Framework\MockObject\MockObject {
                    self::assertSame('A', $name);
                    self::assertSame('A', $value);

                    return $request;
                }),
                new ReturnCallback(static function (string $name, string $value) use ($request): \PHPUnit\Framework\MockObject\MockObject {
                    self::assertSame('C', $name);
                    self::assertSame('C', $value);

                    return $request;
                }),
            )
        ;
        $request
            ->method('withoutHeader')
            ->willReturnSelf()
        ;

        $fakeCallback = function (RequestInterface $request): FulfilledPromise {
            $this->addToAssertionCount(1);

            return new FulfilledPromise('ok');
        };
        $this->authenticationRegistry->handleRequest($request, $fakeCallback, $fakeCallback);
    }
}
