<?php

use SbWereWolf\XmlNavigator\Conversion\XmlConverter;

require_once __DIR__ . '/../vendor/autoload.php';

$converter = new XmlConverter(
    'value',
    'attributes',
    'name',
    'children'
);

$hierarchy = $converter->toHierarchyOfElements(
    '<price currency="USD">129.90</price>'
);

var_export($hierarchy);
