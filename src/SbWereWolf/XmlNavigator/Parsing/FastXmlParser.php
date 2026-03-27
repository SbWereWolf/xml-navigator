<?php

declare(strict_types=1);

namespace SbWereWolf\XmlNavigator\Parsing;

use Generator;
use SbWereWolf\XmlNavigator\Extraction\HierarchyComposer;
use SbWereWolf\XmlNavigator\Extraction\PrettyPrintComposer;
use SbWereWolf\XmlNavigator\General\Notation;
use XMLReader;

/**
 * Static XML parser with callable to filter elements
 */
class FastXmlParser
{
    /**
     * @param XMLReader $reader
     * @param callable $detectElement
     * @param string $val
     * @param string $attr
     * @param string $name
     * @param string $seq
     * @return Generator
     */
    public static function extractHierarchy(
        XMLReader $reader,
        callable $detectElement,
        string $val = Notation::VALUE,
        string $attr = Notation::ATTRIBUTES,
        string $name = Notation::NAME,
        string $seq = Notation::SEQUENCE
    ): Generator {
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
     * @param callable $detectElement
     * @param string $val
     * @param string $attr
     * @return Generator
     */
    public static function extractPrettyPrint(
        XMLReader $reader,
        callable $detectElement,
        string $val = Notation::VAL,
        string $attr = Notation::ATTR
    ): Generator {
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
     * @param callable $detectElement
     *
     * @return bool
     */
    private static function seekSuitable(
        XMLReader $reader,
        callable $detectElement
    ): bool {
        do {
            if (
                $reader->nodeType === XMLReader::ELEMENT
                && (bool) $detectElement($reader)
            ) {
                return true;
            }
        } while ($reader->read());

        return false;
   }
}
