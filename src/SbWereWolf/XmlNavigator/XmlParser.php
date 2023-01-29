<?php

declare(strict_types=1);

namespace SbWereWolf\XmlNavigator;

use Generator;
use XMLReader;

/**
 * XML parser with callable to filter elements
 */
class XmlParser
{
    private XMLReader $reader;
    private string $val;
    private string $attr;
    private string $name;
    private string $seq;

    /**
     * @param XMLReader $reader
     * @param string $val
     * @param string $attr
     * @param string $name
     * @param string $seq
     */
    public function __construct(
        XMLReader $reader,
        string $val = IElementComposer::VALUE,
        string $attr = IElementComposer::ATTRIBUTES,
        string $name = IElementComposer::NAME,
        string $seq = IElementComposer::SEQUENCE,
    ) {
        $this->reader = $reader;
        $this->val = $val;
        $this->attr = $attr;
        $this->name = $name;
        $this->seq = $seq;
    }

    /**
     * @param callable $detectElement
     * @return Generator
     */
    public function extractHierarchy(
        callable $detectElement,
    ): Generator {
        $reader = $this->reader;
        $isSuitable = $detectElement($reader);
        $mayRead = true;
        while ($mayRead && !$isSuitable) {
            $mayRead = $reader->read();

            $isSuitable = $detectElement($reader);
        }

        while ($isSuitable) {
            $result = HierarchyComposer::compose(
                $reader,
                $this->val,
                $this->attr,
                $this->name,
                $this->seq,
            );

            yield $result;

            while (
                $mayRead &&
                $reader->nodeType !== XMLReader::ELEMENT
            ) {
                $mayRead = $reader->read();
            }

            $isSuitable = $detectElement($reader);
        }
    }

    /**
     * @param callable $detectElement
     * @return Generator
     */
    public function extractPrettyPrint(
        callable $detectElement,
    ): Generator {
        $reader = $this->reader;
        $isSuitable = $detectElement($reader);
        $mayRead = true;
        while ($mayRead && !$isSuitable) {
            $mayRead = $reader->read();

            $isSuitable = $detectElement($reader);
        }

        while ($isSuitable) {
            $result = PrettyPrintComposer::compose(
                $reader,
                $this->val,
                $this->attr,
            );

            yield $result;

            while (
                $mayRead &&
                $reader->nodeType !== XMLReader::ELEMENT
            ) {
                $mayRead = $reader->read();
            }

            $isSuitable = $detectElement($reader);
        }
    }
}