<?php

declare(strict_types=1);

namespace Performance;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use SbWereWolf\XmlNavigator\Convertation\FastXmlToArray;
use SbWereWolf\XmlNavigator\Convertation\XmlConverter;
use SbWereWolf\XmlNavigator\Extraction\HierarchyComposer;
use SbWereWolf\XmlNavigator\Extraction\PrettyPrintComposer;
use SbWereWolf\XmlNavigator\Navigation\XmlAttribute;
use SbWereWolf\XmlNavigator\Navigation\XmlElement;
use SbWereWolf\XmlNavigator\Parsing\FastXmlParser;
use XMLReader;
use function SbWereWolf\XmlNavigator\Bench\generateFixturePack;
use function SbWereWolf\XmlNavigator\Bench\runAcceptanceBenchmark;

require_once __DIR__ . '/BenchmarkSupport.php';

#[CoversNothing]
final class PerformanceParityTest extends TestCase
{
    private const STRUCTURED_XML = <<<'XML'
<doc attrib="a" option="o">
    <valuable>element value</valuable>
    <complex>
        <a empty=""/>
        <b val="x"/>
        <b val="y"/>
        <c>0</c>
        <c v="o"/>
        <c/>
    </complex>
</doc>
XML;

    private const STREAM_XML = <<<'XML'
<Collection>
    <One attr="x">first</One>
    <Other attr="y"/>
    <One attr="z">second</One>
</Collection>
XML;

    private const EXPECTED_HIERARCHY = [
        'n' => 'doc',
        'a' => [
            'attrib' => 'a',
            'option' => 'o',
        ],
        's' => [
            [
                'n' => 'valuable',
                'v' => 'element value',
            ],
            [
                'n' => 'complex',
                's' => [
                    [
                        'n' => 'a',
                        'a' => [
                            'empty' => '',
                        ],
                    ],
                    [
                        'n' => 'b',
                        'a' => [
                            'val' => 'x',
                        ],
                    ],
                    [
                        'n' => 'b',
                        'a' => [
                            'val' => 'y',
                        ],
                    ],
                    [
                        'n' => 'c',
                        'v' => '0',
                    ],
                    [
                        'n' => 'c',
                        'a' => [
                            'v' => 'o',
                        ],
                    ],
                    [
                        'n' => 'c',
                    ],
                ],
            ],
        ],
    ];

    private const EXPECTED_PRETTY = [
        'doc' => [
            '@attributes' => [
                'attrib' => 'a',
                'option' => 'o',
            ],
            'valuable' => 'element value',
            'complex' => [
                'a' => [
                    '@attributes' => [
                        'empty' => '',
                    ],
                ],
                'b' => [
                    [
                        '@attributes' => [
                            'val' => 'x',
                        ],
                    ],
                    [
                        '@attributes' => [
                            'val' => 'y',
                        ],
                    ],
                ],
                'c' => [
                    '0',
                    [
                        '@attributes' => [
                            'v' => 'o',
                        ],
                    ],
                    [],
                ],
            ],
        ],
    ];

    private const EXPECTED_STREAM = [
        [
            'n' => 'One',
            'v' => 'first',
            'a' => [
                'attr' => 'x',
            ],
        ],
        [
            'n' => 'One',
            'v' => 'second',
            'a' => [
                'attr' => 'z',
            ],
        ],
    ];

    public function testHierarchyParity(): void
    {
        self::assertSame(
            self::EXPECTED_HIERARCHY,
            FastXmlToArray::convert(self::STRUCTURED_XML)
        );

        $converter = new XmlConverter();
        self::assertSame(
            self::EXPECTED_HIERARCHY,
            $converter->toHierarchyOfElements(self::STRUCTURED_XML)
        );
    }

    public function testPrettyPrintParity(): void
    {
        self::assertSame(
            self::EXPECTED_PRETTY,
            FastXmlToArray::prettyPrint(self::STRUCTURED_XML)
        );

        $reader = XMLReader::XML(self::STRUCTURED_XML);
        self::assertInstanceOf(XMLReader::class, $reader);
        self::assertSame(
            self::EXPECTED_PRETTY,
            PrettyPrintComposer::compose($reader)
        );
        $reader->close();
    }

    public function testStreamExtractionParity(): void
    {
        $reader = XMLReader::XML(self::STREAM_XML);
        self::assertInstanceOf(XMLReader::class, $reader);
        $extractor = FastXmlParser::extractHierarchy(
            $reader,
            static fn (XMLReader $cursor): bool =>
                $cursor->nodeType === XMLReader::ELEMENT
                && $cursor->name === 'One'
        );
        $actual = iterator_to_array($extractor, false);
        $reader->close();

        self::assertSame(self::EXPECTED_STREAM, $actual);
    }

    public function testXmlElementScalarParity(): void
    {
        $xmlElement = new XmlElement(FastXmlToArray::convert(self::STRUCTURED_XML));

        self::assertSame('doc', $xmlElement->name());
        self::assertSame('', $xmlElement->value());
        self::assertTrue($xmlElement->hasAttribute('attrib'));
        self::assertSame('a', $xmlElement->get('attrib'));

        $attributes = $xmlElement->attributes();
        self::assertCount(2, $attributes);
        self::assertContainsOnlyInstancesOf(XmlAttribute::class, $attributes);
        self::assertSame('attrib', $attributes[0]->name());
        self::assertSame('a', $attributes[0]->value());
        self::assertSame('option', $attributes[1]->name());
        self::assertSame('o', $attributes[1]->value());
        self::assertSame('a', $xmlElement->get());
    }

    public function testXmlElementTraversalParity(): void
    {
        $xmlElement = new XmlElement(FastXmlToArray::convert(self::STRUCTURED_XML));

        self::assertTrue($xmlElement->hasElement('complex'));

        $names = array_map(
            static fn (XmlElement $element): string => $element->name(),
            $xmlElement->elements()
        );
        self::assertSame(['valuable', 'complex'], $names);

        $complex = $xmlElement->pull('complex')->current();
        self::assertInstanceOf(XmlElement::class, $complex);
        self::assertSame(
            [
                'n' => 'complex',
                's' => [
                    [
                        'n' => 'a',
                        'a' => [
                            'empty' => '',
                        ],
                    ],
                    [
                        'n' => 'b',
                        'a' => [
                            'val' => 'x',
                        ],
                    ],
                    [
                        'n' => 'b',
                        'a' => [
                            'val' => 'y',
                        ],
                    ],
                    [
                        'n' => 'c',
                        'v' => '0',
                    ],
                    [
                        'n' => 'c',
                        'a' => [
                            'v' => 'o',
                        ],
                    ],
                    [
                        'n' => 'c',
                    ],
                ],
            ],
            $complex->serialize()
        );

        $bNames = [];
        foreach ($complex->pull('b') as $bElement) {
            self::assertInstanceOf(XmlElement::class, $bElement);
            $bNames[] = $bElement->get('val');
        }

        self::assertSame(['x', 'y'], $bNames);
    }

    public function testXmlElementSerializeParity(): void
    {
        $hierarchy = FastXmlToArray::convert(self::STRUCTURED_XML);
        $xmlElement = new XmlElement($hierarchy);

        self::assertSame($hierarchy, $xmlElement->serialize());
    }

    public function testHierarchyComposerLeavesReaderReadyForNextSibling(): void
    {
        $reader = XMLReader::XML(
            '<Collection><One attr="x">first</One><Two>next</Two></Collection>'
        );
        self::assertInstanceOf(XMLReader::class, $reader);

        while ($reader->nodeType !== XMLReader::ELEMENT && $reader->read()) {
        }
        self::assertSame('Collection', $reader->name);
        $reader->read();
        while ($reader->nodeType !== XMLReader::ELEMENT && $reader->read()) {
        }

        self::assertSame(
            [
                'n' => 'One',
                'v' => 'first',
                'a' => [
                    'attr' => 'x',
                ],
            ],
            HierarchyComposer::compose($reader)
        );

        while ($reader->nodeType !== XMLReader::ELEMENT && $reader->read()) {
        }
        self::assertSame('Two', $reader->name);
        $reader->close();
    }

    public function testPrettyPrintComposerLeavesReaderReadyForNextSibling(): void
    {
        $reader = XMLReader::XML(
            '<Collection><One attr="x">first</One><Two>next</Two></Collection>'
        );
        self::assertInstanceOf(XMLReader::class, $reader);

        while ($reader->nodeType !== XMLReader::ELEMENT && $reader->read()) {
        }
        self::assertSame('Collection', $reader->name);
        $reader->read();
        while ($reader->nodeType !== XMLReader::ELEMENT && $reader->read()) {
        }

        self::assertSame(
            [
                'One' => [
                    '@value' => 'first',
                    '@attributes' => [
                        'attr' => 'x',
                    ],
                ],
            ],
            PrettyPrintComposer::compose($reader)
        );

        while ($reader->nodeType !== XMLReader::ELEMENT && $reader->read()) {
        }
        self::assertSame('Two', $reader->name);
        $reader->close();
    }

    public function testBenchmarkReportSchema(): void
    {
        $dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'xml-bench-' . uniqid('', true);
        $manifest = generateFixturePack($dir, true, 'smoke');
        $report = runAcceptanceBenchmark($manifest);

        self::assertSame('smoke', $manifest['profile']);
        self::assertArrayHasKey('environment', $report);
        self::assertArrayHasKey('fixtures', $report);
        self::assertArrayHasKey('correctness', $report);
        self::assertArrayHasKey('results', $report);
        self::assertArrayHasKey('uc1_count', $report['correctness']);
        self::assertIsArray($report['results']);
        self::assertNotEmpty($report['results']);
        self::assertSame(
            'UC-1 FastXmlParser::extractHierarchy stream-large',
            $report['results'][0]['label']
        );
    }
}
