<?php

declare(strict_types=1);

namespace Unit\Navigation;

use PHPUnit\Framework\TestCase;
use SbWereWolf\XmlNavigator\Navigation\XmlElement;

final class XmlElementTest extends TestCase
{
    public function testMissingLookupsReturnStableNegativeResults(): void
    {
        $element = new XmlElement(
            [
                'n' => 'root',
                'a' => [
                    'known' => 'value',
                ],
                's' => [
                    [
                        'n' => 'child',
                    ],
                ],
            ]
        );

        static::assertFalse($element->hasAttribute('missing'));
        static::assertSame('value', $element->get());
        static::assertSame('', $element->get('missing'));
        static::assertFalse($element->hasElement('missing'));
        static::assertSame([], $element->elements('missing'));
        static::assertSame(
            [],
            iterator_to_array($element->pull('missing'), false)
        );
    }

    public function testSerializeRoundtripRemainsStable(): void
    {
        $payload = [
            'n' => 'root',
            'v' => 'value',
            'a' => [
                'id' => '42',
            ],
            's' => [
                [
                    'n' => 'child',
                ],
            ],
        ];

        $element = new XmlElement($payload);

        static::assertSame($payload, $element->serialize());
        static::assertSame('id', $element->attributes()[0]->name());
        static::assertSame('42', $element->attributes()[0]->value());
    }

    public function testAccessorsWithoutExplicitNamesUseStoredData(): void
    {
        $element = new XmlElement(
            [
                'n' => 'root',
                'v' => 'payload',
                'a' => [
                    'id' => '42',
                    'lang' => 'ru',
                ],
                's' => [
                    [
                        'n' => 'first',
                    ],
                    [
                        'n' => 'second',
                        'v' => 'value',
                    ],
                ],
            ]
        );

        static::assertSame('root', $element->name());
        static::assertSame('payload', $element->value());
        static::assertTrue($element->hasValue());
        static::assertTrue($element->hasAttribute());
        static::assertTrue($element->hasElement());
        static::assertSame('42', $element->get());
        static::assertCount(2, $element->elements());
        static::assertCount(2, iterator_to_array($element->pull(), false));
    }

    public function testGetWithoutNameReturnsEmptyStringWhenAttributesAreAbsent(): void
    {
        $element = new XmlElement(
            [
                'n' => 'root',
            ]
        );

        static::assertSame('', $element->get());
    }

    public function testConstructorRejectsInvalidPayloadShapes(): void
    {
        $invalidPayloads = [
            [
                'v' => 'value',
            ],
            [
                'n' => 'root',
                'v' => 42,
            ],
            [
                'n' => 'root',
                'a' => 'invalid',
            ],
            [
                'n' => 'root',
                'a' => [
                    'id' => 42,
                ],
            ],
            [
                'n' => 'root',
                's' => [
                    'child' => [
                        'n' => 'child',
                    ],
                ],
            ],
            [
                'n' => 'root',
                's' => [
                    123,
                ],
            ],
        ];

        foreach ($invalidPayloads as $payload) {
            try {
                new XmlElement($payload);
                static::fail(
                    'Expected InvalidArgumentException was not thrown.'
                );
            } catch (\InvalidArgumentException $exception) {
                static::assertSame(-666, $exception->getCode());
            }
        }
    }
}
