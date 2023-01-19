<?php

namespace SbWereWolf\XmlNavigator;

use Generator;
use XMLReader;

interface IFastXmlToArray
{
    /** Convert to array representation of xml document
     * @return array
     */
    public static function convert(XMLReader $reader): array;

    /** Pull XML element or his value as
     * [`element name` =>
     *     [
     *         0 => element depth,
     *         1 => value of TEXT node, or attributes of ELEMENT node
     *     ]
     * ]
     * @return Generator
     */
    public static function nextElement(XMLReader $reader): Generator;
}