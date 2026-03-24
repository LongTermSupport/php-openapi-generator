<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Tests\Generator\Runtime\data\Client;

use BaseEndpoint;
use Client;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Serializer\SerializerInterface;

require_once __DIR__ . '/../../../../../Generator/Runtime/data/Client/Endpoint.php';
require_once __DIR__ . '/../../../../../Generator/Runtime/data/Client/BaseEndpoint.php';

/**
 * @internal
 */
final class BaseEndpointTest extends TestCase
{
    /** @param array<string, mixed> $queryParams */
    #[DataProvider('queryParamsProvider')]
    public function testQueryParamsWillBeProperlyEncoded(array $queryParams, string $expectedQueryString): void
    {
        $endpoint = $this->getEndpoint($queryParams);

        self::assertEquals($expectedQueryString, $endpoint->getQueryString());
        self::assertEquals(http_build_query($queryParams, encoding_type: PHP_QUERY_RFC3986), $endpoint->getQueryString());
    }

    /** @return iterable<string, array{array<string, mixed>, string}> */
    public static function queryParamsProvider(): iterable
    {
        yield 'string' => [['queryParam' => 'test'], 'queryParam=test'];
        yield 'string with reserved character' => [['queryParam' => 'te?st'], 'queryParam=te%3Fst'];
        yield 'int' => [['queryParam' => 1], 'queryParam=1'];
        yield 'bool' => [['queryParam' => false], 'queryParam=0'];
        yield 'multiple params' => [['queryParam' => 'test', 'anotherParam' => 'test2'], 'queryParam=test&anotherParam=test2'];
        yield 'string array' => [['queryParam' => ['test1', 'test2']], 'queryParam%5B0%5D=test1&queryParam%5B1%5D=test2'];
        yield 'int array' => [['queryParam' => [1, 2, 3]], 'queryParam%5B0%5D=1&queryParam%5B1%5D=2&queryParam%5B2%5D=3'];
        yield 'array with string keys' => [['queryParam' => ['key' => 1]], 'queryParam%5Bkey%5D=1'];
        yield 'nested array' => [['queryParam' => ['key' => ['test' => 'test1']]], 'queryParam%5Bkey%5D%5Btest%5D=test1'];
    }

    /**
     * @param array<string, mixed> $queryParams
     * @param array<string>        $allowedQueryParams
     */
    #[DataProvider('queryParamsProviderWithAllowingReservedCharacters')]
    public function testQueryParamsWillBeProperlyEncodedWithReservedCharacters(
        array $queryParams,
        array $allowedQueryParams,
        string $expectedQueryString
    ): void {
        $endpoint = $this->getEndpoint($queryParams, $allowedQueryParams);

        self::assertEquals($expectedQueryString, $endpoint->getQueryString());
    }

    /** @return iterable<string, array{array<string, mixed>, list<string>, string}> */
    public static function queryParamsProviderWithAllowingReservedCharacters(): iterable
    {
        yield 'string' => [['queryParam' => 'te?st'], ['queryParam'], 'queryParam=te?st'];
        yield 'array' => [['queryParam' => ['te?st', 'tes*&']], ['queryParam'], 'queryParam%5B0%5D=te?st&queryParam%5B1%5D=tes*&'];
    }

    /**
     * @param array<string, mixed> $queryParams
     * @param array<string>        $allowReserved
     */
    private function getEndpoint(array $queryParams, array $allowReserved = []): object
    {
        return new class($queryParams, $allowReserved) extends BaseEndpoint {
            /**
             * @param array<string, mixed> $queryParams
             * @param array<string>        $allowReserved
             */
            public function __construct(array $queryParams, private readonly array $allowReserved)
            {
                /** @var array<string, mixed> $queryParams */
                $this->queryParameters = $queryParams;
            }

            public function getMethod(): string
            {
                return 'GET';
            }

            public function getBody(SerializerInterface $serializer, ?\Psr\Http\Message\StreamFactoryInterface $streamFactory = null): array
            {
                return [[], null];
            }

            public function getUri(): string
            {
                return '/test';
            }

            public function getAuthenticationScopes(): array
            {
                return [];
            }

            protected function transformResponseBody(
                ResponseInterface $response,
                SerializerInterface $serializer,
                ?string $contentType = null
            ): mixed {
                return null;
            }

            public function parseResponse(
                ResponseInterface $response,
                SerializerInterface $serializer,
                string $fetchMode = Client::FETCH_OBJECT
            ): mixed {
                return $response;
            }

            protected function getQueryOptionsResolver(): OptionsResolver
            {
                $optionsResolver = parent::getQueryOptionsResolver();
                $optionsResolver->setDefined(array_keys($this->queryParameters));

                return $optionsResolver;
            }

            /** @return list<string> */
            protected function getQueryAllowReserved(): array
            {
                return array_values($this->allowReserved);
            }
        };
    }
}
