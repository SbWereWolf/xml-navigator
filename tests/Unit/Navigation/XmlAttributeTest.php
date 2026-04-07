<?php


namespace SbWereWolf\XmlNavigator\Test\Unit\Navigation;

use PHPUnit\Framework\TestCase;
use SbWereWolf\XmlNavigator\Navigation\XmlAttribute;

final class XmlAttributeTest extends TestCase
{
    public function testNameAndValueReturnStoredAttributeData()
    {
        $attribute = new XmlAttribute('currency', 'USD');

        self::assertSame('currency', $attribute->name());
        self::assertSame('USD', $attribute->value());
    }
}
