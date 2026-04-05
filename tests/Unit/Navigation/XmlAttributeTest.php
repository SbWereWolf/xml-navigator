<?php

declare(strict_types=1);

namespace Unit\Navigation;

use PHPUnit\Framework\TestCase;
use SbWereWolf\XmlNavigator\Navigation\XmlAttribute;

final class XmlAttributeTest extends TestCase
{
    public function testNameAndValueReturnStoredAttributeData(): void
    {
        $attribute = new XmlAttribute('currency', 'USD');

        self::assertSame('currency', $attribute->name());
        self::assertSame('USD', $attribute->value());
    }
}
