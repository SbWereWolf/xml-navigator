<?php

declare(strict_types=1);

namespace Unit\Parsing;

use PHPUnit\Framework\TestCase;
use SbWereWolf\XmlNavigator\Parsing\XmlParser;

final class XmlParserTest extends TestCase
{
    public function testExtractHierarchySupportsCustomNotationKeys(): void
    {
        $reader = \XMLReader::XML(
            '<dataset><row id="1"><value>alpha</value></row></dataset>'
        );
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
                static fn(\XMLReader $cursor): bool => $cursor->name === 'row'
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

    public function testExtractPrettyPrintSupportsCustomNotationKeys(): void
    {
        $reader = \XMLReader::XML(
            '<dataset><row id="1"><value>alpha</value></row></dataset>'
        );
        static::assertInstanceOf(\XMLReader::class, $reader);
        $parser = new XmlParser(
            val: 'value',
            attr: 'attributes',
        );

        $actual = iterator_to_array(
            $parser->extractPrettyPrint(
                $reader,
                static fn(\XMLReader $cursor): bool =>
                    $cursor->name === 'row'
            ),
            false
        );

        static::assertSame(
            [
                [
                    'row' => [
                        'attributes' => [
                            'id' => '1',
                        ],
                        'value' => 'alpha',
                    ],
                ],
            ],
            $actual
        );
        $reader->close();
    }
}
