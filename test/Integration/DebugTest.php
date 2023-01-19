<?php
/*
 * storage-for-all-things
 * Copyright © 2021 Volkhin Nikolay
 * 30.07.2021, 5:46
 */

declare(strict_types=1);

namespace Integration;

use PHPUnit\Framework\TestCase;
use SbWereWolf\XmlNavigator\IXmlNavigator;
use SbWereWolf\XmlNavigator\NavigatorFabric;

class DebugTest extends TestCase
{
    public function testXmlNavigator()
    {
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

        $fabric = (new NavigatorFabric())->makeFromXmlString($xml);
        $navigator = $fabric->makeNavigator();

        /* get element name */
        echo $navigator->name() . PHP_EOL;
        /* doc */

        /* get element value */
        echo $navigator->value() . PHP_EOL;
        /* '' */

        /* get list of attributes */
        echo var_export($navigator->attributes(), true) . PHP_EOL;
        /*
        array (
          0 => 'attrib',
          1 => 'option',
        )
        */

        /* get attribute value */
        echo $navigator->get('attrib') . PHP_EOL;
        /* a */

        /* get list of nested elements */
        echo var_export($navigator->elements(), true) . PHP_EOL;
        /*
        array (
          0 => 'base',
          1 => 'valuable',
          2 => 'complex',
        )
        */

        /* get desired nested element */
        $elem = $navigator->pull('valuable')->current();
        echo $elem->name() . PHP_EOL;
        /* valuable */

        /* get all nested elements */
        foreach ($navigator->pull() as $pulled) {
            /** @var IXmlNavigator $pulled */
            echo $pulled->name() . PHP_EOL;
            /* base */
            /* valuable */
            /* complex */
        }

        /* get nested element */
        /** @var IXmlNavigator $nested */
        $nested = $navigator->pull('complex')->current();
        /* get names of all elements of nested element */
        echo var_export($nested->elements(), true) . PHP_EOL;
        /*
        array (
          0 => 'a',
          1 => 'b',
          2 => 'b',
          3 => 'b',
          4 => 'c',
          5 => 'c',
          6 => 'c',
          7 => 'different',
        )
        */

        /* get all elements with name `b` */
        foreach ($nested->pull('b') as $b) {
            /** @var IXmlNavigator $b */
            echo ' element with name' .
                ' `' . $b->name() .
                '` have attribute `val` with value' .
                ' `' . $b->get('val') . '`' .
                PHP_EOL;
            /*
            element with name `b` have attribute `val` with value `x`
            element with name `b` have attribute `val` with value `y`
            element with name `b` have attribute `val` with value `z`
            */
        }

        $this->assertTrue(true);
    }

    public function testConverter()
    {
        $xml = <<<XML
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
XML;

        $fabric = (new NavigatorFabric())
            ->makeFromXmlString($xml);
        $converter = $fabric->makeConverter();
        $arrayRepresentationOfXml = $converter->toNormalizedArray();
        echo var_export($arrayRepresentationOfXml, true);
        /*
        array (
  'elems' =>
  array (
    0 =>
    array (
      'name' => 'complex',
      'elems' =>
      array (
        0 =>
        array (
          'name' => 'a',
          'attribs' =>
          array (
            'empty' => '',
          ),
          'elems' =>
          array (
          ),
        ),
        1 =>
        array (
          'name' => 'b',
          'attribs' =>
          array (
            'val' => 'x',
          ),
          'elems' =>
          array (
          ),
        ),
        2 =>
        array (
          'name' => 'b',
          'attribs' =>
          array (
            'val' => 'y',
          ),
          'elems' =>
          array (
          ),
        ),
        3 =>
        array (
          'name' => 'b',
          'attribs' =>
          array (
            'val' => 'z',
          ),
          'elems' =>
          array (
          ),
        ),
        4 =>
        array (
          'name' => 'c',
          'val' => '0',
          'elems' =>
          array (
          ),
        ),
        5 =>
        array (
          'name' => 'c',
          'attribs' =>
          array (
            'v' => 'o',
          ),
          'elems' =>
          array (
          ),
        ),
        6 =>
        array (
          'name' => 'c',
          'elems' =>
          array (
          ),
        ),
        7 =>
        array (
          'name' => 'different',
          'elems' =>
          array (
          ),
        ),
      ),
    ),
  ),
)
        */

        $this->assertTrue(true);
    }
}
