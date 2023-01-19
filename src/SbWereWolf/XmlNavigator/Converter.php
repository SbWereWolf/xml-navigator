<?php

declare(strict_types=1);

namespace SbWereWolf\XmlNavigator;

use Generator;
use XMLReader;

class Converter implements IConverter
{
    private const DEPTH = 'depth';

    private XMLReader $reader;
    private array $data = [];

    /**
     * @param XMLReader $reader
     */
    public function __construct(XMLReader $reader)
    {
        $this->reader = $reader;
    }

    /* @inheritdoc */
    public function toPrettyArray(): array
    {
        if (!count($this->data)) {
            $this->toNormalizedArray();
        }

        return $this->data;
    }

    /* @inheritdoc */
    public function toNormalizedArray(): array
    {
        if (count($this->data)) {
            return $this->data;
        }

        $elems = [];
        foreach ($this->pull() as $name => $data) {
            $isSet = isset($data[1]);
            if (!$isSet) {
                $elems[] = [$name => [static::DEPTH => $data[0]]];
            }

            $isArr = $isSet && is_array($data[1]);
            if ($isSet && $isArr) {
                $elems[] = [$name => [static::DEPTH => $data[0]]];

                end($elems);
                $elems[key($elems)][$name][static::ATTRIBS] = $data[1];
            }
            if ($isSet && !$isArr) {
                end($elems);
                $elems[key($elems)][$name][static::VAL] = $data[1];
            }
        }
        $this->reader->close();

        $hierarchy = [];
        $prev = 0;
        $this->data = [static::ELEMS => []];
        $ptr = &$this->data[static::ELEMS];
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
                $ptr = &$this->data[static::ELEMS];
                /*$logger->debug('перевели указатель на корень=>' . json_encode($ptr, JSON_PRETTY_PRINT));*/
                for ($d = 0; $d < $curr; $d++) {
                    /*$logger->debug("текущий уровень=>`$d`");*/

                    $end = 0;
                    if (count($ptr)) {
                        end($ptr);
                        $end = key($ptr);
                    }
                    /*$logger->debug("последний индекс на текущем уровне=>`$end`");*/

                    $ptr = &$ptr[$end][static::ELEMS];
                    /*$logger->debug('переместили указатель на следующую последовательность=>' . json_encode($ptr, JSON_PRETTY_PRINT));*/
                }
                /*$logger->debug('указатель на последовательности=>' . json_encode($ptr, JSON_PRETTY_PRINT));*/
            }

            $new = [];
            $new[static::NAME] = $name;
            if (isset($data[static::VAL])) {
                $new[static::VAL] = $data[static::VAL];
            }
            if (isset($data[static::ATTRIBS])) {
                $new[static::ATTRIBS] = $data[static::ATTRIBS];
            }
            $new[static::ELEMS] = [];

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

        return $this->data;
    }

    /* @inheritdoc */
    public function pull(): Generator
    {
        $reader = $this->reader;
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
                    yield $path[$reader->depth] => [0 => $reader->depth];
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

    /*
    const NODE_TYPES = [
        XMLReader::NONE => 'NONE',
        XMLReader::ELEMENT => 'ELEMENT',
        XMLReader::ATTRIBUTE => 'ATTRIBUTE',
        XMLReader::TEXT => 'TEXT',
        XMLReader::CDATA => 'CDATA',
        XMLReader::ENTITY_REF => 'ENTITY_REF',
        XMLReader::ENTITY => 'ENTITY',
        XMLReader::PI => 'PI',
        XMLReader::COMMENT => 'COMMENT',
        XMLReader::DOC => 'DOC',
        XMLReader::DOC_TYPE => 'DOC_TYPE',
        XMLReader::DOC_FRAGMENT => 'DOC_FRAGMENT',
        XMLReader::NOTATION => 'NOTATION',
        XMLReader::WHITESPACE => 'WHITESPACE',
        XMLReader::SIGNIFICANT_WHITESPACE => 'SIGNIFICANT_WHITESPACE',
        XMLReader::END_ELEMENT => 'END_ELEMENT',
        XMLReader::END_ENTITY => 'END_ENTITY',
        XMLReader::XML_DECLARATION => 'XML_DECLARATION',
    ];
    */

    /*
    $vars = [
        'attributeCount' => $reader->attributeCount,
        'baseURI' => $reader->baseURI,
        'depth' => $reader->depth,
        'hasAttributes' => $reader->hasAttributes,
        'hasValue' => $reader->hasValue,
        'isDefault' => $reader->isDefault,
        'isEmptyElement' => $reader->isEmptyElement,
        'localName' => $reader->localName,
        'name' => $reader->name,
        'namespaceURI' => $reader->namespaceURI,
        'nodeType' => NODE_TYPES[$reader->nodeType],
        'prefix' => $reader->prefix,
        'value' => $reader->value,
        'xmlLang' => $reader->xmlLang,
    ];
    $logger->debug('E => ' . json_encode($vars, JSON_PRETTY_PRINT));
    */
}
