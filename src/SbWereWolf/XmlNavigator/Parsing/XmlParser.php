<?php

namespace SbWereWolf\XmlNavigator\Parsing;

use Generator;
use SbWereWolf\XmlNavigator\General\Notation;
use XMLReader;

/**
 * XML parser with callable to filter elements
 *
 * @phpstan-import-type HierarchyNode from \SbWereWolf\XmlNavigator\Conversion\IFastXmlToArray
 * @phpstan-import-type PrettyNode from \SbWereWolf\XmlNavigator\Conversion\IFastXmlToArray
 */
class XmlParser
{
    /** @var string */
    private $val;
    /** @var string */
    private $attr;
    /** @var string */
    private $name;
    /** @var string */
    private $seq;

    /**
     * @param string $val
     * @param string $attr
     * @param string $name
     * @param string $seq
     */
    public function __construct(
        $val = Notation::VALUE,
        $attr = Notation::ATTRIBUTES,
        $name = Notation::NAME,
        $seq = Notation::SEQUENCE
    ) {
        $this->val = $val;
        $this->attr = $attr;
        $this->name = $name;
        $this->seq = $seq;
    }

    /**
     * @param XMLReader $reader
     * @param callable(XMLReader):bool $detectElement
     * @return Generator<int, HierarchyNode>
     */
    public function extractHierarchy(
        XMLReader $reader,
        callable $detectElement
    ) {
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
     * @param callable(XMLReader):bool $detectElement
     * @return Generator<int, PrettyNode>
     */
    public function extractPrettyPrint(
        XMLReader $reader,
        callable $detectElement
    ) {
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
