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
    private $reader;
    private $val;
    private $attr;
    private $name;
    private $seq;

    /**
     * @param XMLReader $reader
     * @param string $val
     * @param string $attr
     * @param string $name
     * @param string $seq
     */
    public function __construct(
        XMLReader $reader,
        string $val = Notation::VALUE,
        string $attr = Notation::ATTRIBUTES,
        string $name = Notation::NAME,
        string $seq = Notation::SEQUENCE
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
        callable $detectElement
    ): Generator
    {
        $extractor = FastXmlParser::extractHierarchy(
            $this->reader,
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
     * @param callable $detectElement
     * @return Generator
     */
    public function extractPrettyPrint(
        callable $detectElement
    ): Generator
    {
        $extractor = FastXmlParser::extractPrettyPrint(
            $this->reader,
            $detectElement,
            $this->val,
            $this->attr
        );

        foreach ($extractor as $result) {
            yield $result;
        }
    }
}