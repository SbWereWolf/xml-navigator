<?php

declare(strict_types=1);

namespace SbWereWolf\XmlNavigator\Test\Mutation\Parsing;

use PHPUnit\Framework\TestCase;
use SbWereWolf\XmlNavigator\Parsing\XmlParser;
use XMLReader;

final class XmlParserMutationTest extends TestCase
{
    public function testExtractHierarchyFirstYieldMatchesRequestedElement(): void
    {
        $reader = new \XMLReader();
        $reader->XML('<catalog><offer id="1001"><name>Keyboard</name></offer></catalog>');
        $parser = new XmlParser(
            val: 'value',
            attr: 'attributes',
            name: 'name',
            seq: 'children',
        );

        $generator = $parser->extractHierarchy(
            $reader,
            static fn (XMLReader $cursor): bool => $cursor->name === 'offer'
        );

        self::assertTrue($generator->valid());

        $first = $generator->current();

        $reader->close();

        self::assertSame('offer', $first['name']);
        self::assertSame('1001', $first['attributes']['id']);
        self::assertSame('Keyboard', $first['children'][0]['value']);
    }

    public function testExtractHierarchyUsesConfiguredNotationForSingleMatch(): void
    {
        $reader = new \XMLReader();
        $reader->XML('<catalog><offer id="1001"><name>Keyboard</name></offer></catalog>');
        $parser = new XmlParser(
            val: 'value',
            attr: 'attributes',
            name: 'name',
            seq: 'children',
        );

        $generator = $parser->extractHierarchy(
            $reader,
            static fn (XMLReader $cursor): bool => $cursor->name === 'offer'
        );

        self::assertTrue($generator->valid());
        $actual = $generator->current();
        $generator->next();

        $reader->close();

        self::assertSame('offer', $actual['name']);
        self::assertSame('1001', $actual['attributes']['id']);
        self::assertSame('Keyboard', $actual['children'][0]['value']);
        self::assertFalse($generator->valid());
    }

    public function testExtractHierarchyUsesConfiguredNotationForTinyDocument(): void
    {
        $reader = new \XMLReader();
        $reader->XML(
            '<catalog generated_at="2026-04-05T10:00:00Z">' .
            '<offer id="1001"><name>Keyboard</name></offer>' .
            '</catalog>'
        );
        $parser = new XmlParser(
            val: 'value',
            attr: 'attributes',
            name: 'name',
            seq: 'children',
        );

        $actual = iterator_to_array(
            $parser->extractHierarchy(
                $reader,
                static fn (XMLReader $cursor): bool => $cursor->name === 'offer'
            ),
            false
        );

        $reader->close();

        self::assertSame(
            [
                [
                    'name' => 'offer',
                    'attributes' => [
                        'id' => '1001',
                    ],
                    'children' => [
                        [
                            'name' => 'name',
                            'value' => 'Keyboard',
                        ],
                    ],
                ],
            ],
            $actual
        );
    }

    public function testExtractHierarchyGeneratorStopsAfterSingleMatch(): void
    {
        $reader = new \XMLReader();
        $reader->XML(
            '<catalog><offer id="1001"><name>Keyboard</name></offer><service id="s-1"/></catalog>'
        );
        $parser = new XmlParser(
            val: 'value',
            attr: 'attributes',
            name: 'name',
            seq: 'children',
        );

        $generator = $parser->extractHierarchy(
            $reader,
            static fn (XMLReader $cursor): bool => $cursor->name === 'offer'
        );

        self::assertTrue($generator->valid());
        self::assertSame(
            [
                'name' => 'offer',
                'attributes' => [
                    'id' => '1001',
                ],
                'children' => [
                    [
                        'name' => 'name',
                        'value' => 'Keyboard',
                    ],
                ],
            ],
            $generator->current()
        );

        $generator->next();
        self::assertFalse($generator->valid());

        $reader->close();
    }

    public function testExtractPrettyPrintUsesConfiguredNotationForTinyDocument(): void
    {
        $reader = new \XMLReader();
        $reader->XML(
            '<catalog generated_at="2026-04-05T10:00:00Z">' .
            '<offer id="1001">lead<child>v</child>tail</offer>' .
            '</catalog>'
        );
        $parser = new XmlParser(
            val: 'value',
            attr: 'attributes',
        );

        $actual = iterator_to_array(
            $parser->extractPrettyPrint(
                $reader,
                static fn (XMLReader $cursor): bool => $cursor->name === 'offer'
            ),
            false
        );

        $reader->close();

        self::assertSame(
            [
                [
                    'offer' => [
                        'attributes' => [
                            'id' => '1001',
                        ],
                        'value' => 'leadtail',
                        'child' => 'v',
                    ],
                ],
            ],
            $actual
        );
    }

    public function testExtractPrettyPrintGeneratorStopsAfterSingleMatch(): void
    {
        $reader = new \XMLReader();
        $reader->XML(
            '<catalog><offer id="1001">lead<child>v</child>tail</offer><service id="s-1"/></catalog>'
        );
        $parser = new XmlParser(
            val: 'value',
            attr: 'attributes',
        );

        $generator = $parser->extractPrettyPrint(
            $reader,
            static fn (XMLReader $cursor): bool => $cursor->name === 'offer'
        );

        self::assertTrue($generator->valid());
        self::assertSame(
            [
                'offer' => [
                    'attributes' => [
                        'id' => '1001',
                    ],
                    'value' => 'leadtail',
                    'child' => 'v',
                ],
            ],
            $generator->current()
        );

        $generator->next();
        self::assertFalse($generator->valid());

        $reader->close();
    }
}
