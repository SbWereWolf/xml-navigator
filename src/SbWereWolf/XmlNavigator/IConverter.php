<?php

declare(strict_types=1);

namespace SbWereWolf\XmlNavigator;

use XMLReader;

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

    /**
     * @param XMLReader $reader
     * @param string $valueIndex index for element value
     * @param string $attributesIndex index for element attributes collection
     * @return array
     */
    public function extractElements(XMLReader $reader): array;

    /**
     * @param array $elems
     * @param string $nameIndex index for element name
     * @param string $valueIndex index for element value
     * @param string $attributesIndex index for attributes collection
     * @param string $elementsIndex index for child elements collection
     * @return array[]
     */
    public function createTheHierarchyOfElements(array $elems): array;

    /**
     * @param array $elems
     * @param string $valueIndex index for element value
     * @param string $attributesIndex index for attributes collection
     * @return array[]
     */
    public function composePrettyPrintByXmlElements(
        array $elems,
    ): array;
}
