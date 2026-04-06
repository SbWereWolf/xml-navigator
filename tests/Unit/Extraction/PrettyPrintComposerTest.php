<?php

declare(strict_types=1);

namespace SbWereWolf\XmlNavigator\Test\Unit\Extraction;

use PHPUnit\Framework\TestCase;
use SbWereWolf\XmlNavigator\Extraction\PrettyPrintComposer;
use SbWereWolf\XmlNavigator\Test\Support\XmlFixture;

final class PrettyPrintComposerTest extends TestCase
{
    public function testComposeBuildsPrettyPrintedTree(): void
    {
        $reader = XmlFixture::readerFromFixture('hierarchy-catalog.xml');

        self::assertSame(
            [
                'catalog' => [
                    '@attributes' => [
                        'region' => 'eu',
                        'generated_at' => '2026-04-05T10:00:00Z',
                    ],
                    'offer' => [
                        [
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
                            'tag' => [
                                'office',
                                'usb',
                            ],
                        ],
                        [
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
                            'tag' => 'gaming',
                        ],
                    ],
                ],
            ],
            PrettyPrintComposer::compose($reader)
        );

        $reader->close();
    }

    public function testComposeReturnsEmptyArrayWhenReaderIsExhausted(): void
    {
        $reader = XmlFixture::readerFromFixture('hierarchy-catalog.xml');

        /** @noinspection PhpStatementHasEmptyBodyInspection */
        while ($reader->read()) {
        }

        self::assertSame([], PrettyPrintComposer::compose($reader));
        $reader->close();
    }

    public function testComposePreservesMixedContentWithAttributesAndChild(): void
    {
        $reader = XmlFixture::readerFromFixture('mixed-content.xml');

        self::assertSame(
            [
                'root' => [
                    '@attributes' => [
                        'attr' => '1',
                    ],
                    '@value' => 'lead',
                    'child' => 'v',
                ],
            ],
            PrettyPrintComposer::compose($reader)
        );

        $reader->close();
    }

    public function testComposeTurnsRepeatedChildTagsIntoList(): void
    {
        $reader = XmlFixture::readerFromFixture('repeated-pretty-print.xml');

        self::assertSame(
            [
                'root' => [
                    'item' => [
                        'value-only',
                        [
                            '@attributes' => [
                                'code' => 'A',
                            ],
                        ],
                        [
                            '@value' => 'value-and-attributes',
                            '@attributes' => [
                                'code' => 'B',
                            ],
                        ],
                        [],
                    ],
                ],
            ],
            PrettyPrintComposer::compose($reader)
        );

        $reader->close();
    }
}
