<?php

declare(strict_types=1);

namespace SbWereWolf\XmlNavigator;

use InvalidArgumentException;
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
        string $val = IElementComposer::VALUE,
        string $attribs = IElementComposer::ATTRIBUTES,
        string $name = IElementComposer::NAME,
        string $seq = IElementComposer::SEQUENCE,
        string $encoding = null,
        int $flags = LIBXML_BIGLINES | LIBXML_COMPACT,
    ): array {
        $reader = static::createXmlReader(
            $xmlText,
            $xmlUri,
            $encoding,
            $flags
        );
        $result = HierarchyComposer::compose(
            $reader,
            $val,
            $attribs,
            $name,
            $seq,
        );

        $reader->close();

        return $result;
    }

    /* @inheritdoc */
    public static function prettyPrint(
        string $xmlText = '',
        string $xmlUri = '',
        string $val = IElementComposer::VAL,
        string $attribs = IElementComposer::ATTR,
        string $encoding = null,
        int $flags = LIBXML_BIGLINES | LIBXML_COMPACT,
    ): array {
        $reader = static::createXmlReader(
            $xmlText,
            $xmlUri,
            $encoding,
            $flags
        );
        $result = PrettyPrintComposer::compose(
            $reader,
            $val,
            $attribs,
        );

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
                ' please assign only one of them, other MUST BE empty'
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