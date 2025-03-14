# Xml Navigator

The PHP library `Xml Navigator` base on `XMLReader`.

You can assign XML as string or as URI ( or file system path to file).

Navigator can provide XML-document as array or as object.

## How to use

```php
$xml =<<<XML
<outer any_attrib="attribute value">
    <inner>element value</inner>
    <nested nested-attrib="nested attribute value">nested element value</nested>
</outer>
XML;
$result =
    \SbWereWolf\XmlNavigator\Convertation\FastXmlToArray
    ::prettyPrint($xml);
echo json_encode($result, JSON_PRETTY_PRINT);
```

OUTPUT:

```json
{
  "outer": {
    "@attributes": {
      "any_attrib": "attribute value"
    },
    "inner": "element value",
    "nested": {
      "@value": "nested element value",
      "@attributes": {
        "nested-attrib": "nested attribute value"
      }
    }
  }
}
```

## How To Install

`composer require sbwerewolf/xml-navigator`

## Use cases

### XML file processing with no worries of file size

Access time to first element do not depend on file size.

Let explain this with example.

First generate XML files by script:

```php
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
```

```bash
temp-465b.xml size is 0.45 Kb
temp-429Kb.xml size is 429.71 Kb
temp-429Mb.xml size is 429687.52 Kb
```

Now, run benchmark by script:

```php
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
    while ($mayRead && $reader->name !== 'SomeElement') {
        $mayRead = $reader->read();
    }
    
    $result =
        \SbWereWolf\XmlNavigator\Extraction\PrettyPrintComposer
        ::compose($reader);

    $finish = hrtime(true);
    $duration = $finish - $start;
    $duration = number_format($duration,);
    echo "First element parsing duration of $filename is $duration ns" .
        PHP_EOL;

    $reader->close();
}

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
```

```bash
Warm up OPcache
First element parsing duration of temp-465b.xml is 1,250,700 ns
Benchmark is starting
First element parsing duration of temp-465b.xml is 114,400 ns
First element parsing duration of temp-429Kb.xml is 132,400 ns
First element parsing duration of temp-429Mb.xml is 119,900 ns
Benchmark was finished
```

### Parse XML fast with callback for detect suitable elements

```php
$xml = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<CARPLACES>
    <CARPLACE
            ID="11356925"
            OBJECTID="20318444"
            OBJECTGUID="6e237b93-09d6-4adf-9567-e9678608543b"
            CHANGEID="31810106"
            NUMBER="1"
            OPERTYPEID="10"
            PREVID="0"
            NEXTID="0"
            UPDATEDATE="2019-07-09"
            STARTDATE="2019-07-09"
            ENDDATE="2079-06-06"
            ISACTUAL="1"
            ISACTIVE="1"
    />
    <CARPLACE
            ID="11361653"
            OBJECTID="20326793"
            OBJECTGUID="11d9f79b-be6f-43dc-bdcc-70bbfc9f86b0"
            CHANGEID="31822630"
            NUMBER="1"
            OPERTYPEID="10"
            PREVID="0"
            NEXTID="0"
            UPDATEDATE="2019-07-30"
            STARTDATE="2019-07-30"
            ENDDATE="2079-06-06"
            ISACTUAL="1"
            ISACTIVE="1"
    />
    <CARPLACE
            ID="94824"
            OBJECTID="101032823"
            OBJECTGUID="4f37e0eb-141f-4c19-b416-0ec85e2e9e76"
            CHANGEID="192339336"
            NUMBER="0"
            OPERTYPEID="10"
            PREVID="0"
            NEXTID="0"
            UPDATEDATE="2021-04-22"
            STARTDATE="2021-04-22"
            ENDDATE="2079-06-06"
            ISACTUAL="1"
            ISACTIVE="1"
    />
</CARPLACES>
XML;

$reader = XMLReader::XML($xml);

$extractor = \SbWereWolf\XmlNavigator\Parsing\FastXmlParser
    ::extractPrettyPrint(
        $reader,
        /* callback for detect desired elements */
        function (XMLReader $cursor) {
            return $cursor->name === 'CARPLACE';
        }
    );

$results = [];
foreach ($extractor as $result) {
    $results[] = $result;
}
$reader->close();

echo json_encode($results, JSON_PRETTY_PRINT) . PHP_EOL;
```

Output to console will be:

```json
[
  {
    "CARPLACE": {
      "@attributes": {
        "ID": "11356925",
        "OBJECTID": "20318444",
        "OBJECTGUID": "6e237b93-09d6-4adf-9567-e9678608543b",
        "CHANGEID": "31810106",
        "NUMBER": "1",
        "OPERTYPEID": "10",
        "PREVID": "0",
        "NEXTID": "0",
        "UPDATEDATE": "2019-07-09",
        "STARTDATE": "2019-07-09",
        "ENDDATE": "2079-06-06",
        "ISACTUAL": "1",
        "ISACTIVE": "1"
      }
    }
  },
  {
    "CARPLACE": {
      "@attributes": {
        "ID": "11361653",
        "OBJECTID": "20326793",
        "OBJECTGUID": "11d9f79b-be6f-43dc-bdcc-70bbfc9f86b0",
        "CHANGEID": "31822630",
        "NUMBER": "1",
        "OPERTYPEID": "10",
        "PREVID": "0",
        "NEXTID": "0",
        "UPDATEDATE": "2019-07-30",
        "STARTDATE": "2019-07-30",
        "ENDDATE": "2079-06-06",
        "ISACTUAL": "1",
        "ISACTIVE": "1"
      }
    }
  },
  {
    "CARPLACE": {
      "@attributes": {
        "ID": "94824",
        "OBJECTID": "101032823",
        "OBJECTGUID": "4f37e0eb-141f-4c19-b416-0ec85e2e9e76",
        "CHANGEID": "192339336",
        "NUMBER": "0",
        "OPERTYPEID": "10",
        "PREVID": "0",
        "NEXTID": "0",
        "UPDATEDATE": "2021-04-22",
        "STARTDATE": "2021-04-22",
        "ENDDATE": "2079-06-06",
        "ISACTUAL": "1",
        "ISACTIVE": "1"
      }
    }
  }
]
```

### XML-document as array

XmlConverter implements array approach.

XmlConverter can use to convert XML-document to array, example:

```php
$xml = <<<XML
<elemWithNestedElems>
    <elemWithVal>val</elemWithVal>
    <elemWithAttribs one="atrib" other="atrib"/>
    <elemWithAll attribute_name="attribute_value">element value</elemWithAll>
</elemWithNestedElems>
XML;

$converter = new \SbWereWolf\XmlNavigator\Convertation\XmlConverter(
    \SbWereWolf\XmlNavigator\General\Notation::VAL,
    \SbWereWolf\XmlNavigator\General\Notation::ATTR,
);
$xmlAsArray =
    $converter->toPrettyPrint($xml);

$prettyPrint = json_encode($xmlAsArray, JSON_PRETTY_PRINT);
echo 'JSON representation of XML:'
    . PHP_EOL
    . $prettyPrint
    . PHP_EOL;

echo 'Array representation of XML:'
    . PHP_EOL
    . var_export($xmlAsArray, true)
    . PHP_EOL;
```

OUTPUT:

```php
JSON representation of XML:
{
    "elemWithNestedElems": {
        "elemWithVal": "val",
        "elemWithAttribs": {
            "@attributes": {
                "one": "atrib",
                "other": "atrib"
            }
        },
        "elemWithAll": {
            "@value": "element value",
            "@attributes": {
                "attribute_name": "attribute_value"
            }
        }
    }
}
Array representation of XML:
array (
  'elemWithNestedElems' => 
  array (
    'elemWithVal' => 'val',
    'elemWithAttribs' => 
    array (
      '@attributes' => 
      array (
        'one' => 'atrib',
        'other' => 'atrib',
      ),
    ),
    'elemWithAll' => 
    array (
      '@value' => 'element value',
      '@attributes' => 
      array (
        'attribute_name' => 'attribute_value',
      ),
    ),
  ),
)
```

### XML-document as object

XmlElement implements object-oriented approach.

#### Navigator API

- `name(): string` // Returns the name of XML element
- `hasValue(): bool` // Returns `true` if XML element has value
- `value(): string` // Returns the value of XML element
- `hasAttribute(string $name = ''): bool` // Returns `true` if XML
  element has attribute with `$name`. If `$name` omitted, than returns
  `true` if XML element has any attribute
- `get(string $name = null): string` // Get value of attribute with
  the `$name`, if `$name` is omitted, than returns value of random
  attribute
- `attributes(): XmlAttribute[]` // Returns all attributes of XML
  element
- `hasElement(?string $name = null): bool` // Returns `true` if XML
  element has nested element with `$name`. If `$name` omitted, than
  returns `true` if XML element has any nested element
- `pull(string $name = ''): Generator` // Pull nested elements as
  `IXmlElement`
  , if `$name` is defined, than pull elements only with the
  `$name`
- `elements(): IXmlElement[]` // Returns all nested elements
- `serialize(): array;` Generates a storable representation (`$data`)
  of a IXmlElement, use `new XmlElement($data)` to restore
  `XmlElement` object

### Interact with XML as object

```php
$xml = <<<XML
<doc attrib="a" option="o" >
    <base/>
    <valuable>element value</valuable>
    <complex>
        <a empty=""/>
        <b val="x"/>
        <b val="y"/>
        <b val="z"/>
        <c>0</c>
        <c v="o"/>
        <c/>
        <different/>
    </complex>
</doc>
XML;

$content = \SbWereWolf\XmlNavigator\Convertation\FastXmlToArray
::convert($xml);
$navigator = 
new \SbWereWolf\XmlNavigator\Navigation\XmlElement($content);

/* Convert this XmlElement to array,
 with the array you may restore XmlElement
 (create same as original one) */
$gist = $navigator->serialize();
echo assert($content === $gist) ? 'is same' : 'is different';
echo PHP_EOL;

/* get name of element */
echo $navigator->name() . PHP_EOL;
/* doc */

/* get value of element */
echo "`{$navigator->value()}`" . PHP_EOL;
/* `` */

/* get list of attributes */
$attributes = $navigator->attributes();
foreach ($attributes as $attribute) {
    /** @var \SbWereWolf\XmlNavigator\Navigation\IXmlAttribute $attribute */
    echo "`{$attribute->name()}` `{$attribute->value()}`" . PHP_EOL;
}
/*
`attrib` `a`
`option` `o`
*/

/* get value of attribute */
echo $navigator->get('attrib') . PHP_EOL;
/* a */

/* get list of nested elements */
$elements = $navigator->elements();
foreach ($elements as $element) {
    echo "{$element->name()}" . PHP_EOL;
}
/*
base
valuable
complex
 */

/* get desired nested element */
/** @var \SbWereWolf\XmlNavigator\Navigation\IXmlElement $elem */
$elem = $navigator->pull('valuable')->current();
echo $elem->name() . PHP_EOL;
/* valuable */

/* get all nested elements */
foreach ($navigator->pull() as $pulled) {
    /** @var \SbWereWolf\XmlNavigator\Navigation\IXmlElement $pulled */
    echo $pulled->name() . PHP_EOL;
    /*
    base
    valuable
    complex
    */
}

/* get nested element with given name */
/** @var \SbWereWolf\XmlNavigator\Navigation\IXmlElement $nested */
$nested = $navigator->pull('complex')->current();
/* get names of all elements of nested element */
$elements = $nested->elements();
foreach ($elements as $element) {
    echo "{$element->name()}" . PHP_EOL;
}
/*
a
b
b
b
c
c
c
different
*/

/* pull all elements with name `b` */
foreach ($nested->pull('b') as $b) {
    /** @var \SbWereWolf\XmlNavigator\Navigation\IXmlElement $b */
    echo ' element with name' .
        ' `' . $b->name() .
        '` have attribute `val` with value' .
        ' `' . $b->get('val') . '`' .
        PHP_EOL;
}
/*
 element with name `b` have attribute `val` with value `x`
 element with name `b` have attribute `val` with value `y`
 element with name `b` have attribute `val` with value `z`
*/
```

## Advanced using

[Unit tests](test/Integration/DebugTest.php) have more examples of
using, please investigate them.

## Run tests

```bash 
composer test
```

## Contacts

```
Volkhin Nikolay
e-mail ulfnew@gmail.com
phone +7-902-272-65-35
Telegram @sbwerewolf
```

- [Telegram chat with me](https://t.me/SbWereWolf)
- [WhatsApp chat with me](https://wa.me/79022726535) 