<?php


namespace SbWereWolf\XmlNavigator\Test\Unit\Conversion;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use SbWereWolf\XmlNavigator\Conversion\FastXmlToArray;
use SbWereWolf\XmlNavigator\Test\Support\XmlFixture;

final class FastXmlToArrayTest extends TestCase
{
    public function testConvertSupportsXmlTextAndXmlFile()
    {
        $xmlText = XmlFixture::read('hierarchy-catalog.xml');
        $xmlFile = XmlFixture::path('hierarchy-catalog.xml');

        $expected = [
            'n' => 'catalog',
            'a' => [
                'region' => 'eu',
                'generated_at' => '2026-04-05T10:00:00Z',
            ],
            's' => [
                [
                    'n' => 'offer',
                    'a' => [
                        'id' => '1001',
                        'available' => 'true',
                    ],
                    's' => [
                        [
                            'n' => 'name',
                            'v' => 'Keyboard',
                        ],
                        [
                            'n' => 'price',
                            'v' => '49.90',
                            'a' => [
                                'currency' => 'USD',
                            ],
                        ],
                        [
                            'n' => 'tag',
                            'v' => 'office',
                        ],
                        [
                            'n' => 'tag',
                            'v' => 'usb',
                        ],
                    ],
                ],
                [
                    'n' => 'offer',
                    'a' => [
                        'id' => '1002',
                        'available' => 'false',
                    ],
                    's' => [
                        [
                            'n' => 'name',
                            'v' => 'Mouse',
                        ],
                        [
                            'n' => 'price',
                            'v' => '19.90',
                            'a' => [
                                'currency' => 'USD',
                            ],
                        ],
                        [
                            'n' => 'tag',
                            'v' => 'gaming',
                        ],
                    ],
                ],
            ],
        ];

        self::assertSame($expected, FastXmlToArray::convert($xmlText));
        self::assertSame($expected, FastXmlToArray::convert('', $xmlFile));
    }

    public function testPrettyPrintSupportsXmlTextAndXmlFile()
    {
        $xmlText = XmlFixture::read('repeated-pretty-print.xml');
        $xmlFile = XmlFixture::path('repeated-pretty-print.xml');

        $expected = [
            'root' => [
                'item' => [
                    'value-only',
                    [
                        '@attributes' => [
                            'code' => 'A',
                        ],
                    ],
                    [
                        '@value' => 'value-and-attributes',
                        '@attributes' => [
                            'code' => 'B',
                        ],
                    ],
                    [],
                ],
            ],
        ];

        self::assertSame($expected, FastXmlToArray::prettyPrint($xmlText));
        self::assertSame($expected, FastXmlToArray::prettyPrint('', $xmlFile));
    }

    public function testConvertSupportsExplicitLibxmlFlags()
    {
        $xmlText = XmlFixture::read('hierarchy-catalog.xml');

        self::assertSame(
            'catalog',
            FastXmlToArray::convert(
                $xmlText,
                '',
                'v',
                'a',
                'n',
                's',
                null,
                LIBXML_COMPACT
            )['n']
        );
    }

    public function testPrettyPrintSupportsExplicitLibxmlFlags()
    {
        $xmlText = XmlFixture::read('repeated-pretty-print.xml');

        self::assertSame(
            'value-only',
            FastXmlToArray::prettyPrint(
                $xmlText,
                '',
                '@value',
                '@attributes',
                null,
                LIBXML_COMPACT
            )['root']['item'][0]
        );
    }

    /**
     * @dataProvider missingSourceProvider
     */
    public function testMethodsRejectMissingXmlSource($method)
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(-667);
        $this->expectExceptionMessage(
            'Exactly one XML source must be provided: set either ' .
            '$xmlText or $xmlUri.'
        );

        FastXmlToArray::{$method}();
    }

    /**
     * @return list<array{string}>
     */
    public static function missingSourceProvider(){
        return [
            ['convert'],
            ['prettyPrint'],
        ];
    }

    /**
     * @dataProvider ambiguousSourceProvider
     */
    public function testMethodsRejectAmbiguousXmlSource(
        $method,
        $xmlText,
        $xmlUri
    ) {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(-668);
        $this->expectExceptionMessage(
            'XML source selection is ambiguous: use either ' .
            '$xmlText or $xmlUri, not both.'
        );

        FastXmlToArray::{$method}($xmlText, $xmlUri);
    }

    /**
     * @return list<array{string,string,string}>
     */
    public static function ambiguousSourceProvider(){
        return [
            [
                'convert',
                '<from-text/>',
                XmlFixture::path('hierarchy-catalog.xml'),
            ],
            [
                'prettyPrint',
                '<from-text/>',
                XmlFixture::path('hierarchy-catalog.xml'),
            ],
        ];
    }

    public function testConvertRejectsMalformedXmlText()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(-669);
        $this->expectExceptionMessage(
            'Unable to parse XML from $xmlText. ' .
            'Extra content at the end of the document'
        );

        FastXmlToArray::convert('<broken>');
    }

    public function testConvertRejectsXmlTextWithTrailingGarbage()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(-669);
        $this->expectExceptionMessage(
            'Unable to parse XML from $xmlText. ' .
            'Extra content at the end of the document'
        );

        FastXmlToArray::convert('<root/>junk');
    }

    public function testPrettyPrintRejectsMalformedXmlText()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(-669);
        $this->expectExceptionMessage(
            'Unable to parse XML from $xmlText. ' .
            'Extra content at the end of the document'
        );

        FastXmlToArray::prettyPrint('<broken>');
    }

    /**
     * @dataProvider malformedUriProvider
     */
    public function testMethodsRejectMalformedXmlFile($method)
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(-670);
        $this->expectExceptionMessage(
            'Unable to parse XML from URI `' .
            XmlFixture::path('malformed.xml') .
            '`. Opening and ending tag mismatch: offer line 3 and catalog'
        );

        FastXmlToArray::{$method}('', XmlFixture::path('malformed.xml'));
    }

    /**
     * @return list<array{string}>
     */
    public static function malformedUriProvider(){
        return [
            ['convert'],
            ['prettyPrint'],
        ];
    }

    public function testConvertRejectsUnreadableXmlUri()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(-671);
        $this->expectExceptionMessage(
            'Unable to open XML source from URI `/definitely/missing.xml`.'
        );

        FastXmlToArray::convert('', '/definitely/missing.xml');
    }

    public function testFormatLibxmlErrorsReturnsEmptyStringWhenNoErrorsExist()
    {
        libxml_clear_errors();

        $method = new ReflectionMethod(
            FastXmlToArray::class,
            'formatLibxmlErrors'
        );
        /** @noinspection PhpExpressionResultUnusedInspection */
        $method->setAccessible(true);

        self::assertSame('', $method->invoke(null));
    }

    public function testParseRootElementRejectsBufferedLibxmlErrorsAfterParse()
    {
        $method = new ReflectionMethod(
            FastXmlToArray::class,
            'parseRootElement'
        );
        /** @noinspection PhpExpressionResultUnusedInspection */
        $method->setAccessible(true);
        try {
            $method->invoke(
                null,
                '<root/>',
                '',
                null,
                self::defaultLibxmlFlags(),
                static function (\XMLReader $reader){
                    $dom = new \DOMDocument();
                    @$dom->loadXML('<broken>');

                    return [
                        'n' => $reader->name,
                    ];
                }
            );
            self::fail('Expected parsing exception was not thrown.');
        } catch (InvalidArgumentException $exception) {
            self::assertSame(-669, $exception->getCode());
            self::assertTrue(
                strpos(
                    $exception->getMessage(),
                    'Unable to parse XML from $xmlText.'
                ) !== false
            );
            self::assertTrue(
                strpos(
                    $exception->getMessage(),
                    'Premature end of data in tag broken line 1'
                ) !== false
                || strpos(
                    $exception->getMessage(),
                    "EndTag: '</' not found"
                ) !== false
            );
        }
    }

    public function testDefaultLibxmlFlagsFallbackToCompactWhenBiglinesMissing()
    {
        if (defined('LIBXML_BIGLINES')) {
            self::markTestSkipped('LIBXML_BIGLINES is available in this runtime.');
        }

        $method = new ReflectionMethod(
            FastXmlToArray::class,
            'defaultLibxmlFlags'
        );
        /** @noinspection PhpExpressionResultUnusedInspection */
        $method->setAccessible(true);

        self::assertSame(LIBXML_COMPACT, $method->invoke(null));
    }

    public function testDefaultLibxmlFlagsIncludeBiglinesWhenDefined()
    {
        if (!defined('LIBXML_BIGLINES')) {
            define('LIBXML_BIGLINES', 4194304);
        }

        $method = new ReflectionMethod(
            FastXmlToArray::class,
            'defaultLibxmlFlags'
        );
        /** @noinspection PhpExpressionResultUnusedInspection */
        $method->setAccessible(true);

        self::assertSame(
            LIBXML_COMPACT | LIBXML_BIGLINES,
            $method->invoke(null)
        );
    }

    /**
     * @return int
     */
    private static function defaultLibxmlFlags()
    {
        $flags = LIBXML_COMPACT;

        if (defined('LIBXML_BIGLINES')) {
            $flags |= LIBXML_BIGLINES;
        }

        return $flags;
    }
}
