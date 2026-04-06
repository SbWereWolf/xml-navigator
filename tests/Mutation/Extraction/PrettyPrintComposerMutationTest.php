<?php

declare(strict_types=1);

namespace SbWereWolf\XmlNavigator\Test\Mutation\Extraction;

use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use SbWereWolf\XmlNavigator\Extraction\PrettyPrintComposer;
use SbWereWolf\XmlNavigator\Test\Support\XmlFixture;

final class PrettyPrintComposerMutationTest extends TestCase
{
    public function testComposeMovesReaderPastTopLevelEmptyElementWithAttributes(): void
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
                'root' => [
                    '@attributes' => [
                        'attr' => '1',
                    ],
                ],
            ],
            PrettyPrintComposer::compose($reader)
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
            PrettyPrintComposer::class,
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

    public function testComposePreservesMixedContentWithoutAttributes(): void
    {
        $reader = new \XMLReader();
        $reader->XML('<root>lead<child>v</child>tail</root>');

        self::assertSame(
            [
                'root' => [
                    '@value' => 'leadtail',
                    'child' => 'v',
                ],
            ],
            PrettyPrintComposer::compose($reader)
        );

        $reader->close();
    }

    public function testComposeConcatenatesTextAndCdataNodes(): void
    {
        $reader = new \XMLReader();
        $reader->XML('<root>lead<![CDATA[mid]]>tail</root>');

        self::assertSame(
            [
                'root' => 'leadmidtail',
            ],
            PrettyPrintComposer::compose($reader)
        );

        $reader->close();
    }

    public function testComposeSmallCompositeRootReturnsAllTopLevelKeys(): void
    {
        $reader = new \XMLReader();
        $reader->XML('<root attr="1">lead<child>v</child></root>');

        $actual = PrettyPrintComposer::compose($reader);

        self::assertArrayHasKey('root', $actual);
        self::assertSame('1', $actual['root']['@attributes']['attr']);
        self::assertSame('lead', $actual['root']['@value']);
        self::assertSame('v', $actual['root']['child']);

        $reader->close();
    }

    public function testNormalizeValueHandlesEmptyAndAttributeOnlyNodes(): void
    {
        $method = new ReflectionMethod(
            PrettyPrintComposer::class,
            'normalizeValue'
        );
        /** @noinspection PhpExpressionResultUnusedInspection */
        $method->setAccessible(true);

        self::assertSame(
            [],
            $method->invoke(null, [], '', false, [], '@value', '@attributes')
        );
        self::assertSame(
            'lead',
            $method->invoke(null, [], 'lead', true, [], '@value', '@attributes')
        );
        self::assertSame(
            [
                '@attributes' => [
                    'code' => 'A',
                ],
            ],
            $method->invoke(
                null,
                [],
                '',
                false,
                [
                    'code' => 'A',
                ],
                '@value',
                '@attributes'
            )
        );
        self::assertSame(
            [
                '@value' => 'lead',
                '@attributes' => [
                    'code' => 'A',
                ],
            ],
            $method->invoke(
                null,
                [],
                'lead',
                true,
                [
                    'code' => 'A',
                ],
                '@value',
                '@attributes'
            )
        );
    }
}
