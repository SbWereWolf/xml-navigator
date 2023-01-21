# Xml Navigator

Xml Navigator base on XMLReader.

You can assign XML as string or as URI ( or file system path).

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

        $converter = new \SbWereWolf\XmlNavigator\Converter();
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
      'a' => 
      array (
        'empty' => '',
      ),
    ),
    'b' => 
    array (
      0 => 
      array (
        'a' => 
        array (
          'val' => 'x',
        ),
      ),
      1 => 
      array (
        'a' => 
        array (
          'val' => 'y',
        ),
      ),
      2 => 
      array (
        'a' => 
        array (
          'val' => 'z',
        ),
      ),
    ),
    'c' => 
    array (
      0 => 
      array (
        'v' => '0',
      ),
      1 => 
      array (
        'a' => 
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
)
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
use SbWereWolf\XmlNavigator\FastXmlToArray;
use SbWereWolf\XmlNavigator\IXmlElement;
use SbWereWolf\XmlNavigator\XmlElement;

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

$content = FastXmlToArray::convert($xml);
$navigator = new XmlElement($content);

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
/** @var IXmlElement $elem */
$elem = $navigator->pull('valuable')->current();
echo $elem->name() . PHP_EOL;
/* valuable */

/* get all nested elements */
foreach ($navigator->pull() as $pulled) {
    /** @var IXmlElement $pulled */
    echo $pulled->name() . PHP_EOL;
    /*
    base
    valuable
    complex
    */
}

/* get nested element with given name */
/** @var IXmlElement $nested */
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
)
*/

/* pull all elements with name `b` */
foreach ($nested->pull('b') as $b) {
    /** @var IXmlElement $b */
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
*/
```

# Advanced using

[Unit tests](test/Integration/DebugTest.php) have more examples of
using, please investigate them.

# Contacts

```
Volkhin Nikolay
e-mail ulfnew@gmail.com
phone +7-902-272-65-35
Telegram @sbwerewolf
```

[Telegram chat with me](https://t.me/SbWereWolf)
[WhatsApp chat with me](https://wa.me/79022726535) 