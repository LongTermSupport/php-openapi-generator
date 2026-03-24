<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\OpenApiCommon;

use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Application as JsonSchemaApplication;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Console\Command\DumpConfigCommand;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Console\Command\GenerateCommand;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Console\Loader\ConfigLoader;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Console\Loader\OpenApiMatcher;
use LongTermSupport\OpenApiGenerator\Component\OpenApiCommon\Console\Loader\SchemaLoader;

class Application extends JsonSchemaApplication
{
    protected function boot(): void
    {
        $configLoader = new ConfigLoader();

        $this->addCommands([
            new GenerateCommand($configLoader, new SchemaLoader(), new OpenApiMatcher()),
            new DumpConfigCommand($configLoader),
        ]);
    }
}
