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

## How to use

- Create exemplar of NavigatorFabric
- with using setXml(string $xml) or using
  setSimpleXmlElement(SimpleXMLElement $xml) pass xml to Fabric
- Create Converter with makeConverter() method and with using
  toArray() method, obtain representation of xml document as array
- Or create Navigator with makeNavigator() method and using Navigator
API, perform needed actions
 
## Navigator API
- `name(): string;` // get xml element name
- `hasValue(): string;` // true if xml element has value
- `value(): string;` // the value of xml element
- `hasAttribs(): bool;` // true if xml element has attributes
- `attribs(): array;` // get all attributes of xml element
- `get(string $name = null): string;` // get value of attribute $name
- `hasElements(): bool;` // true if xml element has nested elements
- `elements(): array;` // get all elements of xml element
- `pull(string $name): IXmlNavigator;` // get Navigator for nested
element
- `isMultiple(): bool;` // true if xml element is multiple
- `next();` // get next one of multiple nested xml element

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
/* array representation is
complex][*elements][a]
complex][*elements][a][*attributes]
complex][*elements][a][*attributes][empty]
complex][*elements][different]
complex][*elements][b]
complex][*elements][b][*multiple]
complex][*elements][b][*multiple][0]
complex][*elements][b][*multiple][0][*attributes]
complex][*elements][b][*multiple][0][*attributes][val]=>x
complex][*elements][b][*multiple][1][*attributes]
complex][*elements][b][*multiple][1][*attributes][val]=y
complex][*elements][b][*multiple][2][*attributes]
complex][*elements][b][*multiple][2][*attributes][val]=z
complex][*elements][c][*multiple]
complex][*elements][c][*multiple][0]
complex][*elements][c][*multiple][0][*value]=0
complex][*elements][c][*multiple][1]
complex][*elements][c][*multiple][1][*attributes]
complex][*elements][c][*multiple][1][*attributes][v]=o
complex][*elements][c][*multiple][2]
 * */

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
              'c' =>
              array (
                '*multiple' =>
                array (
                  0 =>
                  array (
                    '*value' => '0',
                  ),
                  1 =>
                  array (
                    '*attributes' =>
                    array (
                      'v' => 'o',
                    ),
                  ),
                  2 =>
                  array (
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

### XML-document as object

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

### Known bugs

Working with SimpleXMLElement you may have some discomfort when you
process with XML document having different namespaces

In this case you may try something like this:

```php
        $content = <<<XML
<QueryResult xmlns=
"urn://x-artefacts-smev-gov-ru/services/service-adapter/types">
    <Message
        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:type="RequestMessageType">
        <RequestContent>
            <content>
                <MessagePrimaryContent>
                    <rprn:Query xmlns:rprn=
                    "urn://rpn.gov.ru/services/smev/cites/1.0.0">
                        <rprn:Search>
                            <rprn:SearchNumber Number="00AA000000"/>
                        </rprn:Search>
                    </rprn:Query>
                </MessagePrimaryContent>
            </content>
        </RequestContent>
    </Message>
</QueryResult>
XML;

$xml = simplexml_load_string($content);
$xml = $xml
    ->Message
    ->RequestContent
    ->content
    ->MessagePrimaryContent;
$xml = $xml
    ->children(
        'urn://rpn.gov.ru/services/smev/cites/1.0.0'
    );

$gotIt = false;
foreach ($xml->getNamespaces() as $prefix => $namespace) {
    if ($namespace === 'urn://rpn.gov.ru/services/smev/cites/1.0.0') {
        $gotIt = true;
        break;
    }
}

$arrayRepresentationOfXml = [];
if ($gotIt) {
    $nodeText = $xml->saveXML();
    $nodeText = str_replace($prefix . ':', '', $nodeText);
    $pureXml = simplexml_load_string($nodeText);

    $fabric = (new NavigatorFabric())->setXml($pureXml);
    $converter = $fabric->makeConverter();    
    $arrayRepresentationOfXml = $converter->toArray();
}
```

# Contacts

```
Volkhin Nikolay
e-mail ulfnew@gmail.com
phone +7-902-272-65-35
Telegram @sbwerewolf
```

[Telegram chat with me](https://t.me/SbWereWolf) 