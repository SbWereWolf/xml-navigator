<?php

declare(strict_types=1);

namespace Unit\Extraction;

use PHPUnit\Framework\TestCase;
use SbWereWolf\XmlNavigator\Extraction\HierarchyComposer;
use SbWereWolf\XmlNavigator\Extraction\PrettyPrintComposer;

final class ComposerEdgeCasesTest extends TestCase
{
    public function testPrettyPrintComposerMovesReaderPastTopLevelEmptyElement(): void
    {
        $reader = \XMLReader::XML(
            '<doc><root attr="1"/><next>v</next></doc>'
        );
        static::assertInstanceOf(\XMLReader::class, $reader);

        while ($reader->read()) {
            if (
                $reader->nodeType === \XMLReader::ELEMENT
                && $reader->name === 'root'
            ) {
                break;
            }
        }

        static::assertSame(
            [
                'root' => [
                    '@attributes' => [
                        'attr' => '1',
                    ],
                ],
            ],
            PrettyPrintComposer::compose($reader)
        );
        static::assertSame('next', $reader->name);
        $reader->close();
    }

    public function testPrettyPrintComposerPreservesMixedContentWithAttributes(): void
    {
        $reader = \XMLReader::XML(
            '<root attr="1">lead<child>v</child></root>'
        );
        static::assertInstanceOf(\XMLReader::class, $reader);

        static::assertSame(
            [
                'root' => [
                    '@attributes' => [
                        'attr' => '1',
                    ],
                    '@value' => 'lead',
                    'child' => 'v',
                ],
            ],
            PrettyPrintComposer::compose($reader)
        );
        $reader->close();
    }

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
