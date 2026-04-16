<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Console\Command;

use LogicException;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Console\Command\GenerateCommand as BaseGenerateCommand;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Console\Loader\ConfigLoaderInterface;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Console\Loader\SchemaLoaderInterface;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Printer;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Console\Loader\OpenApiMatcher;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\JaneOpenApi;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Registry\Registry;
use PhpParser\PrettyPrinter\Standard;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateCommand extends BaseGenerateCommand
{
    public function __construct(ConfigLoaderInterface $configLoader, SchemaLoaderInterface $schemaLoader, private readonly OpenApiMatcher $matcher)
    {
        parent::__construct($configLoader, $schemaLoader);
    }

    protected function configure(): void
    {
        $this->setName('generate');
        $this->setDescription('Generate an api client: class, normalizers and resources given a specific Json OpenApi file');
        $this->addOption('config-file', 'c', InputOption::VALUE_REQUIRED, 'File to use for PHP OpenAPI configuration', '.php-openapi');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $configFile = $input->getOption('config-file');
        if (!\is_string($configFile)) {
            throw new LogicException('Expected string, got ' . get_debug_type($configFile));
        }

        $options    = $this->configLoader->load($configFile);
        $registries = $this->registries($options);

        foreach ($registries as $registry) {
            if (!$registry instanceof Registry) {
                throw new LogicException('Expected Registry, got ' . get_debug_type($registry));
            }

            $openApiClass = $registry->getOpenApiClass();
            $janeOpenApi  = $openApiClass::build($options);
            if (!$janeOpenApi instanceof JaneOpenApi) {
                throw new LogicException('Expected JaneOpenApi, got ' . get_debug_type($janeOpenApi));
            }

            $fixerConfigFile = '';

            if (\array_key_exists('fixer-config-file', $options) && \is_string($options['fixer-config-file'])) {
                $fixerConfigFile = $options['fixer-config-file'];
            }

            $printer = new Printer(new Standard(['shortArraySyntax' => true]), $fixerConfigFile);

            if (\array_key_exists('use-fixer', $options) && \is_bool($options['use-fixer'])) {
                $printer->setUseFixer($options['use-fixer']);
            }

            if (\array_key_exists('clean-generated', $options) && \is_bool($options['clean-generated'])) {
                $printer->setCleanGenerated($options['clean-generated']);
            }

            $janeOpenApi->generate($registry);
            $printer->output($registry);
        }

        return 0;
    }

    /** @param array<string, mixed> $options */
    protected function newRegistry(string $schemaFile, array $options): Registry
    {
        $registry = new Registry();
        $registry->setOpenApiClass($this->matcher->match($schemaFile));

        $whitelistedPaths = $options['whitelisted-paths'] ?? [];
        if (!\is_array($whitelistedPaths)) {
            throw new LogicException('Expected array, got ' . get_debug_type($whitelistedPaths));
        }

        /** @var array<string> $whitelistedPaths */
        $registry->setWhitelistedPaths($whitelistedPaths);
        $throwUnexpected = \array_key_exists('throw-unexpected-status-code', $options) ? $options['throw-unexpected-status-code'] : null;
        $registry->setThrowUnexpectedStatusCode(\is_bool($throwUnexpected) && $throwUnexpected);

        // Two distinct shapes merged into one output array to preserve the
        // existing setCustomQueryResolver(array<string, mixed>) contract:
        //  - $typeResolvers: map of parameter-type => class name (under '__type' key)
        //  - $pathResolvers: map of path => method => param name => class name
        /** @var array<string, string> $typeResolvers */
        $typeResolvers          = [];
        /** @var array<string, array<string, array<string, string>>> $pathResolvers */
        $pathResolvers          = [];
        $customQueryResolverRaw = $options['custom-query-resolver'] ?? [];
        if (\is_array($customQueryResolverRaw)) {
            foreach ($customQueryResolverRaw as $rawPath => $methods) {
                $path = (string)$rawPath;
                if (!\is_array($methods)) {
                    continue;
                }

                if ('__type' === $path) {
                    // For '__type':
                    // - method => parameter-type name
                    // - parameters => class name string
                    foreach ($methods as $rawType => $class) {
                        $type                  = mb_strtolower((string)$rawType);
                        $typeResolvers[$type]  = $this->formatClassName(\is_scalar($class) ? (string)$class : '');
                    }

                    continue;
                }

                if (!\array_key_exists($path, $pathResolvers)) {
                    $pathResolvers[$path] = [];
                }

                foreach ($methods as $rawMethod => $parameters) {
                    $method = mb_strtolower((string)$rawMethod);
                    if (!\is_array($parameters)) {
                        continue;
                    }

                    if (!\array_key_exists($method, $pathResolvers[$path])) {
                        $pathResolvers[$path][$method] = [];
                    }

                    foreach ($parameters as $rawName => $class) {
                        $name                                    = (string)$rawName;
                        $pathResolvers[$path][$method][$name]    = $this->formatClassName(\is_scalar($class) ? (string)$class : '');
                    }
                }
            }
        }

        // Merge the two distinct shapes into the loose array<string, mixed>
        // contract expected by Registry::setCustomQueryResolver().
        $customQueryResolver = [...$pathResolvers];
        if ([] !== $typeResolvers) {
            $customQueryResolver['__type'] = $typeResolvers;
        }

        $registry->setCustomQueryResolver($customQueryResolver);

        return $registry;
    }

    private function formatClassName(string $class): string
    {
        if ('\\' === $class[0]) {
            return $class;
        }

        return '\\' . $class;
    }
}
