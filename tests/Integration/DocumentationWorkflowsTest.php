<?php

declare(strict_types=1);

namespace SbWereWolf\XmlNavigator\Test\Integration;

use PHPUnit\Framework\TestCase;
use SbWereWolf\XmlNavigator\Conversion\FastXmlToArray;
use SbWereWolf\XmlNavigator\Navigation\XmlElement;
use SbWereWolf\XmlNavigator\Parsing\FastXmlParser;
use SbWereWolf\XmlNavigator\Test\Support\XmlFixture;
use XMLReader;

final class DocumentationWorkflowsTest extends TestCase
{
    public function testStreamLargeXmlExtractOnlyOffersAsHierarchy(): void
    {
        $reader = XmlFixture::readerFromFixture('stream-catalog.xml');

        $offers = iterator_to_array(
            FastXmlParser::extractHierarchy(
                $reader,
                static fn (XMLReader $cursor): bool =>
                    $cursor->nodeType === XMLReader::ELEMENT
                    && $cursor->name === 'offer'
            ),
            false
        );

        $reader->close();

        self::assertCount(2, $offers);
        self::assertSame('offer', $offers[0]['n']);
        self::assertSame('1001', $offers[0]['a']['id']);
        self::assertSame('Keyboard', $offers[0]['s'][0]['v']);
        self::assertSame('Mouse', $offers[1]['s'][0]['v']);
    }

    public function testStreamLargeXmlWithCustomHierarchyKeys(): void
    {
        $reader = XmlFixture::readerFromFixture('stream-catalog.xml');

        $offers = iterator_to_array(
            FastXmlParser::extractHierarchy(
                $reader,
                static fn (XMLReader $cursor): bool =>
                    $cursor->nodeType === XMLReader::ELEMENT
                    && $cursor->name === 'offer',
                'value',
                'attributes',
                'name',
                'children',
            ),
            false
        );

        $reader->close();

        self::assertCount(2, $offers);
        self::assertSame('offer', $offers[0]['name']);
        self::assertSame('1001', $offers[0]['attributes']['id']);
        self::assertSame('Keyboard', $offers[0]['children'][0]['value']);
        self::assertSame('Mouse', $offers[1]['children'][0]['value']);
    }

    public function testConvertWholeDocumentAndNavigateWithXmlElement(): void
    {
        $root = new XmlElement(
            FastXmlToArray::convert(
                '',
                XmlFixture::path('hierarchy-catalog.xml')
            )
        );

        $firstOffer = $root->pull('offer')->current();

        self::assertSame('catalog', $root->name());
        self::assertSame('eu', $root->get('region'));
        self::assertTrue($root->hasElement('offer'));
        self::assertInstanceOf(XmlElement::class, $firstOffer);
        self::assertSame('1001', $firstOffer->get('id'));
        self::assertSame(
            ['office', 'usb'],
            array_map(
                static fn (XmlElement $tag): string => $tag->value(),
                $firstOffer->elements('tag')
            )
        );
    }
}
