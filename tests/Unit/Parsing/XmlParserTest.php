<?php

declare(strict_types=1);

namespace Unit\Parsing;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use SbWereWolf\XmlNavigator\Extraction\HierarchyComposer;
use SbWereWolf\XmlNavigator\Extraction\PrettyPrintComposer;
use SbWereWolf\XmlNavigator\Parsing\FastXmlParser;
use SbWereWolf\XmlNavigator\Parsing\XmlParser;

#[CoversClass(XmlParser::class)]
#[UsesClass(FastXmlParser::class)]
#[UsesClass(HierarchyComposer::class)]
#[UsesClass(PrettyPrintComposer::class)]
final class XmlParserTest extends TestCase
{
    public function testExtractHierarchySupportsCustomNotationKeys(): void
    {
        $reader = \XMLReader::XML('<dataset><row id="1"><value>alpha</value></row></dataset>');
        static::assertInstanceOf(\XMLReader::class, $reader);
        $parser = new XmlParser(
            val: 'value',
            attr: 'attributes',
            name: 'name',
            seq: 'children',
        );

        $actual = iterator_to_array(
            $parser->extractHierarchy(
                $reader,
                static fn (\XMLReader $cursor): bool => $cursor->name === 'row'
            ),
            false
        );

        static::assertSame(
            [
                [
                    'name' => 'row',
                    'attributes' => [
                        'id' => '1',
                    ],
                    'children' => [
                        [
                            'name' => 'value',
                            'value' => 'alpha',
                        ],
                    ],
                ],
            ],
            $actual
        );
        $reader->close();
    }
}
