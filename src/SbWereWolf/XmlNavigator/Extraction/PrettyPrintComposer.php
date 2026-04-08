<?php

declare(strict_types=1);

namespace SbWereWolf\XmlNavigator\Extraction;

use SbWereWolf\XmlNavigator\General\Notation;
use XMLReader;

/**
 * Converts an XML element into a PHP array
 */
class PrettyPrintComposer implements Notation
{
    /**
     * @param XMLReader $reader
     * @param string $valueIndex index for element value
     * @param string $attributesIndex index for attributes collection
     * @return array<string, mixed>
     */
    public static function compose(
        XMLReader $reader,
        string $valueIndex = Notation::VAL,
        string $attributesIndex = Notation::ATTR
    ): array {
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
        bool $canRead
    ): bool {
        if ($reader->nodeType === XMLReader::ELEMENT) {
            return false;
        }

        return $canRead;
    }

    /**
     * @param string $valueIndex
     * @param string $attributesIndex
     * @return array<string, mixed>
     */
    private static function composeEmptyElement(
        XMLReader $reader,
        string $valueIndex,
        string $attributesIndex
    ): array {
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
     * @return array<string, mixed>
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

        /** @var array<string, mixed> $children */
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

            if ($reader->nodeType === XMLReader::END_ELEMENT) {
                if ($reader->depth === $startDepth) {
                    break;
                }
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
     * @param array<string, mixed> $target
     * @param string $childName
     * @param mixed $childValue
     * @param-out array<string, mixed> $target
     */
    private static function appendChild(
        array &$target,
        string $childName,
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
     * @param array<string, mixed> $children
     * @param array<string, string> $attributes
     * @return mixed
     */
    private static function normalizeValue(
        array $children,
        string $value,
        bool $hasValue,
        array $attributes,
        string $valueIndex,
        string $attributesIndex
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

        /** @var array<string, mixed> $result */
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

    /**
     * @param array<mixed, mixed> $value
     */
    private static function isList(array $value): bool
    {
        return $value === array_values($value);
    }
}
