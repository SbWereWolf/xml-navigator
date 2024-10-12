<?php

declare(strict_types=1);

namespace SbWereWolf\XmlNavigator;

use XMLReader;

/**
 * Статический конвертор XML элемента в PHP массив
 */
class PrettyPrintComposer
    extends ElementComposer
    implements IElementComposer
{
    /**
     * @param XMLReader $reader
     * @param string $valueIndex index for element value
     * @param string $attributesIndex index for attributes collection
     * @return array[]
     */
    public static function compose(
        XMLReader $reader,
        string $valueIndex = IElementComposer::VAL,
        string $attributesIndex = IElementComposer::ATTR,
    ): array {
        $elems = ElementExtractor::extractElements(
            $reader,
            $valueIndex,
            $attributesIndex,
        );

        /** @noinspection PhpUnnecessaryLocalVariableInspection */
        $result = static::composePrettyPrintByXmlElements(
            $elems,
            $valueIndex,
            $attributesIndex,
        );

        return $result;
    }

    /**
     * @param array $elems
     * @param string $valueIndex
     * @param string $attributesIndex
     * @return array|array[]
     */
    private static function composePrettyPrintByXmlElements(
        array $elems,
        string $valueIndex,
        string $attributesIndex
    ): array {
        $base = static::extractBaseDepth($elems);
        $prev = $base;
        $result = [];
        $ptr = &$result;
        $isMulti = false;
        foreach ($elems as $elem) {
            $data = current($elem);

            $curr = $data[ElementExtractor::DEPTH];
            $name = key($elem);

            $letDoSearch = $prev !== $curr;
            if ($letDoSearch) {
                $ptr = &$result;
                for ($d = $base; $d < $curr; $d++) {
                    $end = 0;
                    if (count($ptr)) {
                        end($ptr);
                        $end = key($ptr);
                    }
                    if ($isMulti && ($d + 1) === $curr) {
                        $ptr = &$ptr[$end];
                        end($ptr);
                        $end = key($ptr);
                    }

                    $ptr = &$ptr[$end];
                }
            }

            $new = [];
            if (
                isset($data[$valueIndex]) &&
                !isset($data[$attributesIndex])
            ) {
                $new = $data[$valueIndex];
            }
            if (
                isset($data[$valueIndex]) &&
                isset(
                    $data[$attributesIndex]
                )
            ) {
                $new[$valueIndex] = $data[$valueIndex];
            }
            if (isset($data[$attributesIndex])) {
                $new[$attributesIndex] = $data[$attributesIndex];
            }

            /* Order of IF operators is most important */
            $isMulti = false;
            if (
                key_exists($name, $ptr) &&
                is_array($ptr[$name]) &&
                key_exists(0, $ptr[$name])
            ) {
                $ptr[$name][] = $new;
                $isMulti = true;
            }

            if (
                key_exists($name, $ptr) &&
                (
                    !is_array($ptr[$name]) ||
                    !key_exists(0, $ptr[$name])
                )
            ) {
                $first = $ptr[$name];
                $ptr[$name] = [];
                $isStr = is_string($first);
                if ($isStr) {
                    $ptr[$name][] = [$valueIndex => $first];
                }
                if (!$isStr) {
                    $ptr[$name][] = $first;
                }

                $ptr[$name][] = $new;
                $isMulti = true;
            }

            if (!key_exists($name, $ptr)) {
                $ptr[$name] = $new;
            }

            $prev = $curr;
        }
        return $result;
    }
}