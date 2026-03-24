<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Generator;

use Generator;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Generator\Context\Context;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Registry\Schema;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Declare_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Parser;

class RuntimeGenerator implements GeneratorInterface
{
    public const FILE_TYPE_RUNTIME = 'runtime';

    public function __construct(
        private readonly Naming $naming,
        private readonly Parser $parser,
    ) {
    }

    /**
     * Generate a set of files given an object and a context.
     */
    public function generate(Schema $schema, string $className, Context $context): void
    {
        foreach ($this->collectFiles() as [$directory, $file]) {
            $fileContents = \Safe\file_get_contents($file);
            $ast          = $this->parser->parse($fileContents);
            // Strip declare(strict_types=1) from template — Printer prepends it to every file.
            $ast = array_values(array_filter($ast ?? [], static fn ($node): bool => !($node instanceof Declare_)));

            $fileBasename = basename($file);
            $namespace    = explode('/', str_replace([$fileBasename, $directory], '', $file));
            array_shift($namespace);
            array_pop($namespace);

            $prefixNamespace = '';
            if ([] !== $namespace) {
                $prefixNamespace = implode('/', $namespace) . '/';
            }

            $stmts = new Namespace_(new Name($this->naming->getRuntimeNamespace($schema->getNamespace(), $namespace)), $ast);
            $schema->addFile(new File($schema->getDirectory() . '/Runtime/' . $prefixNamespace . $fileBasename, $stmts, self::FILE_TYPE_RUNTIME));
        }
    }

    /** @return Generator<int, string> */
    protected function directories(): Generator
    {
        yield __DIR__ . '/Runtime/data';
    }

    /** @return Generator<int, array{string, string}> */
    private function collectFiles(): Generator
    {
        foreach ($this->directories() as $directory) {
            foreach ($this->files($directory) as $file) {
                yield [$directory, $file];
            }
        }
    }

    /** @return Generator<int, string> */
    private function files(string $directory): Generator
    {
        /** @var list<string> $files */
        $files = \Safe\scandir($directory);
        foreach ($files as $file) {
            $fullPath = \sprintf('%s/%s', $directory, $file);
            if (\in_array($file, ['.', '..'], true)) {
                continue;
            }

            if (is_dir($fullPath)) {
                foreach ($this->files($fullPath) as $dirFile) {
                    yield $dirFile;
                }
            } else {
                yield $fullPath;
            }
        }
    }
}
