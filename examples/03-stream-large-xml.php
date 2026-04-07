<?php

declare(strict_types=1);

use SbWereWolf\XmlNavigator\Parsing\FastXmlParser;

require_once __DIR__ . '/../vendor/autoload.php';

$uri = tempnam(sys_get_temp_dir(), 'xml-extract-kit-');
file_put_contents($uri, <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<catalog>
  <offer id="1001">
    <name>Keyboard</name>
    <price>49.90</price>
  </offer>
  <service id="s-1">
    <name>Warranty</name>
  </service>
  <offer id="1002">
    <name>Mouse</name>
    <price>19.90</price>
  </offer>
</catalog>
XML);

$reader = XMLReader::open($uri);

if ($reader === false) {
    throw new RuntimeException('Cannot open XML file.');
}

$offers = FastXmlParser::extractHierarchy(
    $reader,
    static function (XMLReader $cursor): bool {
        return $cursor->nodeType === XMLReader::ELEMENT
            && $cursor->name === 'offer';
        }
);

foreach ($offers as $offer) {
    var_export($offer);
    echo PHP_EOL;
}

$reader->close();
unlink($uri);
