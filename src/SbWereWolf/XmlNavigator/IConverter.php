<?php

namespace SbWereWolf\XmlNavigator;

interface IConverter
{
    /** get array representation of xml document
     * @return array
     */
    public function prettyPrint(
        string $xmlText = '',
        string $xmlUri = '',
    ): array;

    /** get array representation of xml document
     * @return array
     */
    public function xmlStructure(
        string $xmlText = '',
        string $xmlUri = '',
    ): array;
}
