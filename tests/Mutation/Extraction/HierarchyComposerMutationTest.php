<?php

declare(strict_types=1);

namespace SbWereWolf\XmlNavigator\Test\Mutation\Extraction;

use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use SbWereWolf\XmlNavigator\Extraction\HierarchyComposer;
use SbWereWolf\XmlNavigator\Test\Support\XmlFixture;

final class HierarchyComposerMutationTest extends TestCase
{
    public function testComposeMovesReaderPastTopLevelEmptyElement(): void
    {
        $reader = XmlFixture::readerFromFixture('empty-elements.xml');

        while ($reader->read()) {
            if (
                $reader->nodeType === \XMLReader::ELEMENT
                && $reader->name === 'root'
            ) {
                break;
            }
        }

        self::assertSame(
            [
                'n' => 'root',
                'a' => [
                    'attr' => '1',
                ],
            ],
            HierarchyComposer::compose($reader)
        );
        /** @noinspection PhpStatementHasEmptyBodyInspection */
        while ($reader->nodeType !== \XMLReader::ELEMENT && $reader->read()) {
        }
        self::assertSame('next', $reader->name);

        $reader->close();
    }

    public function testNeedsReadToReachElementDependsOnReaderStateAndCanReadFlag(): void
    {
        $method = new ReflectionMethod(
            HierarchyComposer::class,
            'needsReadToReachElement'
        );
        /** @noinspection PhpExpressionResultUnusedInspection */
        $method->setAccessible(true);

        $reader = new \XMLReader();
        $reader->XML('<root/>');

        self::assertTrue($method->invoke(null, $reader, true));
        self::assertFalse($method->invoke(null, $reader, false));

        $reader->read();

        self::assertFalse($method->invoke(null, $reader, true));

        $reader->close();
    }

    public function testComposeKeepsTailTextAfterChildElement(): void
    {
        $reader = new \XMLReader();
        $reader->XML('<root>lead<child>v</child>tail</root>');

        $actual = HierarchyComposer::compose($reader);

        self::assertSame('root', $actual['n']);
        self::assertSame('leadtail', $actual['v']);
        self::assertCount(1, $actual['s']);
        self::assertSame('child', $actual['s'][0]['n']);
        self::assertSame('v', $actual['s'][0]['v']);

        $reader->close();
    }

    public function testComposeConcatenatesTextAndCdataNodes(): void
    {
        $reader = new \XMLReader();
        $reader->XML('<root attr="1">lead<![CDATA[mid]]>tail</root>');

        self::assertSame(
            [
                'n' => 'root',
                'v' => 'leadmidtail',
                'a' => [
                    'attr' => '1',
                ],
            ],
            HierarchyComposer::compose($reader)
        );

        $reader->close();
    }

    public function testComposePreservesMixedContentWithChildElements(): void
    {
        $reader = new \XMLReader();
        $reader->XML('<root attr="1">lead<child>v</child>tail</root>');

        self::assertSame(
            [
                'n' => 'root',
                'v' => 'leadtail',
                'a' => [
                    'attr' => '1',
                ],
                's' => [
                    [
                        'n' => 'child',
                        'v' => 'v',
                    ],
                ],
            ],
            HierarchyComposer::compose($reader)
        );

        /** @noinspection PhpStatementHasEmptyBodyInspection */
        while ($reader->read()) {
        }
        self::assertFalse($reader->read());

        $reader->close();
    }
}
