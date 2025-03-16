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

$xml =
    '<One attr="val">text</One><Other attr1="" attr2=""/>' . PHP_EOL;

$file = fopen('data-for-stream.xml', 'w');
fwrite($file, "<Collection>$xml$xml$xml</Collection>");
fclose($file);

/** @var XMLReader $reader */
$reader = XMLReader::open('data-for-stream.xml');

$extractor = \SbWereWolf\XmlNavigator\Parsing\FastXmlParser
    ::extractHierarchy(
        $reader,
        /* callback for detect element for parsing */
        function (XMLReader $cursor) {
            return $cursor->name === 'One';
        }
    );
/* Extract all elements with name `One` */
foreach ($extractor as $element) {
    echo json_encode($element, JSON_PRETTY_PRINT) . PHP_EOL;
}

$reader->close();