<?php

declare(strict_types=1);

namespace SbWereWolf\XmlNavigator\Extraction;

use SbWereWolf\XmlNavigator\General\Notation;
use XMLReader;

/**
 * Статический конвертор XML элемента в PHP массив
 */
class PrettyPrintComposer extends ElementComposer implements Notation
{
    /**
     * @param XMLReader $reader
     * @param string $valueIndex index for element value
     * @param string $attributesIndex index for attributes collection
     * @return array<string,array>
     */
    public static function compose(
        XMLReader $reader,
        string $valueIndex = Notation::VAL,
        string $attributesIndex = Notation::ATTR
    ): array {
        while ($reader->nodeType !== XMLReader::ELEMENT && $reader->read()) {
        }

        if ($reader->nodeType !== XMLReader::ELEMENT) {
            return [];
        }

        $isEmpty = $reader->isEmptyElement;
        $result = self::composeElement(
            $reader,
            $valueIndex,
            $attributesIndex
        );
        if ($isEmpty) {
            $reader->read();
        }

        return $result;
    }

    /**
     * @param string $valueIndex
     * @param string $attributesIndex
     * @return array<string,array>
     */
    private static function composeElement(
        XMLReader $reader,
        string $valueIndex,
        string $attributesIndex
    ): array {
        $name = $reader->name;
        $startDepth = $reader->depth;
        $attributes = self::collectAttributes($reader);

        if ($reader->isEmptyElement) {
            return [$name => self::normalizeValue([], '', false, $attributes, $valueIndex, $attributesIndex)];
        }

        $children = [];
        $value = '';
        $hasValue = false;
        while ($reader->read()) {
            if ($reader->nodeType === XMLReader::ELEMENT) {
                $child = self::composeElement(
                    $reader,
                    $valueIndex,
                    $attributesIndex
                );
                $childName = key($child);
                $childValue = current($child);
                self::appendChild($children, $childName, $childValue);
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

        return [
            $name => self::normalizeValue(
                $children,
                $value,
                $hasValue,
                $attributes,
                $valueIndex,
                $attributesIndex
            ),
        ];
    }

    /**
     * @param array<string,mixed> $target
     * @param mixed $childValue
     */
    private static function appendChild(
        array &$target,
        string $childName,
        mixed $childValue
    ): void {
        if (!array_key_exists($childName, $target)) {
            $target[$childName] = $childValue;
            return;
        }

        if (
            is_array($target[$childName])
            && array_key_exists(0, $target[$childName])
        ) {
            $target[$childName][] = $childValue;
            return;
        }

        $target[$childName] = [
            $target[$childName],
            $childValue,
        ];
    }

    /**
     * @param array<string,mixed> $children
     * @param array<string,string> $attributes
     */
    private static function normalizeValue(
        array $children,
        string $value,
        bool $hasValue,
        array $attributes,
        string $valueIndex,
        string $attributesIndex
    ): mixed {
        if ($children === [] && $attributes === [] && !$hasValue) {
            return [];
        }

        if ($children === [] && $attributes === [] && $hasValue) {
            return $value;
        }

        if ($children === [] && $attributes !== [] && !$hasValue) {
            return [
                $attributesIndex => $attributes,
            ];
        }

        if ($children === [] && $attributes !== [] && $hasValue) {
            return [
                $valueIndex => $value,
                $attributesIndex => $attributes,
            ];
        }

        $result = [];
        if ($attributes !== []) {
            $result[$attributesIndex] = $attributes;
        }
        if ($hasValue) {
            $result[$valueIndex] = $value;
        }
        foreach ($children as $name => $childValue) {
            $result[$name] = $childValue;
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
