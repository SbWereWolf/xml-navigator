<?php

declare(strict_types=1);

namespace SbWereWolf\XmlNavigator\Parsing;

use Generator;
use SbWereWolf\XmlNavigator\General\Notation;
use XMLReader;

/**
 * XML parser with callable to filter elements
 */
class XmlParser
{
    private $val;
    private $attr;
    private $name;
    private $seq;

    /**
     * @param string $val
     * @param string $attr
     * @param string $name
     * @param string $seq
     */
    public function __construct(
        string $val = Notation::VALUE,
        string $attr = Notation::ATTRIBUTES,
        string $name = Notation::NAME,
        string $seq = Notation::SEQUENCE
    ) {
        $this->val = $val;
        $this->attr = $attr;
        $this->name = $name;
        $this->seq = $seq;
    }

    /**
     * @param XMLReader $reader
     * @param callable $detectElement
     * @return Generator
     */
    public function extractHierarchy(
        XMLReader $reader,
        callable $detectElement,
    ): Generator {
        $extractor = FastXmlParser::extractHierarchy(
            $reader,
            $detectElement,
            $this->val,
            $this->attr,
            $this->name,
            $this->seq
        );

        foreach ($extractor as $result) {
            yield $result;
        }
    }

    /**
     * @param XMLReader $reader
     * @param callable $detectElement
     * @return Generator
     */
    public function extractPrettyPrint(
        XMLReader $reader,
        callable $detectElement,
    ): Generator {
        $extractor = FastXmlParser::extractPrettyPrint(
            $reader,
            $detectElement,
            $this->val,
            $this->attr
        );

        foreach ($extractor as $result) {
            yield $result;
        }
    }
}
