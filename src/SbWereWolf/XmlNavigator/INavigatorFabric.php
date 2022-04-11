<?php

namespace SbWereWolf\XmlNavigator;

use Exception;
use SimpleXMLElement;

interface INavigatorFabric
{
    /** define SimpleXMLElement
     * @throws Exception
     */
    public function setSimpleXmlElement(SimpleXMLElement $xml): static;

    /** define SimpleXMLElement with using of raw XML document text
     * @param string $xml
     * @return $this
     * @throws Exception
     */
    public function setXml(string $xml): static;

    /** get instance of IConverter implementation
     * @return IConverter
     */
    public function makeConverter(): IConverter;

    /** get instance of IXmlNavigator implementation
     * @return IXmlNavigator
     */
    public function makeNavigator(): IXmlNavigator;
}