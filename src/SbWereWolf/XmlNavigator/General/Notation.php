<?php

declare(strict_types=1);

namespace SbWereWolf\XmlNavigator\General;

/**
 * Interface with array indexes witch one uses every other classes
 */
interface Notation
{
    /** @var string Индекс Имени в нормализованном виде */
    const NAME = 'n';
    /** @var string Индекс Значения в нормализованном виде */
    const VALUE = 'v';
    /** @var string Индекс Атрибутов в нормализованном виде */
    const ATTRIBUTES = 'a';
    /** @var string Индекс Последовательности вложенных элементов
     * в нормализованном виде
     */
    const SEQUENCE = 's';

    /** @var string Индекс для Значения в формате pretty print */
    const VAL = '@value';
    /** @var string Индекс для Атрибутов в формате pretty print */
    const ATTR = '@attributes';
}
