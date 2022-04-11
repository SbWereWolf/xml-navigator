<?php
/*
 * storage-for-all-things
 * Copyright © 2021 Volkhin Nikolay
 * 30.07.2021, 5:46
 */

declare(strict_types=1);

namespace Integration;

use PHPUnit\Framework\TestCase;
use SbWereWolf\XmlNavigator\NavigatorFabric;
use SimpleXMLElement;

class DebugTest extends TestCase
{
    public function testXmlNavigator()
    {
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

        $fabric = (new NavigatorFabric())->setXml($xml);
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

        $multiple = $navigator->pull('complex')->pull('b');
        /* get nested  elements that occur multiple times */
        foreach ($multiple->next() as $index => $instance) {
            echo " {$instance->name()}[$index]" .
                " => {$instance->get('val')};";
        }
        /*
        b[0] => x; b[1] => y; b[2] => z;
        */

        $this->assertTrue(true);
    }

    public function testConverter()
    {
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
            ->setSimpleXmlElement($xmlObj->complex);
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

        $this->assertTrue(true);
    }
}
