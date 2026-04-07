<?php

declare(strict_types=1);

namespace SbWereWolf\XmlNavigator\Navigation;

/**
 * XML attribute value object
 */
class XmlAttribute implements IXmlAttribute
{
    private string $name;
    private string $value;

    /**
     * @param string $name Attribute name
     * @param string $value Attribute value
     */
    public function __construct(string $name, string $value)
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
}
