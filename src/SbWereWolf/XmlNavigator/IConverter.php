<?php

namespace SbWereWolf\XmlNavigator;

use Generator;

interface IConverter
{
    public const VALUE = '@value';
    public const ATTRIBUTES = '@attributes';

    public const NAME = 'name';
    public const VAL = 'val';
    public const ATTRIBS = 'attribs';
    public const ELEMS = 'elems';

    /** get array representation of xml document
     * @return array
     */
    public function toPrettyArray(): array;

    /** get array representation of xml document
     * @return array
     */
    public function toNormalizedArray(): array;

    /** Pull XML element or his value as
     * [`element name` =>
     *     [
     *         0 => element depth,
     *         1 => value of TEXT node, or attributes of ELEMENT node
     *     ]
     * ]
     * @return Generator
     */
    public function pull(): Generator;
}
