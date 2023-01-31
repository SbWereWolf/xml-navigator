<?php

declare(strict_types=1);

namespace SbWereWolf\XmlNavigator\Extraction;

use SbWereWolf\XmlNavigator\General\Notation;
use XMLReader;

/**
 * Статический конвертор XML элемента в PHP массив
 */
class HierarchyComposer
    extends ElementComposer
    implements Notation
{
    /**
     * @param XMLReader $reader
     * @param string $valueIndex index for element value
     * @param string $attributesIndex index for attributes collection
     * @param string $nameIndex index for element name
     * @param string $elementsIndex index for child elements collection
     * @return array[]
     */
    public static function compose(
        XMLReader $reader,
        string $valueIndex = Notation::VALUE,
        string $attributesIndex = Notation::ATTRIBUTES,
        string $nameIndex = Notation::NAME,
        string $elementsIndex = Notation::SEQUENCE,
    ): array {
        $elems = ElementExtractor::extractElements(
            $reader,
            $valueIndex,
            $attributesIndex,
        );
        /** @noinspection PhpUnnecessaryLocalVariableInspection */
        $result = static::createTheHierarchyOfElements(
            $elems,
            $elementsIndex,
            $nameIndex,
            $valueIndex,
            $attributesIndex,
        );

        return $result;
    }

    /**
     * @param array $elems
     * @param string $elementsIndex
     * @param string $nameIndex
     * @param string $valueIndex
     * @param string $attributesIndex
     * @return array
     */
    private static function createTheHierarchyOfElements(
        array $elems,
        string $elementsIndex,
        string $nameIndex,
        string $valueIndex,
        string $attributesIndex
    ): array {
        $base = static::extractBaseDepth($elems);
        $prev = $base;

        $hierarchy = [$elementsIndex => []];
        $ptr = &$hierarchy[$elementsIndex];
        foreach ($elems as $i => $elem) {
            $data = current($elem);
            /*$logger->debug('будем добавлять элемент' . json_encode($data, JSON_PRETTY_PRINT));*/

            $curr = $data[ElementExtractor::DEPTH];
            /*$logger->debug("уровень элемента `$curr`");*/
            $name = key($elem);
            /*$logger->debug("имя элемента `$name`");*/

            $letDoSearch = $prev !== $curr;
            /*$logger->debug('надо ли искать последовательность элементов' . json_encode($letDoSearch, JSON_PRETTY_PRINT));*/
            if ($letDoSearch) {
                $ptr = &$hierarchy[$elementsIndex];
                /*$logger->debug('перевели указатель на корень=>' . json_encode($ptr, JSON_PRETTY_PRINT));*/
                for ($d = $base; $d < $curr; $d++) {
                    /*$logger->debug("текущий уровень=>`$d`");*/

                    $end = 0;
                    if (count($ptr)) {
                        end($ptr);
                        $end = key($ptr);
                    }
                    /*$logger->debug("последний индекс на текущем уровне=>`$end`");*/

                    $ptr = &$ptr[$end][$elementsIndex];
                    /*$logger->debug('переместили указатель на следующую последовательность=>' . json_encode($ptr, JSON_PRETTY_PRINT));*/
                }
                /*$logger->debug('указатель на последовательности=>' . json_encode($ptr, JSON_PRETTY_PRINT));*/
            }

            $new = [];
            $new[$nameIndex] = $name;
            if (isset($data[$valueIndex])) {
                $new[$valueIndex] = $data[$valueIndex];
            }
            if (isset($data[$attributesIndex])) {
                $new[$attributesIndex] = $data[$attributesIndex];
            }

            $ii = $i + 1;
            if (
                isset($elems[$ii])
                && current($elems[$ii])[ElementExtractor::DEPTH] > $curr
            ) {
                $new[$elementsIndex] = [];
            }

            /*$logger->debug('новый элемент=>' . json_encode($new, JSON_PRETTY_PRINT));*/
            $ptr[] = $new;
            /*$logger->debug('последовательность после добавления элемента=>' . json_encode($ptr, JSON_PRETTY_PRINT));*/

            $prev = $curr;
            /*$logger->debug("предыдущий уровень вложенности `$prev`");*/
        }

        $result = &$hierarchy[$elementsIndex][0];

        return $result;
    }

}