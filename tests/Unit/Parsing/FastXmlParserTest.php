<?php

declare(strict_types=1);

namespace Unit\Parsing;

use PHPUnit\Framework\TestCase;
use SbWereWolf\XmlNavigator\Parsing\FastXmlParser;
use SbWereWolf\XmlNavigator\Test\Support\XmlFixture;
use XMLReader;

final class FastXmlParserTest extends TestCase
{
    public function testExtractHierarchyStreamsOnlyMatchingElements(): void
    {
        $reader = XmlFixture::readerFromFixture('stream-catalog.xml');

        $actual = iterator_to_array(
            FastXmlParser::extractHierarchy(
                $reader,
                static fn (XMLReader $cursor): bool =>
                    $cursor->nodeType === XMLReader::ELEMENT
                    && $cursor->name === 'offer'
            ),
            false
        );

        $reader->close();

        self::assertSame(
            [
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
                    ],
                ],
            ],
            $actual
        );
    }

    public function testExtractHierarchySupportsCustomNotation(): void
    {
        $reader = XmlFixture::readerFromFixture('stream-catalog.xml');

        $actual = iterator_to_array(
            FastXmlParser::extractHierarchy(
                $reader,
                static fn (XMLReader $cursor): bool => $cursor->name === 'offer',
                'value',
                'attributes',
                'name',
                'children',
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
                    ],
                ],
            ],
            $actual
        );
    }

    public function testExtractHierarchyReturnsEmptyWhenNoElementMatches(): void
    {
        $reader = XmlFixture::readerFromFixture('stream-catalog.xml');

        $actual = iterator_to_array(
            FastXmlParser::extractHierarchy(
                $reader,
                static fn (XMLReader $cursor): bool =>
                    $cursor->name === 'missing'
            ),
            false
        );

        $reader->close();

        self::assertSame([], $actual);
    }

    public function testExtractPrettyPrintStreamsMatchingElements(): void
    {
        $reader = XmlFixture::readerFromFixture('stream-catalog.xml');

        $actual = iterator_to_array(
            FastXmlParser::extractPrettyPrint(
                $reader,
                static fn (XMLReader $cursor): bool =>
                    $cursor->nodeType === XMLReader::ELEMENT
                    && $cursor->name === 'offer'
            ),
            false
        );

        $reader->close();

        self::assertSame(
            [
                [
                    'offer' => [
                        '@attributes' => [
                            'id' => '1001',
                            'available' => 'true',
                        ],
                        'name' => 'Keyboard',
                        'price' => [
                            '@value' => '49.90',
                            '@attributes' => [
                                'currency' => 'USD',
                            ],
                        ],
                    ],
                ],
                [
                    'offer' => [
                        '@attributes' => [
                            'id' => '1002',
                            'available' => 'false',
                        ],
                        'name' => 'Mouse',
                        'price' => [
                            '@value' => '19.90',
                            '@attributes' => [
                                'currency' => 'USD',
                            ],
                        ],
                    ],
                ],
            ],
            $actual
        );
    }

    public function testExtractPrettyPrintReturnsEmptyWhenNoElementMatches(): void
    {
        $reader = XmlFixture::readerFromFixture('stream-catalog.xml');

        $actual = iterator_to_array(
            FastXmlParser::extractPrettyPrint(
                $reader,
                static fn (XMLReader $cursor): bool =>
                    $cursor->name === 'missing'
            ),
            false
        );

        $reader->close();

        self::assertSame([], $actual);
    }
}
