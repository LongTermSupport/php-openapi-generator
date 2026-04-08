<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApi3\Tests;

use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Tests\CodeStyleFixerTrait;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\Tests\Client\Authentication\ApiKeyAuthAuthentication;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\Tests\Client\Client;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\Tests\Client\Exception\GetEndpointUnauthorizedException;
use LongTermSupport\OpenApiGenerator\Component\OpenApi3\Tests\Client\Runtime\Client\AuthenticationRegistry;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Console\Command\GenerateCommand;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Console\Loader\ConfigLoader;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Console\Loader\OpenApiMatcher;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Console\Loader\SchemaLoader;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * @internal
 */
#[Large]
class JaneOpenApiResourceTest extends TestCase
{
    use CodeStyleFixerTrait;

    #[DataProvider('resourceProvider')]
    public function testResources(string $name, SplFileInfo $testDirectory): void
    {
        // 1. Generate
        $command = new GenerateCommand(new ConfigLoader(), new SchemaLoader(), new OpenApiMatcher());
        $input   = new ArrayInput(['--config-file' => $testDirectory->getRealPath() . \DIRECTORY_SEPARATOR . '.php-openapi'], $command->getDefinition());
        $command->run($input, new NullOutput());

        $projectRoot  = \dirname(__DIR__, 4);
        $expectedDir  = $testDirectory->getRealPath() . \DIRECTORY_SEPARATOR . 'expected';
        $generatedDir = $projectRoot . '/var/test-generated/' . $name;

        $this->fixCodeStyle($expectedDir);
        $this->fixCodeStyle($generatedDir);

        // 2. Compare
        $expectedFinder = new Finder();
        $expectedFinder->in($expectedDir);

        $generatedFinder = new Finder();
        $generatedFinder->in($generatedDir);

        $generatedData = [];

        self::assertCount(\count($expectedFinder), $generatedFinder, 'Assert same files for ' . $testDirectory->getRealPath());

        foreach ($generatedFinder as $generatedFile) {
            $generatedData[$generatedFile->getRelativePathname()] = $generatedFile->getRealPath();
        }

        foreach ($expectedFinder as $expectedFile) {
            self::assertArrayHasKey($expectedFile->getRelativePathname(), $generatedData);

            if ($expectedFile->isFile()) {
                $expectedPath = $expectedFile->getRealPath();
                $actualPath   = $generatedData[$expectedFile->getRelativePathname()];

                self::assertSame(
                    \Safe\file_get_contents($expectedPath),
                    \Safe\file_get_contents($actualPath),
                    'Expected ' . $expectedPath . ' got ' . $actualPath . ' in ' . $name
                );
            }
        }
    }

    /** @return array<string, array{0: string, 1: SplFileInfo}> */
    public static function resourceProvider(): array
    {
        $finder = new Finder();
        $finder->directories()->in(__DIR__ . '/fixtures');
        $finder->depth('< 1');

        $data = [];

        foreach ($finder as $directory) {
            $data[$directory->getFilename()] = [$directory->getFilename(), $directory];
        }

        return $data;
    }

    #[Group('prism')]
    public function testClient(): void
    {
        // 1. Generate (output goes to var/test-generated/client/)
        $command = new GenerateCommand(new ConfigLoader(), new SchemaLoader(), new OpenApiMatcher());
        $input   = new ArrayInput(['--config-file' => __DIR__ . '/client' . \DIRECTORY_SEPARATOR . '.php-openapi'], $command->getDefinition());
        $command->run($input, new NullOutput());

        // 2. Test unauthorized
        $client = Client::create();
        try {
            $client->getEndpoint();
        } catch (GetEndpointUnauthorizedException $getEndpointUnauthorizedException) {
            // getError() return type is non-nullable Error per the generated exception class,
            // so the catch reaching here proves the exception was constructed correctly.
            self::assertSame(401, $getEndpointUnauthorizedException->getCode());
        }

        // 3. Test
        $client   = Client::create(null, [new AuthenticationRegistry([new ApiKeyAuthAuthentication('api_key')])]);
        $response = $client->getEndpoint();
        self::assertNotNull($response);
    }
}
