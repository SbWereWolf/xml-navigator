<?php

declare(strict_types=1);

namespace Unit\Convertation;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use SbWereWolf\XmlNavigator\Conversation\FastXmlToArray;
use SbWereWolf\XmlNavigator\Extraction\HierarchyComposer;
use SbWereWolf\XmlNavigator\Extraction\PrettyPrintComposer;
use SbWereWolf\XmlNavigator\Parsing\FastXmlParser;

#[CoversClass(FastXmlToArray::class)]
#[UsesClass(FastXmlParser::class)]
#[UsesClass(HierarchyComposer::class)]
#[UsesClass(PrettyPrintComposer::class)]
final class FastXmlToArrayTest extends TestCase
{
    public function testConvertParsesXmlText(): void
    {
        static::assertSame(
            [
                'n' => 'root',
                's' => [
                    [
                        'n' => 'value',
                        'v' => 'alpha',
                    ],
                ],
            ],
            FastXmlToArray::convert('<root><value>alpha</value></root>')
        );
    }

    public function testPrettyPrintParsesXmlUri(): void
    {
        $path = $this->createTempXmlFile('<root attr="x"><value>alpha</value></root>');

        try {
            static::assertSame(
                [
                    'root' => [
                        '@attributes' => [
                            'attr' => 'x',
                        ],
                        'value' => 'alpha',
                    ],
                ],
                FastXmlToArray::prettyPrint('', $path)
            );
        } finally {
            @unlink($path);
        }
    }

    public function testConvertRejectsBothXmlTextAndXmlUri(): void
    {
        $path = $this->createTempXmlFile('<from-uri/>');

        try {
            $this->expectException(InvalidArgumentException::class);
            $this->expectExceptionCode(-668);

            FastXmlToArray::convert('<from-text/>', $path);
        } finally {
            @unlink($path);
        }
    }

    public function testPrettyPrintRejectsBothXmlTextAndXmlUri(): void
    {
        $path = $this->createTempXmlFile('<from-uri/>');

        try {
            $this->expectException(InvalidArgumentException::class);
            $this->expectExceptionCode(-668);

            FastXmlToArray::prettyPrint('<from-text/>', $path);
        } finally {
            @unlink($path);
        }
    }

    public function testConvertRejectsMalformedXmlText(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(-669);

        FastXmlToArray::convert('<broken>');
    }

    public function testConvertRejectsUnreadableXmlUri(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(-670);

        FastXmlToArray::convert('', '/definitely/missing.xml');
    }

    private function createTempXmlFile(string $xml): string
    {
        $path = tempnam(sys_get_temp_dir(), 'xml-nav-');
        static::assertIsString($path);
        $written = file_put_contents($path, $xml);
        static::assertNotFalse($written);

        return $path;
    }
}
