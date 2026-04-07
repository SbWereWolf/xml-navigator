<?php

declare(strict_types=1);

use SbWereWolf\XmlNavigator\Conversion\FastXmlToArray;
use SbWereWolf\XmlNavigator\Navigation\XmlElement;

require_once __DIR__ . '/../vendor/autoload.php';

$xml = <<<'XML'
<catalog region="eu">
  <offer id="1001" available="true">
    <name>Keyboard</name>
    <tag>office</tag>
    <tag>usb</tag>
  </offer>
</catalog>
XML;

$root = new XmlElement(FastXmlToArray::convert($xml));
$offer = $root->pull('offer')->current();

echo $root->name() . PHP_EOL;
echo $root->get('region') . PHP_EOL;
echo ($root->hasElement('offer') ? 'yes' : 'no') . PHP_EOL;

foreach ($offer->attributes() as $attribute) {
    echo $attribute->name() . '=' . $attribute->value() . PHP_EOL;
}

$tagValues = array_map(
    static function (XmlElement $tag): string { return $tag->value(); },
    $offer->elements('tag')
);

var_export($tagValues);
