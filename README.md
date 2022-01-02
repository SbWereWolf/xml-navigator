# Xml Navigator

Xml Navigator is library for navigation through xml.

With the Navigator you are able to get xml-element name, it
attributes, nested elements, and nested elements that occur multiple
times.

## How To Install

`composer require sbwerewolf/xml-navigator`

## About Xml Navigator

Xml Navigator base on SimpleXMLElement.

Navigator can provide XML-document as array or as object.

## XML-document as array

Converter implements array approach.

Converter can use to convert XML-document to array, example:
```php
        $xml = <<<XML
<complex>
    <b val="x"/>
    <b val="y"/>
    <b val="z"/>
</complex>
XML;

        $fabric = (new NavigatorFabric())->setXml($xml);
        $converter = $fabric->makeConverter();
        
        $arrayRepresentationOfXml = $converter->toArray();
        echo var_export($arrayRepresentationOfXml, true);
        /*
        array (
          'complex' =>
          array (
            '*elements' =>
            array (
              'a' =>
              array (
                '*attributes' =>
                array (
                  'empty' => '',
                ),
              ),
              'different' =>
              array (
              ),
              'b' =>
              array (
                '*multiple' =>
                array (
                  0 =>
                  array (
                    '*attributes' =>
                    array (
                      'val' => 'x',
                    ),
                  ),
                  1 =>
                  array (
                    '*attributes' =>
                    array (
                      'val' => 'y',
                    ),
                  ),
                  2 =>
                  array (
                    '*attributes' =>
                    array (
                      'val' => 'z',
                    ),
                  ),
                ),
              ),              
            ),
          ),
        )
        */
```
You should use constants of interface
`\SbWereWolf\XmlNavigator\IConverter` for access to value, attributes,
elements.
```php
    public const VALUE = '*value';
    public const ATTRIBUTES = '*attributes';
    public const ELEMENTS = '*elements';
    public const MULTIPLE = '*multiple';
```

## XML-document as object

XmlNavigator implements object-oriented approach.

```php
        $xml = <<<XML
<doc attrib="a" option="o" >666
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

        $xmlObj = new SimpleXMLElement($xml);
        $fabric = (new NavigatorFabric())
            ->setSimpleXmlElement($xmlObj);
        $navigator = $fabric->makeNavigator();

        /* get element name */
        echo $navigator->name();
        /* doc */

        /* get element value */
        echo $navigator->value();
        /* 666 */

        /* get list of attributes */
        echo var_export($navigator->attribs(), true);
        /*
        array (
          0 => 'attrib',
          1 => 'option',
        )
        */

        /* get attribute value */
        echo $navigator->get('attrib');
        /* a */

        /* get list of nested elements */
        echo var_export($navigator->elements(), true);
        /*
        array (
          0 => 'base',
          1 => 'valuable',
          2 => 'complex',
        )
        */

        /* get nested element */
        $nested = $navigator->pull('complex');

        echo $nested->name();
        /* complex */

        echo var_export($nested->elements(), true);
        /*
        array (
          0 => 'a',
          1 => 'different',
          2 => 'b',
          3 => 'c',
        )
        */

        /* get nested elements of nested element */
        $multiple = $navigator->pull('complex')->pull('b');
        
        /* get nested elements that occur multiple times */
        foreach ($multiple->next() as $index => $instance) {
            echo " {$instance->name()}[$index]" .
                " => {$instance->get('val')};";
        }
        /*
        b[0] => x; b[1] => y; b[2] => z;
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