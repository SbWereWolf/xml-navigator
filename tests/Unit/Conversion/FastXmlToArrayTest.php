<?php

declare(strict_types=1);

namespace Unit\Conversion;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use SbWereWolf\XmlNavigator\Conversion\FastXmlToArray;
use SbWereWolf\XmlNavigator\Test\Support\XmlFixture;

final class FastXmlToArrayTest extends TestCase
{
    public function testConvertSupportsXmlTextAndXmlFile(): void
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

    public function testPrettyPrintSupportsXmlTextAndXmlFile(): void
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

    #[DataProvider('missingSourceProvider')]
    public function testMethodsRejectMissingXmlSource(string $method): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(-667);

        FastXmlToArray::{$method}();
    }

    /**
     * @return list<array{string}>
     */
    public static function missingSourceProvider(): array
    {
        return [
            ['convert'],
            ['prettyPrint'],
        ];
    }

    #[DataProvider('ambiguousSourceProvider')]
    public function testMethodsRejectAmbiguousXmlSource(
        string $method,
        string $xmlText,
        string $xmlUri
    ): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(-668);

        FastXmlToArray::{$method}($xmlText, $xmlUri);
    }

    /**
     * @return list<array{string,string,string}>
     */
    public static function ambiguousSourceProvider(): array
    {
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

    public function testConvertRejectsMalformedXmlText(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(-669);

        FastXmlToArray::convert('<broken>');
    }

    public function testConvertRejectsXmlTextWithTrailingGarbage(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(-669);

        FastXmlToArray::convert('<root/>junk');
    }

    public function testPrettyPrintRejectsMalformedXmlText(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(-669);

        FastXmlToArray::prettyPrint('<broken>');
    }

    #[DataProvider('malformedUriProvider')]
    public function testMethodsRejectMalformedXmlFile(string $method): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(-670);

        FastXmlToArray::{$method}('', XmlFixture::path('malformed.xml'));
    }

    /**
     * @return list<array{string}>
     */
    public static function malformedUriProvider(): array
    {
        return [
            ['convert'],
            ['prettyPrint'],
        ];
    }

    public function testConvertRejectsUnreadableXmlUri(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(-671);

        FastXmlToArray::convert('', '/definitely/missing.xml');
    }

    public function testFormatLibxmlErrorsReturnsEmptyStringWhenNoErrorsExist(): void
    {
        libxml_clear_errors();

        $method = new ReflectionMethod(
            FastXmlToArray::class,
            'formatLibxmlErrors'
        );
        $method->setAccessible(true);

        self::assertSame('', $method->invoke(null));
    }

    public function testParseRootElementRejectsBufferedLibxmlErrorsAfterParse(): void
    {
        $method = new ReflectionMethod(
            FastXmlToArray::class,
            'parseRootElement'
        );
        $method->setAccessible(true);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(-669);

        $method->invoke(
            null,
            '<root/>',
            '',
            null,
            LIBXML_BIGLINES | LIBXML_COMPACT,
            static function (\XMLReader $reader): array {
                $dom = new \DOMDocument();
                @$dom->loadXML('<broken>');

                return [
                    'n' => $reader->name,
                ];
            }
        );
    }
}
