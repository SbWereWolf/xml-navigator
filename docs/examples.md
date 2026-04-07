# Examples by workflow and API

This file keeps the documentation examples in a form that is easy to
copy, run, and adapt.

## Index

[Normalized hierarchy for traversal and wrappers](#1-fastxmltoarrayconvert)
[Streaming extraction for large XML](#2-fastxmlparserextracthierarchy)
[Streaming extraction with custom hierarchy keys](#3-fastxmlparserextracthierarchy-with-custom-keys)
[Traversal wrapper over the normalized hierarchy](#4-xmlelement)
[Custom key names for one-shot conversion](#5-xmlconverter)
[Readable output for application code and JSON](#6-fastxmltoarrayprettyprint)
[Readable streaming output](#7-fastxmlparserextractprettyprint)
[Compose the current node when you already control the `XMLReader` cursor](#8-low-level-composers)

## 1) `FastXmlToArray::convert()`

Normalized hierarchy for traversal and wrappers.

```php
use SbWereWolf\XmlNavigator\Conversion\FastXmlToArray;

$xml = <<<'XML'
<feed generated_at="2026-03-28T09:00:00Z">
  <offer id="206111" available="true">
    <name>USB-C Dock</name>
    <price currency="USD">129.90</price>
    <picture>https://cdn.example.test/1.jpg</picture>
    <picture>https://cdn.example.test/2.jpg</picture>
  </offer>
</feed>
XML;

$result = FastXmlToArray::convert($xml);

var_export($result);
```

Output:

```php
array (
  'n' => 'feed',
  'a' =>
  array (
    'generated_at' => '2026-03-28T09:00:00Z',
  ),
  's' =>
  array (
    0 =>
    array (
      'n' => 'offer',
      'a' =>
      array (
        'id' => '206111',
        'available' => 'true',
      ),
      's' =>
      array (
        0 =>
        array (
          'n' => 'name',
          'v' => 'USB-C Dock',
        ),
        1 =>
        array (
          'n' => 'price',
          'v' => '129.90',
          'a' =>
          array (
            'currency' => 'USD',
          ),
        ),
        2 =>
        array (
          'n' => 'picture',
          'v' => 'https://cdn.example.test/1.jpg',
        ),
        3 =>
        array (
          'n' => 'picture',
          'v' => 'https://cdn.example.test/2.jpg',
        ),
      ),
    ),
  ),
)
```

## 2) `FastXmlParser::extractHierarchy()`

Streaming extraction for large XML. This is the recommended workflow
when the file is large and only some nodes matter.

```php
use SbWereWolf\XmlNavigator\Parsing\FastXmlParser;

$uri = tempnam(sys_get_temp_dir(), 'xml-extract-kit-');
file_put_contents($uri, <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<catalog>
  <offer id="1">
    <name>Keyboard</name>
    <price>49.90</price>
  </offer>
  <service id="s-1">
    <name>Warranty</name>
  </service>
  <offer id="2">
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
```

## 3) `FastXmlParser::extractHierarchy()` with custom keys

The same hierarchy-first workflow can use your own internal key names.

```php
use SbWereWolf\XmlNavigator\Parsing\FastXmlParser;

$uri = tempnam(sys_get_temp_dir(), 'xml-extract-kit-');
file_put_contents($uri, <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<catalog>
  <offer id="1">
    <name>Keyboard</name>
    <price>49.90</price>
  </offer>
  <service id="s-1">
    <name>Warranty</name>
  </service>
  <offer id="2">
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
        },
    'value',
    'attributes',
    'name',
    'children',
);

foreach ($offers as $offer) {
    var_export($offer);
    echo PHP_EOL;
}

$reader->close();
unlink($uri);
```

## 4) `XmlElement`

Traversal wrapper over the normalized hierarchy.

```php
use SbWereWolf\XmlNavigator\Conversion\FastXmlToArray;
use SbWereWolf\XmlNavigator\Navigation\XmlElement;

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

echo $root->name() . PHP_EOL; // catalog
echo $root->get('region') . PHP_EOL; // eu
echo ($root->hasElement('offer') ? 'yes' : 'no') . PHP_EOL; // yes

foreach ($offer->attributes() as $attribute) {
    echo $attribute->name() . '=' . $attribute->value() . PHP_EOL;
}

$tagValues = array_map(
    static function (XmlElement $tag): string { return $tag->value(); },
    $offer->elements('tag')
);

var_export($tagValues);

$snapshot = $offer->serialize();
$restored = new XmlElement($snapshot);

echo PHP_EOL;
echo $restored->get('id') . PHP_EOL; // 1001
```

Output:

```text
catalog
eu
yes
id=1001
available=true
array (
  0 => 'office',
  1 => 'usb',
)
1001
```

## 5) `XmlConverter`

Custom key names for one-shot conversion.

```php
use SbWereWolf\XmlNavigator\Conversion\XmlConverter;

$converter = new XmlConverter(
    'value',
    'attributes',
    'name',
    'children'
);

$hierarchy = $converter->toHierarchyOfElements(
    '<price currency="USD">129.90</price>'
);

var_export($hierarchy);
```

Output:

```php
array (
  'name' => 'price',
  'value' => '129.90',
  'attributes' =>
  array (
    'currency' => 'USD',
  ),
)
```

## 6) `FastXmlToArray::prettyPrint()`

Readable output for application code and JSON.

```php
use SbWereWolf\XmlNavigator\Conversion\FastXmlToArray;

$xml = <<<'XML'
<feed generated_at="2026-03-28T09:00:00Z">
  <offer id="206111" available="true">
    <name>USB-C Dock</name>
    <price currency="USD">129.90</price>
    <picture>https://cdn.example.test/1.jpg</picture>
    <picture>https://cdn.example.test/2.jpg</picture>
  </offer>
</feed>
XML;

$result = FastXmlToArray::prettyPrint($xml);

echo json_encode(
    $result,
    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
);
```

Output:

```json
{
  "feed": {
    "@attributes": {
      "generated_at": "2026-03-28T09:00:00Z"
    },
    "offer": {
      "@attributes": {
        "id": "206111",
        "available": "true"
      },
      "name": "USB-C Dock",
      "price": {
        "@value": "129.90",
        "@attributes": {
          "currency": "USD"
        }
      },
      "picture": [
        "https://cdn.example.test/1.jpg",
        "https://cdn.example.test/2.jpg"
      ]
    }
  }
}
```

## 7) `FastXmlParser::extractPrettyPrint()`

Readable streaming output when you want JSON-like arrays directly.

```php
use SbWereWolf\XmlNavigator\Parsing\FastXmlParser;

$uri = tempnam(sys_get_temp_dir(), 'xml-extract-kit-');
file_put_contents($uri, <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<catalog>
  <offer id="1">
    <name>Keyboard</name>
    <price>49.90</price>
  </offer>
  <service id="s-1">
    <name>Warranty</name>
  </service>
  <offer id="2">
    <name>Mouse</name>
    <price>19.90</price>
  </offer>
</catalog>
XML);

$reader = XMLReader::open($uri);

if ($reader === false) {
    throw new RuntimeException('Cannot open XML file.');
}

$offers = FastXmlParser::extractPrettyPrint(
    $reader,
    static function (XMLReader $cursor): bool { return $cursor->name === 'offer'; }
);

foreach ($offers as $offer) {
    echo json_encode($offer, JSON_PRETTY_PRINT) . PHP_EOL;
}

$reader->close();
unlink($uri);
```

## 8) Low-level composers

When you already control the `XMLReader` cursor, the low-level 
composers can serialize the current element directly.

```php
use SbWereWolf\XmlNavigator\Extraction\HierarchyComposer;
use SbWereWolf\XmlNavigator\Extraction\PrettyPrintComposer;

$reader = XMLReader::XML(
    '<price currency="USD">129.90</price>'
);

$hierarchy = HierarchyComposer::compose($reader);
$reader->close();

$reader = XMLReader::XML(
    '<price currency="USD">129.90</price>'
);

$pretty = PrettyPrintComposer::compose($reader);
$reader->close();

var_export($hierarchy);
echo PHP_EOL;
var_export($pretty);
```
