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
use XMLReader;

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

    public function test1()
    {
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<QueryResult
        xmlns="urn://x-artefacts-smev-gov-ru/services/service-adapter/types">
    <smevMetadata
            b="2">
        <MessageId
                c="re">c0f7b4bf-7453-11ed-8f6b-005056ac53b6
        </MessageId>
        <Sender>CUST01</Sender>
        <Recipient>RPRN01</Recipient>
    </smevMetadata>
    <Message
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:type="RequestMessageType">
        <RequestMetadata>
            <clientId>a0efcf22-b199-4e1c-984a-63fd59ed9345</clientId>
            <linkedGroupIdentity>
                <refClientId>a0efcf22-b199-4e1c-984a-63fd59ed9345</refClientId>
            </linkedGroupIdentity>
            <testMessage>false</testMessage>
        </RequestMetadata>
        <RequestContent>
            <content>
                <MessagePrimaryContent>
                    <ns:Query
                            xmlns:ns="urn://rpn.gov.ru/services/smev/cites/1.0.0"
                            xmlns="urn://x-artefacts-smev-gov-ru/services/message-exchange/types/basic/1.2"
                    >
                        <ns:Search>
                            <ns:SearchNumber
                                    Number="22RU006228DV"/>
                        </ns:Search>
                    </ns:Query>
                </MessagePrimaryContent>
            </content>
        </RequestContent>
    </Message>
</QueryResult>
XML;

        $mayRead = true;
        $reader = XMLReader::XML($xml);
        while ($mayRead && $reader->name !== 'ns:Query') {
            $mayRead = $reader->read();
        }

        while ($reader->name === 'ns:Query') {
            $elementsCollection = FastXmlToArray::extractElements(
                $reader,
            );
            $result = FastXmlToArray::createTheHierarchyOfElements(
                $elementsCollection,
            );

            echo json_encode($result, JSON_PRETTY_PRINT);

            while (
                $mayRead &&
                $reader->nodeType !== XMLReader::ELEMENT
            ) {
                $mayRead = $reader->read();
            }
        }
        $reader->close();

        self::assertTrue(true);
    }

    public function test2()
    {
        $xml = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<CARPLACES>
    <CARPLACE
            ID="11356925"
            OBJECTID="20318444"
            OBJECTGUID="6e237b93-09d6-4adf-9567-e9678608543b"
            CHANGEID="31810106"
            NUMBER="1"
            OPERTYPEID="10"
            PREVID="0"
            NEXTID="0"
            UPDATEDATE="2019-07-09"
            STARTDATE="2019-07-09"
            ENDDATE="2079-06-06"
            ISACTUAL="1"
            ISACTIVE="1"
    />
    <CARPLACE
            ID="11361653"
            OBJECTID="20326793"
            OBJECTGUID="11d9f79b-be6f-43dc-bdcc-70bbfc9f86b0"
            CHANGEID="31822630"
            NUMBER="1"
            OPERTYPEID="10"
            PREVID="0"
            NEXTID="0"
            UPDATEDATE="2019-07-30"
            STARTDATE="2019-07-30"
            ENDDATE="2079-06-06"
            ISACTUAL="1"
            ISACTIVE="1"
    />
    <CARPLACE
            ID="94824"
            OBJECTID="101032823"
            OBJECTGUID="4f37e0eb-141f-4c19-b416-0ec85e2e9e76"
            CHANGEID="192339336"
            NUMBER="0"
            OPERTYPEID="10"
            PREVID="0"
            NEXTID="0"
            UPDATEDATE="2021-04-22"
            STARTDATE="2021-04-22"
            ENDDATE="2079-06-06"
            ISACTUAL="1"
            ISACTIVE="1"
    />
</CARPLACES>
XML;

        $reader = XMLReader::XML($xml);
        $mayRead = true;
        while ($mayRead && $reader->name !== 'CARPLACE') {
            $mayRead = $reader->read();
        }

        while ($mayRead && $reader->name === 'CARPLACE') {
            $elementsCollection = FastXmlToArray::extractElements(
                $reader,
            );
            $result = FastXmlToArray::createTheHierarchyOfElements(
                $elementsCollection,
            );
            echo json_encode($result, JSON_PRETTY_PRINT);

            while (
                $mayRead &&
                $reader->nodeType !== XMLReader::ELEMENT
            ) {
                $mayRead = $reader->read();
            }
        }
        $reader->close();

        self::assertTrue(true);
    }
}
