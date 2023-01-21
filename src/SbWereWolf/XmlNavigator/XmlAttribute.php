<?php

declare(strict_types=1);

namespace SbWereWolf\XmlNavigator;

use JsonSerializable;

/**
 * Объект для XML атрибута
 */
class XmlAttribute implements IXmlAttribute, JsonSerializable
{
    private string $name;
    private string $value;

    /**
     * @param string $name Имя атрибута
     * @param string $value Значение атрибута
     */
    public function __construct(string $name, string $value,)
    {
        $this->name = $name;
        $this->value = $value;
    }

    /* @inheritdoc */
    public function name(): string
    {
        return $this->name;
    }

    /* @inheritdoc */
    public function value(): string
    {
        return $this->value;
    }

    /* @inheritdoc */
    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}