<?php

declare(strict_types=1);

namespace SbWereWolf\XmlNavigator\General;

/**
 * Interface with array indexes witch one uses every other classes
 */
interface Notation
{
    /** @var string Name index in normalized form */
    const NAME = 'n';
    /** @var string Value index in normalized form */
    const VALUE = 'v';
    /** @var string Attributes index in normalized form */
    const ATTRIBUTES = 'a';
    /** @var string Nested elements sequence index in normalized form */
    const SEQUENCE = 's';

    /** @var string Value index in pretty-print format */
    const VAL = '@value';
    /** @var string Attributes index in pretty-print format */
    const ATTR = '@attributes';
}
