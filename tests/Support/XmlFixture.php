<?php


namespace SbWereWolf\XmlNavigator\Test\Support;

use PHPUnit\Framework\Assert;
use XMLReader;

final class XmlFixture
{
    public static function path($name){
        return dirname(__DIR__) . '/Fixtures/Xml/' . $name;
    }

    public static function read($name){
        $content = file_get_contents(self::path($name));
        Assert::assertNotFalse($content);

        return $content;
    }

    public static function readerFromFixture($name){
        $reader = XMLReader::open(self::path($name));
        Assert::assertInstanceOf(XMLReader::class, $reader);

        return $reader;
    }
}
