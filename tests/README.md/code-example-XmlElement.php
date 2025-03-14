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
<doc attrib="a" option="o" >
    <base/>
    <valuable>element value</valuable>
    <complex>
        <a empty=""/>
        <b val="x"/>
        <b val="y"/>
        <b val="z"/>
        <c>0</c>
        <c v="o"/>
        <c/>
        <different/>
    </complex>
</doc>
XML;

$content = \SbWereWolf\XmlNavigator\Convertation\FastXmlToArray
    ::convert($xml);
$navigator =
    new \SbWereWolf\XmlNavigator\Navigation\XmlElement($content);

/* Convert this XmlElement to array,
with the array you may restore XmlElement
 (create same as original one) */
$gist = $navigator->serialize();
echo assert($content === $gist) ? 'is same' : 'is different';
echo PHP_EOL;

/* get name of element */
echo $navigator->name() . PHP_EOL;
/* doc */

/* get value of element */
echo "`{$navigator->value()}`" . PHP_EOL;
/* `` */

/* get list of attributes */
$attributes = $navigator->attributes();
foreach ($attributes as $attribute) {
    /** @var \SbWereWolf\XmlNavigator\Navigation\IXmlAttribute $attribute */
    echo "`{$attribute->name()}` `{$attribute->value()}`" . PHP_EOL;
}
/*
`attrib` `a`
`option` `o`
*/

/* get value of attribute */
echo $navigator->get('attrib') . PHP_EOL;
/* a */

/* get list of nested elements */
$elements = $navigator->elements();
foreach ($elements as $element) {
    echo "{$element->name()}" . PHP_EOL;
}
/*
base
valuable
complex
 */

/* get desired nested element */
/** @var \SbWereWolf\XmlNavigator\Navigation\IXmlElement $elem */
$elem = $navigator->pull('valuable')->current();
echo $elem->name() . PHP_EOL;
/* valuable */

/* get all nested elements */
foreach ($navigator->pull() as $pulled) {
    /** @var \SbWereWolf\XmlNavigator\Navigation\IXmlElement $pulled */
    echo $pulled->name() . PHP_EOL;
    /*
    base
    valuable
    complex
    */
}

/* get nested element with given name */
/** @var \SbWereWolf\XmlNavigator\Navigation\IXmlElement $nested */
$nested = $navigator->pull('complex')->current();
/* get names of all elements of nested element */
$elements = $nested->elements();
foreach ($elements as $element) {
    echo "{$element->name()}" . PHP_EOL;
}
/*
a
b
b
b
c
c
c
different
*/

/* pull all elements with name `b` */
foreach ($nested->pull('b') as $b) {
    /** @var \SbWereWolf\XmlNavigator\Navigation\IXmlElement $b */
    echo ' element with name' .
        ' `' . $b->name() .
        '` have attribute `val` with value' .
        ' `' . $b->get('val') . '`' .
        PHP_EOL;
}
/*
 element with name `b` have attribute `val` with value `x`
 element with name `b` have attribute `val` with value `y`
 element with name `b` have attribute `val` with value `z`
*/
