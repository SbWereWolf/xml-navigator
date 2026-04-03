<?php

declare(strict_types=1);

namespace Unit\Conversion;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use SbWereWolf\XmlNavigator\Conversion\FastXmlToArray;

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
            FastXmlToArray::convert(
                '<root><value>alpha</value></root>'
            )
        );
    }

    public function testPrettyPrintParsesXmlUri(): void
    {
        $path = $this->createTempXmlFile(
            '<root attr="x"><value>alpha</value></root>'
        );

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

    public function testConvertRejectsMissingXmlSource(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(-667);

        FastXmlToArray::convert();
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
        $this->expectExceptionCode(-671);

        FastXmlToArray::convert('', '/definitely/missing.xml');
    }

    public function testPrettyPrintRejectsMalformedXmlUri(): void
    {
        $path = $this->createTempXmlFile('<broken>');

        try {
            $this->expectException(InvalidArgumentException::class);
            $this->expectExceptionCode(-670);

            FastXmlToArray::prettyPrint('', $path);
        } finally {
            @unlink($path);
        }
    }

    public function testFormatLibxmlErrorsReturnsEmptyStringWhenNoErrorsExist(): void
    {
        libxml_clear_errors();

        $method = new ReflectionMethod(
            FastXmlToArray::class,
            'formatLibxmlErrors'
        );
        $method->setAccessible(true);

        static::assertSame('', $method->invoke(null));
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
