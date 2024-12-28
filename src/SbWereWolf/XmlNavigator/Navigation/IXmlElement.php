<?php

declare(strict_types=1);

namespace SbWereWolf\XmlNavigator\Navigation;

use Generator;

/**
 * Интерфейс для объекта XML элемента
 */
interface IXmlElement
{
    /** Returns the name of XML element
     * @return string
     */
    public function name(): string;

    /** Returns true if XML element has value
     * @return bool
     */
    public function hasValue(): bool;

    /** Returns the value of XML element
     * @return string
     */
    public function value(): string;

    /** Returns true if XML element has attributes with $name.
     * If $name omitted, then
     * returns true if XML element has any attribute
     * @param string $name
     * @return bool
     */
    public function hasAttribute(string $name = ''): bool;

    /** Returns all attributes of XML element
     * @return IXmlAttribute[]
     */
    public function attributes(): array;

    /** Get value of attribute with the $name.
     * If $name is omitted, then returns value of random attribute
     * @param string|null $name
     * @return string
     */
    public function get(string|null $name = null): string;

    /** Returns true if XML element has nested element with `$name`.
     * If $name omitted, than
     * returns true if XML element has any nested element
     * @param string $name
     * @return bool
     */
    public function hasElement(string $name = ''): bool;

    /** Returns all nested elements
     * @return IXmlElement[]
     */
    public function elements(string $name = ''): array;

    /** Pull IXmlElement for nested elements,
     * if $name is defined, than pull elements with the $name,
     * elements will be type of IXmlElement
     * @param string $name
     * @return Generator<IXmlElement>
     */
    public function pull(string $name = ''): Generator;
}