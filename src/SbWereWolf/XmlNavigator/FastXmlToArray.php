<?php

namespace SbWereWolf\XmlNavigator;

use Generator;
use JetBrains\PhpStorm\ArrayShape;
use XMLReader;

class FastXmlToArray implements IFastXmlToArray
{
    private const DEPTH = 'depth';

    /* @inheritdoc */
    #[ArrayShape([IConverter::ELEMS => "array"])]
    public static function convert(XMLReader $reader): array
    {
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
                $elems[key($elems)][$name][IConverter::ATTRIBS] =
                    $data[1];
            }
            if ($isSet && !$isArr) {
                end($elems);
                $elems[key($elems)][$name][IConverter::VAL] = $data[1];
            }
        }
        $reader->close();

        $hierarchy = [];
        $prev = 0;
        $result = [IConverter::ELEMS => []];
        $ptr = &$result[IConverter::ELEMS];
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
                $ptr = &$result[IConverter::ELEMS];
                /*$logger->debug('перевели указатель на корень=>' . json_encode($ptr, JSON_PRETTY_PRINT));*/
                for ($d = 0; $d < $curr; $d++) {
                    /*$logger->debug("текущий уровень=>`$d`");*/

                    $end = 0;
                    if (count($ptr)) {
                        end($ptr);
                        $end = key($ptr);
                    }
                    /*$logger->debug("последний индекс на текущем уровне=>`$end`");*/

                    $ptr = &$ptr[$end][IConverter::ELEMS];
                    /*$logger->debug('переместили указатель на следующую последовательность=>' . json_encode($ptr, JSON_PRETTY_PRINT));*/
                }
                /*$logger->debug('указатель на последовательности=>' . json_encode($ptr, JSON_PRETTY_PRINT));*/
            }

            $new = [];
            $new[IConverter::NAME] = $name;
            if (isset($data[IConverter::VAL])) {
                $new[IConverter::VAL] = $data[IConverter::VAL];
            }
            if (isset($data[IConverter::ATTRIBS])) {
                $new[IConverter::ATTRIBS] = $data[IConverter::ATTRIBS];
            }
            $new[IConverter::ELEMS] = [];

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
}