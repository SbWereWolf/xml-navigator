<?php

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/BenchmarkSupport.php';

use function SbWereWolf\XmlNavigator\Bench\ensureDirectory;
use function SbWereWolf\XmlNavigator\Bench\loadManifest;
use function SbWereWolf\XmlNavigator\Bench\parseArgs;
use function SbWereWolf\XmlNavigator\Bench\runAcceptanceBenchmark;

$options = parseArgs($argv);
$fixturesDir =
    $options['fixtures-dir']
    ?? (__DIR__ . '/../../task/performance-refactor-benchmark/fixtures');
$output =
    $options['output']
    ?? (__DIR__ . '/../../task/performance-refactor-benchmark/reports/acceptance-benchmark.json');

$report = runAcceptanceBenchmark(loadManifest($fixturesDir));
$encoded = json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
if ($encoded === false) {
    throw new RuntimeException('Cannot encode benchmark report');
}

ensureDirectory(dirname($output));
if (file_put_contents($output, $encoded . PHP_EOL) === false) {
    throw new RuntimeException("Cannot write `$output`");
}

echo $encoded . PHP_EOL;
