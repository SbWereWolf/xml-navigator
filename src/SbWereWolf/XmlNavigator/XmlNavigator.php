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

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $name = key($data);
        if ('string' !== gettype($name)) {
            throw new InvalidArgumentException(
                'input array MUST BE like' .
                ' [ `name`=>string, `val`=>string,' .
                ' `attribs`=>[], `elems`=>[] ]',
                -666
            );
        }
        if ($name !== IConverter::NAME) {
            throw new InvalidArgumentException(
                'input array MUST BE like' .
                ' [ `name`=>string, `val`=>string,' .
                ' `attribs`=>[], `elems`=>[] ]',
                -667
            );
        }

        $this->handler = new ArrayHandler($data);
    }

    /* @inheritdoc */
    public function attributes(): array
    {
        $content = (array)$this
            ->getIndexContent(IConverter::ATTRIBS);
        $attribs = array_keys($content);

        return $attribs;
    }

    /** get content of element with given index ($key)
     * @param string $key
     * @return mixed|null
     */
    private function getIndexContent(string $key)
    {
        $content = null;
        if ($this->handler->has($key)) {
            $content = $this->handler->get($key)->asIs();
        }
        return $content;
    }

    /* @inheritdoc */
    public function get(string $name = null): string
    {
        $value = $this->handler->pull(IConverter::ATTRIBS)
            ->get($name)->str();

        return $value;
    }

    /* @inheritdoc */
    public function elements(): array
    {
        $content = (array)$this
            ->getIndexContent(IConverter::ELEMS);
        $props = array_column($content, IConverter::NAME);

        return $props;
    }

    /* @inheritdoc */
    public function pull(string $name = ''): Generator
    {
        $elems = $this->handler->pull(IConverter::ELEMS)->raw();
        foreach ($elems as $elem) {
            if ($name === '') {
                $result = new static($elem);

                yield $result;
            }
            if ($elem[IConverter::NAME] === $name) {
                $result = new static($elem);

                yield $result;
            }
        }
    }

    /* @inheritdoc */
    public function value(): string
    {
        $result = (string)$this
            ->getIndexContent(IConverter::VAL);

        return $result;
    }

    /* @inheritdoc */
    public function name(): string
    {
        return $this->handler
            ->get(IConverter::NAME)
            ->str();
    }

    /* @inheritdoc */
    public function hasValue(): bool
    {
        return $this->handler->has(IConverter::VAL);
    }

    /* @inheritdoc */
    public function hasAttributes(): bool
    {
        return $this->handler->has(IConverter::ATTRIBS);
    }

    /* @inheritdoc */
    public function hasElements(): bool
    {
        return $this->handler->has(IConverter::ELEMS);
    }
}