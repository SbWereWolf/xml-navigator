<?php
/*
 * storage-for-all-things
 * Copyright © 2021 Volkhin Nikolay
 * 30.07.2021, 5:46
 */

namespace Integration;

use PHPUnit\Framework\TestCase;
use SbWereWolf\XmlNavigator\Converter;
use SbWereWolf\XmlNavigator\NavigatorFabric;
use SimpleXMLElement;

class DebugTest extends TestCase
{
    public function testXmlNavigator()
    {
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

        $fabric = new NavigatorFabric($xml);
        $navigator = $fabric->make();

        /* get element name */
        echo $navigator->name();
        /* doc */

        /* get element value */
        echo $navigator->value();
        /* 666 */

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

        echo $nested->name();
        /* b */

        echo $nested->value();
        /* 0000 */

        /* get list of element attributes */
        echo var_export($nested->attribs(), true);
        /*
        array (
            0 => 'attr4',
        )
        */

        /* get attribute value */
        echo $nested->get('attr4');
        /* 55 */

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

        $this->assertTrue(true);
    }

    public function testConverter()
    {
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
        echo var_export($arrayRepresentationOfXml, true);

        $this->assertTrue(true);
    }
}
