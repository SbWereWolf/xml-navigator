<?php


$parts = [
    __DIR__,
    '..',
    '..',
    'vendor',
    'autoload.php',
];
$autoload = join(DIRECTORY_SEPARATOR, $parts);

require_once $autoload;

/**
 * @param string $filename
 * @param int $limit
 * @param string $xml
 * @return void
 */
function generateFile(string $filename, int $limit, string $xml): void
{
    $file = fopen($filename, 'a');
    fwrite($file, '<Collection>');

    for ($i = 0; $i < $limit; $i++) {
        $content = "$xml$xml$xml$xml$xml$xml$xml$xml$xml$xml";
        fwrite($file, $content);
    }

    fwrite($file, '</Collection>');
    fclose($file);

    $size = round(filesize($filename) / 1024, 2);
    echo "$filename size is $size Kb" . PHP_EOL;
}

$xml = '<SomeElement key="123">value</SomeElement>' . PHP_EOL;
$generation['temp-465b.xml'] = 1;
$generation['temp-429Kb.xml'] = 1_000;
$generation['temp-429Mb.xml'] = 1_000_000;

foreach ($generation as $filename => $size) {
    generateFile($filename, $size, $xml);
}

/**
 * @param string $filename
 * @return void
 */
function parseFirstElement(string $filename): void
{
    $start = hrtime(true);

    /** @var XMLReader $reader */
    $reader = XMLReader::open($filename);

    $mayRead = true;
    /* scroll to first `SomeElement` */
    while ($mayRead && $reader->name !== 'SomeElement') {
        $mayRead = $reader->read();
    }
    /* Compose array from XML element with name `SomeElement` */
    $result =
        \SbWereWolf\XmlNavigator\Extraction\PrettyPrintComposer
            ::compose($reader);

    $reader->close();

    $finish = hrtime(true);
    $duration = $finish - $start;
    $duration = number_format($duration,);
    echo "First element parsing duration of $filename is $duration ns" .
        PHP_EOL;
}
/* files to metering with benchmark */
$files = [
    'temp-465b.xml',
    'temp-429Kb.xml',
    'temp-429Mb.xml',
];

echo 'Warm up OPcache' . PHP_EOL;
parseFirstElement(current($files));

echo 'Benchmark is starting' . PHP_EOL;
foreach ($files as $filename) {
    parseFirstElement($filename);
}
echo 'Benchmark was finished' . PHP_EOL;
