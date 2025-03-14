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
<elemWithNestedElems>
    <elemWithVal>val</elemWithVal>
    <elemWithAttribs one="atrib" other="atrib"/>
    <elemWithAll attribute_name="attribute_value">element value</elemWithAll>
</elemWithNestedElems>
XML;

$converter = new \SbWereWolf\XmlNavigator\Convertation\XmlConverter(
    \SbWereWolf\XmlNavigator\General\Notation::VAL,
    \SbWereWolf\XmlNavigator\General\Notation::ATTR,
);
$xmlAsArray =
    $converter->toPrettyPrint($xml);

$prettyPrint = json_encode($xmlAsArray, JSON_PRETTY_PRINT);
echo 'JSON representation of XML:'
    . PHP_EOL
    . $prettyPrint
    . PHP_EOL;

echo 'Array representation of XML:'
    . PHP_EOL
    . var_export($xmlAsArray, true)
    . PHP_EOL;
