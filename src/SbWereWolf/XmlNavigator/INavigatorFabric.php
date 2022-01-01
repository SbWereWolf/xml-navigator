<?php

namespace SbWereWolf\XmlNavigator;

use SimpleXMLElement;

interface INavigatorFabric
{
    public function setSimpleXmlElement(SimpleXMLElement $xml): static;

    public function setXml(string $xml): static;

    public function makeConverter(): IConverter;

    public function makeNavigator(): IXmlNavigator;
}