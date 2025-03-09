<?php

declare(strict_types=1);

namespace SbWereWolf\XmlNavigator\Convertation;

use SbWereWolf\XmlNavigator\General\Notation;

/**
 * Интерфейс для статического преобразователя XML документа в PHP массив
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
     * @param int $flags A bitmask of the LIBXML_* constants.
     * @return array<string,string|array<string,string>>
     */
    public static function convert(
        string $xmlText = '',
        string $xmlUri = '',
        string $val = Notation::VALUE,
        string $attr = Notation::ATTRIBUTES,
        string $name = Notation::NAME,
        string $seq = Notation::SEQUENCE,
        string|null $encoding = null,
        int $flags = LIBXML_BIGLINES | LIBXML_COMPACT,
    ): array;

    /** Convert xml document into compact array
     * @param string $xmlText The text of XML document
     * @param string $xmlUri Path or link to XML document
     * @param string $val index for element value
     * @param string $attr index for element attributes collection
     * @param string|null $encoding The document encoding or NULL
     * @param int $flags A bitmask of the LIBXML_* constants.
     * @return array<string,string|array<string,string>>
     */
    public static function prettyPrint(
        string $xmlText = '',
        string $xmlUri = '',
        string $val = Notation::VAL,
        string $attr = Notation::ATTR,
        string|null $encoding = null,
        int $flags = LIBXML_BIGLINES | LIBXML_COMPACT,
    ): array;
}
