<?php

declare(strict_types=1);

namespace SbWereWolf\XmlNavigator\Navigation;

use Generator;
use InvalidArgumentException;
use JsonSerializable;
use LanguageSpecific\ArrayHandler;
use LanguageSpecific\IArrayHandler;
use SbWereWolf\JsonSerializable\JsonSerializeTrait;
use SbWereWolf\XmlNavigator\General\Notation;

/**
 * Объект для XML элемента
 */
class XmlElement implements IXmlElement, JsonSerializable
{
    use JsonSerializeTrait;

    /** @var IArrayHandler Массив со свойствами XML элемента */
    private $handler;
    /** @var string Индекс имени элемента */
    private $name;
    /** @var string Индекс значения элемента */
    private $val;
    /** @var string Индекс для атрибутов элемента */
    private $attr;
    /** @var string Индекс для вложенных элементов */
    private $seq;

    /**
     * @param array $data Массив со свойствами XML элемента
     * @param string $name Индекс для имени
     * @param string $val Индекс для значения
     * @param string $attr Индекс для атрибутов
     * @param string $seq Индекс для вложенных элементов
     */
    public function __construct(
        array $data,
        string $name = Notation::NAME,
        string $val = Notation::VALUE,
        string $attr = Notation::ATTRIBUTES,
        string $seq = Notation::SEQUENCE
    ) {
        $keys = array_flip(array_keys($data));
        $letThrow = !key_exists($name, $keys);
        if ($letThrow) {
            throw new InvalidArgumentException(
                'input array MUST BE like' .
                ' [ `name`=>string, `value`=>string,' .
                ' `attributes`=>[], `sequence`=>[] ]',
                -666
            );
        }

        $this->name = $name;
        $this->val = $val;
        $this->attr = $attr;
        $this->seq = $seq;

        $this->handler = new ArrayHandler($data);
    }

    /* @inheritdoc */
    public function attributes(): array
    {
        $attributes = (array)$this->getIndexContent($this->attr);

        $result = [];
        foreach ($attributes as $name => $value) {
            $result[] = new XmlAttribute($name, $value);
        }

        return $result;
    }

    /** Get content of XmlElement::$handler with given key $index
     * @param string $index
     * @return null|string|array
     */
    private function getIndexContent(string $index)
    {
        $content = null;
        if ($this->handler->has($index)) {
            $content = $this->handler->get($index)->asIs();
        }

        return $content;
    }

    /* @inheritdoc */
    public function get(string $name = null): string
    {
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
        $elems = $this->handler->pull($this->seq)->raw();
        foreach ($elems as $elem) {
            if ('' === $name) {
                $result = new static($elem);

                yield $result;
            }
            if ($elem[$this->name] === $name) {
                $result = new static($elem);

                yield $result;
            }
        }
    }

    /* @inheritdoc */
    public function value(): string
    {
        $result = (string)$this->getIndexContent($this->val);

        return $result;
    }

    /* @inheritdoc */
    public function name(): string
    {
        return $this->handler->get($this->name)->str();
    }

    /** Check existence of element with key $name
     * inside XmlElement::$handler element with key $index
     * @param string $index
     * @param string $name
     * @return bool
     */
    private function checkNameInsideIndex(
        string $index,
        string $name
    ): bool {
        $result = $this->handler->has($index);
        if ($result && '' !== $name) {
            $result =
                isset($this->handler->get($index)->array()[$name]);
        }
        return $result;
    }

    /* @inheritdoc */
    public function hasValue(): bool
    {
        return $this->handler->has($this->val);
    }

    /* @inheritdoc */
    public function hasAttribute(string $name = ''): bool
    {
        $index = $this->attr;
        $result = $this->checkNameInsideIndex($index, $name);

        return $result;
    }

    /* @inheritdoc */
    public function hasElement(string $name = ''): bool
    {
        $index = $this->seq;
        $result = $this->handler->has($index);
        if ($result && '' !== $name) {
            $elems = $this->handler->get($index)->array();
            foreach ($elems as $elem) {
                $result = $elem[$this->name] === $name;
                if ($result) {
                    break;
                }
            }
        }

        return $result;
    }
}