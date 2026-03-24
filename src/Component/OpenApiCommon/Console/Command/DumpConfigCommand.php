<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Console\Command;

use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Console\Command\DumpConfigCommand as BaseDumpConfigCommand;
use Symfony\Component\Console\Input\InputOption;

class DumpConfigCommand extends BaseDumpConfigCommand
{
    protected function configure(): void
    {
        $this->setName('dump-config');
        $this->setDescription('Dump PHP OpenAPI configuration for debugging purpose');
        $this->addOption('config-file', 'c', InputOption::VALUE_REQUIRED, 'File to use for PHP OpenAPI configuration', '.php-openapi');
    }
}
