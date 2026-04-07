<?php

declare(strict_types=1);

use SbWereWolf\XmlNavigator\Parsing\FastXmlParser;

require_once __DIR__ . '/../vendor/autoload.php';

$uri = tempnam(sys_get_temp_dir(), 'xml-extract-kit-');
file_put_contents($uri, <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<catalog generated_at="2026-04-05T10:00:00Z">
  <offer id="1001" available="true">
    <name>Keyboard</name>
    <price currency="USD">49.90</price>
  </offer>
  <offer id="1002" available="false">
    <name>Mouse</name>
    <price currency="USD">19.90</price>
  </offer>
  <service id="s-1">
    <name>Warranty</name>
  </service>
</catalog>
XML);

$reader = XMLReader::open($uri);

if ($reader === false) {
    throw new RuntimeException('Cannot open XML file.');
}

foreach (
    FastXmlParser::extractHierarchy(
        $reader,
        static function (XMLReader $cursor): bool {
            return $cursor->nodeType === XMLReader::ELEMENT
                && $cursor->name === 'offer';
            }
    ) as $offer
) {
    var_export($offer);
    echo PHP_EOL;
}

$reader->close();
unlink($uri);
