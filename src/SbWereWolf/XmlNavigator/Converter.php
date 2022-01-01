<?php

declare(strict_types=1);

namespace SbWereWolf\XmlNavigator;

use SimpleXMLElement;

class Converter implements IConverter
{
    private SimpleXMLElement $xml;

    public function __construct(SimpleXMLElement $xml)
    {
        $this->xml = $xml;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $result = [];
        foreach (static::yieldChild($this->xml) as $item) {
            $root = $this->xml->getName();
            $result[$root] = $item;
        }

        return $result;
    }

    private static function yieldChild(
        SimpleXMLElement $xml,
        array $collection = []
    ) {
        $value = trim(strval($xml));
        if ('' !== $value) {
            $collection[static::VALUE] = $value;
        }

        $attributes = $xml->attributes();
        if (0 !== count($attributes)) {
            foreach ($attributes as $attrName => $attrValue) {
                $collection[static::ATTRIBUTES][$attrName] =
                    strval($attrValue);
            }
        }

        $children = [];
        $nodes = $xml->children();
        foreach ($nodes as $nodeName => $nodeValue) {
            foreach (static::yieldChild($nodeValue) as $item) {
                $children[$nodeName][] = $item;
            }
        }
        foreach ($children as $element => $parts) {
            $amount = count($parts);
            if (1 === $amount) {
                $children[$element] = $parts[0];
            }
            if ($amount > 1) {
                unset($children[$element]);
                $children[$element][static::MULTIPLE] = $parts;
            }
        }
        if (0 !== count($children)) {
            $collection[static::ELEMENTS] = $children;
        }

        yield $collection;
    }
}
