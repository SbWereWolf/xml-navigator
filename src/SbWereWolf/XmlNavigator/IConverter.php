<?php

namespace SbWereWolf\XmlNavigator;

interface IConverter
{
    public const VALUE = '*value';
    public const ATTRIBUTES = '*attributes';
    public const ELEMENTS = '*elements';
    public const MULTIPLE = '*multiple';

    /** get array representation of xml document
     * @return array
     */
    public function toArray(): array;
}
