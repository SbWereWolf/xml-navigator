<?php

declare(strict_types=1);

namespace SbWereWolf\XmlNavigator;

use Generator;
use InvalidArgumentException;
use LanguageSpecific\ArrayHandler;
use LanguageSpecific\IArrayHandler;

class XmlNavigator implements IXmlNavigator
{
    /**
     * @var IArrayHandler
     */
    private IArrayHandler $handler;
    /** @var string Индекс имени элемента */
    private string $name;
    /** @var string Индекс значения элемента */
    private string $val;
    /** @var string Индекс для атрибутов элемента */
    private string $attribs;
    /** @var string Индекс для вложенных элементов */
    private string $elems;

    /**
     * @param array $data
     */
    public function __construct(
        array $data,
        string $name = IFastXmlToArray::NAME,
        string $val = IFastXmlToArray::VAL,
        string $attribs = IFastXmlToArray::ATTRIBS,
        string $elems = IFastXmlToArray::ELEMS,
    ) {
        reset($data);
        $first = key($data);
        if ('string' !== gettype($first)) {
            throw new InvalidArgumentException(
                'input array MUST BE like' .
                ' [ `name`=>string, `val`=>string,' .
                ' `attribs`=>[], `elems`=>[] ]',
                -666
            );
        }
        if ($name !== $first) {
            throw new InvalidArgumentException(
                'input array MUST BE like' .
                ' [ `name`=>string, `val`=>string,' .
                ' `attribs`=>[], `elems`=>[] ]',
                -667
            );
        }

        $this->name = $name;
        $this->val = $val;
        $this->attribs = $attribs;
        $this->elems = $elems;

        $this->handler = new ArrayHandler($data);
    }

    /* @inheritdoc */
    public function attributes(): array
    {
        $content = (array)$this
            ->getIndexContent($this->attribs);
        $attribs = array_keys($content);

        return $attribs;
    }

    /** get content of data[] with given index
     * @param string $index
     * @return mixed|null
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
        $value = $this->handler->pull($this->attribs)
            ->get($name)->str();

        return $value;
    }

    /* @inheritdoc */
    public function elements(): array
    {
        $content = (array)$this
            ->getIndexContent($this->elems);
        $props = array_column($content, $this->name);

        return $props;
    }

    /* @inheritdoc */
    public function pull(string $name = ''): Generator
    {
        $elems = $this->handler->pull($this->elems)->raw();
        foreach ($elems as $elem) {
            if ($name === '') {
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
        $result = (string)$this
            ->getIndexContent($this->val);

        return $result;
    }

    /* @inheritdoc */
    public function name(): string
    {
        return $this->handler
            ->get($this->name)
            ->str();
    }

    /* @inheritdoc */
    public function hasValue(): bool
    {
        return $this->handler->has($this->val);
    }

    /* @inheritdoc */
    public function hasAttributes(): bool
    {
        return $this->handler->has($this->attribs);
    }

    /* @inheritdoc */
    public function hasElements(): bool
    {
        return $this->handler->has($this->elems);
    }
}