<?php

namespace SbWereWolf\XmlNavigator;

use Generator;
use InvalidArgumentException;
use JetBrains\PhpStorm\ArrayShape;
use XMLReader;

class FastXmlToArray implements IFastXmlToArray
{
    private const DEPTH = 'depth';

    /* @inheritdoc */
    #[ArrayShape([IFastXmlToArray::ELEMS => "array"])]
    public static function convert(
        string $xmlText = '',
        string $xmlUri = '',
        string $name = IFastXmlToArray::NAME,
        string $val = IFastXmlToArray::VAL,
        string $attribs = IFastXmlToArray::ATTRIBS,
        string $elems = IFastXmlToArray::ELEMS,
        string $encoding = null,
        int $flags = LIBXML_BIGLINES | LIBXML_COMPACT,
    ): array {
        $allElements = static::extractAllElements(
            $xmlText,
            $xmlUri,
            $encoding,
            $flags,
            $val,
            $attribs
        );

        $result = static::createTheHierarchyOfElements(
            $allElements,
            $name,
            $val,
            $attribs,
            $elems,
        );

        return $result;
    }

    /* @inheritdoc */
    public static function prettyPrint(
        string $xmlText = '',
        string $xmlUri = '',
        string $val = IFastXmlToArray::VALUE,
        string $attribs = IFastXmlToArray::ATTRIBUTES,
        string $encoding = null,
        int $flags = LIBXML_BIGLINES | LIBXML_COMPACT,
    ): array {
        $allElements = static::extractAllElements(
            $xmlText,
            $xmlUri,
            $encoding,
            $flags,
            $val,
            $attribs
        );

        $result = static::composePrettyPrintByXmlElements(
            $allElements,
            $val,
            $attribs,
        );

        return $result;
    }

    /* @inheritdoc */
    public static function nextElement(XMLReader $reader): Generator
    {
        $path = [];
        /** @var XMLReader $reader */
        while ($reader->read()) {
            $result = [];
            if (
                $reader->nodeType === XMLReader::ELEMENT
            ) {
                $path[$reader->depth] = $reader->name;
                $attribs = [];
                while ($reader->moveToNextAttribute()) {
                    $attribs[$reader->name] = $reader->value;
                }

                $hasAttribs = count($attribs) !== 0;
                if (!$hasAttribs) {
                    yield
                    $path[$reader->depth] => [0 => $reader->depth];
                }
                if ($hasAttribs) {
                    $result[0] = $reader->depth - 1;
                    $result[1] = $attribs;

                    yield $path[$reader->depth - 1] => $result;
                }
            }

            if (
                $reader->nodeType === XMLReader::TEXT ||
                $reader->nodeType === XMLReader::CDATA
            ) {
                $result[0] = $reader->depth - 1;
                $result[1] = $reader->value;

                yield $path[$reader->depth - 1] => $result;
            }
        }
    }

    /**
     * @param string $xmlText
     * @param string $xmlUri
     * @param string|null $encoding
     * @param int $flags
     * @param string $val
     * @param string $attribs
     * @return array
     */
    private static function extractAllElements(
        string $xmlText,
        string $xmlUri,
        ?string $encoding,
        int $flags,
        string $val,
        string $attribs
    ): array {
        if ($xmlText === '' && $xmlUri === '') {
            throw new InvalidArgumentException(
                'One of $xmlText or $xmlUri MUST BE defined,' .
                ' please assign only one of them, other MUST BE empty'
            );
        }

        $reader = new XMLReader();
        if ($xmlText !== '') {
            $reader = XMLReader::XML(
                $xmlText,
                $encoding,
                $flags,
            );
        }
        if ($xmlText === '' && $xmlUri !== '') {
            $reader = XMLReader::open(
                $xmlUri,
                $encoding,
                $flags,
            );
        }

        $elementsCollection = static::extractElementsWithDepth(
            $reader,
            $val,
            $attribs,
        );

        $reader->close();

        return $elementsCollection;
    }

    /**
     * @param XMLReader $reader
     * @return array
     */
    private static function extractElementsWithDepth(
        XMLReader $reader,
        string $valueIndex,
        string $attributesIndex,
    ): array {
        $elems = [];
        foreach (static::nextElement($reader) as $name => $data) {
            $isSet = isset($data[1]);
            if (!$isSet) {
                $elems[] = [$name => [static::DEPTH => $data[0]]];
            }

            $isArr = $isSet && is_array($data[1]);
            if ($isSet && $isArr) {
                $elems[] = [$name => [static::DEPTH => $data[0]]];

                end($elems);
                $elems[key($elems)][$name][$attributesIndex] =
                    $data[1];
            }
            if ($isSet && !$isArr) {
                end($elems);
                $elems[key($elems)][$name][$valueIndex] = $data[1];
            }
        }
        return $elems;
    }

    /**
     * @param array $elems
     * @return array[]
     */
    private static function createTheHierarchyOfElements(
        array $elems,
        string $nameIndex,
        string $valueIndex,
        string $attributesIndex,
        string $elementsIndex,
    ): array {
        $hierarchy = [];
        $prev = 0;
        $result = [$elementsIndex => []];
        $ptr = &$result[$elementsIndex];
        foreach ($elems as $elem) {
            $data = current($elem);
            /*$logger->debug('будем добавлять элемент' . json_encode($data, JSON_PRETTY_PRINT));*/

            $curr = $data[static::DEPTH];
            /*$logger->debug("уровень элемента `$curr`");*/
            $name = key($elem);
            /*$logger->debug("имя элемента `$name`");*/

            $letDoSearch = $prev !== $curr;
            /*$logger->debug('надо ли искать последовательность элементов' . json_encode($letDoSearch, JSON_PRETTY_PRINT));*/
            if ($letDoSearch) {
                $ptr = &$result[$elementsIndex];
                /*$logger->debug('перевели указатель на корень=>' . json_encode($ptr, JSON_PRETTY_PRINT));*/
                for ($d = 0; $d < $curr; $d++) {
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
            $new[$elementsIndex] = [];

            /*$logger->debug('новый элемент=>' . json_encode($new, JSON_PRETTY_PRINT));*/
            $ptr[] = $new;
            /*$logger->debug('последовательность после добавления элемента=>' . json_encode($ptr, JSON_PRETTY_PRINT));*/

            end($ptr);
            $last = key($ptr);
            $hierarchy[$curr] = $last;
            /*$logger->debug("на уровне `{$curr}` последний индекс `{$last}`");*/
            $prev = $curr;
            /*$logger->debug("предыдущий уровень вложенности `$prev`");*/
        }

        return $result;
    }

    /**
     * @param array $elems
     * @return array[]
     */
    private static function composePrettyPrintByXmlElements(
        array $elems,
        string $valueIndex,
        string $attributesIndex,
    ): array {
        $prev = 0;
        $result = [];
        $ptr = &$result;
        foreach ($elems as $elem) {
            $data = current($elem);

            $curr = $data[static::DEPTH];
            $name = key($elem);

            $letDoSearch = $prev !== $curr;
            if ($letDoSearch) {
                $ptr = &$result;
                for ($d = 0; $d < $curr; $d++) {
                    $end = 0;
                    if (count($ptr)) {
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
            if (
                key_exists($name, $ptr) &&
                is_array($ptr[$name]) &&
                key_exists(0, $ptr[$name])
            ) {
                $ptr[$name][] = $new;
            }

            if (
                key_exists($name, $ptr) &&
                (!is_array($ptr[$name]) ||
                    (
                        is_array($ptr[$name]) &&
                        !key_exists(0, $ptr[$name])
                    )
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
            }

            if (!key_exists($name, $ptr)) {
                $ptr[$name] = $new;
            }

            $prev = $curr;
        }

        return $result;
    }
}