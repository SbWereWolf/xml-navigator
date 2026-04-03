<?php

declare(strict_types=1);

namespace SbWereWolf\XmlNavigator\Extraction;

use SbWereWolf\XmlNavigator\General\Notation;
use XMLReader;

/**
 * Статический конвертер XML элемента в PHP массив
 *
 * @phpstan-import-type HierarchyNode from \SbWereWolf\XmlNavigator\Conversion\IFastXmlToArray
 */
class HierarchyComposer implements Notation
{
    /**
     * @param XMLReader $reader
     * @param string $valueIndex index for element value
     * @param string $attributesIndex index for attributes collection
     * @param string $nameIndex index for element name
     * @param string $elementsIndex index for child elements collection
     * @return HierarchyNode
     */
    public static function compose(
        XMLReader $reader,
        string $valueIndex = Notation::VALUE,
        string $attributesIndex = Notation::ATTRIBUTES,
        string $nameIndex = Notation::NAME,
        string $elementsIndex = Notation::SEQUENCE
    ): array {
        while ($reader->nodeType !== XMLReader::ELEMENT && $reader->read()) {
        }

        if ($reader->nodeType !== XMLReader::ELEMENT) {
            return [];
        }

        $isEmpty = $reader->isEmptyElement;
        $result = self::composeElement(
            $reader,
            $elementsIndex,
            $nameIndex,
            $valueIndex,
            $attributesIndex
        );
        if ($isEmpty) {
            $reader->read();
        }

        return $result;
    }

    /**
     * @param string $elementsIndex
     * @param string $nameIndex
     * @param string $valueIndex
     * @param string $attributesIndex
     * @return HierarchyNode
     */
    private static function composeElement(
        XMLReader $reader,
        string $elementsIndex,
        string $nameIndex,
        string $valueIndex,
        string $attributesIndex
    ): array {
        $startDepth = $reader->depth;
        $result = [
            $nameIndex => $reader->name,
        ];

        $attributes = self::collectAttributes($reader);

        if ($reader->isEmptyElement) {
            if ($attributes !== []) {
                $result[$attributesIndex] = $attributes;
            }
            return $result;
        }

        /** @var list<HierarchyNode> $children */
        $children = [];
        $value = '';
        $hasValue = false;
        while ($reader->read()) {
            if ($reader->nodeType === XMLReader::ELEMENT) {
                $children[] = self::composeElement(
                    $reader,
                    $elementsIndex,
                    $nameIndex,
                    $valueIndex,
                    $attributesIndex
                );
                continue;
            }

            if (
                (
                    $reader->nodeType === XMLReader::TEXT
                    || $reader->nodeType === XMLReader::CDATA
                )
                && $reader->depth === ($startDepth + 1)
            ) {
                $value .= $reader->value;
                $hasValue = true;
                continue;
            }

            if (
                $reader->nodeType === XMLReader::END_ELEMENT
                && $reader->depth === $startDepth
            ) {
                break;
            }
        }

        if ($hasValue) {
            $result[$valueIndex] = $value;
        }
        if ($attributes !== []) {
            $result[$attributesIndex] = $attributes;
        }
        if ($children !== []) {
            $result[$elementsIndex] = $children;
        }

        return $result;
    }

    /**
     * @return array<string,string>
     */
    private static function collectAttributes(XMLReader $reader): array
    {
        $attributes = [];
        while ($reader->moveToNextAttribute()) {
            $attributes[$reader->name] = $reader->value;
        }

        if ($attributes !== []) {
            $reader->moveToElement();
        }

        return $attributes;
    }
}
