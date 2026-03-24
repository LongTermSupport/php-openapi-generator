<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Console\Command;

use LogicException;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Console\Loader\ConfigLoaderInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\VarDumper\VarDumper;

class DumpConfigCommand extends Command
{
    public function __construct(
        protected ConfigLoaderInterface $configLoader,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('dump-config');
        $this->setDescription('Dump configuration for debugging purpose');
        $this->addOption('config-file', 'c', InputOption::VALUE_REQUIRED, 'File to use for configuration', '.php-openapi');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $configFile = $input->getOption('config-file');
        if (!\is_string($configFile)) {
            throw new LogicException('Expected string, got ' . get_debug_type($configFile));
        }

        VarDumper::dump($this->configLoader->load($configFile));

        return Command::SUCCESS;
    }
}
