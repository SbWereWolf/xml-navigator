<?php

declare(strict_types=1);

use SbWereWolf\XmlNavigator\Conversion\XmlConverter;

require_once __DIR__ . '/../vendor/autoload.php';

$converter = new XmlConverter(
    val: 'value',
    attr: 'attributes',
    name: 'name',
    seq: 'children',
);

$hierarchy = $converter->toHierarchyOfElements(
    '<price currency="USD">129.90</price>'
);

var_export($hierarchy);
