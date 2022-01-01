<?php

namespace SbWereWolf\XmlNavigator;

interface IXmlNavigator
{
    public function name(): string;

    public function hasValue(): string;

    public function value(): string;

    public function hasAttribs(): bool;

    public function attribs(): array;

    public function get(string $name = null): string;

    public function hasElements(): bool;

    public function elements(): array;

    public function pull(string $name): IXmlNavigator;

    public function isMultiple(): bool;

    public function next();
}