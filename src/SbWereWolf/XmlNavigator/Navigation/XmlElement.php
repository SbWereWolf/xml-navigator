<?php

declare(strict_types=1);

namespace SbWereWolf\XmlNavigator\Navigation;

use Generator;
use InvalidArgumentException;
use JsonSerializable;
use SbWereWolf\JsonSerializable\JsonSerializeTrait;
use SbWereWolf\LanguageSpecific\AdvancedArrayFactory;
use SbWereWolf\LanguageSpecific\AdvancedArrayInterface;
use SbWereWolf\XmlNavigator\General\Notation;

/**
 * Объект для XML элемента
 */
class XmlElement implements IXmlElement, JsonSerializable
{
    use JsonSerializeTrait;

    /** @var AdvancedArrayInterface Массив со свойствами XML элемента */
    private AdvancedArrayInterface $handler;
    /** @var string Индекс имени элемента */
    private string $name;
    /** @var string Индекс значения элемента */
    private string $val;
    /** @var string Индекс для атрибутов элемента */
    private string $attr;
    /** @var string Индекс для вложенных элементов */
    private string $seq;

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
        $this->handler = (new AdvancedArrayFactory())
            ->makeAdvancedArray($data);
    }

    /* @inheritdoc */
    public function attributes(): array
    {
        $attributes = $this->handler[$this->attr] ?? [];

        $result = [];
        foreach ($attributes as $name => $value) {
            $result[] = new XmlAttribute($name, $value);
        }

        return $result;
    }

    /* @inheritdoc */
    public function get(string $name = ''): string
    {
        if ('' === $name) {
            $name = null;
        }
        $value = $this->handler->pull($this->attr)->get($name)->str();

        return $value;
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
        $elems = $this->handler[$this->seq] ?? [];
        if ('' !== $name) {
            $elems = array_filter(
                $elems,
                fn($val) => $val[$this->name] === $name
            );
        }
        foreach ($elems as $elem) {
            $result = new static($elem);

            yield $result;
        }
    }

    /* @inheritdoc */
    public function value(): string
    {
        $result = $this->handler[$this->val] ?? '';

        return $result;
    }

    /* @inheritdoc */
    public function name(): string
    {
        return $this->handler[$this->name];
    }

    /* @inheritdoc */
    public function hasValue(): bool
    {
        return isset($this->handler[$this->val]);
    }

    /* @inheritdoc */
    public function hasAttribute(string $name = ''): bool
    {
        if ('' === $name) {
            $name = null;
        }
        $result = $this->handler->pull($this->attr)->has($name);

        return $result;
    }

    /* @inheritdoc */
    public function hasElement(string $name = ''): bool
    {
        if ('' === $name) {
            $result = $this->handler->has($this->seq);
        }
        if ('' !== $name) {
            $elems = $this->handler[$this->seq] ?? [];
            $result = array_any(
                $elems,
                fn($value, $key) => $value[$this->name] === $name
            );
        }

        return $result;
    }
}