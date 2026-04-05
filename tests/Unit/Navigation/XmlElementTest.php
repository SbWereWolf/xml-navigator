<?php

declare(strict_types=1);

namespace Unit\Navigation;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use SbWereWolf\XmlNavigator\Conversion\FastXmlToArray;
use SbWereWolf\XmlNavigator\Navigation\XmlAttribute;
use SbWereWolf\XmlNavigator\Navigation\XmlElement;
use SbWereWolf\XmlNavigator\Test\Support\XmlFixture;

final class XmlElementTest extends TestCase
{
    public function testHierarchyRoundtripProvidesStableAccessors(): void
    {
        $payload = FastXmlToArray::convert(
            '',
            XmlFixture::path('hierarchy-catalog.xml')
        );
        $element = new XmlElement($payload);

        self::assertSame('catalog', $element->name());
        self::assertSame('', $element->value());
        self::assertFalse($element->hasValue());
        self::assertTrue($element->hasAttribute());
        self::assertTrue($element->hasAttribute('region'));
        self::assertSame('eu', $element->get('region'));
        self::assertSame('eu', $element->get());
        self::assertTrue($element->hasElement());
        self::assertTrue($element->hasElement('offer'));
        self::assertCount(2, $element->elements('offer'));
        self::assertSame($payload, $element->serialize());

        $attributes = $element->attributes();
        self::assertContainsOnlyInstancesOf(XmlAttribute::class, $attributes);
        self::assertSame('region', $attributes[0]->name());
        self::assertSame('eu', $attributes[0]->value());
    }

    public function testNestedElementsCanBePulledAndInspected(): void
    {
        $root = new XmlElement(
            FastXmlToArray::convert(
                '',
                XmlFixture::path('hierarchy-catalog.xml')
            )
        );

        $offer = $root->pull('offer')->current();

        self::assertInstanceOf(XmlElement::class, $offer);
        self::assertSame('offer', $offer->name());
        self::assertSame('', $offer->value());
        self::assertFalse($offer->hasValue());
        self::assertTrue($offer->hasAttribute('id'));
        self::assertSame('1001', $offer->get('id'));
        self::assertTrue($offer->hasElement('name'));
        self::assertTrue($offer->hasElement('price'));

        $names = array_map(
            static fn (XmlElement $element): string => $element->name(),
            $offer->elements()
        );

        self::assertSame(['name', 'price', 'tag', 'tag'], $names);
        self::assertSame(
            ['office', 'usb'],
            array_map(
                static fn (XmlElement $tag): string => $tag->value(),
                $offer->elements('tag')
            )
        );
    }

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

        self::assertFalse($element->hasAttribute('missing'));
        self::assertSame('', $element->get('missing'));
        self::assertFalse($element->hasElement('missing'));
        self::assertSame([], $element->elements('missing'));
        self::assertSame([], iterator_to_array($element->pull('missing'), false));
    }

    public function testGetWithoutNameReturnsEmptyStringWhenAttributesAreAbsent(): void
    {
        $element = new XmlElement(
            [
                'n' => 'root',
            ]
        );

        self::assertSame('', $element->get());
        self::assertFalse($element->hasAttribute());
        self::assertFalse($element->hasElement());
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
                self::fail('Expected InvalidArgumentException was not thrown.');
            } catch (InvalidArgumentException $exception) {
                self::assertSame(-666, $exception->getCode());
            }
        }
    }
}
