<?php

declare(strict_types=1);

namespace SbWereWolf\XmlNavigator\Extraction;

use XMLReader;

/**
 * Extract whole element
 *
 * @phpstan-type ExtractedAttributes array<string, string>
 * @phpstan-type ExtractedProps array<string, array{0: int, 1?: string|ExtractedAttributes}>
 * @phpstan-type ExtractedElementData array<string, int|string|ExtractedAttributes>
 * @phpstan-type ExtractedElementFragment array<string, ExtractedElementData>
 * @phpstan-type ExtractedElementList list<ExtractedElementFragment>
 */
class ElementExtractor
{
    /** @var string Индекс для уровня вложенности элемента */
    public const string DEPTH = 'depth';
    private const array ALLOWED_NODE_TYPES = [
        XMLReader::ELEMENT,
        XMLReader::TEXT,
        XMLReader::CDATA,
    ];

    /**
     * @param XMLReader $reader
     * @param string $valueIndex index for element value
     * @param string $attributesIndex index for element attributes collection
     * @return ExtractedElementList
     */
    public static function extractElements(
        XMLReader $reader,
        string $valueIndex,
        string $attributesIndex,
    ): array {
        $elems = [];
        $path = [];
        $tryRead = true;
        $base = $reader->depth;
        while ($tryRead && $reader->nodeType !== XMLReader::ELEMENT) {
            $tryRead = $reader->read();
        }
        if ($tryRead) {
            $props = self::props($reader, $path);
            self::assume(
                $props,
                $elems,
                $attributesIndex,
                $valueIndex
            );
            $tryRead = $reader->read();
        }
        while ($tryRead && $reader->depth > $base) {
            $isAllowed = in_array(
                $reader->nodeType,
                self::ALLOWED_NODE_TYPES,
                true
            );
            if ($isAllowed) {
                $props = self::props($reader, $path);
                self::assume(
                    $props,
                    $elems,
                    $attributesIndex,
                    $valueIndex
                );
            }

            $tryRead = $reader->read();
        }

        return $elems;
    }

    /**
     * @param XMLReader $reader
     * @param array<int,string> $path
     * @return ExtractedProps
     */
    private static function props(
        XMLReader $reader,
        array &$path
    ): array {
        $result = [];
        $props = [];
        if (
            $reader->nodeType === XMLReader::ELEMENT
        ) {
            $path[$reader->depth] = $reader->name;
            $attribs = [];
            while ($reader->moveToNextAttribute()) {
                $attribs[$reader->name] = $reader->value;
            }

            $hasAttribs = count($attribs) !== 0;
            if (!$hasAttribs) {
                $result = [
                    $path[$reader->depth] => [0 => $reader->depth]
                ];
            }
            if ($hasAttribs) {
                $props[0] = $reader->depth - 1;
                $props[1] = $attribs;

                $result = [$path[$reader->depth - 1] => $props];
            }
        }

        if (
            $reader->nodeType === XMLReader::TEXT ||
            $reader->nodeType === XMLReader::CDATA
        ) {
            $props[0] = $reader->depth - 1;
            $props[1] = $reader->value;

            $result = [$path[$reader->depth - 1] => $props];
        }

        return $result;
    }

    /**
     * @param ExtractedProps $props
     * @param ExtractedElementList $elems
     * @param string $attributesIndex
     * @param string $valueIndex
     * @return void
     */
    private static function assume(
        array $props,
        array &$elems,
        string $attributesIndex,
        string $valueIndex
    ): void {
        $name = key($props);
        $data = current($props);
        if (!is_string($name) || !is_array($data)) {
            return;
        }

        $element = [
            $name => [
                static::DEPTH => $data[0],
            ],
        ];

        if (!array_key_exists(1, $data)) {
            $elems[] = $element;
            return;
        }

        $payload = $data[1];
        if (is_array($payload)) {
            $element[$name][$attributesIndex] = $payload;
            $elems[] = $element;
            return;
        }

        $element[$name][$valueIndex] = $payload;
        $elems[] = $element;
    }
}
