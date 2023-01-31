<?php

declare(strict_types=1);

namespace SbWereWolf\XmlNavigator\Convertation;

use InvalidArgumentException;
use SbWereWolf\XmlNavigator\General\Notation;
use SbWereWolf\XmlNavigator\Parsing\FastXmlParser;
use XMLReader;

/**
 * Статический конвертор XML документа в PHP массив
 */
class FastXmlToArray implements IFastXmlToArray
{
    /* @inheritdoc */
    public static function convert(
        string $xmlText = '',
        string $xmlUri = '',
        string $val = Notation::VALUE,
        string $attr = Notation::ATTRIBUTES,
        string $name = Notation::NAME,
        string $seq = Notation::SEQUENCE,
        string $encoding = null,
        int $flags = LIBXML_BIGLINES | LIBXML_COMPACT,
    ): array {
        $reader = static::createXmlReader(
            $xmlText,
            $xmlUri,
            $encoding,
            $flags
        );

        $detectElement = function (XMLReader $cursor) {
            return $cursor->nodeType === XMLReader::ELEMENT;
        };
        $extractor = FastXmlParser::extractHierarchy(
            $reader,
            $detectElement,
            $val,
            $attr,
            $name,
            $seq,
        );
        $result = $extractor->current();

        $reader->close();

        return $result;
    }

    /* @inheritdoc */
    public static function prettyPrint(
        string $xmlText = '',
        string $xmlUri = '',
        string $val = Notation::VAL,
        string $attr = Notation::ATTR,
        string $encoding = null,
        int $flags = LIBXML_BIGLINES | LIBXML_COMPACT,
    ): array {
        $reader = static::createXmlReader(
            $xmlText,
            $xmlUri,
            $encoding,
            $flags
        );

        $detectElement = function (XMLReader $cursor) {
            return $cursor->nodeType === XMLReader::ELEMENT;
        };
        $extractor = FastXmlParser::extractPrettyPrint(
            $reader,
            $detectElement,
            $val,
            $attr,
        );
        $result = $extractor->current();

        $reader->close();

        return $result;
    }

    /**
     * @param string $xmlText The text of XML document
     * @param string $xmlUri Path or link to XML document
     * @param string|null $encoding The document encoding or NULL
     * @param int $flags A bitmask of the LIBXML_* constants.
     * @return XMLReader
     */
    private static function createXmlReader(
        string $xmlText,
        string $xmlUri,
        ?string $encoding,
        int $flags,
    ): XMLReader {
        if ($xmlText === '' && $xmlUri === '') {
            throw new InvalidArgumentException(
                'One of $xmlText or $xmlUri MUST BE defined,' .
                ' please assign only one of them, other MUST BE empty',
                -667
            );
        }

        $reader = new XMLReader();
        if ($xmlText !== '') {
            $reader = XMLReader::XML(
                $xmlText,
                $encoding,
                $flags,
            );
        }
        if ($xmlText === '' && $xmlUri !== '') {
            $reader = XMLReader::open(
                $xmlUri,
                $encoding,
                $flags,
            );
        }

        return $reader;
    }
}