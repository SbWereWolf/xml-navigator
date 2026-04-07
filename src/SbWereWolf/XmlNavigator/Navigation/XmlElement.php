<?php

namespace SbWereWolf\XmlNavigator\Navigation;

use Generator;
use InvalidArgumentException;
use SbWereWolf\XmlNavigator\Conversion\IFastXmlToArray;
use SbWereWolf\XmlNavigator\General\Notation;

/**
 * XML element value object
 *
 * @phpstan-import-type HierarchyNode from IFastXmlToArray
 * @phpstan-import-type XmlAttributes from IFastXmlToArray
 * @phpstan-consistent-constructor
 */
class XmlElement implements IXmlElement
{
    /** @var bool */
    private static $trustChildData = false;

    /** @var HierarchyNode Serialized XML element representation */
    private $data;
    /** @var string Index for the element name */
    private $name;
    /** @var string Index for the element value */
    private $val;
    /** @var string index for element attributes */
    private $attr;
    /** @var string Index for child elements */
    private $seq;
    /** @var string XML element name */
    private $elementName;
    /** @var string XML element value */
    private $elementValue;
    /** @var XmlAttributes XML element attributes */
    private $attributesData;
    /** @var list<array<string, mixed>> Child elements */
    private $sequenceData;

    /**
     * @param HierarchyNode $initial Serialized XML element payload
     * @param string $name index for the element name
     * @param string $val index for the element value
     * @param string $attr index for element attributes
     * @param string $seq Index for child elements
     */
    public function __construct(
        array $initial,
        $name = Notation::NAME,
        $val = Notation::VALUE,
        $attr = Notation::ATTRIBUTES,
        $seq = Notation::SEQUENCE
    ) {
        $elementName = array_key_exists($name, $initial)
            ? $initial[$name]
            : null;
        $elementValue = array_key_exists($val, $initial)
            ? $initial[$val]
            : '';
        $attributesData = array_key_exists($attr, $initial)
            ? $initial[$attr]
            : [];
        $sequenceData = array_key_exists($seq, $initial)
            ? $initial[$seq]
            : [];
        $hasValidElementName = is_string($elementName);
        $hasValidElementValue = is_string($elementValue);
        $hasValidAttributes = self::isXmlAttributes($attributesData);
        $hasValidSequence = self::isHierarchySequence($sequenceData);

        if (
            !self::$trustChildData
            && (
            !$hasValidElementName
            || !$hasValidElementValue
            || !$hasValidAttributes
            || !$hasValidSequence
            )
        ) {
            throw new InvalidArgumentException(
                '$initial array MUST BE like' .
                " [ `$name`=>string, `$val`=>string," .
                " `$attr`=>[], `$seq`=>[] ]",
                -666
            );
        }

        $keys = [$name, $val, $attr, $seq];
        /** @var HierarchyNode $data */
        $data = [];
        foreach ($keys as $key) {
            if (array_key_exists($key, $initial)) {
                $data[$key] = $initial[$key];
            }
        }

        $this->name = $name;
        $this->val = $val;
        $this->attr = $attr;
        $this->seq = $seq;
        $this->data = $data;
        $this->elementName = $hasValidElementName ? $elementName : '';
        $this->elementValue = $hasValidElementValue ? $elementValue : '';
        $this->attributesData = $hasValidAttributes ? $attributesData : [];
        $this->sequenceData = $hasValidSequence ? $sequenceData : [];
    }

    /**
     * @return list<IXmlAttribute>
     */
    public function attributes()
    {
        $result = [];
        foreach ($this->attributesData as $name => $value) {
            $result[] = new XmlAttribute($name, $value);
        }

        return $result;
    }

    /* @inheritdoc */
    public function get($name = '')
    {
        if ('' !== $name) {
            return array_key_exists($name, $this->attributesData)
                ? $this->attributesData[$name]
                : '';
        }

        $first = reset($this->attributesData);
        if ($first === false) {
            return '';
        }

        return $first;
    }

    /**
     * @return list<IXmlElement>
     */
    public function elements($name = '')
    {
        $result = [];
        foreach ($this->pull($name) as $xmlElement) {
            $result[] = $xmlElement;
        }

        return $result;
    }

    /**
     * @return Generator<int, static>
     */
    public function pull($name = '')
    {
        foreach ($this->sequenceData as $elem) {
            /** @var HierarchyNode $elem */
            if (
                '' !== $name
                && (
                    !array_key_exists($this->name, $elem)
                    || $elem[$this->name] !== $name
                )
            ) {
                continue;
            }

            self::$trustChildData = true;
            try {
                $result = new static(
                    $elem,
                    $this->name,
                    $this->val,
                    $this->attr,
                    $this->seq
                );
            } finally {
                self::$trustChildData = false;
            }

            yield $result;
        }
    }

    /* @inheritdoc */
    public function value()
    {
        return $this->elementValue;
    }

    /* @inheritdoc */
    public function name()
    {
        return $this->elementName;
    }

    /* @inheritdoc */
    public function hasValue()
    {
        return array_key_exists($this->val, $this->data);
    }

    /* @inheritdoc */
    public function hasAttribute($name = '')
    {
        if ('' === $name) {
            return $this->attributesData !== [];
        }

        return array_key_exists($name, $this->attributesData);
    }

    /* @inheritdoc */
    public function hasElement($name = '')
    {
        if ('' === $name) {
            return $this->sequenceData !== [];
        }

        foreach ($this->sequenceData as $elem) {
            if (
                array_key_exists($this->name, $elem)
                && $elem[$this->name] === $name
            ) {
                return true;
            }
        }

        return false;
    }

    /* @inheritdoc */
    public function serialize()
    {
        return $this->data;
    }

    /**
     * @param mixed $value Value that may contain XML attributes
     * @phpstan-assert-if-true XmlAttributes $value
     */
    private static function isXmlAttributes($value)
    {
        if (!is_array($value)) {
            return false;
        }

        foreach ($value as $key => $item) {
            if (!is_string($key) || !is_string($item)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param mixed $value Value that may contain a list of child elements
     * @phpstan-assert-if-true list<array<string, mixed>> $value
     */
    private static function isHierarchySequence($value)
    {
        if (!is_array($value) || !self::isList($value)) {
            return false;
        }

        foreach ($value as $item) {
            if (!is_array($item)) {
                return false;
            }
        }

        return true;
    }

    private static function isList(array $value)
    {
        return $value === array_values($value);
    }
}
