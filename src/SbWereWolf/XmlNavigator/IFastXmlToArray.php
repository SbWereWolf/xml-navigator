<?php

declare(strict_types=1);

namespace SbWereWolf\XmlNavigator;

use Generator;
use XMLReader;

interface IFastXmlToArray
{
    public const NAME = 'n';
    public const VALUE = 'v';
    public const ATTRIBUTES = 'a';
    public const SEQUENCE = 's';

    public const VAL = '@value';
    public const ATTR = '@attributes';

    /** Convert xml document into normalized array
     * with call XMLReader::close() on $reader
     * @param string $xmlText The text of XML document
     * @param string $xmlUri Path or link to XML document
     * @param string $name index for element name
     * @param string $val index for element value
     * @param string $attribs index for element attributes collection
     * @param string $elems index for child elements collection
     * @param string|null $encoding The document encoding or NULL
     * @param int $flags A bitmask of the LIBXML_* constants.
     * @return array
     */
    public static function convert(
        string $xmlText = '',
        string $xmlUri = '',
        string $name = IFastXmlToArray::NAME,
        string $val = IFastXmlToArray::VALUE,
        string $attribs = IFastXmlToArray::ATTRIBUTES,
        string $elems = IFastXmlToArray::SEQUENCE,
        string $encoding = null,
        int $flags = LIBXML_BIGLINES | LIBXML_COMPACT,
    ): array;

    /** Convert xml document into compact array
     * @param string $xmlText The text of XML document
     * @param string $xmlUri Path or link to XML document
     * @param string $val index for element value
     * @param string $attribs index for element attributes collection
     * @param string|null $encoding The document encoding or NULL
     * @param int $flags A bitmask of the LIBXML_* constants.
     * @return array
     */
    public static function prettyPrint(
        string $xmlText = '',
        string $xmlUri = '',
        string $val = IFastXmlToArray::VAL,
        string $attribs = IFastXmlToArray::ATTR,
        string $encoding = null,
        int $flags = LIBXML_BIGLINES | LIBXML_COMPACT,
    ): array;

    /** Pull XML element or it value as
     * [`element name` =>
     *     [
     *         0 => element depth,
     *         1 => value of TEXT node, or attributes of ELEMENT node
     *     ]
     * ]
     * @return Generator
     */
    public static function nextElement(XMLReader $reader): Generator;
}