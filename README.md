# Xml Navigator

Xml Navigator is library for navigation through xml.

With Navigator you able to get xml-element name, it attributes, nested
elements, and nested elements that occur multiple times.

## How To Install

`composer require sbwerewolf/xml-navigator`

## About Xml Navigator

Xml Navigator base on SimpleXMLElement.

Navigator can provide XML-document as array or as object.

## XML-document as array

Converter implements array approach.

Converter can use to convert XML-document to array, explanation with
code:
```php

        $xml = <<<XML
<doc>666
    <a attr1="22">
        <a2 attr3="aaa"/>
    </a>
    <b attr4="55">
        <c>ccc
            <d/>
        </c>
        0000
    </b>
    <t/>
    <t/>
    <qwe>first occurrence</qwe>
    <qwe>second occurrence</qwe>
    <qwe>last occurrence</qwe>
</doc>
XML;

        $xmlObj = new SimpleXMLElement($xml);
        $converter = new Converter($xmlObj);
        $arrayRepresentationOfXml = $converter->toArray();
        echo var_export($arrayRepresentationOfXml,true);
/*
array (
  'doc' =>
  array (
    '*value' => '666',
    '*elements' =>
    array (
      'a' =>
      array (
        '*attributes' =>
        array (
          'attr1' => '22',
        ),
        '*elements' =>
        array (
          'a2' =>
          array (
            '*attributes' =>
            array (
              'attr3' => 'aaa',
            ),
          ),
        ),
      ),
      'b' =>
      array (
        '*value' => '0000',
        '*attributes' =>
        array (
          'attr4' => '55',
        ),
        '*elements' =>
        array (
          'c' =>
          array (
            '*value' => 'ccc',
            '*elements' =>
            array (
              'd' =>
              array (
              ),
            ),
          ),
        ),
      ),
      't' =>
      array (
        '*multiple' =>
        array (
          0 =>
          array (
          ),
          1 =>
          array (
          ),
        ),
      ),
      'qwe' =>
      array (
        '*multiple' =>
        array (
          0 =>
          array (
            '*value' => 'first occurrence',
          ),
          1 =>
          array (
            '*value' => 'second occurrence',
          ),
          2 =>
          array (
            '*value' => 'last occurrence',
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
<doc>666
    <a attr1="22">
        <a2 attr3="aaa"/>
    </a>
    <b attr4="55">
        <c>ccc
            <d/>
        </c>
        0000
    </b>
    <t/>
    <t/>
    <qwe>first occurrence</qwe>
    <qwe>second occurrence</qwe>
    <qwe>last occurrence</qwe>
</doc>
XML;
        $fabric = new BrowserFabric($xml);
        $navigator = $fabric->make();

        /* get element name */
        echo $navigator->name(); /* doc */
        /* get element value */
        echo $navigator->value(); /* 666 */
        /* get list of nested elements */
        echo var_export($navigator->elements(), true);
        /*
        array (
            0 => 'a',
            1 => 'b',
            2 => 't',
            3 => 'qwe',
        )
        */

        /* get nested element */
        $nested = $navigator->pull('b');
        echo $nested->name(); /* b */
        echo $nested->value(); /* 0000 */
        /* get list of element attributes */
        echo var_export($nested->attribs(), true);
        /*
        array (
            0 => 'attr4',
        )
        */
        /* get attribute value */
        echo $nested->get('attr4'); /* 55 */
        echo var_export($nested->elements(), true);
        /*
        array (
            0 => 'c',
        )
        */

        /* get nested  elements that occur multiple times */
        $multiple = $navigator->pull('qwe');
        if ($multiple->isMultiple()) {
            foreach ($multiple->next() as $index => $instance) {
                echo "{$instance->name()}[$index]" .
                    " => {$instance->value()}" .
                    PHP_EOL;
            }
        }
        /*
        qwe[0] => first occurrence
        qwe[1] => second occurrence
        qwe[2] => last occurrence
        */
```

# Контакты

```
Вольхин Николай
e-mail ulfnew@gmail.com
phone +7-902-272-65-35
Telegram @sbwerewolf
```

[Telegram chat with me](https://t.me/SbWereWolf) 