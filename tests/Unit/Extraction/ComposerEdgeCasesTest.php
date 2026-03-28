<?php

declare(strict_types=1);

namespace Unit\Extraction;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use SbWereWolf\XmlNavigator\Extraction\HierarchyComposer;
use SbWereWolf\XmlNavigator\Extraction\PrettyPrintComposer;

#[CoversClass(HierarchyComposer::class)]
#[CoversClass(PrettyPrintComposer::class)]
final class ComposerEdgeCasesTest extends TestCase
{
    public function testHierarchyComposerReturnsEmptyArrayWhenReaderIsExhausted(): void
    {
        $reader = \XMLReader::XML('<root/>');
        static::assertInstanceOf(\XMLReader::class, $reader);

        while ($reader->read()) {
        }

        static::assertSame([], HierarchyComposer::compose($reader));
        $reader->close();
    }

    public function testPrettyPrintComposerReturnsEmptyArrayWhenReaderIsExhausted(): void
    {
        $reader = \XMLReader::XML('<root/>');
        static::assertInstanceOf(\XMLReader::class, $reader);

        while ($reader->read()) {
        }

        static::assertSame([], PrettyPrintComposer::compose($reader));
        $reader->close();
    }
}
