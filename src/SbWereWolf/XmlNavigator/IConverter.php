<?php

declare(strict_types=1);

namespace SbWereWolf\XmlNavigator;

interface IConverter
{
    /** Convert xml document into compact array
     * @return array
     */
    public function prettyPrint(
        string $xmlText = '',
        string $xmlUri = '',
    ): array;

    /** Convert xml document into normalized array
     * @return array
     */
    public function xmlStructure(
        string $xmlText = '',
        string $xmlUri = '',
    ): array;
}
