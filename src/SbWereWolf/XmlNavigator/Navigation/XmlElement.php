<?php

declare(strict_types=1);

namespace SbWereWolf\XmlNavigator\Navigation;

use Generator;
use InvalidArgumentException;
use JsonSerializable;
use SbWereWolf\JsonSerializable\JsonSerializeTrait;
use SbWereWolf\XmlNavigator\Conversion\IFastXmlToArray;
use SbWereWolf\XmlNavigator\General\Notation;

/**
 * Объект для XML элемента
 *
 * @phpstan-import-type HierarchyNode from IFastXmlToArray
 * @phpstan-import-type XmlAttributes from IFastXmlToArray
 * @phpstan-consistent-constructor
 */
class XmlElement implements IXmlElement, JsonSerializable
{
    use JsonSerializeTrait;

    private static bool $trustChildData = false;

    /** @var HierarchyNode Сериализуемое представление XML элемента */
    private array $data;
    /** @var string Индекс имени элемента */
    private string $name;
    /** @var string Индекс значения элемента */
    private string $val;
    /** @var string Индекс для атрибутов элемента */
    private string $attr;
    /** @var string Индекс для вложенных элементов */
    private string $seq;
    /** @var string Имя XML элемента */
    private string $elementName;
    /** @var string Значение XML элемента */
    private string $elementValue;
    /** @var XmlAttributes Атрибуты XML элемента */
    private array $attributesData;
    /** @var list<array<string, mixed>> Дочерние элементы */
    private array $sequenceData;

    /**
     * @param HierarchyNode $initial Массив со свойствами
     *                                              XML элемента
     * @param string $name Индекс для имени
     * @param string $val Индекс для значения
     * @param string $attr Индекс для атрибутов
     * @param string $seq Индекс для вложенных элементов
     */
    public function __construct(
        array $initial,
        string $name = Notation::NAME,
        string $val = Notation::VALUE,
        string $attr = Notation::ATTRIBUTES,
        string $seq = Notation::SEQUENCE,
    ) {
        $elementName = $initial[$name] ?? null;
        $elementValue = $initial[$val] ?? '';
        $attributesData = $initial[$attr] ?? [];
        $sequenceData = $initial[$seq] ?? [];
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
    public function attributes(): array
    {
        $result = [];
        foreach ($this->attributesData as $name => $value) {
            $result[] = new XmlAttribute($name, $value);
        }

        return $result;
    }

    /* @inheritdoc */
    public function get(string $name = ''): string
    {
        if ('' !== $name) {
            return $this->attributesData[$name] ?? '';
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
    public function elements(string $name = ''): array
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
    public function pull(string $name = ''): Generator
    {
        foreach ($this->sequenceData as $elem) {
            if (
                '' !== $name
                && (($elem[$this->name] ?? null) !== $name)
            ) {
                continue;
            }

            /** @var HierarchyNode $elem */
            $elem = $elem;

            self::$trustChildData = true;
            try {
                $result = new static(
                    $elem,
                    $this->name,
                    $this->val,
                    $this->attr,
                    $this->seq,
                );
            } finally {
                self::$trustChildData = false;
            }

            yield $result;
        }
    }

    /* @inheritdoc */
    public function value(): string
    {
        return $this->elementValue;
    }

    /* @inheritdoc */
    public function name(): string
    {
        return $this->elementName;
    }

    /* @inheritdoc */
    public function hasValue(): bool
    {
        return array_key_exists($this->val, $this->data);
    }

    /* @inheritdoc */
    public function hasAttribute(string $name = ''): bool
    {
        if ('' === $name) {
            return $this->attributesData !== [];
        }

        return array_key_exists($name, $this->attributesData);
    }

    /* @inheritdoc */
    public function hasElement(string $name = ''): bool
    {
        if ('' === $name) {
            return $this->sequenceData !== [];
        }

        foreach ($this->sequenceData as $elem) {
            if (($elem[$this->name] ?? null) === $name) {
                return true;
            }
        }

        return false;
    }

    /* @inheritdoc */
    public function serialize(): array
    {
        return $this->data;
    }

    /**
     * @param mixed $value
     * @phpstan-assert-if-true XmlAttributes $value
     */
    private static function isXmlAttributes(mixed $value): bool
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
     * @param mixed $value
     * @phpstan-assert-if-true list<array<string, mixed>> $value
     */
    private static function isHierarchySequence(mixed $value): bool
    {
        if (!is_array($value) || !array_is_list($value)) {
            return false;
        }

        foreach ($value as $item) {
            if (!is_array($item)) {
                return false;
            }
        }

        return true;
    }
}
