<?php

namespace SbWereWolf\XmlNavigator;

use Generator;

interface IXmlNavigator
{
    /** Returns the name of xml element
     * @return string
     */
    public function name(): string;

    /** Returns true if xml element has value
     * @return bool
     */
    public function hasValue(): bool;

    /** Returns the value of xml element
     * @return string
     */
    public function value(): string;

    /** Returns true if xml element has attributes
     * @return bool
     */
    public function hasAttributes(): bool;

    /** Returns names of all attributes of xml element
     * @return array
     */
    public function attributes(): array;

    /** Get value of attribute with the $name,
     * if $name is not defined, than returns value of random attribute
     * @param string|null $name
     * @return string
     */
    public function get(string $name = null): string;

    /** Returns true if xml element has nested elements
     * @return bool
     */
    public function hasElements(): bool;

    /** Returns names of all nested elements
     * @return array
     */
    public function elements(): array;

    /** Pull IXmlNavigator for nested element,
     * if $name is defined, than pull elements with the $name
     * @param string $name
     * @return IXmlNavigator
     */
    public function pull(string $name = ''): Generator;
}