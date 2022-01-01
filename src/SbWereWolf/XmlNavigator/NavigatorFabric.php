<?php

namespace SbWereWolf\XmlNavigator;

use Exception;
use SimpleXMLElement;

class NavigatorFabric implements INavigatorFabric
{
    private SimpleXMLElement $xml;

    /**
     * @throws Exception
     */
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