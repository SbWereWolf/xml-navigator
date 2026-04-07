<?php

namespace SbWereWolf\XmlNavigator\Parsing;

use Generator;
use SbWereWolf\XmlNavigator\Extraction\HierarchyComposer;
use SbWereWolf\XmlNavigator\Extraction\PrettyPrintComposer;
use SbWereWolf\XmlNavigator\General\Notation;
use XMLReader;

/**
 * Static XML parser with callable to filter elements
 *
 * @phpstan-import-type HierarchyNode from \SbWereWolf\XmlNavigator\Conversion\IFastXmlToArray
 * @phpstan-import-type PrettyNode from \SbWereWolf\XmlNavigator\Conversion\IFastXmlToArray
 */
class FastXmlParser
{
    /**
     * @param XMLReader $reader
     * @param callable(XMLReader):bool $detectElement
     * @param string $val
     * @param string $attr
     * @param string $name
     * @param string $seq
     * @return Generator<int, HierarchyNode>
     */
    public static function extractHierarchy(
        XMLReader $reader,
        callable $detectElement,
        $val = Notation::VALUE,
        $attr = Notation::ATTRIBUTES,
        $name = Notation::NAME,
        $seq = Notation::SEQUENCE
    ) {
        while (self::seekSuitable($reader, $detectElement)) {
            yield HierarchyComposer::compose(
                $reader,
                $val,
                $attr,
                $name,
                $seq
            );
        }
    }

    /**
     * @param XMLReader $reader
     * @param callable(XMLReader):bool $detectElement
     * @param string $val
     * @param string $attr
     * @return Generator<int, PrettyNode>
     */
    public static function extractPrettyPrint(
        XMLReader $reader,
        callable $detectElement,
        $val = Notation::VAL,
        $attr = Notation::ATTR
    ) {
        while (self::seekSuitable($reader, $detectElement)) {
            yield PrettyPrintComposer::compose(
                $reader,
                $val,
                $attr
            );
        }
    }

    /**
     * @param XMLReader $reader
     * @param callable(XMLReader):bool $detectElement
     *
     * @return bool
     */
    private static function seekSuitable(
        XMLReader $reader,
        callable $detectElement
    ) {
        do {
            if (
                $reader->nodeType === XMLReader::ELEMENT
                && $detectElement($reader)
            ) {
                return true;
            }
        } while ($reader->read());

        return false;
    }
}
