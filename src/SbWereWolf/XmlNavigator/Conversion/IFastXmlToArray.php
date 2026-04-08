<?php

namespace SbWereWolf\XmlNavigator\Conversion;

use SbWereWolf\XmlNavigator\General\Notation;

/**
 * Contract for static XML document converters that return PHP arrays
 *
 * @phpstan-type XmlAttributes array<string, string>
 * @phpstan-type HierarchyNode array<string, string|XmlAttributes|list<array<string, mixed>>>
 * @phpstan-type PrettyNodeValue string|XmlAttributes|array<string, mixed>|list<array<string, mixed>|string>
 * @phpstan-type PrettyNode array<string, PrettyNodeValue>
 */
interface IFastXmlToArray
{
    /** Convert xml document into normalized array
     * @param string $xmlText The text of XML document
     * @param string $xmlUri Path or link to XML document
     * @param string $val index for element value
     * @param string $attr index for element attributes collection
     * @param string $name index for element name
     * @param string $seq index for child elements collection
     * @param string|null $encoding The document encoding or NULL
     * @param int|null $flags A bitmask of the LIBXML_* constants.
     * @return HierarchyNode
     */
    public static function convert(
        $xmlText = '',
        $xmlUri = '',
        $val = Notation::VALUE,
        $attr = Notation::ATTRIBUTES,
        $name = Notation::NAME,
        $seq = Notation::SEQUENCE,
        $encoding = null,
        $flags = null
    );

    /** Convert xml document into compact array
     * @param string $xmlText The text of XML document
     * @param string $xmlUri Path or link to XML document
     * @param string $val index for element value
     * @param string $attr index for element attributes collection
     * @param string|null $encoding The document encoding or NULL
     * @param int|null $flags A bitmask of the LIBXML_* constants.
     * @return PrettyNode
     */
    public static function prettyPrint(
        $xmlText = '',
        $xmlUri = '',
        $val = Notation::VAL,
        $attr = Notation::ATTR,
        $encoding = null,
        $flags = null
    );
}
