<?php

declare(strict_types=1);

namespace SbWereWolf\XmlNavigator;

use Generator;
use XMLReader;

/**
 * Интерфейс для статического преобразователя XML документа в PHP массив
 */
interface IFastXmlToArray
{
    /** @var string Индекс Имени в нормализованном виде */
    public const NAME = 'n';
    /** @var string Индекс Значения в нормализованном виде */
    public const VALUE = 'v';
    /** @var string Индекс Атрибутов в нормализованном виде */
    public const ATTRIBUTES = 'a';
    /** @var string Индекс Последовательности вложенных элементов
     * в нормализованном виде
     */
    public const SEQUENCE = 's';

    /** @var string Индекс для Значения в формате pretty print */
    public const VAL = '@value';
    /** @var string Индекс для Атрибутов в формате pretty print */
    public const ATTR = '@attributes';

    /** Convert xml document into normalized array
     * with call XMLReader::close() on $reader
     * @param string $xmlText The text of XML document
     * @param string $xmlUri Path or link to XML document
     * @param string $name index for element name
     * @param string $val index for element value
     * @param string $attribs index for element attributes collection
     * @param string $seq index for child elements collection
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
        string $seq = IFastXmlToArray::SEQUENCE,
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
     * @param XMLReader $reader
     * @return Generator
     */
    public static function nextElement(XMLReader $reader): Generator;
}