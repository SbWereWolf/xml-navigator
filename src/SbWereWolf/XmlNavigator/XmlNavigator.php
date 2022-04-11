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
     * @var IArrayHandler|ArrayHandler
     */
    private IArrayHandler $handler;

    /** Name of xml element
     * @var string
     */
    private string $name;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $name = key($data);
        if ('string' !== gettype($name)) {
            throw new InvalidArgumentException(
                'input array MUST BE like' .
                ' [ `element name`=>array( `*value`=>string,' .
                '`*attributes`=>[],`*elements`=>[],`*multiple`=>[] )' .
                ' ]',
                -666
            );
        }
        $this->name = $name;
        $this->handler = new ArrayHandler(current($data));
    }

    public function attribs(): array
    {
        $content = (array)$this
            ->getIndexContent(IConverter::ATTRIBUTES);
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

    public function get(string $name = null): string
    {
        $value = $this->handler->pull(IConverter::ATTRIBUTES)
            ->get($name)->str();

        return $value;
    }

    public function elements(): array
    {
        $content = (array)$this
            ->getIndexContent(IConverter::ELEMENTS);
        $props = array_keys($content);

        return $props;
    }

    public function pull(string $name): IXmlNavigator
    {
        $value = $this->handler->pull(IConverter::ELEMENTS)
            ->get($name)
            ->array();
        $result = new static([$name => $value]);

        return $result;
    }

    public function value(): string
    {
        $result = (string)$this
            ->getIndexContent(IConverter::VALUE);

        return $result;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function hasValue(): bool
    {
        return $this->handler->has(IConverter::VALUE);
    }

    public function hasAttribs(): bool
    {
        return $this->handler->has(IConverter::ATTRIBUTES);
    }

    public function hasElements(): bool
    {
        return $this->handler->has(IConverter::ELEMENTS);
    }

    public function isMultiple(): bool
    {
        return $this->handler->has(IConverter::MULTIPLE);
    }

    public function next(): Generator
    {
        $elements = $this->handler->pull(IConverter::MULTIPLE);
        foreach ($elements->pulling() as $index => $element) {
            /* @var IArrayHandler $element */
            $props = $element->raw();
            $result = new static([$this->name => $props]);

            yield $index => $result;
        }
    }
}