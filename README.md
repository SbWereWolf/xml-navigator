# Xml Navigator

The PHP library Xml Navigator base on XMLReader.

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
    \SbWereWolf\XmlNavigator\FastXmlToArray::prettyPrint($xml);
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

Let generate XML files by script:

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

Let run benchmark by script:

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

    $elementsCollection =
        SbWereWolf\XmlNavigator\FastXmlToArray::extractElements(
            $reader,
            SbWereWolf\XmlNavigator\FastXmlToArray::VAL,
            SbWereWolf\XmlNavigator\FastXmlToArray::ATTR,
        );
    $result =
        SbWereWolf\XmlNavigator\FastXmlToArray
            ::composePrettyPrintByXmlElements(
                $elementsCollection,
            );

    $finish = hrtime(true);
    $duration = $finish - $start;
    $duration = number_format($duration,);
    echo 'First element parsing duration of' .
        " $filename is $duration ns" .
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
First element parsing duration of temp-465b.xml is 1,291,100 ns
Benchmark is starting
First element parsing duration of temp-465b.xml is 156,600 ns
First element parsing duration of temp-429Kb.xml is 133,700 ns
First element parsing duration of temp-429Mb.xml is 122,100 ns
Benchmark was finished
```

### XML-document as array

Converter implements array approach.

Converter can use to convert XML-document to array, example:

```php
$xml = <<<XML
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
XML;

$converter = new \SbWereWolf\XmlNavigator\Converter(
    \SbWereWolf\XmlNavigator\IFastXmlToArray::VAL,
    \SbWereWolf\XmlNavigator\IFastXmlToArray::ATTR,
);
$arrayRepresentationOfXml =
    $converter->prettyPrint($xml);
echo var_export($arrayRepresentationOfXml,true);
```

OUTPUT:

```php
array (
    'complex' =>
        array (
            'a' =>
                array (
                    '@attributes' =>
                        array (
                            'empty' => '',
                        ),
                ),
            'b' =>
                array (
                    0 =>
                        array (
                            '@attributes' =>
                                array (
                                    'val' => 'x',
                                ),
                        ),
                    1 =>
                        array (
                            '@attributes' =>
                                array (
                                    'val' => 'y',
                                ),
                        ),
                    2 =>
                        array (
                            '@attributes' =>
                                array (
                                    'val' => 'z',
                                ),
                        ),
                ),
            'c' =>
                array (
                    0 =>
                        array (
                            '@value' => '0',
                        ),
                    1 =>
                        array (
                            '@attributes' =>
                                array (
                                    'v' => 'o',
                                ),
                        ),
                    2 =>
                        array (
                        ),
                ),
            'different' =>
                array (
                ),
        ),
);
```

### XML-document as object

XmlNavigator implements object-oriented approach.

#### Navigator API

- `name(): string` // Returns the name of XML element
- `hasValue(): bool` // Returns `true` if XML element has value
- `value(): string` // Returns the value of XML element
- `hasAttribute(string $name = ''): bool` // Returns `true` if XML
  element has attribute with `$name`. If `$name` omitted, than returns
  `true` if XML element has any attribute
- `attributes(): XmlAttribute[]` // Returns all attributes of XML
  element
- `get(string $name = null): string` // Get value of attribute with
  the `$name`, if `$name` is omitted, than returns value of random
  attribute
- `hasElement(string $name = ''): bool` // Returns `true` if XML
  element has nested element with `$name`. If `$name` omitted, than
  returns `true` if XML element has any nested element
- `elements(): IXmlElement[]` // Returns all nested elements
- `pull(string $name = ''): Generator` // Pull `IXmlElement` for
  nested element, if `$name` is defined, than pull elements with the
  `$name`

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

$converter = new \SbWereWolf\XmlNavigator\Converter();
$content = $converter->xmlStructure($xml);
$navigator = new SbWereWolf\XmlNavigator\XmlElement($content);

/* get name of element */
echo $navigator->name() . PHP_EOL;
/* doc */

/* get value of element */
echo "`{$navigator->value()}`" . PHP_EOL;
/* `` */

/* get list of attributes */
$attributes = $navigator->attributes();
foreach ($attributes as $attribute) {
    /** @var \SbWereWolf\XmlNavigator\IXmlAttribute $attribute */
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
/** @var SbWereWolf\XmlNavigator\IXmlElement $elem */
$elem = $navigator->pull('valuable')->current();
echo $elem->name() . PHP_EOL;
/* valuable */

/* get all nested elements */
foreach ($navigator->pull() as $pulled) {
    /** @var SbWereWolf\XmlNavigator\IXmlElement $pulled */
    echo $pulled->name() . PHP_EOL;
    /*
    base
    valuable
    complex
    */
}

/* get nested element with given name */
/** @var SbWereWolf\XmlNavigator\IXmlElement $nested */
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
    /** @var SbWereWolf\XmlNavigator\IXmlElement $b */
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

## Contacts

```
Volkhin Nikolay
e-mail ulfnew@gmail.com
phone +7-902-272-65-35
Telegram @sbwerewolf
```

[Telegram chat with me](https://t.me/SbWereWolf)
[WhatsApp chat with me](https://wa.me/79022726535) 