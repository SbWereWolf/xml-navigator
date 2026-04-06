<?php

declare(strict_types=1);

namespace SbWereWolf\XmlNavigator\Test\Mutation\Conversion;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use SbWereWolf\XmlNavigator\Conversion\XmlConverter;
use SbWereWolf\XmlNavigator\Test\Support\XmlFixture;

final class XmlConverterMutationTest extends TestCase
{
    public function testSmallHierarchyCallInvalidatesChangedUriImmediately(): void
    {
        $converter = new XmlConverter();

        $first = $converter->toHierarchyOfElements(
            '',
            XmlFixture::path('empty-elements.xml')
        );
        $second = $converter->toHierarchyOfElements(
            '',
            XmlFixture::path('mixed-content.xml')
        );

        self::assertSame('doc', $first['n']);
        self::assertSame('root', $first['s'][0]['n']);
        self::assertSame('root', $second['n']);
        self::assertSame('lead', $second['v']);
    }

    public function testSmallPrettyPrintCallDoesNotReuseDifferentUriState(): void
    {
        $converter = new XmlConverter(
            val: 'value',
            attr: 'attributes',
        );

        $actual = $converter->toPrettyPrint(
            '<root attr="1">lead<child>v</child></root>'
        );

        self::assertSame('1', $actual['root']['attributes']['attr']);
        self::assertSame('lead', $actual['root']['value']);
        self::assertSame('v', $actual['root']['child']);
    }

    public function testConstructorUsesBigLinesAndCompactByDefault(): void
    {
        $constructor = new ReflectionClass(XmlConverter::class);
        $parameter = null;
        foreach ($constructor->getConstructor()->getParameters() as $item) {
            if ($item->getName() === 'flags') {
                $parameter = $item;
                break;
            }
        }

        self::assertSame(
            LIBXML_BIGLINES | LIBXML_COMPACT,
            $parameter?->getDefaultValue()
        );
    }

    public function testClearingPrettyCacheForcesRecalculationWithSameSource(): void
    {
        $converter = new XmlConverter();
        $xmlText = XmlFixture::read('repeated-pretty-print.xml');

        $expected = $converter->toPrettyPrint($xmlText);

        $reflection = new ReflectionClass($converter);
        $prettyXml = $reflection->getProperty('prettyXml');
        $prettyXml->setAccessible(true);
        $prettyXml->setValue($converter, []);

        self::assertSame($expected, $converter->toPrettyPrint($xmlText));
    }

    public function testIsPreviousAcceptsUnchangedXmlText(): void
    {
        $converter = new XmlConverter();
        $reflection = new ReflectionClass($converter);
        $previousXmlText = $reflection->getProperty('previousXmlText');
        $previousXmlText->setAccessible(true);
        $previousXmlText->setValue(
            $converter,
            '<price currency="USD">129.90</price>'
        );

        $method = new ReflectionMethod(XmlConverter::class, 'isPrevious');
        /** @noinspection PhpExpressionResultUnusedInspection */
        $method->setAccessible(true);

        self::assertTrue(
            $method->invoke(
                $converter,
                '<price currency="USD">129.90</price>',
                ''
            )
        );
    }

    public function testIsPreviousRejectsChangedXmlText(): void
    {
        $converter = new XmlConverter();
        $reflection = new ReflectionClass($converter);
        $previousXmlText = $reflection->getProperty('previousXmlText');
        $previousXmlText->setAccessible(true);
        $previousXmlText->setValue(
            $converter,
            '<price currency="USD">129.90</price>'
        );

        $method = new ReflectionMethod(XmlConverter::class, 'isPrevious');
        /** @noinspection PhpExpressionResultUnusedInspection */
        $method->setAccessible(true);

        self::assertFalse(
            $method->invoke(
                $converter,
                '<price currency="USD">19.90</price>',
                ''
            )
        );
    }

    public function testIsPreviousRejectsChangedXmlUriWhenXmlTextIsEmpty(): void
    {
        $converter = new XmlConverter();
        $reflection = new ReflectionClass($converter);
        $previousXmlUri = $reflection->getProperty('previousXmlUri');
        $previousXmlUri->setAccessible(true);
        $previousXmlUri->setValue(
            $converter,
            XmlFixture::path('hierarchy-catalog.xml')
        );

        $method = new ReflectionMethod(XmlConverter::class, 'isPrevious');
        /** @noinspection PhpExpressionResultUnusedInspection */
        $method->setAccessible(true);

        self::assertFalse(
            $method->invoke(
                $converter,
                '',
                XmlFixture::path('stream-catalog.xml')
            )
        );
    }

    public function testIsPreviousKeepsUriOutOfDecisionWhenXmlTextIsPresent(): void
    {
        $converter = new XmlConverter();
        $reflection = new ReflectionClass($converter);
        $previousXmlText = $reflection->getProperty('previousXmlText');
        $previousXmlText->setAccessible(true);
        $previousXmlText->setValue(
            $converter,
            '<price currency="USD">129.90</price>'
        );

        $method = new ReflectionMethod(XmlConverter::class, 'isPrevious');
        /** @noinspection PhpExpressionResultUnusedInspection */
        $method->setAccessible(true);

        self::assertTrue(
            $method->invoke(
                $converter,
                '<price currency="USD">129.90</price>',
                XmlFixture::path('stream-catalog.xml')
            )
        );
    }

    public function testIsPreviousKeepsEmptySourcePreviousAfterUriCall(): void
    {
        $converter = new XmlConverter();
        $reflection = new ReflectionClass($converter);
        $previousXmlUri = $reflection->getProperty('previousXmlUri');
        $previousXmlUri->setAccessible(true);
        $previousXmlUri->setValue(
            $converter,
            XmlFixture::path('hierarchy-catalog.xml')
        );

        $method = new ReflectionMethod(XmlConverter::class, 'isPrevious');
        /** @noinspection PhpExpressionResultUnusedInspection */
        $method->setAccessible(true);

        self::assertTrue(
            $method->invoke(
                $converter,
                '',
                ''
            )
        );
    }
}
