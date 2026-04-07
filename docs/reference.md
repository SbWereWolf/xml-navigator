# Public API reference

This file documents every public method of the current runtime API.
Constructors and `jsonSerialize()` are intentionally omitted from the
example sections.

## Index

- [FastXmlToArray](#fastxmltoarray)
- [XmlConverter](#xmlconverter)
- [FastXmlParser](#fastxmlparser)
- [XmlParser](#xmlparser)
- [XmlElement](#xmlelement)
- [XmlAttribute](#xmlattribute)

## Shared pretty-print showcase

All `prettyPrint` examples use the same XML input so the output rules
are easy to compare at a glance.

Input:

```xml
<root>
  <item>value-only</item>
  <item code="A" />
  <item code="B">value-and-attributes</item>
  <item />
</root>
```

This single input shows every important output form:

- one element with only a text value;
- one element with only attributes;
- one element with both value and attributes;
- one empty element with neither value nor attributes;
- repeated elements with the same name, collected into an indexed list.

## FastXmlToArray

### `convert()`

Convert a full XML document into the stable hierarchy format.

```php
use SbWereWolf\XmlNavigator\Conversion\FastXmlToArray;

$xml = <<<'XML'
<catalog region="eu">
  <offer id="1001">
    <name>Keyboard</name>
    <price currency="USD">49.90</price>
  </offer>
</catalog>
XML;

$result = FastXmlToArray::convert($xml);
```

Result:

```php
[
    'n' => 'catalog',
    'a' => [
        'region' => 'eu',
    ],
    's' => [
        [
            'n' => 'offer',
            'a' => [
                'id' => '1001',
            ],
            's' => [
                [
                    'n' => 'name',
                    'v' => 'Keyboard',
                ],
                [
                    'n' => 'price',
                    'v' => '49.90',
                    'a' => [
                        'currency' => 'USD',
                    ],
                ],
            ],
        ],
    ],
]
```

### `prettyPrint()`

Convert a full XML document into the readable pretty-print format.

```php
use SbWereWolf\XmlNavigator\Conversion\FastXmlToArray;

$xml = <<<'XML'
<root>
  <item>value-only</item>
  <item code="A" />
  <item code="B">value-and-attributes</item>
  <item />
</root>
XML;

$result = FastXmlToArray::prettyPrint($xml);
```

Result:

```php
[
    'root' => [
        'item' => [
            'value-only',
            [
                '@attributes' => [
                    'code' => 'A',
                ],
            ],
            [
                '@value' => 'value-and-attributes',
                '@attributes' => [
                    'code' => 'B',
                ],
            ],
            [],
        ],
    ],
]
```

## XmlConverter

`XmlConverter` keeps the same conversion logic, but lets you rename the
array keys once and reuse that notation.

### `toHierarchyOfElements()`

```php
use SbWereWolf\XmlNavigator\Conversion\XmlConverter;

$converter = new XmlConverter(
    'value',
    'attributes',
    'name',
    'children'
);

$result = $converter->toHierarchyOfElements(
    '<price currency="USD">129.90</price>'
);
```

Result:

```php
[
    'name' => 'price',
    'value' => '129.90',
    'attributes' => [
        'currency' => 'USD',
    ],
]
```

### `toPrettyPrint()`

```php
use SbWereWolf\XmlNavigator\Conversion\XmlConverter;

$converter = new XmlConverter(
    'value',
    'attributes'
);

$result = $converter->toPrettyPrint(
    <<<'XML'
<root>
  <item>value-only</item>
  <item code="A" />
  <item code="B">value-and-attributes</item>
  <item />
</root>
XML
);
```

Result:

```php
[
    'root' => [
        'item' => [
            'value-only',
            [
                'attributes' => [
                    'code' => 'A',
                ],
            ],
            [
                'value' => 'value-and-attributes',
                'attributes' => [
                    'code' => 'B',
                ],
            ],
            [],
        ],
    ],
]
```

## FastXmlParser

`FastXmlParser` is the hierarchy-first streaming API for large files.

### `extractHierarchy()`

```php
use SbWereWolf\XmlNavigator\Parsing\FastXmlParser;

$reader = XMLReader::XML(<<<'XML'
<catalog>
  <offer id="1001"><name>Keyboard</name></offer>
  <service id="s-1"><name>Warranty</name></service>
  <offer id="1002"><name>Mouse</name></offer>
</catalog>
XML);

$offers = iterator_to_array(
    FastXmlParser::extractHierarchy(
        $reader,
        static function (XMLReader $cursor): bool { return $cursor->name === 'offer'; }
    ),
    false
);
```

Result:

```php
[
    [
        'n' => 'offer',
        'a' => [
            'id' => '1001',
        ],
        's' => [
            [
                'n' => 'name',
                'v' => 'Keyboard',
            ],
        ],
    ],
    [
        'n' => 'offer',
        'a' => [
            'id' => '1002',
        ],
        's' => [
            [
                'n' => 'name',
                'v' => 'Mouse',
            ],
        ],
    ],
]
```

### `extractPrettyPrint()`

```php
use SbWereWolf\XmlNavigator\Parsing\FastXmlParser;

$reader = XMLReader::XML(<<<'XML'
<root>
  <item>value-only</item>
  <item code="A" />
  <item code="B">value-and-attributes</item>
  <item />
</root>
XML);

$items = iterator_to_array(
    FastXmlParser::extractPrettyPrint(
        $reader,
        static function (XMLReader $cursor): bool { return $cursor->name === 'item'; }
    ),
    false
);
```

Result:

```php
[
    ['item' => 'value-only'],
    [
        'item' => [
            '@attributes' => [
                'code' => 'A',
            ],
        ],
    ],
    [
        'item' => [
            '@value' => 'value-and-attributes',
            '@attributes' => [
                'code' => 'B',
            ],
        ],
    ],
    ['item' => []],
]
```

### `extractHierarchy()` with custom notation

Custom key names can be applied directly in `FastXmlParser`, without
switching to `XmlParser`.

```php
use SbWereWolf\XmlNavigator\Parsing\FastXmlParser;

$reader = XMLReader::XML(<<<'XML'
<catalog>
  <offer id="1001"><name>Keyboard</name></offer>
</catalog>
XML);

$offers = iterator_to_array(
    FastXmlParser::extractHierarchy(
        $reader,
        static function (XMLReader $cursor): bool { return $cursor->name === 'offer'; },
        'value',
        'attributes',
        'name',
        'children',
    ),
    false
);
```

Result:

```php
[
    [
        'name' => 'offer',
        'attributes' => [
            'id' => '1001',
        ],
        'children' => [
            [
                'name' => 'name',
                'value' => 'Keyboard',
            ],
        ],
    ],
]
```

## XmlParser

`XmlParser` wraps `FastXmlParser` in an object that stores the chosen
notation.

### `extractHierarchy()`

```php
use SbWereWolf\XmlNavigator\Parsing\XmlParser;

$reader = XMLReader::XML(<<<'XML'
<dataset>
  <row id="1"><value>alpha</value></row>
  <row id="2"><value>beta</value></row>
</dataset>
XML);

$parser = new XmlParser(
    'value',
    'attributes',
    'name',
    'children',
);

$rows = iterator_to_array(
    $parser->extractHierarchy(
        $reader,
        static function (XMLReader $cursor): bool { return $cursor->name === 'row'; }
    ),
    false
);
```

Result:

```php
[
    [
        'name' => 'row',
        'attributes' => [
            'id' => '1',
        ],
        'children' => [
            [
                'name' => 'value',
                'value' => 'alpha',
            ],
        ],
    ],
    [
        'name' => 'row',
        'attributes' => [
            'id' => '2',
        ],
        'children' => [
            [
                'name' => 'value',
                'value' => 'beta',
            ],
        ],
    ],
]
```

### `extractPrettyPrint()`

```php
use SbWereWolf\XmlNavigator\Parsing\XmlParser;

$reader = XMLReader::XML(<<<'XML'
<root>
  <item>value-only</item>
  <item code="A" />
  <item code="B">value-and-attributes</item>
  <item />
</root>
XML);

$parser = new XmlParser(
    'value',
    'attributes',
);

$items = iterator_to_array(
    $parser->extractPrettyPrint(
        $reader,
        static function (XMLReader $cursor): bool { return $cursor->name === 'item'; }
    ),
    false
);
```

Result:

```php
[
    ['item' => 'value-only'],
    [
        'item' => [
            'attributes' => [
                'code' => 'A',
            ],
        ],
    ],
    [
        'item' => [
            'value' => 'value-and-attributes',
            'attributes' => [
                'code' => 'B',
            ],
        ],
    ],
    ['item' => []],
]
```

## XmlElement

`XmlElement` is the navigation wrapper for hierarchy arrays. The
examples below assume this setup:

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
```

### `name()`

```php
$root->name();
```

Result:

```php
'catalog'
```

### `hasValue()`

```php
$offer->hasValue();
```

Result:

```php
false
```

### `value()`

```php
$offer->pull('name')->current()->value();
```

Result:

```php
'Keyboard'
```

### `hasAttribute()`

```php
$offer->hasAttribute('id');
```

Result:

```php
true
```

### `attributes()`

```php
array_map(
    static function ($attribute): array { return [
        $attribute->name(),
        $attribute->value(),
    ]; },
    $offer->attributes()
);
```

Result:

```php
[
    ['id', '1001'],
    ['available', 'true'],
]
```

### `get()`

```php
$offer->get('id');
```

Result:

```php
'1001'
```

### `hasElement()`

```php
$offer->hasElement('tag');
```

Result:

```php
true
```

### `elements()`

```php
array_map(
    static function (XmlElement $tag): string { return $tag->value(); },
    $offer->elements('tag')
);
```

Result:

```php
[
    'office',
    'usb',
]
```

### `pull()`

```php
$root->pull('offer')->current()->get('id');
```

Result:

```php
'1001'
```

### `serialize()`

```php
$offer->serialize();
```

Result:

```php
[
    'n' => 'offer',
    'a' => [
        'id' => '1001',
        'available' => 'true',
    ],
    's' => [
        [
            'n' => 'name',
            'v' => 'Keyboard',
        ],
        [
            'n' => 'tag',
            'v' => 'office',
        ],
        [
            'n' => 'tag',
            'v' => 'usb',
        ],
    ],
]
```

## XmlAttribute

These examples assume:

```php
$attribute = $offer->attributes()[0];
```

### `name()`

```php
$attribute->name();
```

Result:

```php
'id'
```

### `value()`

```php
$attribute->value();
```

Result:

```php
'1001'
```
