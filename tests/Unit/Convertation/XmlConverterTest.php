<?php

declare(strict_types=1);

namespace Unit\Convertation;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use SbWereWolf\XmlNavigator\Conversation\FastXmlToArray;
use SbWereWolf\XmlNavigator\Conversation\XmlConverter;
use SbWereWolf\XmlNavigator\Extraction\HierarchyComposer;
use SbWereWolf\XmlNavigator\Extraction\PrettyPrintComposer;
use SbWereWolf\XmlNavigator\Parsing\FastXmlParser;

#[CoversClass(XmlConverter::class)]
#[UsesClass(FastXmlToArray::class)]
#[UsesClass(FastXmlParser::class)]
#[UsesClass(HierarchyComposer::class)]
#[UsesClass(PrettyPrintComposer::class)]
final class XmlConverterTest extends TestCase
{
    public function testToPrettyPrintRefreshesWhenXmlChanges(): void
    {
        $converter = new XmlConverter();

        static::assertSame(
            ['root' => ['value' => 'first']],
            $converter->toPrettyPrint('<root><value>first</value></root>')
        );
        static::assertSame(
            ['root' => ['value' => 'second']],
            $converter->toPrettyPrint('<root><value>second</value></root>')
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
            $converter->toPrettyPrint('<price currency="USD">129.90</price>')
        );
        static::assertSame(
            [
                'name' => 'price',
                'value' => '129.90',
                'attributes' => [
                    'currency' => 'USD',
                ],
            ],
            $converter->toHierarchyOfElements('<price currency="USD">129.90</price>')
        );
    }
}
