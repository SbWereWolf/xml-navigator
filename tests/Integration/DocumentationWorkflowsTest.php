<?php


namespace SbWereWolf\XmlNavigator\Test\Integration;

use PHPUnit\Framework\TestCase;
use SbWereWolf\XmlNavigator\Conversion\FastXmlToArray;
use SbWereWolf\XmlNavigator\Navigation\XmlElement;
use SbWereWolf\XmlNavigator\Parsing\FastXmlParser;
use SbWereWolf\XmlNavigator\Test\Support\XmlFixture;
use XMLReader;

final class DocumentationWorkflowsTest extends TestCase
{
    public function testStreamLargeXmlExtractOnlyOffersAsHierarchy()
    {
        $reader = XmlFixture::readerFromFixture('stream-catalog.xml');

        $offers = FastXmlParser::extractHierarchy(
            $reader,
            static function (XMLReader $cursor){
                return $cursor->nodeType === XMLReader::ELEMENT
                    && $cursor->name === 'offer';
            }
        );

        self::assertTrue($offers->valid());
        $firstOffer = $offers->current();
        $offers->next();

        self::assertTrue($offers->valid());
        $secondOffer = $offers->current();
        $offers->next();

        $reader->close();

        self::assertFalse($offers->valid());
        self::assertSame('offer', $firstOffer['n']);
        self::assertSame('1001', $firstOffer['a']['id']);
        self::assertSame('Keyboard', $firstOffer['s'][0]['v']);
        self::assertSame('Mouse', $secondOffer['s'][0]['v']);
    }

    public function testStreamLargeXmlWithCustomHierarchyKeys()
    {
        $reader = XmlFixture::readerFromFixture('stream-catalog.xml');

        $offers = FastXmlParser::extractHierarchy(
            $reader,
            static function (XMLReader $cursor){
                return $cursor->nodeType === XMLReader::ELEMENT
                    && $cursor->name === 'offer';
            },
            'value',
            'attributes',
            'name',
            'children'
        );

        self::assertTrue($offers->valid());
        $firstOffer = $offers->current();
        $offers->next();

        self::assertTrue($offers->valid());
        $secondOffer = $offers->current();
        $offers->next();

        $reader->close();

        self::assertFalse($offers->valid());
        self::assertSame('offer', $firstOffer['name']);
        self::assertSame('1001', $firstOffer['attributes']['id']);
        self::assertSame('Keyboard', $firstOffer['children'][0]['value']);
        self::assertSame('Mouse', $secondOffer['children'][0]['value']);
    }

    public function testConvertWholeDocumentAndNavigateWithXmlElement()
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
                static function (XmlElement $tag){
                    return $tag->value();
                },
                $firstOffer->elements('tag')
            )
        );
    }
}
