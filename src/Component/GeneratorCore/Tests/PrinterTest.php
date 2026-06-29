<?php

declare(strict_types=1);

namespace LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Tests;

use InvalidArgumentException;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Generator\File;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Printer;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Registry\Registry;
use LongTermSupport\OpenApiGenerator\Component\GeneratorCore\Registry\Schema;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\PrettyPrinter\Standard;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @internal
 *
 * Covers the configurable generated-code visibility policy (api-annotation +
 * api-annotation-overrides) stamped by {@see Printer::output()}
 */
final class PrinterTest extends TestCase
{
    private string $outputDir = '';

    protected function setUp(): void
    {
        $this->outputDir = \Safe\realpath(sys_get_temp_dir()) . '/php-openapi-printer-test-' . uniqid('', true);
        \Safe\mkdir($this->outputDir, 0o755, true);
    }

    protected function tearDown(): void
    {
        if ('' !== $this->outputDir) {
            new Filesystem()->remove($this->outputDir);
        }
    }

    #[Test]
    public function itStampsInternalByDefault(): void
    {
        $content = $this->generateClass('internal', [], 'My\Api\Model', 'Foo');

        self::assertStringContainsString('@internal', $content);
        self::assertStringNotContainsString('@api', $content);
    }

    #[Test]
    public function itStampsApiWhenConfigured(): void
    {
        $content = $this->generateClass('api', [], 'My\Api\Model', 'Foo');

        self::assertStringContainsString('@api', $content);
        self::assertStringNotContainsString('@internal', $content);
    }

    #[Test]
    public function itStampsNothingWhenAnnotationIsNone(): void
    {
        $content = $this->generateClass('none', [], 'My\Api\Model', 'Foo');

        self::assertStringNotContainsString('@internal', $content);
        self::assertStringNotContainsString('@api', $content);
    }

    #[Test]
    public function itPromotesAnFqcnMatchingAnOverrideToApiWhileLeavingOthersInternal(): void
    {
        // Default 'internal', but the Model namespace is promoted to @api.
        $overrides = ['#^My\\\Api\\\Model\\\#'];

        $model = $this->generateClass('internal', $overrides, 'My\Api\Model', 'Foo');
        self::assertStringContainsString('@api', $model);
        self::assertStringNotContainsString('@internal', $model);

        $service = $this->generateClass('internal', $overrides, 'My\Api\Service', 'Bar');
        self::assertStringContainsString('@internal', $service);
        self::assertStringNotContainsString('@api', $service);
    }

    #[Test]
    public function itRejectsAnUnknownAnnotationValue(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Printer(new Standard(['shortArraySyntax' => true]), '', 'public');
    }

    /**
     * Generate a single class through the real Printer and return the emitted file contents.
     *
     * @param list<string> $overrides
     */
    private function generateClass(string $apiAnnotation, array $overrides, string $namespace, string $className): string
    {
        $filename = $this->outputDir . '/' . $className . '.php';
        $node     = new Namespace_(new Name($namespace), [new Class_($className)]);

        $schema = new Schema($this->outputDir . '/origin', $namespace, $this->outputDir, $className);
        $schema->addFile(new File($filename, $node, 'model'));

        $registry = new Registry();
        $registry->addSchema($schema);

        $printer = new Printer(new Standard(['shortArraySyntax' => true]), '', $apiAnnotation, $overrides);
        $printer->setCleanGenerated(false);
        $printer->output($registry);

        return \Safe\file_get_contents($filename);
    }
}
