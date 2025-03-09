<?php

declare(strict_types=1);

namespace Integration;

use PHPUnit\Framework\TestCase;
use SbWereWolf\XmlNavigator\Convertation\FastXmlToArray;
use SbWereWolf\XmlNavigator\Convertation\XmlConverter;
use SbWereWolf\XmlNavigator\Extraction\ElementExtractor;
use SbWereWolf\XmlNavigator\Extraction\HierarchyComposer;
use SbWereWolf\XmlNavigator\Extraction\PrettyPrintComposer;
use SbWereWolf\XmlNavigator\General\Notation;
use SbWereWolf\XmlNavigator\Navigation\IXmlElement;
use SbWereWolf\XmlNavigator\Navigation\XmlElement;
use SbWereWolf\XmlNavigator\Parsing\FastXmlParser;
use SbWereWolf\XmlNavigator\Parsing\XmlParser;
use XMLReader;

/**
 * Testing library classes
 */
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
                            0 => '0',
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


    private const PRETTY_PRINT_FOR_MULTIPLE =
        array(
            'offer' =>
                array(
                    '@attributes' =>
                        array(
                            'id' => '206111',
                            'available' => 'false',
                        ),
                    'picture' =>
                        array(
                            0 => 'upload/iblock/2a9/1_6251_22_2.jpg',
                            1 => 'upload/iblock/5d2/q5kkwmq6zznu/1_6251_22_6.jpg',
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
                            0 => '0',
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
    private const CONVERTER_PRETTY_PRINT_FOR_MULTIPLE =
        array(
            'offer' =>
                array(
                    'a' =>
                        array(
                            'id' => '206111',
                            'available' => 'false',
                        ),
                    'picture' =>
                        array(
                            0 => 'upload/iblock/2a9/1_6251_22_2.jpg',
                            1 => 'upload/iblock/5d2/q5kkwmq6zznu/1_6251_22_6.jpg',
                        ),
                ),
        );

    private const XML_HIERARCHY =
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

    private const NS_QUERY = <<<XML
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

    private const NS_QUERY_HIERARCHY =
        array(
            0 => array
            (
                'n' => 'ns:Query',
                'a' => array
                (
                    'xmlns:ns' =>
                        'urn://rpn.gov.ru/services/smev/cites/1.0.0',
                    'xmlns' =>
                        'urn://x-artefacts-smev-gov-ru/services/message' .
                        '-exchange/types/basic/1.2',
                ),
                's' => array
                (
                    0 => array
                    (
                        'n' => 'ns:Search',
                        's' => array
                        (
                            0 => array
                            (
                                'n' => 'ns:SearchNumber',
                                'a' => array
                                (
                                    'Number' => '22RU006228DV',
                                )
                            )
                        )
                    )
                )
            )
        );

    private const NS_QUERY_PRETTY_PRINT =
        array(
            0 => array
            (
                'ns:Query' => array
                (
                    '@attributes' => array
                    (
                        'xmlns:ns' =>
                            'urn://rpn.gov.ru/services/smev/cites/1.0.0',
                        'xmlns' =>
                            'urn://x-artefacts-smev-gov-ru/services/message-exchange/types/basic/1.2'
                    ),
                    'ns:Search' => array
                    (
                        'ns:SearchNumber' => array
                        (
                            '@attributes' => array
                            (
                                'Number' => '22RU006228DV'
                            )
                        )
                    )
                )
            )
        );


    private const CARPLACE = <<<XML
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

    private const CARPLACE_HIERARCHY =
        array(
            0 => array
            (
                'n' => 'CARPLACE',
                'a' => array
                (
                    'ID' => '11356925',
                    'OBJECTID' => '20318444',
                    'OBJECTGUID' => '6e237b93-09d6-4adf-9567-e9678608543b',
                    'CHANGEID' => '31810106',
                    'NUMBER' => '1',
                    'OPERTYPEID' => '10',
                    'PREVID' => '0',
                    'NEXTID' => '0',
                    'UPDATEDATE' => '2019-07-09',
                    'STARTDATE' => '2019-07-09',
                    'ENDDATE' => '2079-06-06',
                    'ISACTUAL' => '1',
                    'ISACTIVE' => '1',
                )
            ),
            1 => array
            (
                'n' => 'CARPLACE',
                'a' => array
                (
                    'ID' => '11361653',
                    'OBJECTID' => '20326793',
                    'OBJECTGUID' => '11d9f79b-be6f-43dc-bdcc-70bbfc9f86b0',
                    'CHANGEID' => '31822630',
                    'NUMBER' => '1',
                    'OPERTYPEID' => '10',
                    'PREVID' => '0',
                    'NEXTID' => '0',
                    'UPDATEDATE' => '2019-07-30',
                    'STARTDATE' => '2019-07-30',
                    'ENDDATE' => '2079-06-06',
                    'ISACTUAL' => '1',
                    'ISACTIVE' => '1',
                )

            ),
            2 => array
            (
                'n' => 'CARPLACE',
                'a' => array
                (
                    'ID' => '94824',
                    'OBJECTID' => '101032823',
                    'OBJECTGUID' => '4f37e0eb-141f-4c19-b416-0ec85e2e9e76',
                    'CHANGEID' => '192339336',
                    'NUMBER' => '0',
                    'OPERTYPEID' => '10',
                    'PREVID' => '0',
                    'NEXTID' => '0',
                    'UPDATEDATE' => '2021-04-22',
                    'STARTDATE' => '2021-04-22',
                    'ENDDATE' => '2079-06-06',
                    'ISACTUAL' => '1',
                    'ISACTIVE' => '1',
                )
            )
        );

    private const CARPLACE_PRETTY_PRINT =
        array(
            '0' => array
            (
                'CARPLACE' => array
                (
                    '@attributes' => array
                    (
                        'ID' => '11356925',
                        'OBJECTID' => '20318444',
                        'OBJECTGUID' =>
                            '6e237b93-09d6-4adf-9567-e9678608543b',
                        'CHANGEID' => '31810106',
                        'NUMBER' => '1',
                        'OPERTYPEID' => '10',
                        'PREVID' => '0',
                        'NEXTID' => '0',
                        'UPDATEDATE' => '2019-07-09',
                        'STARTDATE' => '2019-07-09',
                        'ENDDATE' => '2079-06-06',
                        'ISACTUAL' => '1',
                        'ISACTIVE' => '1',
                    )
                )
            ),
            1 => array
            (
                'CARPLACE' => array
                (
                    '@attributes' => array
                    (
                        'ID' => '11361653',
                        'OBJECTID' => '20326793',
                        'OBJECTGUID' =>
                            '11d9f79b-be6f-43dc-bdcc-70bbfc9f86b0',
                        'CHANGEID' => '31822630',
                        'NUMBER' => '1',
                        'OPERTYPEID' => '10',
                        'PREVID' => '0',
                        'NEXTID' => '0',
                        'UPDATEDATE' => '2019-07-30',
                        'STARTDATE' => '2019-07-30',
                        'ENDDATE' => '2079-06-06',
                        'ISACTUAL' => '1',
                        'ISACTIVE' => '1',
                    )
                )
            ),
            2 => array
            (
                'CARPLACE' => array
                (
                    '@attributes' => array
                    (
                        'ID' => '94824',
                        'OBJECTID' => '101032823',
                        'OBJECTGUID' =>
                            '4f37e0eb-141f-4c19-b416-0ec85e2e9e76',
                        'CHANGEID' => '192339336',
                        'NUMBER' => '0',
                        'OPERTYPEID' => '10',
                        'PREVID' => '0',
                        'NEXTID' => '0',
                        'UPDATEDATE' => '2021-04-22',
                        'STARTDATE' => '2021-04-22',
                        'ENDDATE' => '2079-06-06',
                        'ISACTUAL' => '1',
                        'ISACTIVE' => '1',
                    )
                )
            )
        );

    private const MULTI_NESTED =
        'a:1:{i:0;a:1:{s:10:"Товар";a:3:{s:36:' .
        '"ЗначенияРеквизитов";a:1:{s:34:"ЗначениеРеквизита";a:3:{i:0;a:' .
        '2:{s:24:"Наименование";s:30:"ВидНоменклатуры";s:16:"Значение";' .
        's:10:"Товар";}i:1;a:2:{s:24:"Наименование";s:30:"ТипНоменклату' .
        'ры";s:16:"Значение";s:10:"Товар";}i:2;a:2:{s:24:"Наименование"' .
        ';s:37:"Полное наименование";s:16:"Значение";s:29:"Фильтр масля' .
        'ный";}}}s:36:"Взаимозаменяемости";a:1:{s:36:"Взаимозаменяемост' .
        'ь";a:5:{i:0;a:3:{s:10:"Марка";s:7:"CUMMINS";s:12:"Модель";s:10' .
        ':"B3.9 (4BT)";s:22:"КатегорияТС";s:18:"Двигатели";}i:1;a:3:{s:' .
        '10:"Марка";s:10:"КАМАЗ";s:12:"Модель";s:4:"4308";s:22:"Категор' .
        'ияТС";s:37:"Грузовые автомобили";}i:2;a:3:{s:10:"Марка";s:8:"D' .
        'ONGFENG";s:12:"Модель";s:7:"DFA1063";s:22:"КатегорияТС";s:37:"' .
        'Грузовые автомобили";}i:3;a:3:{s:10:"Марка";s:13:"GOLDEN DRAGO' .
        'N";s:12:"Модель";s:7:"XML6720";s:22:"КатегорияТС";s:16:"Автобу' .
        'сы";}i:4;a:3:{s:10:"Марка";s:6:"YUTONG";s:12:"Модель";s:12:"ZK' .
        '6737D (E2)";s:22:"КатегорияТС";s:16:"Автобусы";}}}s:44:"Дополн' .
        'ительныеАртикулы";a:1:{s:42:"ДополнительныйАртикул";a:5:{i:0;a' .
        ':4:{s:14:"Артикул";s:6:"LF3345";s:10:"Марка";a:0:{}s:26:"Произ' .
        'водитель";a:0:{}s:22:"ТипАртикула";s:31:"Артикул Основной";}i:' .
        '1;a:4:{s:14:"Артикул";s:11:"1012Q01-010";s:10:"Марка";a:0:{}s:' .
        '26:"Производитель";a:0:{}s:22:"ТипАртикула";s:12:"Аналог";}i:2' .
        ';a:4:{s:14:"Артикул";s:10:"1012-00121";s:10:"Марка";a:0:{}s:26' .
        ':"Производитель";a:0:{}s:22:"ТипАртикула";s:12:"Аналог";}i:3;a' .
        ':4:{s:14:"Артикул";s:7:"3908616";s:10:"Марка";a:0:{}s:26:"Прои' .
        'зводитель";a:0:{}s:22:"ТипАртикула";s:12:"Аналог";}i:4;a:4:{s:' .
        '14:"Артикул";s:7:"3903224";s:10:"Марка";a:0:{}s:26:"Производит' .
        'ель";a:0:{}s:22:"ТипАртикула";s:12:"Аналог";}}}}}}';

    /**
     * @return void
     */
    public function testPrettyPrintWithNestedMultiElements(): void
    {
        $xml = <<<XML
			<Товар>
				<ЗначенияРеквизитов>
					<ЗначениеРеквизита>
						<Наименование>ВидНоменклатуры</Наименование>
						<Значение>Товар</Значение>
					</ЗначениеРеквизита>
					<ЗначениеРеквизита>
						<Наименование>ТипНоменклатуры</Наименование>
						<Значение>Товар</Значение>
					</ЗначениеРеквизита>
					<ЗначениеРеквизита>
						<Наименование>Полное наименование</Наименование>
						<Значение>Фильтр масляный</Значение>
					</ЗначениеРеквизита>
				</ЗначенияРеквизитов>
				<Взаимозаменяемости>
					<Взаимозаменяемость>
						<Марка>CUMMINS</Марка>
						<Модель>B3.9 (4BT)</Модель>
						<КатегорияТС>Двигатели</КатегорияТС>
					</Взаимозаменяемость>
					<Взаимозаменяемость>
						<Марка>КАМАЗ</Марка>
						<Модель>4308</Модель>
						<КатегорияТС>Грузовые автомобили</КатегорияТС>
					</Взаимозаменяемость>
					<Взаимозаменяемость>
						<Марка>DONGFENG</Марка>
						<Модель>DFA1063</Модель>
						<КатегорияТС>Грузовые автомобили</КатегорияТС>
					</Взаимозаменяемость>
					<Взаимозаменяемость>
						<Марка>GOLDEN DRAGON</Марка>
						<Модель>XML6720</Модель>
						<КатегорияТС>Автобусы</КатегорияТС>
					</Взаимозаменяемость>
					<Взаимозаменяемость>
						<Марка>YUTONG</Марка>
						<Модель>ZK6737D (E2)</Модель>
						<КатегорияТС>Автобусы</КатегорияТС>
					</Взаимозаменяемость>
				</Взаимозаменяемости>
				<ДополнительныеАртикулы>
					<ДополнительныйАртикул>
						<Артикул>LF3345</Артикул>
						<Марка/>
						<Производитель/>
						<ТипАртикула>Артикул Основной</ТипАртикула>
					</ДополнительныйАртикул>
					<ДополнительныйАртикул>
						<Артикул>1012Q01-010</Артикул>
						<Марка/>
						<Производитель/>
						<ТипАртикула>Аналог</ТипАртикула>
					</ДополнительныйАртикул>
					<ДополнительныйАртикул>
						<Артикул>1012-00121</Артикул>
						<Марка/>
						<Производитель/>
						<ТипАртикула>Аналог</ТипАртикула>
					</ДополнительныйАртикул>
					<ДополнительныйАртикул>
						<Артикул>3908616</Артикул>
						<Марка/>
						<Производитель/>
						<ТипАртикула>Аналог</ТипАртикула>
					</ДополнительныйАртикул>
					<ДополнительныйАртикул>
						<Артикул>3903224</Артикул>
						<Марка/>
						<Производитель/>
						<ТипАртикула>Аналог</ТипАртикула>
					</ДополнительныйАртикул>
				</ДополнительныеАртикулы>
			</Товар>
XML;
        /* @var XMLReader $reader */
        $reader = XMLReader::XML($xml);

        $results = [];
        $extractor = FastXmlParser
            ::extractPrettyPrint(
                $reader,
                function (XMLReader $cursor) {
                    return $cursor->name === 'Товар';
                }
            );
        foreach ($extractor as $element) {
            $results[] = $element;
        }

        $reader->close();

        self::assertEquals(
            static::MULTI_NESTED,
            serialize($results)
        );
    }

    /**
     * @return void
     */
    public function testXmlNavigatorEmpty(): void
    {
        self::expectExceptionCode(-666);
        new XmlElement([]);
    }

    /**
     * @return void
     */
    public function testXmlNavigator(): void
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

        /* check has element value */
        $exported = $navigator->hasValue();
        $this->assertEquals(false, $exported);

        /* get element value */
        $exported = $navigator->value();
        $this->assertEquals('', $exported);

        /* check has element attribute */
        $exported = $navigator->hasAttribute('str');
        $this->assertEquals(true, $exported);


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

        /* check has element attribute */
        $exported = $navigator->hasElement('empty');
        $this->assertEquals(true, $exported);

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
    }

    /**
     * @return void
     */
    public function testElementExtractorExtractElements(): void
    {
        $xml = <<<XML
<complex>
    <ONLY_VALUE>element has only value</ONLY_VALUE>
    <empty/>
</complex>
XML;
        /** @var XMLReader $reader */
        $reader = XMLReader::XML($xml);

        $mayRead = true;
        while (
            $mayRead &&
            $reader->nodeType !== XMLReader::TEXT
        ) {
            $mayRead = $reader->read();
        }
        $arrayRepresentationOfXml =
            ElementExtractor::extractElements($reader, 'v', 'a');

        $expected = array
        (
            0 => array
            (
                'empty' => array
                (
                    'depth' => 1
                )
            )
        );
        self::assertEquals($expected, $arrayRepresentationOfXml);
    }

    /**
     * @return void
     */
    public function testXmlConverterToHierarchyOfElements(): void
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
            (new XmlConverter())->toHierarchyOfElements($xml);

        self::assertEquals(
            static::XML_HIERARCHY,
            $arrayRepresentationOfXml
        );
    }

    /**
     * @return void
     */
    public function testXmlConverterToPrettyPrint(): void
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

        $converter = new XmlConverter();
        $arrayRepresentationOfXml =
            $converter->toPrettyPrint($xml);

        self::assertEquals(
            static::CONVERTER_PRETTY_PRINT,
            $arrayRepresentationOfXml
        );

        $path = __DIR__ . DIRECTORY_SEPARATOR . 'text.xml';
        $actual[] =
            $converter->toPrettyPrint('', $path);

        $expected = array(
            0 => array(
                'e' => array
                (
                    'v' => 'v',
                    'a' => array
                    (
                        'a' => ''
                    )
                )
            )
        );
        self::assertEquals($expected, $actual);
    }

    /**
     * @return void
     */
    public function testXmlConverterToPrettyPrintWithMultipleElements
    (): void
    {
        $xml = <<<XML
<offer id="206111" available="false">
    <picture>upload/iblock/2a9/1_6251_22_2.jpg</picture>
    <picture>upload/iblock/5d2/q5kkwmq6zznu/1_6251_22_6.jpg</picture>
</offer>
XML;

        $converter = new XmlConverter();
        $arrayRepresentationOfXml =
            $converter->toPrettyPrint($xml);

        self::assertEquals(
            static::CONVERTER_PRETTY_PRINT_FOR_MULTIPLE,
            $arrayRepresentationOfXml
        );
    }

    /**
     * @return void
     */
    public function testFastXmlToArrayConvertWithEmpty(): void
    {
        static::expectExceptionCode(-667);
        FastXmlToArray::convert();
    }

    /**
     * @return void
     */
    public function testFastXmlToArrayConvert(): void
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
            static::XML_HIERARCHY,
            $arrayRepresentationOfXml
        );
    }

    /**
     * @return void
     */
    public function testFastXmlToArrayPrettyPrint(): void
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

    /**
     * @return void
     */
    public function testFastXmlToArrayPrettyPrintWithMultipleElements
    (): void
    {
        $xml = <<<XML
<offer id="206111" available="false">
    <picture>upload/iblock/2a9/1_6251_22_2.jpg</picture>
    <picture>upload/iblock/5d2/q5kkwmq6zznu/1_6251_22_6.jpg</picture>
</offer>
XML;

        $arrayRepresentationOfXml = FastXmlToArray::prettyPrint($xml);

        self::assertEquals(
            static::PRETTY_PRINT_FOR_MULTIPLE,
            $arrayRepresentationOfXml
        );
    }

    /**
     * @return void
     */
    public function testHierarchyComposerComposeNsQuery(): void
    {
        /** @var XMLReader $reader */
        $reader = XMLReader::XML(static::NS_QUERY);

        $mayRead = true;
        while ($mayRead && $reader->name !== 'ns:Query') {
            $mayRead = $reader->read();
        }

        $results = [];
        while ($reader->name === 'ns:Query') {
            $result = HierarchyComposer::compose($reader);
            $results[] = $result;

            while (
                $mayRead &&
                $reader->nodeType !== XMLReader::ELEMENT
            ) {
                $mayRead = $reader->read();
            }
        }
        $reader->close();


        self::assertEquals(static::NS_QUERY_HIERARCHY, $results);
    }

    /**
     * @return void
     */
    public function testHierarchyComposerComposeCarplace(): void
    {
        /** @var XMLReader $reader */
        $reader = XMLReader::XML(static::CARPLACE);

        $mayRead = true;
        while ($mayRead && $reader->name !== 'CARPLACE') {
            $mayRead = $reader->read();
        }

        $results = [];
        while ($mayRead && $reader->name === 'CARPLACE') {
            $result = HierarchyComposer::compose($reader);
            $results[] = $result;

            while (
                $mayRead &&
                $reader->nodeType !== XMLReader::ELEMENT
            ) {
                $mayRead = $reader->read();
            }
        }
        $reader->close();

        self::assertEquals(static::CARPLACE_HIERARCHY, $results);
    }

    /**
     * @return void
     */
    public function testPrettyPrintComposerComposeNsQuery(): void
    {
        /** @var XMLReader $reader */
        $reader = XMLReader::XML(static::NS_QUERY);

        $mayRead = true;
        while ($mayRead && $reader->name !== 'ns:Query') {
            $mayRead = $reader->read();
        }

        $results = [];
        while ($reader->name === 'ns:Query') {
            $result = PrettyPrintComposer::compose($reader);
            $results[] = $result;

            while (
                $mayRead &&
                $reader->nodeType !== XMLReader::ELEMENT
            ) {
                $mayRead = $reader->read();
            }
        }
        $reader->close();


        self::assertEquals(static::NS_QUERY_PRETTY_PRINT, $results);


        /** @var XMLReader $reader */
        $reader = XMLReader::XML('<e a="">v</e>');

        $mayRead = true;
        while ($mayRead && $reader->nodeType !== XMLReader::ELEMENT) {
            $mayRead = $reader->read();
        }

        $results = [];
        while ($reader->nodeType === XMLReader::ELEMENT) {
            $result = PrettyPrintComposer::compose($reader);
            $results[] = $result;

            while (
                $mayRead &&
                $reader->nodeType !== XMLReader::ELEMENT
            ) {
                $mayRead = $reader->read();
            }
        }
        $reader->close();

        $expected = array(
            0 => array(
                'e' => array
                (
                    '@value' => 'v',
                    '@attributes' => array
                    (
                        'a' => ''
                    )
                )
            )
        );
        self::assertEquals($expected, $results);
    }

    /**
     * @return void
     */
    public function testPrettyPrintComposerComposeCarplace(): void
    {
        /** @var XMLReader $reader */
        $reader = XMLReader::XML(static::CARPLACE);

        $mayRead = true;
        while ($mayRead && $reader->name !== 'CARPLACE') {
            $mayRead = $reader->read();
        }

        $results = [];
        while ($mayRead && $reader->name === 'CARPLACE') {
            $result = PrettyPrintComposer::compose($reader);
            $results[] = $result;

            while (
                $mayRead &&
                $reader->nodeType !== XMLReader::ELEMENT
            ) {
                $mayRead = $reader->read();
            }
        }
        $reader->close();

        self::assertEquals(static::CARPLACE_PRETTY_PRINT, $results);
    }

    /**
     * @return void
     */
    public function testFastXmlParserExtractHierarchyNsQuery(): void
    {
        /** @var XMLReader $reader */
        $reader = XMLReader::XML(static::NS_QUERY);

        $results = [];
        $extractor = FastXmlParser::extractHierarchy(
            $reader,
            function (XMLReader $cursor) {
                return $cursor->name === 'ns:Query';
            }
        );
        foreach ($extractor as $element) {
            $results[] = $element;
        }

        $reader->close();

        self::assertEquals(static::NS_QUERY_HIERARCHY, $results);
    }

    /**
     * @return void
     */
    public function testFastXmlParserExtractPrettyPrintNsQuery(): void
    {
        /** @var XMLReader $reader */
        $reader = XMLReader::XML(static::NS_QUERY);

        $results = [];
        $extractor = FastXmlParser::extractPrettyPrint(
            $reader,
            function (XMLReader $cursor) {
                return $cursor->name === 'ns:Query';
            }
        );
        foreach ($extractor as $element) {
            $results[] = $element;
        }

        $reader->close();

        self::assertEquals(static::NS_QUERY_PRETTY_PRINT, $results);
    }

    /**
     * @return void
     */
    public function testFastXmlParserExtractHierarchyCarplace(): void
    {
        /** @var XMLReader $reader */
        $reader = XMLReader::XML(static::CARPLACE);

        $results = [];
        $extractor = FastXmlParser::extractHierarchy(
            $reader,
            function (XMLReader $cursor) {
                return $cursor->name === 'CARPLACE';
            }
        );
        foreach ($extractor as $result) {
            $results[] = $result;
        }
        $reader->close();

        self::assertEquals(static::CARPLACE_HIERARCHY, $results);
    }

    /**
     * @return void
     */
    public function testFastXmlParserExtractPrettyPrintCarplace(): void
    {
        /** @var XMLReader $reader */
        $reader = XMLReader::XML(static::CARPLACE);

        $results = [];
        $extractor = FastXmlParser::extractPrettyPrint(
            $reader,
            function (XMLReader $cursor) {
                return $cursor->name === 'CARPLACE';
            }
        );
        foreach ($extractor as $result) {
            $results[] = $result;
        }
        $reader->close();

        self::assertEquals(static::CARPLACE_PRETTY_PRINT, $results);
    }

    /**
     * @return void
     */
    public function testXmlParserExtractHierarchyNsQuery(): void
    {
        /** @var XMLReader $reader */
        $reader = XMLReader::XML(static::NS_QUERY);
        $parser = new XmlParser();

        $results = [];
        $extractor = $parser->extractHierarchy(
            $reader,
            function (XMLReader $cursor) {
                return $cursor->name === 'ns:Query';
            }
        );
        foreach ($extractor as $element) {
            $results[] = $element;
        }

        $reader->close();

        self::assertEquals(static::NS_QUERY_HIERARCHY, $results);
    }

    /**
     * @return void
     */
    public function testXmlParserExtractPrettyPrintNsQuery(): void
    {
        /** @var XMLReader $reader */
        $reader = XMLReader::XML(static::NS_QUERY);
        $parser = new XmlParser(
            Notation::VAL,
            Notation::ATTR,
        );

        $results = [];
        $extractor = $parser->extractPrettyPrint(
            $reader,
            function (XMLReader $cursor) {
                return $cursor->name === 'ns:Query';
            }
        );
        foreach ($extractor as $element) {
            $results[] = $element;
        }

        $reader->close();

        self::assertEquals(static::NS_QUERY_PRETTY_PRINT, $results);
    }

    /**
     * @return void
     */
    public function testXmlParserExtractHierarchyCarplace(): void
    {
        /** @var XMLReader $reader */
        $reader = XMLReader::XML(static::CARPLACE);
        $parser = new XmlParser();

        $results = [];
        $extractor = $parser->extractHierarchy(
            $reader,
            function (XMLReader $cursor) {
                return $cursor->name === 'CARPLACE';
            }
        );
        foreach ($extractor as $result) {
            $results[] = $result;
        }
        $reader->close();

        self::assertEquals(static::CARPLACE_HIERARCHY, $results);
    }

    /**
     * @return void
     */
    public function testXmlParserExtractPrettyPrintCarplace(): void
    {
        /** @var XMLReader $reader */
        $reader = XMLReader::XML(static::CARPLACE);
        $parser = new XmlParser(
            Notation::VAL,
            Notation::ATTR,
        );

        $results = [];
        $extractor = $parser->extractPrettyPrint(
            $reader,
            function (XMLReader $cursor) {
                return $cursor->name === 'CARPLACE';
            }
        );
        foreach ($extractor as $result) {
            $results[] = $result;
        }
        $reader->close();

        self::assertEquals(static::CARPLACE_PRETTY_PRINT, $results);
    }
}
