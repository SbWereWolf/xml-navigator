<?php

namespace SbWereWolf\XmlNavigator\Conversion;

use InvalidArgumentException;
use SbWereWolf\XmlNavigator\General\Notation;
use SbWereWolf\XmlNavigator\Parsing\FastXmlParser;
use XMLReader;

/**
 * Converts an XML document into a PHP array with static methods
 *
 * @phpstan-import-type HierarchyNode from IFastXmlToArray
 * @phpstan-import-type PrettyNode from IFastXmlToArray
 */
class FastXmlToArray implements IFastXmlToArray
{
    /**
     * @return HierarchyNode
     */
    public static function convert(
        $xmlText = '',
        $xmlUri = '',
        $val = Notation::VALUE,
        $attr = Notation::ATTRIBUTES,
        $name = Notation::NAME,
        $seq = Notation::SEQUENCE,
        $encoding = null,
        $flags = null
    ) {
        if ($flags === null) {
            $flags = self::defaultLibxmlFlags();
        }

        /** @var \Closure(XMLReader):array<mixed, mixed> $parse */
        $parse = static function (
            XMLReader $reader
        ) use (
            $xmlText,
            $xmlUri,
            $val,
            $attr,
            $name,
            $seq
        ) {
            return self::requireArrayResult(
                FastXmlParser::extractHierarchy(
                    $reader,
                    static function (XMLReader $cursor) {
                        return $cursor->nodeType === XMLReader::ELEMENT;
                    },
                    $val,
                    $attr,
                    $name,
                    $seq
                )->current(),
                $xmlText,
                $xmlUri
            );
        };

        /** @var HierarchyNode $result */
        $result = self::parseRootElement(
            $xmlText,
            $xmlUri,
            $encoding,
            $flags,
            $parse
        );

        return $result;
    }

    /**
     * @return PrettyNode
     */
    public static function prettyPrint(
        $xmlText = '',
        $xmlUri = '',
        $val = Notation::VAL,
        $attr = Notation::ATTR,
        $encoding = null,
        $flags = null
    ) {
        if ($flags === null) {
            $flags = self::defaultLibxmlFlags();
        }

        /** @var \Closure(XMLReader):array<mixed, mixed> $parse */
        $parse = static function (
            XMLReader $reader
        ) use (
            $xmlText,
            $xmlUri,
            $val,
            $attr
        ) {
            return self::requireArrayResult(
                FastXmlParser::extractPrettyPrint(
                    $reader,
                    static function (XMLReader $cursor) {
                        return $cursor->nodeType === XMLReader::ELEMENT;
                    },
                    $val,
                    $attr
                )->current(),
                $xmlText,
                $xmlUri
            );
        };

        /** @var PrettyNode $result */
        $result = self::parseRootElement(
            $xmlText,
            $xmlUri,
            $encoding,
            $flags,
            $parse
        );

        return $result;
    }

    /**
     * @param string|null $encoding
     * @param \Closure(XMLReader):array<mixed, mixed> $parse
     * @return array<mixed, mixed>
     */
    private static function parseRootElement(
        $xmlText,
        $xmlUri,
        $encoding,
        $flags,
        \Closure $parse
    ) {
        $reader = null;
        $hadInternalErrors = libxml_use_internal_errors(true);
        libxml_clear_errors();

        try {
            $reader = self::createXmlReader(
                $xmlText,
                $xmlUri,
                $encoding,
                $flags
            );
            $result = $parse($reader);
            if (libxml_get_errors() !== []) {
                throw self::buildParsingException($xmlText, $xmlUri);
            }

            return $result;
        } finally {
            if ($reader instanceof XMLReader) {
                $reader->close();
            }

            libxml_clear_errors();
            libxml_use_internal_errors($hadInternalErrors);
        }
    }

    /**
     * @param string $xmlText The text of XML document
     * @param string $xmlUri Path or link to XML document
     * @param string|null $encoding The document encoding or NULL
     * @param int $flags A bitmask of the LIBXML_* constants.
     * @return XMLReader
     */
    private static function createXmlReader(
        $xmlText,
        $xmlUri,
        $encoding,
        $flags
    ) {
        if ($xmlText === '' && $xmlUri === '') {
            throw new InvalidArgumentException(
                'Exactly one XML source must be provided: set either ' .
                '$xmlText or $xmlUri.',
                -667
            );
        }
        if ($xmlText !== '' && $xmlUri !== '') {
            throw new InvalidArgumentException(
                'XML source selection is ambiguous: use either ' .
                '$xmlText or $xmlUri, not both.',
                -668
            );
        }

        $reader = new XMLReader();

        if ($xmlText !== '') {
            @$reader->XML(
                $xmlText,
                $encoding,
                $flags
            );

            return $reader;
        }

        $opened = @$reader->open(
            $xmlUri,
            $encoding,
            $flags
        );
        if ($opened !== true) {
            throw new InvalidArgumentException(
                'Unable to open XML source from URI `' . $xmlUri . '`.',
                -671
            );
        }

        return $reader;
    }

    private static function buildParsingException(
        $xmlText,
        $xmlUri
    ) {
        $details = self::formatLibxmlErrors();
        if ($xmlText !== '') {
            return new InvalidArgumentException(
                'Unable to parse XML from $xmlText.' . $details,
                -669
            );
        }

        return new InvalidArgumentException(
            'Unable to parse XML from URI `' . $xmlUri . '`.' . $details,
            -670
        );
    }

    /**
     * @param mixed $result Parsed root element result
     * @return array<mixed, mixed>
     */
    private static function requireArrayResult(
        $result,
        $xmlText,
        $xmlUri
    ) {
        if (!is_array($result) || $result === []) {
            throw self::buildParsingException($xmlText, $xmlUri);
        }

        return $result;
    }

    /**
     * @return int
     */
    private static function defaultLibxmlFlags()
    {
        $flags = LIBXML_COMPACT;

        if (defined('LIBXML_BIGLINES')) {
            $flags |= LIBXML_BIGLINES;
        }

        return $flags;
    }

    private static function formatLibxmlErrors()
    {
        $errors = libxml_get_errors();
        if ($errors === []) {
            return '';
        }

        $messages = array_map(
            static function (\LibXMLError $error) {
                return trim($error->message);
            },
            $errors
        );

        return ' ' . implode(' | ', $messages);
    }
}
