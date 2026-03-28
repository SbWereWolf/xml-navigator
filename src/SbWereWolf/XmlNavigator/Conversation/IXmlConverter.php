<?php

declare(strict_types=1);

namespace SbWereWolf\XmlNavigator\Conversation;

/**
 * Интерфейс для конвертеров XML документов в PHP массивы
 *
 * @phpstan-import-type HierarchyNode from IFastXmlToArray
 * @phpstan-import-type PrettyNode from IFastXmlToArray
 */
interface IXmlConverter
{
    /** Convert xml document into compact array
     * @param string $xmlText The text of XML document
     * @param string $xmlUri Path or link to XML document
     * @return PrettyNode
     */
    public function toPrettyPrint(
        string $xmlText = '',
        string $xmlUri = ''
    ): array;

    /** Convert xml document into normalized array
     * @param string $xmlText The text of XML document
     * @param string $xmlUri Path or link to XML document
     * @return HierarchyNode
     */
    public function toHierarchyOfElements(
        string $xmlText = '',
        string $xmlUri = ''
    ): array;
}
