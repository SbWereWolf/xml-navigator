<?php

declare(strict_types=1);

namespace Unit\Conversion;

use PHPUnit\Framework\TestCase;
use SbWereWolf\XmlNavigator\Conversion\XmlConverter;
use SbWereWolf\XmlNavigator\Test\Support\XmlFixture;

final class XmlConverterTest extends TestCase
{
    public function testToHierarchyOfElementsSupportsCustomKeys(): void
    {
        $converter = new XmlConverter(
            val: 'value',
            attr: 'attributes',
            name: 'name',
            seq: 'children',
        );

        self::assertSame(
            [
                'name' => 'catalog',
                'attributes' => [
                    'region' => 'eu',
                    'generated_at' => '2026-04-05T10:00:00Z',
                ],
                'children' => [
                    [
                        'name' => 'offer',
                        'attributes' => [
                            'id' => '1001',
                            'available' => 'true',
                        ],
                        'children' => [
                            [
                                'name' => 'name',
                                'value' => 'Keyboard',
                            ],
                            [
                                'name' => 'price',
                                'value' => '49.90',
                                'attributes' => [
                                    'currency' => 'USD',
                                ],
                            ],
                            [
                                'name' => 'tag',
                                'value' => 'office',
                            ],
                            [
                                'name' => 'tag',
                                'value' => 'usb',
                            ],
                        ],
                    ],
                    [
                        'name' => 'offer',
                        'attributes' => [
                            'id' => '1002',
                            'available' => 'false',
                        ],
                        'children' => [
                            [
                                'name' => 'name',
                                'value' => 'Mouse',
                            ],
                            [
                                'name' => 'price',
                                'value' => '19.90',
                                'attributes' => [
                                    'currency' => 'USD',
                                ],
                            ],
                            [
                                'name' => 'tag',
                                'value' => 'gaming',
                            ],
                        ],
                    ],
                ],
            ],
            $converter->toHierarchyOfElements(
                '',
                XmlFixture::path('hierarchy-catalog.xml')
            )
        );
    }

    public function testToPrettyPrintSupportsCustomKeys(): void
    {
        $converter = new XmlConverter(
            val: 'value',
            attr: 'attributes',
            name: 'name',
            seq: 'children',
        );

        self::assertSame(
            [
                'root' => [
                    'item' => [
                        'value-only',
                        [
                            'attributes' => [
                                'code' => 'A',
                            ],
                        ],
                        [
                            'value' => 'value-and-attributes',
                            'attributes' => [
                                'code' => 'B',
                            ],
                        ],
                        [],
                    ],
                ],
            ],
            $converter->toPrettyPrint(
                '',
                XmlFixture::path('repeated-pretty-print.xml')
            )
        );
    }

    public function testRepeatedCallOnSameXmlTextReturnsStableResult(): void
    {
        $converter = new XmlConverter();
        $xmlText = XmlFixture::read('hierarchy-catalog.xml');

        $first = $converter->toHierarchyOfElements($xmlText);
        $second = $converter->toHierarchyOfElements($xmlText);

        self::assertSame($first, $second);
    }

    public function testChangingXmlTextInvalidatesPrettyPrintCache(): void
    {
        $converter = new XmlConverter(
            val: 'value',
            attr: 'attributes',
        );

        self::assertSame(
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

        self::assertSame(
            [
                'price' => [
                    'value' => '19.90',
                    'attributes' => [
                        'currency' => 'USD',
                    ],
                ],
            ],
            $converter->toPrettyPrint(
                '<price currency="USD">19.90</price>'
            )
        );
    }

    public function testChangingXmlUriInvalidatesHierarchyCache(): void
    {
        $converter = new XmlConverter();

        $first = $converter->toHierarchyOfElements(
            '',
            XmlFixture::path('hierarchy-catalog.xml')
        );
        $second = $converter->toHierarchyOfElements(
            '',
            XmlFixture::path('stream-catalog.xml')
        );

        self::assertSame('catalog', $first['n']);
        self::assertCount(2, $first['s']);
        self::assertSame('catalog', $second['n']);
        self::assertCount(3, $second['s']);
    }

    public function testSwitchingBetweenHierarchyAndPrettyPrintDoesNotLeakState(): void
    {
        $converter = new XmlConverter(
            val: 'value',
            attr: 'attributes',
            name: 'name',
            seq: 'children',
        );

        $pretty = $converter->toPrettyPrint(
            '<price currency="USD">129.90</price>'
        );
        $hierarchy = $converter->toHierarchyOfElements(
            '<price currency="USD">129.90</price>'
        );

        self::assertSame(
            [
                'price' => [
                    'value' => '129.90',
                    'attributes' => [
                        'currency' => 'USD',
                    ],
                ],
            ],
            $pretty
        );
        self::assertSame(
            [
                'name' => 'price',
                'value' => '129.90',
                'attributes' => [
                    'currency' => 'USD',
                ],
            ],
            $hierarchy
        );
    }
}
