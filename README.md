# XML Navigator

## Installation

```bash
composer require sbwerewolf/xml-navigator
```

## What is this?

A small PHP library for three boring but common XML jobs:

- convert XML into native PHP arrays;
- stream huge XML files and extract only the elements you need;
- emit JSON-friendly structures without hand-written normalization.

If you are integrating feeds, imports, partner exports, SOAP-ish 
payloads, or other legacy XML, this package is meant to remove glue 
code rather than introduce a new abstraction layer.

It is built on top of `XMLReader`, so it can work with very large 
documents without loading the whole file into memory.

## What it is good at

### 1) Turn arbitrary XML into PHP arrays

Use it when you need a plain array now, not an object tree, DOM 
traversal, or custom recursive code.

### 2) Stream large XML files

Use it when the file is too large to load comfortably and you only 
care about specific nodes such as `<offer>`, `<item>`, or `<row>`.

### 3) Produce JSON-friendly output

Use `prettyPrint()` when the result is going to logs, APIs, debug 
dumps, or a queue payload.

## What it is not trying to be

This package is not a full XML query language, schema validator, 
or XML editor.
It is focused on **read, extract, and convert**.

Requirements:

- PHP `>= 8.4`
- `ext-xmlreader`
- `ext-libxml`

## Start with the highest-level API

Most projects only need one of these entry points:

- `FastXmlToArray::prettyPrint()` — readable, JSON-friendly output
- `FastXmlToArray::convert()` — normalized hierarchy for traversal
- `FastXmlParser::extractPrettyPrint()` — stream matching elements as readable arrays
- `FastXmlParser::extractHierarchy()` — stream matching elements as normalized arrays

Everything else in the package exists to support more custom or 
lower-level workflows.

## Choose your output format

### `prettyPrint()`: readable output for application code and JSON

Use this when you want to:

- inspect the result easily;
- serialize it to JSON;
- keep repeated child tags grouped as arrays;
- move XML data into regular PHP or HTTP code quickly.

```php
<?php
use SbWereWolf\XmlNavigator\Conversion\FastXmlToArray;

$xml = '
<feed generated_at="2026-03-28T09:00:00Z">
  <offer id="206111" available="true">
    <name>USB-C Dock</name>
    <price currency="USD">129.90</price>
    <picture>https://cdn.example.test/1.jpg</picture>
    <picture>https://cdn.example.test/2.jpg</picture>
  </offer>
</feed>
';

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

### `convert()`: normalized hierarchy for traversal and wrappers

Use this when you want to:

- traverse XML in a predictable structure;
- keep name, value, attributes, and children explicit;
- wrap the result in `XmlElement` later.

```php
<?php

use SbWereWolf\XmlNavigator\Conversion\FastXmlToArray;

$xml = '
<feed generated_at="2026-03-28T09:00:00Z">
  <offer id="206111" available="true">
    <name>USB-C Dock</name>
    <price currency="USD">129.90</price>
    <picture>https://cdn.example.test/1.jpg</picture>
    <picture>https://cdn.example.test/2.jpg</picture>
  </offer>
</feed>
';

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

## Stream large XML files

This is the performance-oriented part of the package.

When you already have an `XMLReader`, you can extract only the
nodes you care about and process them one by one.
That keeps memory use stable even when the source file is large.

```php
<?php

use SbWereWolf\XmlNavigator\Parsing\FastXmlParser;

$file = fopen('catalog.xml', 'w');
fwrite(
    $file,
'
<catalog>
    <offer id="1001" available="true">
      <name>Keyboard</name>
      <price currency="USD">49.90</price>
    </offer>
    <service id="x1">
        <name>Warranty</name>
    </service>
    <offer id="1002" available="false">
      <name>Mouse</name>
      <price currency="USD">19.90</price>
    </offer>
</catalog>
'
);
fclose($file);

$reader = XMLReader::open('catalog.xml');

$offers = FastXmlParser::extractPrettyPrint(
    $reader,
    static fn (XMLReader $cursor): bool => $cursor->name === 'offer'
);

foreach ($offers as $offer) {
    echo json_encode($offer, JSON_PRETTY_PRINT) . PHP_EOL;
}

$reader->close();
```

`FastXmlParser::extractHierarchy()` works the same way, but yields 
normalized arrays instead of pretty-print arrays.

## Navigate normalized XML with `XmlElement`

`XmlElement` is a thin wrapper over the normalized hierarchy.
Use it when arrays are still the right storage format, but you want 
a more convenient traversal API.

```php
<?php

use SbWereWolf\XmlNavigator\Conversion\FastXmlToArray;
use SbWereWolf\XmlNavigator\Navigation\IXmlAttribute;
use SbWereWolf\XmlNavigator\Navigation\IXmlElement;
use SbWereWolf\XmlNavigator\Navigation\XmlElement;

$xml = '
<catalog region="eu">
  <offer id="1001" available="true">
    <name>Keyboard</name>
    <tag>office</tag>
    <tag>usb</tag>
  </offer>
  <offer id="1002" available="false" />
</catalog>';

$root = new XmlElement(FastXmlToArray::convert($xml));
$offer = $root->pull('offer')->current();

echo $root->name() . PHP_EOL; // catalog
echo $root->get('region') . PHP_EOL; // eu
echo ($root->hasElement('offer') ? 'yes' : 'no') . PHP_EOL; // yes

foreach ($offer->attributes() as $attribute) {
    /** @var IXmlAttribute $attribute */
    echo $attribute->name() . '=' . $attribute->value() . PHP_EOL;
}
// id=1001
// available=true

$tagValues = array_map(
    static fn (IXmlElement $tag): string => $tag->value(),
    $offer->elements('tag')
);

var_export($tagValues);
/*
 array (
  0 => 'office',
  1 => 'usb',
)
*/

$snapshot = $offer->serialize();
$restored = new XmlElement($snapshot);

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

## Customize key names with `XmlConverter`

Use `XmlConverter` when you want the same conversion logic but need 
your own notation.
This is useful when you already have an internal array contract and 
do not want library defaults leaking into the rest of the codebase.

```php
<?php

use SbWereWolf\XmlNavigator\Conversion\XmlConverter;

$converter = new XmlConverter(
    val: 'value',
    attr: 'attributes',
    name: 'name',
    seq: 'children',
);

$pretty = $converter->toPrettyPrint(
    '<price currency="USD">129.90</price>'
);

$hierarchy = $converter->toHierarchyOfElements(
    '<price currency="USD">129.90</price>'
);

var_export($pretty);
echo PHP_EOL;
var_export($hierarchy);
```

Output:

```php
array (
  'price' =>
  array (
    'value' => '129.90',
    'attributes' =>
    array (
      'currency' => 'USD',
    ),
  ),
)
array (
  'name' => 'price',
  'value' => '129.90',
  'attributes' =>
  array (
    'currency' => 'USD',
  ),
)
```

## Reuse your notation in a stream with `XmlParser`

`XmlParser` is the object-oriented wrapper around `FastXmlParser`.
Use it when you want to configure notation once and then reuse the 
parser in several places.

```php
<?php

use SbWereWolf\XmlNavigator\Parsing\XmlParser;

$reader = XMLReader::XML(
'
<dataset>
  <row id="1">
    <value>alpha</value>
  </row>
  <row id="2" />
</dataset>'
);

$parser = new XmlParser(
    val: 'value',
    attr: 'attributes',
    name: 'name',
    seq: 'children',
);

$rows = iterator_to_array(
    $parser->extractHierarchy(
        $reader,
        static fn (XMLReader $cursor): bool => $cursor->name === 'row'
    ),
    false
);

var_export($rows);
```

Output:

```php
array (
  0 =>
  array (
    'name' => 'row',
    'attributes' =>
    array (
      'id' => '1',
    ),
    'children' =>
    array (
      0 =>
      array (
        'name' => 'value',
        'value' => 'alpha',
      ),
    ),
  ),
  1 =>
  array (
    'name' => 'row',
    'attributes' =>
    array (
      'id' => '2',
    ),
  ),
)
```

## Compose the current XML node with low-level composers

If you already control the `XMLReader` cursor, 
you can compose just the current node.
This is the lower-level API for custom streaming workflows.

```php
<?php

use SbWereWolf\XmlNavigator\Extraction\HierarchyComposer;
use SbWereWolf\XmlNavigator\Extraction\PrettyPrintComposer;

$xml = '
<root>
  <offer id="1">
    <name>Keyboard</name>
  </offer>
  <offer id="2">
    <name>Mouse</name>
  </offer>
</root>
';

$prettyReader = XMLReader::XML($xml);
while (
    $prettyReader->read()
    && !(
        $prettyReader->nodeType === XMLReader::ELEMENT
        && $prettyReader->name === 'offer'
        && $prettyReader->getAttribute('id') === '2'
    )
) {
}

$pretty = PrettyPrintComposer::compose($prettyReader);
var_export($pretty);
/*
array (
  'offer' =>
  array (
    '@attributes' =>
    array (
      'id' => '2',
    ),
    'name' => 'Mouse',
  ),
)
 */
echo PHP_EOL;

$hierarchyReader = XMLReader::XML($xml);
while (
    $hierarchyReader->read()
    && !(
        $hierarchyReader->nodeType === XMLReader::ELEMENT
        && $hierarchyReader->name === 'offer'
        && $hierarchyReader->getAttribute('id') === '2'
    )
) {
}

$hierarchy = HierarchyComposer::compose($hierarchyReader);
var_export($hierarchy);
/*
array (
  'n' => 'offer',
  'a' =>
  array (
    'id' => '2',
  ),
  's' =>
  array (
    0 =>
    array (
      'n' => 'name',
      'v' => 'Mouse',
    ),
  ),
)
*/
```

Output:

```php
array (
  'offer' =>
  array (
    '@attributes' =>
    array (
      'id' => '2',
    ),
    'name' => 'Mouse',
  ),
)
array (
  'n' => 'offer',
  'a' =>
  array (
    'id' => '2',
  ),
  's' =>
  array (
    0 =>
    array (
      'n' => 'name',
      'v' => 'Mouse',
    ),
  ),
)
```

## Public API reference

This section lists the full public surface of the package.
The order is intentional: start from the top, 
drop lower only when you actually need more control.

### Everyday API

#### `SbWereWolf\XmlNavigator\Conversion\FastXmlToArray`

Static helpers for one-shot conversion.

- `convert(string $xmlText = '', string $xmlUri = '', string $val = 'v', string $attr = 'a', string $name = 'n', string $seq = 's', ?string $encoding = null, int $flags = LIBXML_BIGLINES | LIBXML_COMPACT): array`  
  Convert XML into the normalized hierarchy format.

- `prettyPrint(string $xmlText = '', string $xmlUri = '', string $val = '@value', string $attr = '@attributes', ?string $encoding = null, int $flags = LIBXML_BIGLINES | LIBXML_COMPACT): array`  
  Convert XML into the readable, JSON-friendly format.

Notes:

- Pass XML either as `$xmlText` or as `$xmlUri`.
- If both inputs are empty, the method throws `InvalidArgumentException`.

#### `SbWereWolf\XmlNavigator\Conversion\XmlConverter`

Stateful converter with configurable key names.

- `__construct(string $val = 'v', string $attr = 'a', string $name = 'n', string $seq = 's', ?string $encoding = null, int $flags = LIBXML_BIGLINES | LIBXML_COMPACT)`  
  Configure the key names and parser options used by the instance.

- `toPrettyPrint(string $xmlText = '', string $xmlUri = ''): array`  
  Convert XML into the readable format using the instance notation.

- `toHierarchyOfElements(string $xmlText = '', string $xmlUri = ''): array`  
  Convert XML into the normalized format using the instance notation.

- `jsonSerialize(): mixed`  
  Available because the class implements `JsonSerializable` 
  through the external package `sbwerewolf/json-serialize-trait`.

Behavior note: the instance caches the last processed XML input and 
reuses the cached result when the next call uses the same source.

#### `SbWereWolf\XmlNavigator\Parsing\FastXmlParser`

Static streaming parser for `XMLReader`.

- `extractHierarchy(XMLReader $reader, callable $detectElement, string $val = 'v', string $attr = 'a', string $name = 'n', string $seq = 's'): Generator`  
  Yield matching elements in normalized hierarchy format.

- `extractPrettyPrint(XMLReader $reader, callable $detectElement, string $val = '@value', string $attr = '@attributes'): Generator`  
  Yield matching elements in readable format.

The callback receives the current `XMLReader` cursor and
should return `true` for nodes that should be extracted.

#### `SbWereWolf\XmlNavigator\Parsing\XmlParser`

Object-oriented wrapper around `FastXmlParser`.

- `__construct(string $val = 'v', string $attr = 'a', string $name = 'n', string $seq = 's')`  
  Configure the notation once.

- `extractHierarchy(XMLReader $reader, callable $detectElement): Generator`  
  Yield matching elements in normalized hierarchy format.

- `extractPrettyPrint(XMLReader $reader, callable $detectElement): Generator`  
  Yield matching elements in readable format.

#### `SbWereWolf\XmlNavigator\Navigation\XmlElement`

Navigation helper for normalized XML arrays.

- `__construct(array $initial, string $name = 'n', string $val = 'v', string $attr = 'a', string $seq = 's')`  
  Wrap a normalized XML element array. 
  Throws `InvalidArgumentException` if the array does not follow 
  the expected shape.

- `name(): string`  
  Return the element name.

- `hasValue(): bool`  
  Return `true` when the element has direct text content.

- `value(): string`  
  Return the direct text content, or an empty string.

- `hasAttribute(string $name = ''): bool`  
  Check whether the element has any attribute, or a specific attribute.

- `attributes(): array`  
  Return all attributes as `XmlAttribute[]`.

- `get(string $name = ''): string`  
  Return the value of a named attribute. If no name is passed, return 
  the first attribute value or an empty string.

- `hasElement(string $name = ''): bool`  
  Check whether the element has any child element, or a child element 
  with a specific name.

- `elements(string $name = ''): array`  
  Return child elements as `XmlElement[]`. When a name is provided, 
  return only matching children.

- `pull(string $name = ''): Generator`  
  Lazily yield child elements as `XmlElement` objects. When a name is 
  provided, yield only matching children.

- `serialize(): array`  
  Return the normalized array snapshot used to restore the same 
  `XmlElement` later.

- `jsonSerialize(): mixed`  
  Available because the class implements `JsonSerializable` through 
  the external package `sbwerewolf/json-serialize-trait`.

#### `SbWereWolf\XmlNavigator\Navigation\XmlAttribute`

Value object for one attribute.

- `__construct(string $name, string $value)`  
  Create an attribute object.

- `name(): string`  
  Return the attribute name.

- `value(): string`  
  Return the attribute value.

- `jsonSerialize(): mixed`  
  Available because the class implements `JsonSerializable` through 
  the external package `sbwerewolf/json-serialize-trait`.

### Advanced building blocks

#### `SbWereWolf\XmlNavigator\Extraction\PrettyPrintComposer`

- `compose(XMLReader $reader, string $valueIndex = '@value', string $attributesIndex = '@attributes'): array`  
  Compose the current element under the reader into the readable format. 
  If the reader is not currently on an element node, 
  it advances until it finds one. If no element is found, 
  it returns an empty array.

#### `SbWereWolf\XmlNavigator\Extraction\HierarchyComposer`

- `compose(XMLReader $reader, string $valueIndex = 'v', string $attributesIndex = 'a', string $nameIndex = 'n', string $elementsIndex = 's'): array`  
  Compose the current element under the reader into the normalized 
  hierarchy format. If the reader is not currently on an element node,
  it advances until it finds one. If no element is found, 
  it returns an empty array.

#### `SbWereWolf\XmlNavigator\Extraction\ElementExtractor`

Low-level internal-style helper used to extract XML element events 
with depth metadata.

- `extractElements(XMLReader $reader, string $valueIndex, string $attributesIndex): array`  
  Return a flat list of extracted element fragments that still contain 
  depth information. Useful when you need to build your own composer 
  or inspect parsing internals.

Public constant:

- `DEPTH`  
  The array key used to store node depth inside extractor output.

### Constants and internal base classes

#### `SbWereWolf\XmlNavigator\General\Notation`

Public constants used by the library defaults:

- `NAME` = `'n'`
- `VALUE` = `'v'`
- `ATTRIBUTES` = `'a'`
- `SEQUENCE` = `'s'`
- `VAL` = `'@value'`
- `ATTR` = `'@attributes'`

#### `SbWereWolf\XmlNavigator\Extraction\ElementComposer`

Base class for composers. It does not expose public methods and is 
not intended for day-to-day use.

### Contracts

The package also exposes interfaces that mirror the concrete APIs:

- `IFastXmlToArray`
- `IXmlConverter`
- `IXmlElement`
- `IXmlAttribute`

## Practical notes

- Attributes are always returned as strings.
- Repeated child tags become indexed arrays in pretty-print output.
- Empty elements become empty arrays in pretty-print output and 
  name-only nodes in hierarchy output.
- The examples in this README are covered by tests in 
  `tests/Integration/ReadmeExamplesTest.php`.

## Development verification

Primary local checks:

- `composer test`
- `composer test-with-coverage`
- `composer phpstan-check`
- `composer check-style`

`composer test-with-coverage` is the canonical coverage entrypoint. It
uses a local coverage driver when one is available and falls back to a
Docker-based PHPUnit runtime otherwise. The HTML report is written to
`continuous-integration/autotests-coverage-report`.

For the high-level conversion API, exactly one XML source must be
provided: use either `$xmlText` or `$xmlUri`, but not both.

```
Nicholas Volkhin
e-mail ulfnew@gmail.com
phone +7-902-272-65-35
Telegram @sbwerewolf
```

- [Telegram chat with me](https://t.me/SbWereWolf)
- [WhatsApp chat with me](https://wa.me/79022726535)
- 