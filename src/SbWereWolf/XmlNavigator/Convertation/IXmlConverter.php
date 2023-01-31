<?php

declare(strict_types=1);

namespace SbWereWolf\XmlNavigator\Convertation;

/**
 * Интерфейс для конвертеров XML документов в PHP массивы
 */
interface IXmlConverter
{
    /** Convert xml document into compact array
     * @param string $xmlText The text of XML document
     * @param string $xmlUri Path or link to XML document
     * @return array
     */
    public function toPrettyPrint(
        string $xmlText = '',
        string $xmlUri = '',
    ): array;

    /** Convert xml document into normalized array
     * @param string $xmlText The text of XML document
     * @param string $xmlUri Path or link to XML document
     * @return array
     */
    public function toHierarchyOfElements(
        string $xmlText = '',
        string $xmlUri = '',
    ): array;
}
