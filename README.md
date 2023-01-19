## About Xml Navigator

Xml Navigator base on XMLReader.

Navigator can provide XML-document as array or as object.

## How To Install

`composer require sbwerewolf/xml-navigator`

## How to use

```php
$xml = '<outer any="123"><inner1>some text</inner1></outer>';
$result = (new \SbWereWolf\XmlNavigator\NavigatorFabric())
    ->makeFromXmlString($xml)
    ->makeConverter()
    ->toNormalizedArray();
echo json_encode($result, JSON_PRETTY_PRINT);
```

Output:

```json
{
  "elems": [
    {
      "name": "outer",
      "attribs": {
        "any": "123"
      },
      "elems": [
        {
          "name": "inner1",
          "val": "some text",
          "elems": []
        }
      ]
    }
  ]
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
- `public function pull(string $name = ''): Generator` // Pull
  IXmlNavigator for nested element, if `$name` is defined, than pull
  elements with the `$name`

## Use cases

### XML-document as array

Converter implements array approach.

You should use constants of interface
`\SbWereWolf\XmlNavigator\IConverter` for access to value, attributes,
elements.

```php
    public const NAME = 'name';
    public const VAL = 'val';
    public const ATTRIBS = 'attribs';
    public const ELEMS = 'elems';
```

Converter can use to convert XML-document to array, example:

```php
use SbWereWolf\XmlNavigator\IXmlNavigator;
use SbWereWolf\XmlNavigator\NavigatorFabric;

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
/* array representation will be
['elems'][0]['name'] => 'complex'

['elems'][0]['elems'][0]['name'] => 'a'
['elems'][0]['elems'][0]['attribs'] => ['empty' => '']
['elems'][0]['elems'][0]['elems'] => []

['elems'][0]['elems'][1]['name'] => 'b'
['elems'][0]['elems'][1]['attribs'] => ['val' => 'x']
['elems'][0]['elems'][1]['elems'] => []

['elems'][0]['elems'][2]['name'] => 'b'
['elems'][0]['elems'][2]['attribs'] => ['val' => 'y']
['elems'][0]['elems'][2]['elems'] => []

['elems'][0]['elems'][3]['name'] => 'b'
['elems'][0]['elems'][3]['attribs'] => ['val' => 'z']
['elems'][0]['elems'][3]['elems'] => []

['elems'][0]['elems'][4]['name'] => 'c'
['elems'][0]['elems'][4]['val'] => 0
['elems'][0]['elems'][4]['elems'] => []

['elems'][0]['elems'][5]['name'] => 'c'
['elems'][0]['elems'][5]['attribs'] => ['v' => '0']
['elems'][0]['elems'][5]['elems'] => []

['elems'][0]['elems'][6]['name'] => 'c'
['elems'][0]['elems'][6]['elems'] => []

['elems'][0]['elems'][7]['name'] => 'different'
['elems'][0]['elems'][7]['elems'] => []

 * */

        $fabric = (new NavigatorFabric())->makeFromXmlString($xml);
        $converter = $fabric->makeConverter();
        
        $arrayRepresentationOfXml = $converter->toNormalizedArray();
        echo var_export($arrayRepresentationOfXml, true);

array (
  'elems' => 
  array (
    0 => 
    array (
      'name' => 'complex',
      'elems' => 
      array (
        0 => 
        array (
          'name' => 'a',
          'attribs' => 
          array (
            'empty' => '',
          ),
          'elems' => 
          array (
          ),
        ),
        1 => 
        array (
          'name' => 'b',
          'attribs' => 
          array (
            'val' => 'x',
          ),
          'elems' => 
          array (
          ),
        ),
        2 => 
        array (
          'name' => 'b',
          'attribs' => 
          array (
            'val' => 'y',
          ),
          'elems' => 
          array (
          ),
        ),
        3 => 
        array (
          'name' => 'b',
          'attribs' => 
          array (
            'val' => 'z',
          ),
          'elems' => 
          array (
          ),
        ),
        4 => 
        array (
          'name' => 'c',
          'val' => '0',
          'elems' => 
          array (
          ),
        ),
        5 => 
        array (
          'name' => 'c',
          'attribs' => 
          array (
            'v' => 'o',
          ),
          'elems' => 
          array (
          ),
        ),
        6 => 
        array (
          'name' => 'c',
          'elems' => 
          array (
          ),
        ),
        7 => 
        array (
          'name' => 'different',
          'elems' => 
          array (
          ),
        ),
      ),
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