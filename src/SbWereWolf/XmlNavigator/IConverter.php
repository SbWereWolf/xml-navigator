<?php

namespace SbWereWolf\XmlNavigator;

interface IConverter
{
    public const VALUE = '*value';
    public const ATTRIBUTES = '*attributes';
    public const ELEMENTS = '*elements';
    public const MULTIPLE = '*multiple';

    public function toArray(): array;
}
