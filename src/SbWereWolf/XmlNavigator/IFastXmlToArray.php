<?php

declare(strict_types=1);

namespace SbWereWolf\XmlNavigator;

/**
 * Интерфейс для статического преобразователя XML документа в PHP массив
 */
interface IFastXmlToArray
{
    /** Convert xml document into normalized array
     * with call XMLReader::close() on $reader
     * @param string $xmlText The text of XML document
     * @param string $xmlUri Path or link to XML document
     * @param string $val index for element value
     * @param string $attribs index for element attributes collection
     * @param string $name index for element name
     * @param string $seq index for child elements collection
     * @param string|null $encoding The document encoding or NULL
     * @param int $flags A bitmask of the LIBXML_* constants.
     * @return array
     */
    public static function convert(
        string $xmlText = '',
        string $xmlUri = '',
        string $val = IElementComposer::VALUE,
        string $attribs = IElementComposer::ATTRIBUTES,
        string $name = IElementComposer::NAME,
        string $seq = IElementComposer::SEQUENCE,
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
        string $val = IElementComposer::VAL,
        string $attribs = IElementComposer::ATTR,
        string $encoding = null,
        int $flags = LIBXML_BIGLINES | LIBXML_COMPACT,
    ): array;
}