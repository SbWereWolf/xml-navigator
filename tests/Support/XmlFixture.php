<?php

declare(strict_types=1);

namespace SbWereWolf\XmlNavigator\Test\Support;

use PHPUnit\Framework\Assert;
use XMLReader;

final class XmlFixture
{
    public static function path(string $name): string
    {
        return dirname(__DIR__) . '/Fixtures/Xml/' . $name;
    }

    public static function read(string $name): string
    {
        $content = file_get_contents(self::path($name));
        Assert::assertNotFalse($content);

        return $content;
    }

    public static function readerFromFixture(string $name): XMLReader
    {
        $reader = XMLReader::open(self::path($name));
        Assert::assertInstanceOf(XMLReader::class, $reader);

        return $reader;
    }
}
