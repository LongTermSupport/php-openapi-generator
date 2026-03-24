<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\GeneratorCore;

use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Console\Command\DumpConfigCommand;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Console\Loader\ConfigLoader;
use Symfony\Component\Console\Application as BaseApplication;

class Application extends BaseApplication
{
    public const VERSION = '6.x-dev';

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct('PHP OpenAPI Generator', self::VERSION);

        $this->boot();
    }

    protected function boot(): void
    {
        $configLoader = new ConfigLoader();

        $this->addCommands([
            new DumpConfigCommand($configLoader),
        ]);
    }
}
