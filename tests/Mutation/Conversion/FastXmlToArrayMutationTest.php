<?php

declare(strict_types=1);

namespace SbWereWolf\XmlNavigator\Test\Mutation\Conversion;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use SbWereWolf\XmlNavigator\Conversion\FastXmlToArray;
use SbWereWolf\XmlNavigator\Test\Support\XmlFixture;

final class FastXmlToArrayMutationTest extends TestCase
{
    public function testConvertSmallUriPreservesAllReturnedKeys(): void
    {
        $actual = FastXmlToArray::convert(
            '',
            XmlFixture::path('mixed-content.xml')
        );

        self::assertSame('root', $actual['n']);
        self::assertSame('lead', $actual['v']);
        self::assertSame('1', $actual['a']['attr']);
        self::assertSame('child', $actual['s'][0]['n']);
    }

    public function testPrettyPrintSmallCompositeXmlReturnsFullRootShape(): void
    {
        $actual = FastXmlToArray::prettyPrint(
            '<root attr="1">lead<child>v</child></root>'
        );

        self::assertArrayHasKey('root', $actual);
        self::assertSame('1', $actual['root']['@attributes']['attr']);
        self::assertSame('lead', $actual['root']['@value']);
        self::assertSame('v', $actual['root']['child']);
    }

    public function testConvertClearsLibxmlErrorsBetweenSmallCalls(): void
    {
        $initialInternalErrors = libxml_use_internal_errors();
        libxml_use_internal_errors(true);
        libxml_clear_errors();

        $dom = new \DOMDocument();
        @$dom->loadXML('<broken>');
        self::assertNotSame([], libxml_get_errors());

        try {
            $actual = FastXmlToArray::convert(
                '',
                XmlFixture::path('empty-elements.xml')
            );

            self::assertSame('doc', $actual['n']);
            self::assertSame([], libxml_get_errors());
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($initialInternalErrors);
        }
    }

    public function testDefaultFlagsUseBigLinesAndCompact(): void
    {
        $expectedFlags = LIBXML_BIGLINES | LIBXML_COMPACT;

        foreach (['convert', 'prettyPrint'] as $methodName) {
            $method = new ReflectionMethod(FastXmlToArray::class, $methodName);
            $flags = null;
            foreach ($method->getParameters() as $parameter) {
                if ($parameter->getName() === 'flags') {
                    $flags = $parameter->getDefaultValue();
                    break;
                }
            }

            self::assertSame($expectedFlags, $flags);
        }
    }

    public function testConvertPreservesMixedContentWithoutAttributes(): void
    {
        $xmlText = '<root>lead<child>v</child>tail</root>';

        self::assertSame(
            [
                'n' => 'root',
                'v' => 'leadtail',
                's' => [
                    [
                        'n' => 'child',
                        'v' => 'v',
                    ],
                ],
            ],
            FastXmlToArray::convert($xmlText)
        );
    }

    public function testPrettyPrintDoesNotTruncateCompositeRootValue(): void
    {
        self::assertSame(
            [
                'root' => [
                    '@attributes' => [
                        'attr' => '1',
                    ],
                    '@value' => 'lead',
                    'child' => 'v',
                ],
            ],
            FastXmlToArray::prettyPrint(
                '<root attr="1">lead<child>v</child></root>'
            )
        );
    }

    public function testMalformedXmlFailureClearsLibxmlErrorsAfterException(): void
    {
        $initialInternalErrors = libxml_use_internal_errors();
        libxml_use_internal_errors(true);
        libxml_clear_errors();

        try {
            try {
                FastXmlToArray::convert('<broken>');
                self::fail('Expected InvalidArgumentException was not thrown.');
            } catch (InvalidArgumentException $exception) {
                self::assertSame(-669, $exception->getCode());
                self::assertSame([], libxml_get_errors());
            }
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($initialInternalErrors);
        }
    }

    public function testFormatLibxmlErrorsTrimsMessages(): void
    {
        $method = new ReflectionMethod(
            FastXmlToArray::class,
            'formatLibxmlErrors'
        );
        /** @noinspection PhpExpressionResultUnusedInspection */
        $method->setAccessible(true);

        $initialInternalErrors = libxml_use_internal_errors();
        libxml_use_internal_errors(true);
        libxml_clear_errors();

        $dom = new \DOMDocument();
        @$dom->loadXML('<broken>');

        try {
            $formatted = $method->invoke(null);

            self::assertStringStartsWith(
                ' Premature end of data in tag broken line 1',
                $formatted
            );
            self::assertStringNotContainsString("\n", $formatted);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($initialInternalErrors);
        }
    }

    public function testParseRootElementClearsPreexistingLibxmlErrorsBeforeParsing(): void
    {
        $method = new ReflectionMethod(
            FastXmlToArray::class,
            'parseRootElement'
        );
        /** @noinspection PhpExpressionResultUnusedInspection */
        $method->setAccessible(true);

        $initialInternalErrors = libxml_use_internal_errors();
        libxml_use_internal_errors(true);
        libxml_clear_errors();

        $dom = new \DOMDocument();
        @$dom->loadXML('<broken>');
        self::assertNotSame([], libxml_get_errors());

        try {
            self::assertSame(
                [
                    'ok' => true,
                ],
                $method->invoke(
                    null,
                    '<root/>',
                    '',
                    null,
                    LIBXML_BIGLINES | LIBXML_COMPACT,
                    static function (\XMLReader $reader): array {
                        return [
                            'ok' => true,
                        ];
                    }
                )
            );
            self::assertSame([], libxml_get_errors());
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($initialInternalErrors);
        }
    }

    public function testParseRootElementReturnsCallbackResultWithoutTruncation(): void
    {
        $method = new ReflectionMethod(
            FastXmlToArray::class,
            'parseRootElement'
        );
        /** @noinspection PhpExpressionResultUnusedInspection */
        $method->setAccessible(true);

        self::assertSame(
            [
                'first' => 'one',
                'second' => 'two',
            ],
            $method->invoke(
                null,
                '<root/>',
                '',
                null,
                LIBXML_BIGLINES | LIBXML_COMPACT,
                static function (\XMLReader $reader): array {
                    return [
                        'first' => 'one',
                        'second' => 'two',
                    ];
                }
            )
        );
    }

    public function testParseRootElementRestoresLibxmlStateAndClosesReader(): void
    {
        $method = new ReflectionMethod(
            FastXmlToArray::class,
            'parseRootElement'
        );
        /** @noinspection PhpExpressionResultUnusedInspection */
        $method->setAccessible(true);

        $initialInternalErrors = libxml_use_internal_errors();
        libxml_use_internal_errors(true);
        libxml_clear_errors();

        $dom = new \DOMDocument();
        @$dom->loadXML('<broken>');
        self::assertNotSame([], libxml_get_errors());

        $capturedReader = null;

        try {
            libxml_use_internal_errors(false);

            self::assertSame(
                [
                    'ok' => true,
                ],
                $method->invoke(
                    null,
                    '<root/>',
                    '',
                    null,
                    LIBXML_BIGLINES | LIBXML_COMPACT,
                    static function (\XMLReader $reader) use (
                        &$capturedReader
                    ): array {
                        $capturedReader = $reader;

                        return [
                            'ok' => true,
                        ];
                    }
                )
            );

            self::assertSame([], libxml_get_errors());
            self::assertFalse(libxml_use_internal_errors());
            self::assertInstanceOf(\XMLReader::class, $capturedReader);

            try {
                $capturedReader->read();
                self::fail('Expected XMLReader::read() to fail after close.');
            } catch (\Error $error) {
                self::assertSame(
                    'Data must be loaded before reading',
                    $error->getMessage()
                );
            }
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($initialInternalErrors);
        }
    }
}
