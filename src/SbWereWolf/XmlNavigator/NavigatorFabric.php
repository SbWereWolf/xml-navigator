<?php

namespace SbWereWolf\XmlNavigator;

use Exception;
use SimpleXMLElement;

class NavigatorFabric
{
    private SimpleXMLElement $xml;

    /**
     * @throws Exception
     */
    public function __construct(string $xml)
    {
        $this->xml = new SimpleXMLElement($xml);
    }

    public function make()
    {
        $converter = new Converter($this->xml);
        $data = $converter->toArray();

        return new XmlNavigator($data);
    }
}