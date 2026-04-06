# XmlExtractKit

[![Packagist Version](https://img.shields.io/packagist/v/sbwerewolf/xml-navigator?label=packagist)](https://packagist.org/packages/sbwerewolf/xml-navigator)
[![Packagist Downloads](https://img.shields.io/packagist/dt/sbwerewolf/xml-navigator)](https://packagist.org/packages/sbwerewolf/xml-navigator)
[![PHP 8.4+](https://img.shields.io/badge/PHP-8.4%2B-777BB4)](https://www.php.net/)
[![Static Analysis](https://github.com/SbWereWolf/xml-navigator/actions/workflows/static-analysis.yml/badge.svg)](https://github.com/SbWereWolf/xml-navigator/actions/workflows/static-analysis.yml)
[![Test Coverage](https://codecov.io/github/SbWereWolf/xml-navigator/graph/badge.svg?token=Q0BQ2COFTC)](https://codecov.io/github/SbWereWolf/xml-navigator)


**XmlExtractKit for PHP: Stream large XML, extract only what matters,
and get plain PHP arrays.**

```text
large XML → selected nodes → plain PHP arrays
```

## Installation

```bash
composer require sbwerewolf/xml-navigator
```

For local test and coverage dependencies on a standard PHP 8.4 setup,
see [`tests/ENVIRONMENT.md`](tests/ENVIRONMENT.md).

## Why this package?

XmlExtractKit is built for the boring XML jobs that show up in real
systems:

- convert XML into native PHP arrays;
- stream huge XML files and extract only the elements you need;
- keep application code working with plain arrays instead of
  cursor-level `XMLReader` logic.

Use it for feeds, partner exports, imports, SOAP-ish payloads,
marketplace catalogs, ETL pipelines, and other legacy XML integrations.

## Core workflow

Open large XML with `XMLReader`, select matching nodes, receive plain
PHP arrays.

```php
use SbWereWolf\XmlNavigator\Parsing\FastXmlParser;

require_once __DIR__ . '/vendor/autoload.php';

$uri = tempnam(sys_get_temp_dir(), 'xml-extract-kit-');
file_put_contents(
    $uri,
    <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<catalog generated_at="2026-04-05T10:00:00Z">
  <offer id="1001" available="true">
    <name>Keyboard</name>
    <price currency="USD">49.90</price>
  </offer>
  <service id="s-1">
    <name>Warranty</name>
  </service>
  <offer id="1002" available="false">
    <name>Mouse</name>
    <price currency="USD">19.90</price>
  </offer>
</catalog>
XML
);

$reader = XMLReader::open($uri);
foreach (
    FastXmlParser::extractHierarchy(
        $reader,
        static fn(XMLReader $cursor): bool =>
            $cursor->nodeType === XMLReader::ELEMENT
            && $cursor->name === 'offer'
    ) as $offer
) {
    var_export($offer);
    echo PHP_EOL;
}
$reader->close();

unlink($uri);
```

Output:

```php
array (
  'n' => 'offer',
  'a' =>
  array (
    'id' => '1001',
    'available' => 'true',
  ),
  's' =>
  array (
    0 =>
    array (
      'n' => 'name',
      'v' => 'Keyboard',
    ),
    1 =>
    array (
      'n' => 'price',
      'v' => '49.90',
      'a' =>
      array (
        'currency' => 'USD',
      ),
    ),
  ),
)
array (
  'n' => 'offer',
  'a' =>
  array (
    'id' => '1002',
    'available' => 'false',
  ),
  's' =>
  array (
    0 =>
    array (
      'n' => 'name',
      'v' => 'Mouse',
    ),
    1 =>
    array (
      'n' => 'price',
      'v' => '19.90',
      'a' =>
      array (
        'currency' => 'USD',
      ),
    ),
  ),
)
```

## Index
- [Turn XML into arrays with custom keys](#turn-xml-into-arrays-with-custom-keys)
- [Extract only the needed elements from large XML without loading the whole document](#extract-only-the-needed-elements-from-large-xml-without-loading-the-whole-document)
- [Convert XML to a traversable array and walk it with `XmlElement`](#convert-xml-to-a-traversable-array-and-walk-it-with-xmlelement)
- [Practical notes](#practical-notes)
- [Detailed documentation](#detailed-documentation)
- [Common use cases](#common-use-cases)
- [Pick your entry point](#pick-your-entry-point)
- [Contacts](#contacts)

## Code test coverage

![Codecov graph](https://codecov.io/github/SbWereWolf/xml-navigator/graphs/tree.svg?token=Q0BQ2COFTC)


## Working examples

### Turn XML into arrays with custom keys

Use `XmlConverter` when your project already has its own internal
array contract and you want hierarchy output with your own key names.

```php
use SbWereWolf\XmlNavigator\Conversion\XmlConverter;

require_once __DIR__ . '/vendor/autoload.php';

$converter = new XmlConverter(
    val: 'value',
    attr: 'attributes',
    name: 'name',
    seq: 'children',
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

### Extract only the needed elements from large XML without loading the whole document

Use `FastXmlParser` on top of `XMLReader` when the file is large and
only some nodes matter.

```php
use SbWereWolf\XmlNavigator\Parsing\FastXmlParser;

require_once __DIR__ . '/vendor/autoload.php';

$uri = tempnam(sys_get_temp_dir(), 'xml-extract-kit-');
file_put_contents(
    $uri,
    <<<'XML'
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
XML
);

$reader = XMLReader::open($uri);

$offers = FastXmlParser::extractHierarchy(
    $reader,
    static fn(XMLReader $cursor):
    bool => $cursor->nodeType === XMLReader::ELEMENT
        && $cursor->name === 'offer'
);

$reader->close();
unlink($uri);

foreach ($offers as $offer) {
    var_export($offer);
    echo PHP_EOL;
}
```

### Convert XML to a traversable array and walk it with `XmlElement`

Use `FastXmlToArray::convert()` when you want a stable normalized
structure, then wrap it with `XmlElement` for convenient traversal.

```php
use SbWereWolf\XmlNavigator\Conversion\FastXmlToArray;
use SbWereWolf\XmlNavigator\Navigation\XmlElement;

require_once __DIR__ . '/vendor/autoload.php';

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

echo $root->name() . PHP_EOL;                // catalog
echo $root->get('region') . PHP_EOL;         // eu
echo ($root->hasElement('offer') ? 'yes' : 'no') . PHP_EOL; // yes

echo PHP_EOL;
echo 'offer attributes:' . PHP_EOL;
foreach ($offer->attributes() as $attribute) {
    echo $attribute->name() . '=' . $attribute->value() . PHP_EOL;
}

echo PHP_EOL;
echo 'offer elements with name `tag`:' . PHP_EOL;
$tagValues = array_map(
    static fn (XmlElement $tag): string => $tag->value(),
    $offer->elements('tag')
);

var_export($tagValues);
```

Output:

```text
catalog
eu
yes

offer attributes:
id=1001
available=true

value of offer elements with name `tag`:
array (
  0 => 'office',
  1 => 'usb',
)
```

## Practical notes

- attributes are always strings;
- repeated child tags become indexed arrays in readable output;
- empty elements become empty arrays in readable output and name-only
  nodes in normalized output;
- for one-shot conversion, provide either `$xmlText` or `$xmlUri`,
  but not both;
- if you already have an `XMLReader`, use the streaming API first
  instead of loading the entire document.

## Detailed documentation

The detailed method-by-method documentation stays available in 
dedicated files:

- [Examples by workflow and API](docs/examples.md)
- [Output formats: readable vs normalized](docs/output-formats.md)
- [Public API reference](docs/reference.md)

Standalone runnable snippets are also included in
[`examples/`](examples).

## Common use cases

- supplier and marketplace feeds;
- partner imports and exports;
- ETL jobs that consume XML in batches;
- SOAP-ish or legacy integration payloads;
- queue payload preparation and JSON serialization;
- large catalogs where only selected nodes are relevant.

## What it is not

XmlExtractKit is not trying to be:

- a full XML query language;
- an XML schema validator;
- an XML editor;
- an object mapper that hides XML structure behind a large 
  abstraction layer.

The value proposition is much simpler:

> stream XML, extract only what matters, and keep working with plain 
> arrays.

## Pick your entry point

| Need                                                   | Start here                                   |
|--------------------------------------------------------|----------------------------------------------|
| I need plain arrays from XML now                       | `FastXmlToArray::prettyPrint()`              |
| I need a stable normalized structure for traversal     | `FastXmlToArray::convert()`                  |
| I need to stream only matching elements from large XML | `FastXmlParser::extractPrettyPrint()`        |
| I need streaming plus normalized output                | `FastXmlParser::extractHierarchy()`          |
| I need custom key names                                | `XmlConverter` or `XmlParser`                |
| I need low-level composition around an existing cursor | `PrettyPrintComposer` or `HierarchyComposer` |

## Contacts

```
Nicholas Volkhin
e-mail ulfnew@gmail.com
phone +7-902-272-65-35
Telegram @sbwerewolf
```

- [Telegram chat with me](https://t.me/SbWereWolf)
- [WhatsApp chat with me](https://wa.me/79022726535)
