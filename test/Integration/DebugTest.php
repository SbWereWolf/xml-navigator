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
use SbWereWolf\XmlNavigator\IXmlElement;
use SbWereWolf\XmlNavigator\XmlElement;

class DebugTest extends TestCase
{
    private const PRETTY_PRINT =
        array(
            'complex' =>
                array(
                    'empty' =>
                        array(),
                    'ONLY_VALUE' => 'element has only value',
                    'a' =>
                        array(
                            '@attributes' =>
                                array(
                                    'element_has_empty_attribute' => '',
                                ),
                        ),
                    'b' =>
                        array(
                            0 =>
                                array(
                                    '@attributes' =>
                                        array(
                                            'val' => 'x',
                                            'attr' => '-3',
                                        ),
                                ),
                            1 =>
                                array(
                                    '@attributes' =>
                                        array(
                                            'val' => 'y',
                                            'y' => 'val',
                                        ),
                                ),
                            2 =>
                                array(
                                    '@attributes' =>
                                        array(
                                            'val' => 'z',
                                        ),
                                ),
                        ),
                    'c' =>
                        array(
                            0 =>
                                array(
                                    '@value' => '0',
                                ),
                            1 =>
                                array(
                                    '@attributes' =>
                                        array(
                                            'a' => 'v',
                                        ),
                                ),
                            2 =>
                                array(),
                        ),
                ),
        );

    private const CONVERTER_PRETTY_PRINT =
        array(
            'complex' =>
                array(
                    'empty' =>
                        array(),
                    'ONLY_VALUE' => 'element has only value',
                    'a' =>
                        array(
                            'a' =>
                                array(
                                    'element_has_empty_attribute' => '',
                                ),
                        ),
                    'b' =>
                        array(
                            0 =>
                                array(
                                    'a' =>
                                        array(
                                            'val' => 'x',
                                            'attr' => '-3',
                                        ),
                                ),
                            1 =>
                                array(
                                    'a' =>
                                        array(
                                            'val' => 'y',
                                            'y' => 'val',
                                        ),
                                ),
                            2 =>
                                array(
                                    'a' =>
                                        array(
                                            'val' => 'z',
                                        ),
                                ),
                        ),
                    'c' =>
                        array(
                            0 =>
                                array(
                                    'v' => '0',
                                ),
                            1 =>
                                array(
                                    'a' =>
                                        array(
                                            'a' => 'v',
                                        ),
                                ),
                            2 =>
                                array(),
                        ),
                ),
        );

    private const XML_STRUCTURE =
        array(
            'n' => 'complex',
            's' =>
                array(
                    0 =>
                        array(
                            'n' => 'empty',
                        ),
                    1 =>
                        array(
                            'n' => 'ONLY_VALUE',
                            'v' => 'element has only value',
                        ),
                    2 =>
                        array(
                            'n' => 'a',
                            'a' =>
                                array(
                                    'element_has_empty_attribute' => '',
                                ),
                        ),
                    3 =>
                        array(
                            'n' => 'b',
                            'a' =>
                                array(
                                    'val' => 'x',
                                    'attr' => '-3',
                                ),
                        ),
                    4 =>
                        array(
                            'n' => 'b',
                            'a' =>
                                array(
                                    'val' => 'y',
                                    'y' => 'val',
                                ),
                        ),
                    5 =>
                        array(
                            'n' => 'b',
                            'a' =>
                                array(
                                    'val' => 'z',
                                ),
                        ),
                    6 =>
                        array(
                            'n' => 'c',
                            'v' => '0',
                        ),
                    7 =>
                        array(
                            'n' => 'c',
                            'a' =>
                                array(
                                    'a' => 'v',
                                ),
                        ),
                    8 =>
                        array(
                            'n' => 'c',
                        ),
                ),
        );

    public function testXmlNavigator()
    {
        $xmlContent =
            array(
                'n' => 'complex',
                'a' =>
                    array(
                        'str' => 'text',
                        'number' => '-3.9',
                    ),
                's' =>
                    array(
                        0 =>
                            array(
                                'n' => 'empty',
                            ),
                        1 =>
                            array(
                                'n' => 'ONLY_VALUE',
                                'v' => 'element has only value',
                            ),
                        2 =>
                            array(
                                'n' => 'a',
                                'a' =>
                                    array(
                                        'element_has_empty_attribute' =>
                                            '',
                                    ),
                            ),
                        3 =>
                            array(
                                'n' => 'b',
                                'a' =>
                                    array(
                                        'v' => 'x',
                                        'attr' => '-3',
                                    ),
                            ),
                        4 =>
                            array(
                                'n' => 'b',
                                'a' =>
                                    array(
                                        'v' => 'y',
                                        'y' => 'val',
                                    ),
                            ),
                        5 =>
                            array(
                                'n' => 'b',
                                'a' =>
                                    array(
                                        'v' => 'z',
                                    ),
                            ),
                        6 =>
                            array(
                                'n' => 'c',
                                'v' => '0',
                            ),
                        7 =>
                            array(
                                'n' => 'c',
                                'a' =>
                                    array(
                                        'a' => 'v',
                                    ),
                            ),
                        8 =>
                            array(
                                'n' => 'c',
                            ),
                        9 =>
                            array(
                                'n' => 'nested',
                                's' =>
                                    array(
                                        0 => array(
                                            'n' => 'any',
                                            'a' =>
                                                array(
                                                    'val' => '1',
                                                ),
                                        ),
                                        1 => array(
                                            'n' => 'any',
                                            'a' =>
                                                array(
                                                    'val' => '2',
                                                ),
                                        ),
                                    ),
                            ),
                    ),
            );
        $navigator = new XmlElement($xmlContent);

        /* get element name */
        $exported = $navigator->name();
        $this->assertEquals('complex', $exported);

        /* get element value */
        $exported = $navigator->value();
        $this->assertEquals('', $exported);

        /* get list of attributes */
        $attributes = $navigator->attributes();
        $expectedName =
            array(
                0 => 'str',
                1 => 'number',
            );
        $expectedVal =
            array(
                0 => 'text',
                1 => '-3.9',
            );
        foreach ($attributes as $i => $attribute) {
            $name = $attribute->name();
            $val = $attribute->value();
            self::assertEquals($expectedName[$i], $name);
            self::assertEquals($expectedVal[$i], $val);
        }

        /* get attribute value */
        $val = $navigator->get('str');
        self::assertEquals('text', $val);
        /* text */

        /* get list of nested elements */
        $elements = $navigator->elements();

        $expected =
            array(
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
            );
        foreach ($elements as $i => $element) {
            $name = $element->name();
            self::assertEquals($expected[$i], $name);
        }

        /* get desired nested element */
        $elem = $navigator->pull()->current();
        $name = $elem->name();
        self::assertEquals('empty', $name);

        $expected = [
            'empty',
            'ONLY_VALUE',
            'a',
            'b',
            'b',
            'b',
            'c',
            'c',
            'c',
            'nested',
        ];
        /* get all nested elements */
        foreach ($navigator->pull() as $i => $pulled) {
            /** @var IXmlElement $pulled */
            $name = $pulled->name();
            self::assertEquals($expected[$i], $name);
        }
        $expected = [
            'any',
            'any',
        ];
        /* get nested element */
        /** @var IXmlElement $nested */
        $nested = $navigator->pull('nested')->current();
        /* get names of all elements of nested element */
        $exported = $nested->elements();
        foreach ($exported as $i => $item) {
            $name = $item->name();
            self::assertEquals($expected[$i], $name);
        }

        $expectedName = [
            'any',
            'any',
        ];
        $expectedVal = [
            '1',
            '2',
        ];
        /* get all elements with name `any` */
        foreach ($nested->pull('any') as $i => $any) {
            /** @var IXmlElement $any */
            $name = $any->name();
            $value = $any->get('val');
            self::assertEquals($expectedName[$i], $name);
            self::assertEquals($expectedVal[$i], $value);
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

        self::assertEquals(
            static::XML_STRUCTURE,
            $arrayRepresentationOfXml
        );
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

        self::assertEquals(
            static::CONVERTER_PRETTY_PRINT,
            $arrayRepresentationOfXml
        );
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

        $arrayRepresentationOfXml = FastXmlToArray::convert($xml);

        self::assertEquals(
            static::XML_STRUCTURE,
            $arrayRepresentationOfXml
        );
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

        $arrayRepresentationOfXml = FastXmlToArray::prettyPrint($xml);

        self::assertEquals(
            static::PRETTY_PRINT,
            $arrayRepresentationOfXml
        );
    }
}
