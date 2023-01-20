## About Xml Navigator

Xml Navigator base on XMLReader.

Navigator can provide XML-document as array or as object.

## How To Install

`composer require sbwerewolf/xml-navigator`

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
Output:

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

## Navigator API

- `name(): string;` // Returns the name of XML element
- `hasValue(): bool;` // Returns true if XML element has value
- `value(): string;` // Returns the value of XML element
- `hasAttributes(): bool;` // Returns true if XML element has
  attributes
- `attributes(): array;` // Returns names of all attributes of xml
  element
- `get(string $name = null): string;` // Get value of attribute with
  the `$name`, if `$name` is not defined, than returns value of random
  attribute
- `hasElements(): bool;` // Returns true if XML element has nested
  elements
- `elements(): array;` // Returns names of all nested elements
- `pull(string $name = ''): Generator` // Pull IXmlNavigator for
  nested element, if `$name` is defined, than pull elements with the
  `$name`

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
        echo var_export($arrayRepresentationOfXml);

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
)
```

### XML-document as object

XmlNavigator implements object-oriented approach.

```php
use SbWereWolf\XmlNavigator\IXmlNavigator;
use SbWereWolf\XmlNavigator\NavigatorFabric;

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

        $fabric = (new NavigatorFabric())->makeFromXmlString($xml);
        $navigator = $fabric->makeNavigator();

        /* get element name */
        echo $navigator->name() . PHP_EOL;
        /* doc */

        /* get element value */
        echo $navigator->value() . PHP_EOL;
        /* '' */

        /* get list of attributes */
        echo var_export($navigator->attributes(), true) . PHP_EOL;
        /*
        array (
          0 => 'attrib',
          1 => 'option',
        )
        */

        /* get attribute value */
        echo $navigator->get('attrib') . PHP_EOL;
        /* a */

        /* get list of nested elements */
        echo var_export($navigator->elements(), true) . PHP_EOL;
        /*
        array (
          0 => 'base',
          1 => 'valuable',
          2 => 'complex',
        )
        */

        /* get desired nested element */
        /** @var IXmlNavigator $elem */
        $elem = $navigator->pull('valuable')->current();
        echo $elem->name() . PHP_EOL;
        /* valuable */

        /* get all nested elements */
        foreach ($navigator->pull() as $pulled) {
            /** @var IXmlNavigator $pulled */
            echo $pulled->name() . PHP_EOL;
            /* base */
            /* valuable */
            /* complex */
        }

        /* get nested element */
        /** @var IXmlNavigator $nested */
        $nested = $navigator->pull('complex')->current();
        /* get names of all elements of nested element */
        echo var_export($nested->elements(), true) . PHP_EOL;
        /*
        array (
          0 => 'a',
          1 => 'b',
          2 => 'b',
          3 => 'b',
          4 => 'c',
          5 => 'c',
          6 => 'c',
          7 => 'different',
        )
        */

        /* pull all elements with name `b` */
        foreach ($nested->pull('b') as $b) {
            /** @var IXmlNavigator $b */
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

# Contacts

```
Volkhin Nikolay
e-mail ulfnew@gmail.com
phone +7-902-272-65-35
Telegram @sbwerewolf
```

[Telegram chat with me](https://t.me/SbWereWolf)
[WhatsApp chat with me](https://wa.me/79022726535) 