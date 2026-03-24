<?php

declare(strict_types=1);

if (!isset($projectRoot) || !is_dir($projectRoot)) {
    throw new RuntimeException('$projectRoot must be defined and must be a valid path to the project root');
}

return (new PhpCsFixer\Finder())
    ->in($projectRoot)
    ->exclude('var')
    ->exclude('CLAUDE')
    ->notPath('#src/Component/OpenApi3/Tests/fixtures/.+/expected/#')
    ->notPath('#src/Component/OpenApi3/Tests/client/expected/#')
    ->notPath('#src/Component/OpenApi3/Tests/client/generated/#')
    ->notPath('#Generator/Runtime/data/#')
    ->ignoreVCSIgnored(true)
;
