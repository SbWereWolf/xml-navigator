<?php


$parts = [
    __DIR__,
    '..',
    '..',
    'vendor',
    'autoload.php',
];
$autoload = join(DIRECTORY_SEPARATOR, $parts);

require_once $autoload;

$xml = <<<XML
<ElemWithNestedElems>
    <ElemWithVal>val</ElemWithVal>
    <ElemWithAttribs one="atrib" other="atrib"/>
    <ElemWithAll attribute_name="attribute-value">
        element value
    </ElemWithAll>
</ElemWithNestedElems>
XML;

$converter = new \SbWereWolf\XmlNavigator\Convertation\XmlConverter(
    val: 'value',
    attr: 'attributes',
    name: 'name',
    seq: 'sequence',
);
$xmlAsArray = $converter->toHierarchyOfElements($xml);

$prettyPrint = json_encode($xmlAsArray, JSON_PRETTY_PRINT);
echo 'JSON representation of XML:'
    . PHP_EOL
    . $prettyPrint
    . PHP_EOL;

echo 'Array representation of XML:'
    . PHP_EOL
    . var_export($xmlAsArray, true)
    . PHP_EOL;
