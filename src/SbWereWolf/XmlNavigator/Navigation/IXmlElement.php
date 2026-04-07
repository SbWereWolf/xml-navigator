<?php

namespace SbWereWolf\XmlNavigator\Navigation;

use Generator;
use SbWereWolf\XmlNavigator\Conversion\IFastXmlToArray;

/**
 * Contract for an XML element object
 *
 * @phpstan-import-type HierarchyNode from IFastXmlToArray
 */
interface IXmlElement
{
    /** Returns the name of XML element
     * @return string
     */
    public function name();

    /** Returns true if XML element has value
     * @return bool
     */
    public function hasValue();

    /** Returns the value of XML element
     * @return string
     */
    public function value();

    /** Returns true if XML element has attributes with $name.
     * If $name omitted, then
     * returns true if XML element has any attribute
     * @param string $name
     * @return bool
     */
    public function hasAttribute($name = '');

    /** Returns all attributes of XML element
     * @return IXmlAttribute[]
     */
    public function attributes();

    /** Get value of attribute with the $name.
     * If $name is omitted, then returns value of random attribute
     * @param string $name
     * @return string
     */
    public function get($name = '');

    /** Returns true if XML element has nested element with `$name`.
     * If $name omitted, than
     * returns true if XML element has any nested element
     * @param string $name
     * @return bool
     */
    public function hasElement($name = '');

    /** Returns all nested elements
     * @return IXmlElement[]
     */
    public function elements($name = '');

    /** Pull nested elements as IXmlElement,
     * if $name is defined, than pull elements only with the $name.
     * @param string $name
     * @return Generator<int, IXmlElement>
     */
    public function pull($name = '');

    /** Generates a storable representation ($data) of a IXmlElement
     * use new XmlElement($data) to restore XmlElement object
     * @return HierarchyNode
     */
    public function serialize();
}
