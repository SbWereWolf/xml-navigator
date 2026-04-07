<?php

declare(strict_types=1);

namespace SbWereWolf\XmlNavigator\Navigation;

/**
 * Contract for an XML attribute object
 */
interface IXmlAttribute
{
    /** Returns name of attribute */
    public function name(): string;

    /** Returns value of attribute */
    public function value(): string;
}
