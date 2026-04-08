<?php

namespace SbWereWolf\XmlNavigator\Extraction;

use SbWereWolf\XmlNavigator\General\Notation;
use XMLReader;

/**
 * Converts an XML element into a PHP array
 *
 * @phpstan-import-type PrettyNode from \SbWereWolf\XmlNavigator\Conversion\IFastXmlToArray
 * @phpstan-import-type PrettyNodeValue from \SbWereWolf\XmlNavigator\Conversion\IFastXmlToArray
 * @phpstan-import-type XmlAttributes from \SbWereWolf\XmlNavigator\Conversion\IFastXmlToArray
 * @phpstan-type PrettyChildren array<string, PrettyNodeValue>
 */
class PrettyPrintComposer implements Notation
{
    /**
     * @param XMLReader $reader
     * @param string $valueIndex index for element value
     * @param string $attributesIndex index for attributes collection
     * @return PrettyNode
     */
    public static function compose(
        XMLReader $reader,
        $valueIndex = Notation::VAL,
        $attributesIndex = Notation::ATTR
    ) {
        for ($canRead = true; self::needsReadToReachElement($reader, $canRead);) {
            $canRead = $reader->read();
        }

        if ($reader->nodeType !== XMLReader::ELEMENT) {
            return [];
        }

        if ($reader->isEmptyElement) {
            return self::composeEmptyElement(
                $reader,
                $valueIndex,
                $attributesIndex
            );
        }

        return self::composeElement(
            $reader,
            $valueIndex,
            $attributesIndex
        );
    }

    private static function needsReadToReachElement(
        XMLReader $reader,
        $canRead
    ) {
        if ($reader->nodeType === XMLReader::ELEMENT) {
            return false;
        }

        return $canRead;
    }

    /**
     * @param string $valueIndex
     * @param string $attributesIndex
     * @return PrettyNode
     */
    private static function composeEmptyElement(
        XMLReader $reader,
        $valueIndex,
        $attributesIndex
    ) {
        $name = $reader->name;
        $attributes = self::collectAttributes($reader);
        $reader->read();

        return [
            $name => self::normalizeValue(
                [],
                '',
                false,
                $attributes,
                $valueIndex,
                $attributesIndex
            ),
        ];
    }

    /**
     * @param string $valueIndex
     * @param string $attributesIndex
     * @return PrettyNode
     */
    private static function composeElement(
        XMLReader $reader,
        $valueIndex,
        $attributesIndex
    ) {
        $name = $reader->name;
        $startDepth = $reader->depth;
        $attributes = self::collectAttributes($reader);

        if ($reader->isEmptyElement) {
            return [
                $name => self::normalizeValue(
                    [],
                    '',
                    false,
                    $attributes,
                    $valueIndex,
                    $attributesIndex
                ),
            ];
        }

        /** @var PrettyChildren $children */
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
                /** @var string $childName */
                $childName = self::firstKey($child);
                /** @var PrettyNodeValue $childValue */
                $childValue = $child[$childName];
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
     * @param PrettyChildren $target
     * @param string $childName
     * @param PrettyNodeValue $childValue
     * @param-out PrettyChildren $target
     */
    private static function appendChild(
        array &$target,
        $childName,
        $childValue
    ) {
        if (!array_key_exists($childName, $target)) {
            $target[$childName] = $childValue;
            return;
        }

        if (
            is_array($target[$childName])
            && $target[$childName] !== []
            && self::isList($target[$childName])
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
     * @param PrettyChildren $children
     * @param XmlAttributes $attributes
     * @return PrettyNodeValue
     */
    private static function normalizeValue(
        array $children,
        $value,
        $hasValue,
        array $attributes,
        $valueIndex,
        $attributesIndex
    ) {
        if ($children === []) {
            if ($attributes === []) {
                return $hasValue ? $value : [];
            }

            if (!$hasValue) {
                return [
                    $attributesIndex => $attributes,
                ];
            }

            return [
                $valueIndex => $value,
                $attributesIndex => $attributes,
            ];
        }

        /** @var array<string, PrettyNodeValue> $result */
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
    private static function collectAttributes(XMLReader $reader)
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

    /**
     * @param array<mixed, mixed> $value
     * @return string|int|null
     */
    private static function firstKey(array $value)
    {
        foreach ($value as $key => $_item) {
            return $key;
        }

        return null;
    }

    private static function isList(array $value)
    {
        return $value === array_values($value);
    }
}
