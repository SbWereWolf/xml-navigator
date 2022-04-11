<?php

namespace SbWereWolf\XmlNavigator;

use Generator;

interface IXmlNavigator
{
    /** get name of xml element
     * @return string
     */
    public function name(): string;

    /** true if xml element has value
     * @return bool
     */
    public function hasValue(): bool;

    /** the value of xml element
     * @return string
     */
    public function value(): string;

    /** true if xml element has attributes
     * @return bool
     */
    public function hasAttribs(): bool;

    /** get all attributes of xml element
     * @return array
     */
    public function attribs(): array;

    /** get value of attribute $name
     * @param string|null $name
     * @return string
     */
    public function get(string $name = null): string;

    /** true if xml element has nested elements
     * @return bool
     */
    public function hasElements(): bool;

    /** get all elements of xml element
     * @return array
     */
    public function elements(): array;

    /** get Navigator for nested element
     * @param string $name
     * @return IXmlNavigator
     */
    public function pull(string $name): IXmlNavigator;

    /** true if xml element is multiple
     * @return bool
     */
    public function isMultiple(): bool;

    /** get next one of multiple nested xml element
     * @return IXmlNavigator
     */
    public function next(): Generator;
}