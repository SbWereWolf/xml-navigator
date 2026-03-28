<?php

declare(strict_types=1);

namespace Unit\Parsing;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use SbWereWolf\XmlNavigator\Extraction\HierarchyComposer;
use SbWereWolf\XmlNavigator\Extraction\PrettyPrintComposer;
use SbWereWolf\XmlNavigator\Parsing\FastXmlParser;

#[CoversClass(FastXmlParser::class)]
#[UsesClass(HierarchyComposer::class)]
#[UsesClass(PrettyPrintComposer::class)]
final class FastXmlParserTest extends TestCase
{
    public function testExtractHierarchyReturnsEmptyWhenNoElementMatches(): void
    {
        $reader = \XMLReader::XML('<catalog><offer id="1"/></catalog>');
        static::assertInstanceOf(\XMLReader::class, $reader);

        $actual = iterator_to_array(
            FastXmlParser::extractHierarchy(
                $reader,
                static fn (\XMLReader $cursor): bool => $cursor->name === 'service'
            ),
            false
        );

        static::assertSame([], $actual);
        $reader->close();
    }

    public function testExtractPrettyPrintReturnsEmptyWhenNoElementMatches(): void
    {
        $reader = \XMLReader::XML('<catalog><offer id="1"/></catalog>');
        static::assertInstanceOf(\XMLReader::class, $reader);

        $actual = iterator_to_array(
            FastXmlParser::extractPrettyPrint(
                $reader,
                static fn (\XMLReader $cursor): bool => $cursor->name === 'service'
            ),
            false
        );

        static::assertSame([], $actual);
        $reader->close();
    }
}
