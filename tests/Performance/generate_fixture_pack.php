<?php

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/BenchmarkSupport.php';

use function SbWereWolf\XmlNavigator\Bench\generateFixturePack;
use function SbWereWolf\XmlNavigator\Bench\parseArgs;

$options = parseArgs($argv);
$fixturesDir =
    $options['fixtures-dir']
    ?? (__DIR__ . '/../../task/performance-refactor-benchmark/fixtures');
$force = ($options['force'] ?? '0') === '1';
$profile = $options['profile'] ?? 'acceptance';

$manifest = generateFixturePack($fixturesDir, $force, $profile);
echo json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
    . PHP_EOL;
