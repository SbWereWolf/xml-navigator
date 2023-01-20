<?php
/*
 * storage-for-all-things
 * Copyright © 2021 Volkhin Nikolay
 * 30.07.2021, 5:46
 */

declare(strict_types=1);

namespace Integration;

use PHPUnit\Framework\TestCase;
use SbWereWolf\XmlNavigator\Converter;
use SbWereWolf\XmlNavigator\FastXmlToArray;
use SbWereWolf\XmlNavigator\IXmlNavigator;
use SbWereWolf\XmlNavigator\XmlNavigator;

class DebugTest extends TestCase
{
    public function testXmlNavigator()
    {
        $xmlContent =
            array(
                'elems' =>
                    array(
                        0 =>
                            array(
                                'name' => 'complex',
                                'attribs' =>
                                    array(
                                        'str' => 'text',
                                        'number' => '-3.9',
                                    ),
                                'elems' =>
                                    array(
                                        0 =>
                                            array(
                                                'name' => 'empty',
                                                'elems' =>
                                                    array(),
                                            ),
                                        1 =>
                                            array(
                                                'name' => 'ONLY_VALUE',
                                                'val' =>
                                                    'element has only' .
                                                    ' value',
                                                'elems' =>
                                                    array(),
                                            ),
                                        2 =>
                                            array(
                                                'name' => 'a',
                                                'attribs' =>
                                                    array(
                                                        'element_has' .
                                                        '_empty_' .
                                                        'attribute' =>
                                                            '',
                                                    ),
                                                'elems' =>
                                                    array(),
                                            ),
                                        3 =>
                                            array(
                                                'name' => 'b',
                                                'attribs' =>
                                                    array(
                                                        'val' => 'x',
                                                        'attr' => '-3',
                                                    ),
                                                'elems' =>
                                                    array(),
                                            ),
                                        4 =>
                                            array(
                                                'name' => 'b',
                                                'attribs' =>
                                                    array(
                                                        'val' => 'y',
                                                        'y' => 'val',
                                                    ),
                                                'elems' =>
                                                    array(),
                                            ),
                                        5 =>
                                            array(
                                                'name' => 'b',
                                                'attribs' =>
                                                    array(
                                                        'val' => 'z',
                                                    ),
                                                'elems' =>
                                                    array(),
                                            ),
                                        6 =>
                                            array(
                                                'name' => 'c',
                                                'val' => '0',
                                                'elems' =>
                                                    array(),
                                            ),
                                        7 =>
                                            array(
                                                'name' => 'c',
                                                'attribs' =>
                                                    array(
                                                        'a' => 'v',
                                                    ),
                                                'elems' =>
                                                    array(),
                                            ),
                                        8 =>
                                            array(
                                                'name' => 'c',
                                                'elems' =>
                                                    array(),
                                            ),
                                        9 =>
                                            array(
                                                'name' => 'nested',
                                                'elems' =>
                                                    array(
                                                        0 => array(
                                                            'name' => 'any',
                                                            'attribs' =>
                                                                array(
                                                                    'val' => '1',
                                                                ),
                                                            'elems' =>
                                                                array(),
                                                        ),
                                                        1 => array(
                                                            'name' => 'any',
                                                            'attribs' =>
                                                                array(
                                                                    'val' => '2',
                                                                ),
                                                            'elems' =>
                                                                array(),
                                                        ),
                                                    ),
                                            ),
                                    ),
                            ),
                    ),
            );
        $navigator = new XmlNavigator($xmlContent['elems'][0]);

        /* get element name */
        echo $navigator->name() . PHP_EOL;
        /* complex */

        /* get element value */
        echo $navigator->value() . PHP_EOL;
        /* '' */

        /* get list of attributes */
        echo var_export($navigator->attributes(), true) . PHP_EOL;
        /*
        array (
          0 => 'str',
          1 => 'number',
        )
        */

        /* get attribute value */
        echo $navigator->get('str') . PHP_EOL;
        /* text */

        /* get list of nested elements */
        echo var_export($navigator->elements(), true) . PHP_EOL;
        /*
        array (
          0 => 'empty',
          1 => 'ONLY_VALUE',
          2 => 'a',
          3 => 'b',
          4 => 'b',
          5 => 'b',
          6 => 'c',
          7 => 'c',
          8 => 'c',
          9 => 'nested',
        )
        */

        /* get desired nested element */
        $elem = $navigator->pull()->current();
        echo $elem->name() . PHP_EOL;
        /* empty */

        /* get all nested elements */
        foreach ($navigator->pull() as $pulled) {
            /** @var IXmlNavigator $pulled */
            echo $pulled->name() . PHP_EOL;
            /*
            empty
            ONLY_VALUE
            a
            b
            b
            b
            c
            c
            c
            nested
            */
        }

        /* get nested element */
        /** @var IXmlNavigator $nested */
        $nested = $navigator->pull('nested')->current();
        /* get names of all elements of nested element */
        echo var_export($nested->elements(), true) . PHP_EOL;
        /*
        array (
          0 => 'any',
          1 => 'any',
        )
        */

        /* get all elements with name `any` */
        foreach ($nested->pull('any') as $any) {
            /** @var IXmlNavigator $any */
            echo ' element with name' .
                ' `' . $any->name() .
                '` have attribute `val` with value' .
                ' `' . $any->get('val') . '`' .
                PHP_EOL;
            /*
            element with name `any` have attribute `val` with value `1`
            element with name `any` have attribute `val` with value `2`
            */
        }

        $this->assertTrue(true);
    }

    public function testConverterXmlStructure()
    {
        $xml = <<<XML
<complex>
    <empty/>
    <ONLY_VALUE>element has only value</ONLY_VALUE>
    <a element_has_empty_attribute=""/>
    <b val="x" attr="-3"/>
    <b val="y" y="val"/>
    <b val="z"/>
    <c>0</c>
    <c a="v"/>
    <c/>
</complex>
XML;

        $arrayRepresentationOfXml =
            (new Converter())->xmlStructure($xml);
        var_export($arrayRepresentationOfXml);
        /*

        */

        $this->assertTrue(true);
    }

    public function testConverterPrettyPrint()
    {
        $xml = <<<XML
<complex>
    <empty/>
    <ONLY_VALUE>element has only value</ONLY_VALUE>
    <a element_has_empty_attribute=""/>
    <b val="x" attr="-3"/>
    <b val="y" y="val"/>
    <b val="z"/>
    <c>0</c>
    <c a="v"/>
    <c/>
</complex>
XML;

        $arrayRepresentationOfXml =
            (new Converter())->prettyPrint($xml);
        var_export($arrayRepresentationOfXml);
        /*

        */

        $this->assertTrue(true);
    }

    public function testFastXmlToArrayConvert()
    {
        $xml = <<<XML
<complex>
    <empty/>
    <ONLY_VALUE>element has only value</ONLY_VALUE>
    <a element_has_empty_attribute=""/>
    <b val="x" attr="-3"/>
    <b val="y" y="val"/>
    <b val="z"/>
    <c>0</c>
    <c a="v"/>
    <c/>
</complex>
XML;

        $result = FastXmlToArray::convert($xml);
        var_export($result);

        $this->assertTrue(true);
    }

    public function testFastXmlToArrayPrettyPrint()
    {
        $xml = <<<XML
<complex>
    <empty/>
    <ONLY_VALUE>element has only value</ONLY_VALUE>
    <a element_has_empty_attribute=""/>
    <b val="x" attr="-3"/>
    <b val="y" y="val"/>
    <b val="z"/>
    <c>0</c>
    <c a="v"/>
    <c/>
</complex>
XML;

        $result = FastXmlToArray::prettyPrint($xml);
        var_export($result);

        $this->assertTrue(true);
    }
}
