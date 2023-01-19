<?php

namespace SbWereWolf\XmlNavigator;

use Exception;

interface INavigatorFabric
{
    /** Define XML string
     * @param string $xml
     * @return $this
     * @throws Exception
     */
    public function makeFromXmlString(string $xml): INavigatorFabric;

    /** Define URI for obtain XML string
     * @param string $xml
     * @return $this
     * @throws Exception
     */
    public function makeFromXmlUri(string $uri): INavigatorFabric;

    /** Make instance of IConverter implementation
     * @return IConverter
     */
    public function makeConverter(): IConverter;

    /** Make instance of IXmlNavigator implementation
     * @return IXmlNavigator
     */
    public function makeNavigator(): IXmlNavigator;
}