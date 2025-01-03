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
    private  AdvancedArrayInterface $handler;
    /** @var string Индекс имени элемента */
    private string $name;
    /** @var string Индекс значения элемента */
    private string $val;
    /** @var string Индекс для атрибутов элемента */
    private string $attr;
    /** @var string Индекс для вложенных элементов */
    private string $seq;

    /**
     * @param array<string,string|array> $data Массив со свойствами XML элемента
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
        string $seq = Notation::SEQUENCE,
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

        $this->handler = (new AdvancedArrayFactory())
            ->makeAdvancedArray($data);
    }

    /* @inheritdoc */
    public function attributes(): array
    {
        $attributes = (array)$this->handler->get($this->attr)->asIs();

        $result = [];
        foreach ($attributes as $name => $value) {
            $result[] = new XmlAttribute($name, $value);
        }

        return $result;
    }

    /* @inheritdoc */
    public function get(string|null $name = null): string
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
            if ('' === $name || $elem[$this->name] === $name) {
                $result = new static($elem);

                yield $result;
            }
        }
    }

    /* @inheritdoc */
    public function value(): string
    {
        $result = (string)$this->handler->get($this->val)->asIs();

        return $result;
    }

    /* @inheritdoc */
    public function name(): string
    {
        return $this->handler->get($this->name)->str();
    }

    /* @inheritdoc */
    public function hasValue(): bool
    {
        return $this->handler->has($this->val);
    }

    /* @inheritdoc */
    public function hasAttribute(string $name = ''): bool
    {
        $result = $this->handler->pull($this->attr)->has($name);

        return $result;
    }

    /* @inheritdoc */
    public function hasElement(string|null $name = null): bool
    {
        $elems = $this->handler->pull($this->seq)->raw();
        $result=false;
        foreach ($elems as $elem) {
            $result = $elem[$this->name] === $name;
            if ($result) {
                break;
            }
        }

        return $result;
    }
}