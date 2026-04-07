<?php

declare(strict_types=1);

namespace SbWereWolf\XmlNavigator\Test\Unit\Parsing;

use PHPUnit\Framework\TestCase;
use SbWereWolf\XmlNavigator\Parsing\XmlParser;
use SbWereWolf\XmlNavigator\Test\Support\XmlFixture;
use XMLReader;

final class XmlParserTest extends TestCase
{
    public function testExtractHierarchyUsesConfiguredNotation(): void
    {
        $reader = XmlFixture::readerFromFixture('stream-catalog.xml');
        $parser = new XmlParser(
            'value',
            'attributes',
            'name',
            'children'
        );

        $actual = iterator_to_array(
            $parser->extractHierarchy(
                $reader,
                static function (XMLReader $cursor): bool {
                    return $cursor->name === 'offer';
                }
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

    public function testExtractHierarchyReusesConfiguredParserAcrossReaders(): void
    {
        $parser = new XmlParser(
            'value',
            'attributes',
            'name',
            'children'
        );

        $firstReader = XmlFixture::readerFromFixture('stream-catalog.xml');
        $secondReader = XmlFixture::readerFromFixture('stream-catalog.xml');

        $first = iterator_to_array(
            $parser->extractHierarchy(
                $firstReader,
                static function (XMLReader $cursor): bool {
                    return $cursor->name === 'offer';
                }
            ),
            false
        );
        $second = iterator_to_array(
            $parser->extractHierarchy(
                $secondReader,
                static function (XMLReader $cursor): bool {
                    return $cursor->name === 'service';
                }
            ),
            false
        );

        $firstReader->close();
        $secondReader->close();

        self::assertCount(2, $first);
        self::assertSame(
            [
                [
                    'name' => 'service',
                    'attributes' => [
                        'id' => 's-1',
                    ],
                    'children' => [
                        [
                            'name' => 'name',
                            'value' => 'Warranty',
                        ],
                    ],
                ],
            ],
            $second
        );
    }

    public function testExtractHierarchyReturnsEmptyWhenNoElementMatches(): void
    {
        $reader = XmlFixture::readerFromFixture('stream-catalog.xml');
        $parser = new XmlParser();

        $actual = iterator_to_array(
            $parser->extractHierarchy(
                $reader,
                static function (XMLReader $cursor): bool {
                    return $cursor->name === 'missing';
                }
            ),
            false
        );

        $reader->close();

        self::assertSame([], $actual);
    }

    public function testExtractPrettyPrintUsesConfiguredNotation(): void
    {
        $reader = XmlFixture::readerFromFixture('stream-catalog.xml');
        $parser = new XmlParser(
            'value',
            'attributes'
        );

        $actual = iterator_to_array(
            $parser->extractPrettyPrint(
                $reader,
                static function (XMLReader $cursor): bool {
                    return $cursor->name === 'offer';
                }
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
                            'available' => 'true',
                        ],
                        'name' => 'Keyboard',
                        'price' => [
                            'value' => '49.90',
                            'attributes' => [
                                'currency' => 'USD',
                            ],
                        ],
                    ],
                ],
                [
                    'offer' => [
                        'attributes' => [
                            'id' => '1002',
                            'available' => 'false',
                        ],
                        'name' => 'Mouse',
                        'price' => [
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
}
