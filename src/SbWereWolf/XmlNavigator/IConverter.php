<?php

declare(strict_types=1);

namespace SbWereWolf\XmlNavigator;

/**
 * Интерфейс для конвертеров XML документов в PHP массивы
 */
interface IConverter
{
    /** Convert xml document into compact array
     * @param string $xmlText The text of XML document
     * @param string $xmlUri Path or link to XML document
     * @return array
     */
    public function prettyPrint(
        string $xmlText = '',
        string $xmlUri = '',
    ): array;

    /** Convert xml document into normalized array
     * @param string $xmlText The text of XML document
     * @param string $xmlUri Path or link to XML document
     * @return array
     */
    public function xmlStructure(
        string $xmlText = '',
        string $xmlUri = '',
    ): array;
}
