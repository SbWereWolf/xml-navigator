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

### Fast XML file processing with no worries of file size

Access time to first element do not depend on file size.

Let generate file with script and parse first element:

```php
$xml = '<SomeElement key="123">value</SomeElement>' . PHP_EOL;

$filename = 'temp-' . uniqid() . '.xml';
$file = fopen($filename, 'a');
fwrite($file, '<Collection>');

$limit = 1;
for ($i = 0; $i < $limit; $i++) {
    $content = "$xml$xml$xml$xml$xml$xml$xml$xml$xml$xml";
    fwrite($file, $content);
}

fwrite($file, '</Collection>');
fclose($file);

$size = round(filesize($filename) / 1024, 2);
echo "$filename size is $size Kb" . PHP_EOL;

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
    SbWereWolf\XmlNavigator\FastXmlToArray::composePrettyPrintByXmlElements(
        $elementsCollection,
    );

$finish = hrtime(true);
$duration = $finish - $start;
$duration = number_format($duration,);
echo "First element parsing duration is $duration ns" . PHP_EOL;
echo json_encode($result, JSON_PRETTY_PRINT) . PHP_EOL;

$reader->close();
```

Run script with `$limit` values of 1, 1 000, 1 000 000, output will be

```bash
temp-63d5a9d9273aa.xml size is 0.45 Kb
First element parsing duration is 2,291,700 ns
{
    "SomeElement": {
        "@value": "value",
        "@attributes": {
            "key": "123"
        }
    }
}
temp-63d5a9d927ede.xml size is 429.71 Kb
First element parsing duration is 11,159,800 ns
{
    "SomeElement": {
        "@value": "value",
        "@attributes": {
            "key": "123"
        }
    }
}
temp-63d5a9d92c676.xml size is 429687.52 Kb
First element parsing duration is 4,621,500 ns
{
    "SomeElement": {
        "@value": "value",
        "@attributes": {
            "key": "123"
        }
    }
}
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