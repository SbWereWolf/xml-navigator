<?php

declare(strict_types=1);

namespace SbWereWolf\XmlNavigator\Test\Unit\Extraction;

use PHPUnit\Framework\TestCase;
use SbWereWolf\XmlNavigator\Extraction\HierarchyComposer;
use SbWereWolf\XmlNavigator\Test\Support\XmlFixture;

final class HierarchyComposerTest extends TestCase
{
    public function testComposeBuildsNestedHierarchy(): void
    {
        $reader = XmlFixture::readerFromFixture('hierarchy-catalog.xml');

        self::assertSame(
            [
                'n' => 'catalog',
                'a' => [
                    'region' => 'eu',
                    'generated_at' => '2026-04-05T10:00:00Z',
                ],
                's' => [
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
                            [
                                'n' => 'tag',
                                'v' => 'office',
                            ],
                            [
                                'n' => 'tag',
                                'v' => 'usb',
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
                            [
                                'n' => 'tag',
                                'v' => 'gaming',
                            ],
                        ],
                    ],
                ],
            ],
            HierarchyComposer::compose($reader)
        );

        $reader->close();
    }

    public function testComposeReturnsEmptyArrayWhenReaderIsExhausted(): void
    {
        $reader = XmlFixture::readerFromFixture('hierarchy-catalog.xml');

        /** @noinspection PhpStatementHasEmptyBodyInspection */
        while ($reader->read()) {
        }

        self::assertSame([], HierarchyComposer::compose($reader));
        $reader->close();
    }
}
