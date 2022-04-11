<?php

declare(strict_types=1);

namespace SbWereWolf\XmlNavigator;

use SimpleXMLElement;

class NavigatorFabric implements INavigatorFabric
{
    private SimpleXMLElement $xml;

    public function setSimpleXmlElement(SimpleXMLElement $xml): static
    {
        $this->xml = $xml;
        return $this;
    }

    public function setXml(string $xml): static
    {
        $this->xml = new SimpleXMLElement($xml);
        return $this;
    }

    public function makeConverter(): IConverter
    {
        return new Converter($this->xml);
    }

    public function makeNavigator(): IXmlNavigator
    {
        $converter = $this->makeConverter();
        $data = $converter->toArray();

        return new XmlNavigator($data);
    }
}