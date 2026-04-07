<?php

namespace SbWereWolf\XmlNavigator\Navigation;

/**
 * Contract for an XML attribute object
 */
interface IXmlAttribute
{
    /** Returns name of attribute */
    public function name();

    /** Returns value of attribute */
    public function value();
}
