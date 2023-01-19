<?php

namespace SbWereWolf\XmlNavigator;

use Generator;

interface IXmlAttributes
{
    /** Get value of attribute with $name,
     * if $name not defined, than get value of random attribute
     * @param string|null $name
     * @return string
     */
    public function get(string $name = null): string;

    /** Pull some attribute,
     * if $name is defined, than pull attribute with the $name
     * @param string $name
     * @return IXmlNavigator
     */
    public function pull(string $name = ''): Generator;
}