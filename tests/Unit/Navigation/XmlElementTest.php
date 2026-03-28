<?php

declare(strict_types=1);

namespace Unit\Navigation;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use SbWereWolf\XmlNavigator\Navigation\XmlAttribute;
use SbWereWolf\XmlNavigator\Navigation\XmlElement;

#[CoversClass(XmlElement::class)]
#[CoversClass(XmlAttribute::class)]
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
        static::assertSame('', $element->get('missing'));
        static::assertFalse($element->hasElement('missing'));
        static::assertSame([], $element->elements('missing'));
        static::assertSame([], iterator_to_array($element->pull('missing'), false));
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
}
