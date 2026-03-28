<?php

declare(strict_types=1);

namespace Unit\Conversation;

use PHPUnit\Framework\TestCase;
use SbWereWolf\XmlNavigator\Conversation\XmlConverter;

final class XmlConverterTest extends TestCase
{
    public function testToPrettyPrintRefreshesWhenXmlChanges(): void
    {
        $converter = new XmlConverter();

        static::assertSame(
            ['root' => ['value' => 'first']],
            $converter->toPrettyPrint(
                '<root><value>first</value></root>'
            )
        );
        static::assertSame(
            ['root' => ['value' => 'second']],
            $converter->toPrettyPrint(
                '<root><value>second</value></root>'
            )
        );
    }

    public function testPrettyPrintAndHierarchyShareSameInputWithoutCorruption(): void
    {
        $converter = new XmlConverter(
            val: 'value',
            attr: 'attributes',
            name: 'name',
            seq: 'children',
        );

        static::assertSame(
            [
                'price' => [
                    'value' => '129.90',
                    'attributes' => [
                        'currency' => 'USD',
                    ],
                ],
            ],
            $converter->toPrettyPrint(
                '<price currency="USD">129.90</price>'
            )
        );
        static::assertSame(
            [
                'name' => 'price',
                'value' => '129.90',
                'attributes' => [
                    'currency' => 'USD',
                ],
            ],
            $converter->toHierarchyOfElements(
                '<price currency="USD">129.90</price>'
            )
        );
    }

    public function testHierarchyRefreshesWhenXmlUriChanges(): void
    {
        $converter = new XmlConverter();
        $firstPath = $this->createTempXmlFile(
            '<root><value>first</value></root>'
        );
        $secondPath = $this->createTempXmlFile(
            '<root><value>second</value></root>'
        );

        try {
            static::assertSame(
                [
                    'n' => 'root',
                    's' => [
                        [
                            'n' => 'value',
                            'v' => 'first',
                        ],
                    ],
                ],
                $converter->toHierarchyOfElements('', $firstPath)
            );
            static::assertSame(
                [
                    'n' => 'root',
                    's' => [
                        [
                            'n' => 'value',
                            'v' => 'second',
                        ],
                    ],
                ],
                $converter->toHierarchyOfElements('', $secondPath)
            );
        } finally {
            @unlink($firstPath);
            @unlink($secondPath);
        }
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
