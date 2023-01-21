<?php

declare(strict_types=1);

namespace SbWereWolf\XmlNavigator;

use JsonSerializable;

class XmlAttribute implements IXmlAttribute, JsonSerializable
{
    private string $name;
    private string $value;

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