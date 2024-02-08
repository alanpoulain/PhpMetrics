<?php
declare(strict_types=1);

namespace Tests\Hal\Application\Workflow\Task;

use Generator;
use Hal\Application\Workflow\Task\AnalyzerTask;
use Hal\Metric\CalculableInterface;
use Hal\Metric\CalculableWithFilesInterface;
use Phake;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use function array_map;

final class AnalyzerTaskTest extends TestCase
{
    /**
     * @return Generator<string, array{array<Phake\IMock&CalculableInterface>}>
     */
    public static function provideCalculableElements(): Generator
    {
        yield 'With only calculable element' => [[Phake::mock(CalculableInterface::class)]];
        yield 'With only calculable with file element' => [[Phake::mock(CalculableWithFilesInterface::class)]];
        yield 'With both kind of calculable elements' => [
            [Phake::mock(CalculableInterface::class), Phake::mock(CalculableWithFilesInterface::class)]
        ];
    }

    /**
     * @param array<Phake\IMock&CalculableInterface> $calculableElements
     * @return void
     */
    #[DataProvider('provideCalculableElements')]
    public function testICanCalculateCalculableMetrics(array $calculableElements): void
    {
        $files = [];

        $analyzerTask = new AnalyzerTask(...$calculableElements);

        array_map(static function (Phake\IMock $mock) use ($files): void {
            if ($mock instanceof CalculableWithFilesInterface) {
                /** @var Phake\IMock&CalculableWithFilesInterface $mock */
                Phake::when($mock)->__call('setFiles', [$files])->thenDoNothing();
            }
            Phake::when($mock)->__call('calculate', [])->thenDoNothing();
        }, $calculableElements);

        $analyzerTask->process($files);

        array_map(static function (Phake\IMock $mock) use ($files): void {
            if ($mock instanceof CalculableWithFilesInterface) {
                /** @var Phake\IMock&CalculableWithFilesInterface $mock */
                Phake::verify($mock)->__call('setFiles', [$files]);
            }
            Phake::verify($mock)->__call('calculate', []);
        }, $calculableElements);

        array_map(Phake::verifyNoOtherInteractions(...), $calculableElements);
    }
}
