<?php

declare(strict_types=1);

namespace SbWereWolf\XmlNavigator\Navigation;

use Generator;
use InvalidArgumentException;
use JsonSerializable;
use SbWereWolf\JsonSerializable\JsonSerializeTrait;
use SbWereWolf\XmlNavigator\General\Notation;

/**
 * Объект для XML элемента
 */
class XmlElement implements IXmlElement, JsonSerializable
{
    use JsonSerializeTrait;

    /** @var array<string,mixed> Сериализуемое представление XML элемента */
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
    /** @var array<string,string> Атрибуты XML элемента */
    private array $attributesData;
    /** @var array<int,array<string,mixed>> Дочерние элементы */
    private array $sequenceData;

    /**
     * @param array<string,string|array> $initial Массив со свойствами
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
        $letThrow = !key_exists($name, $initial);
        $letThrow =
            $letThrow || gettype($initial[$name]) !== 'string';
        $letThrow =
            $letThrow || gettype($initial[$val] ?? '') !== 'string';
        $letThrow =
            $letThrow || gettype($initial[$attr] ?? []) !== 'array';
        $letThrow =
            $letThrow || gettype($initial[$seq] ?? []) !== 'array';
        if ($letThrow) {
            throw new InvalidArgumentException(
                '$initial array MUST BE like' .
                " [ `$name`=>string, `$val`=>string," .
                " `$attr`=>[], `$seq`=>[] ]",
                -666
            );
        }

        $keys = [$name, $val, $attr, $seq];
        $data = [];
        foreach ($keys as $key) {
            if (isset($initial[$key])) {
                $data[$key] = $initial[$key];
            }
        }

        $this->name = $name;
        $this->val = $val;
        $this->attr = $attr;
        $this->seq = $seq;
        $this->data = $data;
        $this->elementName = $initial[$name];
        $this->elementValue = (string)($initial[$val] ?? '');
        $this->attributesData = $initial[$attr] ?? [];
        $this->sequenceData = $initial[$seq] ?? [];
    }

    /* @inheritdoc */
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

    /* @inheritdoc */
    public function elements(string $name = ''): array
    {
        $result = [];
        foreach ($this->pull($name) as $xmlElement) {
            $result[] = $xmlElement;
        }

        return $result;
    }

    /* @inheritdoc */
    public function pull(string $name = ''): Generator
    {
        foreach ($this->sequenceData as $elem) {
            if (
                '' !== $name
                && (($elem[$this->name] ?? null) !== $name)
            ) {
                continue;
            }

            $result = new static(
                $elem,
                $this->name,
                $this->val,
                $this->attr,
                $this->seq,
            );

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
}
