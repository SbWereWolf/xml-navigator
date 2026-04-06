<?php

declare(strict_types=1);

namespace SbWereWolf\XmlNavigator\Test\Mutation\Parsing;

use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use SbWereWolf\XmlNavigator\Parsing\FastXmlParser;
use SbWereWolf\XmlNavigator\Test\Support\XmlFixture;
use XMLReader;

final class FastXmlParserMutationTest extends TestCase
{
    public function testExtractHierarchySingleMatchStaysFinite(): void
    {
        $reader = new XMLReader();
        $reader->XML('<catalog><offer id="1001"><name>Keyboard</name></offer></catalog>');

        $generator = FastXmlParser::extractHierarchy(
            $reader,
            static fn (XMLReader $cursor): bool => $cursor->name === 'offer',
            'value',
            'attributes',
            'name',
            'children',
        );

        self::assertTrue($generator->valid());
        $actual = $generator->current();
        $generator->next();

        $reader->close();

        self::assertSame('offer', $actual['name']);
        self::assertSame('1001', $actual['attributes']['id']);
        self::assertFalse($generator->valid());
    }

    public function testExtractHierarchyDoesNotInvokeDetectorOnNonElementNodes(): void
    {
        $reader = XmlFixture::readerFromFixture('stream-catalog.xml');
        $nonElementCalls = 0;

        $actual = iterator_to_array(
            FastXmlParser::extractHierarchy(
                $reader,
                static function (XMLReader $cursor) use (&$nonElementCalls): bool {
                    if ($cursor->nodeType !== XMLReader::ELEMENT) {
                        $nonElementCalls++;
                    }

                    return $cursor->nodeType === XMLReader::ELEMENT
                        && $cursor->name === 'offer';
                }
            ),
            false
        );

        $reader->close();

        self::assertCount(2, $actual);
        self::assertSame(0, $nonElementCalls);
    }

    public function testExtractHierarchyGeneratorStopsAfterMatchingElement(): void
    {
        $reader = new XMLReader();
        $reader->XML(
            '<catalog><offer id="1001"><name>Keyboard</name></offer><service id="s-1"/></catalog>'
        );

        $generator = FastXmlParser::extractHierarchy(
            $reader,
            static fn (XMLReader $cursor): bool => $cursor->name === 'offer',
            'value',
            'attributes',
            'name',
            'children',
        );

        self::assertTrue($generator->valid());
        self::assertSame(
            [
                'name' => 'offer',
                'attributes' => [
                    'id' => '1001',
                ],
                'children' => [
                    [
                        'name' => 'name',
                        'value' => 'Keyboard',
                    ],
                ],
            ],
            $generator->current()
        );

        $generator->next();
        self::assertFalse($generator->valid());

        $reader->close();
    }

    public function testSeekSuitableMovesReaderToNextMatchingElement(): void
    {
        $method = new ReflectionMethod(FastXmlParser::class, 'seekSuitable');
        /** @noinspection PhpExpressionResultUnusedInspection */
        $method->setAccessible(true);

        $reader = new XMLReader();
        $reader->XML(
            '<catalog><offer id="1001"/><service id="s-1"/></catalog>'
        );

        self::assertTrue(
            $method->invoke(
                null,
                $reader,
                static fn (XMLReader $cursor): bool => $cursor->name === 'service'
            )
        );
        self::assertSame(XMLReader::ELEMENT, $reader->nodeType);
        self::assertSame('service', $reader->name);

        $reader->close();
    }

    public function testSeekSuitableReturnsFalseWhenNothingMatches(): void
    {
        $method = new ReflectionMethod(FastXmlParser::class, 'seekSuitable');
        /** @noinspection PhpExpressionResultUnusedInspection */
        $method->setAccessible(true);

        $reader = new XMLReader();
        $reader->XML('<catalog><offer id="1001"/></catalog>');

        self::assertFalse(
            $method->invoke(
                null,
                $reader,
                static fn (XMLReader $cursor): bool => $cursor->name === 'missing'
            )
        );
        self::assertSame(XMLReader::NONE, $reader->nodeType);

        $reader->close();
    }

    public function testExtractPrettyPrintDoesNotInvokeDetectorOnNonElementNodes(): void
    {
        $reader = XmlFixture::readerFromFixture('stream-catalog.xml');
        $nonElementCalls = 0;

        $actual = iterator_to_array(
            FastXmlParser::extractPrettyPrint(
                $reader,
                static function (XMLReader $cursor) use (&$nonElementCalls): bool {
                    if ($cursor->nodeType !== XMLReader::ELEMENT) {
                        $nonElementCalls++;
                    }

                    return $cursor->nodeType === XMLReader::ELEMENT
                        && $cursor->name === 'offer';
                }
            ),
            false
        );

        $reader->close();

        self::assertCount(2, $actual);
        self::assertSame(0, $nonElementCalls);
    }

    public function testExtractPrettyPrintGeneratorStopsAfterMatchingElement(): void
    {
        $reader = new XMLReader();
        $reader->XML(
            '<catalog><offer id="1001">lead<child>v</child>tail</offer><service id="s-1"/></catalog>'
        );

        $generator = FastXmlParser::extractPrettyPrint(
            $reader,
            static fn (XMLReader $cursor): bool => $cursor->name === 'offer',
            'value',
            'attributes',
        );

        self::assertTrue($generator->valid());
        self::assertSame(
            [
                'offer' => [
                    'attributes' => [
                        'id' => '1001',
                    ],
                    'value' => 'leadtail',
                    'child' => 'v',
                ],
            ],
            $generator->current()
        );

        $generator->next();
        self::assertFalse($generator->valid());

        $reader->close();
    }
}
