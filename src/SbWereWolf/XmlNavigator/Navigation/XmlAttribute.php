<?php

namespace SbWereWolf\XmlNavigator\Navigation;

/**
 * XML attribute value object
 */
class XmlAttribute implements IXmlAttribute
{
    /** @var string */
    private $name;
    /** @var string */
    private $value;

    /**
     * @param string $name Attribute name
     * @param string $value Attribute value
     */
    public function __construct($name, $value)
    {
        $this->name = $name;
        $this->value = $value;
    }

    /* @inheritdoc */
    public function name()
    {
        return $this->name;
    }

    /* @inheritdoc */
    public function value()
    {
        return $this->value;
    }
}
