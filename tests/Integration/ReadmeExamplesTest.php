<?php

declare(strict_types=1);

namespace Integration;

use PHPUnit\Framework\TestCase;
use SbWereWolf\XmlNavigator\Conversation\FastXmlToArray;
use SbWereWolf\XmlNavigator\Conversation\XmlConverter;
use SbWereWolf\XmlNavigator\Extraction\HierarchyComposer;
use SbWereWolf\XmlNavigator\Extraction\PrettyPrintComposer;
use SbWereWolf\XmlNavigator\Navigation\IXmlAttribute;
use SbWereWolf\XmlNavigator\Navigation\IXmlElement;
use SbWereWolf\XmlNavigator\Navigation\XmlElement;
use SbWereWolf\XmlNavigator\Parsing\FastXmlParser;
use SbWereWolf\XmlNavigator\Parsing\XmlParser;
use XMLReader;

/**
 * Validates the executable examples shown in README.md.
 */
class ReadmeExamplesTest extends TestCase
{
    private const FEED_XML =
        '<feed generated_at="2026-03-28T09:00:00Z">' .
        '<offer id="206111" available="true">' .
        '<name>USB-C Dock</name>' .
        '<price currency="USD">129.90</price>' .
        '<picture>https://cdn.example.test/1.jpg</picture>' .
        '<picture>https://cdn.example.test/2.jpg</picture>' .
        '</offer>' .
        '</feed>';

    private const FEED_PRETTY_PRINT = [
        'feed' => [
            '@attributes' => [
                'generated_at' => '2026-03-28T09:00:00Z',
            ],
            'offer' => [
                '@attributes' => [
                    'id' => '206111',
                    'available' => 'true',
                ],
                'name' => 'USB-C Dock',
                'price' => [
                    '@value' => '129.90',
                    '@attributes' => [
                        'currency' => 'USD',
                    ],
                ],
                'picture' => [
                    'https://cdn.example.test/1.jpg',
                    'https://cdn.example.test/2.jpg',
                ],
            ],
        ],
    ];

    private const FEED_HIERARCHY = [
        'n' => 'feed',
        'a' => [
            'generated_at' => '2026-03-28T09:00:00Z',
        ],
        's' => [
            [
                'n' => 'offer',
                'a' => [
                    'id' => '206111',
                    'available' => 'true',
                ],
                's' => [
                    [
                        'n' => 'name',
                        'v' => 'USB-C Dock',
                    ],
                    [
                        'n' => 'price',
                        'v' => '129.90',
                        'a' => [
                            'currency' => 'USD',
                        ],
                    ],
                    [
                        'n' => 'picture',
                        'v' => 'https://cdn.example.test/1.jpg',
                    ],
                    [
                        'n' => 'picture',
                        'v' => 'https://cdn.example.test/2.jpg',
                    ],
                ],
            ],
        ],
    ];

    private const STREAM_XML =
        '<catalog>' .
        '<offer id="1001" available="true">' .
        '<name>Keyboard</name>' .
        '<price currency="USD">49.90</price>' .
        '</offer>' .
        '<service id="x1"><name>Warranty</name></service>' .
        '<offer id="1002" available="false">' .
        '<name>Mouse</name>' .
        '<price currency="USD">19.90</price>' .
        '</offer>' .
        '</catalog>';

    private const STREAM_PRETTY_PRINT = [
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
    ];

    private const CATALOG_XML =
        '<catalog region="eu">' .
        '<offer id="1001" available="true">' .
        '<name>Keyboard</name>' .
        '<tag>office</tag>' .
        '<tag>usb</tag>' .
        '</offer>' .
        '<offer id="1002" available="false" />' .
        '</catalog>';

    public function testFastXmlToArrayPrettyPrintExample(): void
    {
        $result = FastXmlToArray::prettyPrint(static::FEED_XML);

        static::assertSame(static::FEED_PRETTY_PRINT, $result);
    }

    public function testFastXmlToArrayConvertExample(): void
    {
        $result = FastXmlToArray::convert(static::FEED_XML);

        static::assertSame(static::FEED_HIERARCHY, $result);
    }

    public function testFastXmlParserExtractPrettyPrintExample(): void
    {
        $path = $this->createTempXmlFile(static::STREAM_XML);

        $reader = XMLReader::open($path);
        $offers = FastXmlParser::extractPrettyPrint(
            $reader,
            static fn (XMLReader $cursor): bool => $cursor->name === 'offer'
        );

        $result = iterator_to_array($offers, false);

        $reader->close();

        static::assertSame(static::STREAM_PRETTY_PRINT, $result);
    }

    public function testXmlElementNavigationExample(): void
    {
        $root = new XmlElement(FastXmlToArray::convert(static::CATALOG_XML));

        static::assertSame('catalog', $root->name());
        static::assertSame('eu', $root->get('region'));
        static::assertTrue($root->hasAttribute('region'));
        static::assertTrue($root->hasElement('offer'));
        static::assertCount(2, $root->elements('offer'));

        /** @var XmlElement $offer */
        $offer = $root->pull('offer')->current();

        static::assertSame('offer', $offer->name());
        static::assertFalse($offer->hasValue());
        static::assertTrue($offer->hasAttribute('id'));
        static::assertTrue($offer->hasElement('tag'));
        static::assertSame('1001', $offer->get('id'));

        $attributePairs = array_map(
            static fn (IXmlAttribute $attribute): array => [
                $attribute->name(),
                $attribute->value(),
            ],
            $offer->attributes()
        );
        static::assertSame(
            [
                ['id', '1001'],
                ['available', 'true'],
            ],
            $attributePairs
        );

        $tagValues = array_map(
            static fn (IXmlElement $tag): string => $tag->value(),
            $offer->elements('tag')
        );
        static::assertSame(['office', 'usb'], $tagValues);

        $snapshot = $offer->serialize();
        $restored = new XmlElement($snapshot);

        static::assertSame('1001', $restored->get('id'));
        static::assertSame($snapshot, $restored->serialize());
    }

    public function testXmlConverterExample(): void
    {
        $converter = new XmlConverter(
            val: 'value',
            attr: 'attributes',
            name: 'name',
            seq: 'children',
        );

        $pretty = $converter->toPrettyPrint(
            '<price currency="USD">129.90</price>'
        );
        $hierarchy = $converter->toHierarchyOfElements(
            '<price currency="USD">129.90</price>'
        );

        static::assertSame(
            [
                'price' => [
                    'value' => '129.90',
                    'attributes' => [
                        'currency' => 'USD',
                    ],
                ],
            ],
            $pretty
        );
        static::assertSame(
            [
                'name' => 'price',
                'value' => '129.90',
                'attributes' => [
                    'currency' => 'USD',
                ],
            ],
            $hierarchy
        );
    }

    public function testXmlParserExample(): void
    {
        $reader = XMLReader::XML(
            '<dataset><row id="1"><value>alpha</value></row><row id="2" /></dataset>'
        );

        $parser = new XmlParser(
            val: 'value',
            attr: 'attributes',
            name: 'name',
            seq: 'children',
        );

        $rows = iterator_to_array(
            $parser->extractHierarchy(
                $reader,
                static fn (XMLReader $cursor): bool => $cursor->name === 'row'
            ),
            false
        );

        static::assertSame(
            [
                [
                    'name' => 'row',
                    'attributes' => [
                        'id' => '1',
                    ],
                    'children' => [
                        [
                            'name' => 'value',
                            'value' => 'alpha',
                        ],
                    ],
                ],
                [
                    'name' => 'row',
                    'attributes' => [
                        'id' => '2',
                    ],
                ],
            ],
            $rows
        );
    }

    public function testLowLevelComposerExample(): void
    {
        $xml = '<root><offer id="1"><name>Keyboard</name></offer><offer id="2"><name>Mouse</name></offer></root>';

        $prettyReader = XMLReader::XML($xml);
        while (
            $prettyReader->read()
            && !(
                $prettyReader->nodeType === XMLReader::ELEMENT
                && $prettyReader->name === 'offer'
                && $prettyReader->getAttribute('id') === '2'
            )
        ) {
        }

        $pretty = PrettyPrintComposer::compose($prettyReader);

        $hierarchyReader = XMLReader::XML($xml);
        while (
            $hierarchyReader->read()
            && !(
                $hierarchyReader->nodeType === XMLReader::ELEMENT
                && $hierarchyReader->name === 'offer'
                && $hierarchyReader->getAttribute('id') === '2'
            )
        ) {
        }

        $hierarchy = HierarchyComposer::compose($hierarchyReader);

        static::assertSame(
            [
                'offer' => [
                    '@attributes' => [
                        'id' => '2',
                    ],
                    'name' => 'Mouse',
                ],
            ],
            $pretty
        );
        static::assertSame(
            [
                'n' => 'offer',
                'a' => [
                    'id' => '2',
                ],
                's' => [
                    [
                        'n' => 'name',
                        'v' => 'Mouse',
                    ],
                ],
            ],
            $hierarchy
        );
    }

    private function createTempXmlFile(string $xml): string
    {
        $path = tempnam(sys_get_temp_dir(), 'xml-navigator-');
        if ($path === false) {
            static::fail('Failed to create a temporary file for README example.');
        }

        file_put_contents($path, $xml);

        return $path;
    }
}
