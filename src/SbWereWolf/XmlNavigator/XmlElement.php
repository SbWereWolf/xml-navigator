<?php

declare(strict_types=1);

namespace SbWereWolf\XmlNavigator;

use Generator;
use InvalidArgumentException;
use JsonSerializable;
use LanguageSpecific\ArrayHandler;
use LanguageSpecific\IArrayHandler;
use SbWereWolf\JsonSerializable\JsonSerializeTrait;

/**
 * Объект для XML элемента
 */
class XmlElement implements IXmlElement, JsonSerializable
{
    use JsonSerializeTrait;

    /** @var IArrayHandler Массив со свойствами XML элемента */
    private IArrayHandler $handler;
    /** @var string Индекс имени элемента */
    private string $name;
    /** @var string Индекс значения элемента */
    private string $val;
    /** @var string Индекс для атрибутов элемента */
    private string $attr;
    /** @var string Индекс для вложенных элементов */
    private string $seq;

    /**
     * @param array $data Массив со свойствами XML элемента
     * @param string $name Индекс для имени
     * @param string $val Индекс для значения
     * @param string $attr Индекс для атрибутов
     * @param string $seq Индекс для вложенных элементов
     */
    public function __construct(
        array $data,
        string $name = IElementComposer::NAME,
        string $val = IElementComposer::VALUE,
        string $attr = IElementComposer::ATTRIBUTES,
        string $seq = IElementComposer::SEQUENCE,
    ) {
        $keys = array_keys($data);
        if (key_exists($name, $keys)) {
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
    private function getIndexContent(string $index): array|string|null
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
            $result = isset($this->handler->get($index)[$name]);
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
        $result = $this->checkNameInsideIndex($index, $name);

        return $result;
    }
}