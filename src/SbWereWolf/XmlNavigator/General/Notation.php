<?php

declare(strict_types=1);

namespace SbWereWolf\XmlNavigator\General;

/**
 * Interface with array indexes witch one uses every other classes
 */
interface Notation
{
    /** @var string Индекс Имени в нормализованном виде */
    public const NAME = 'n';
    /** @var string Индекс Значения в нормализованном виде */
    public const VALUE = 'v';
    /** @var string Индекс Атрибутов в нормализованном виде */
    public const ATTRIBUTES = 'a';
    /** @var string Индекс Последовательности вложенных элементов
     * в нормализованном виде
     */
    public const SEQUENCE = 's';

    /** @var string Индекс для Значения в формате pretty print */
    public const VAL = '@value';
    /** @var string Индекс для Атрибутов в формате pretty print */
    public const ATTR = '@attributes';
}