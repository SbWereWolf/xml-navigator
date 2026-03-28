<?php

declare(strict_types=1);

namespace SbWereWolf\XmlNavigator\Convertation;

use InvalidArgumentException;
use SbWereWolf\XmlNavigator\General\Notation;
use SbWereWolf\XmlNavigator\Parsing\FastXmlParser;
use XMLReader;

/**
 * Статический конвертер XML документа в PHP массив
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
        string $xmlText = '',
        string $xmlUri = '',
        string $val = Notation::VALUE,
        string $attr = Notation::ATTRIBUTES,
        string $name = Notation::NAME,
        string $seq = Notation::SEQUENCE,
        string|null $encoding = null,
        int $flags = LIBXML_BIGLINES | LIBXML_COMPACT,
    ): array {
        /** @var \Closure(XMLReader):array<mixed, mixed> $parse */
        $parse = static function (
            XMLReader $reader,
        ) use (
            $xmlText,
            $xmlUri,
            $val,
            $attr,
            $name,
            $seq,
        ): array {
            return self::requireArrayResult(
                FastXmlParser::extractHierarchy(
                    $reader,
                    static fn (XMLReader $cursor): bool =>
                        $cursor->nodeType === XMLReader::ELEMENT,
                    $val,
                    $attr,
                    $name,
                    $seq,
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
        string $xmlText = '',
        string $xmlUri = '',
        string $val = Notation::VAL,
        string $attr = Notation::ATTR,
        string|null $encoding = null,
        int $flags = LIBXML_BIGLINES | LIBXML_COMPACT,
    ): array {
        /** @var \Closure(XMLReader):array<mixed, mixed> $parse */
        $parse = static function (
            XMLReader $reader,
        ) use (
            $xmlText,
            $xmlUri,
            $val,
            $attr,
        ): array {
            return self::requireArrayResult(
                FastXmlParser::extractPrettyPrint(
                    $reader,
                    static fn (XMLReader $cursor): bool =>
                        $cursor->nodeType === XMLReader::ELEMENT,
                    $val,
                    $attr,
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
     * @param \Closure(XMLReader):array<mixed, mixed> $parse
     * @return array<mixed, mixed>
     */
    private static function parseRootElement(
        string $xmlText,
        string $xmlUri,
        ?string $encoding,
        int $flags,
        \Closure $parse,
    ): array {
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
        string $xmlText,
        string $xmlUri,
        string|null $encoding,
        int $flags,
    ): XMLReader {
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

        if ($xmlText !== '') {
            $reader = @XMLReader::XML(
                $xmlText,
                $encoding,
                $flags,
            );
            if (!$reader instanceof XMLReader) {
                throw self::buildParsingException($xmlText, '');
            }

            return $reader;
        }

        $reader = @XMLReader::open(
            $xmlUri,
            $encoding,
            $flags,
        );
        if (!$reader instanceof XMLReader) {
            throw new InvalidArgumentException(
                'Unable to open XML source from URI `' . $xmlUri . '`.',
                -670
            );
        }

        return $reader;
    }

    private static function buildParsingException(
        string $xmlText,
        string $xmlUri
    ): InvalidArgumentException {
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
     * @param mixed $result
     * @return array<mixed, mixed>
     */
    private static function requireArrayResult(
        mixed $result,
        string $xmlText,
        string $xmlUri
    ): array {
        if (!is_array($result) || $result === []) {
            throw self::buildParsingException($xmlText, $xmlUri);
        }

        return $result;
    }

    private static function formatLibxmlErrors(): string
    {
        $errors = libxml_get_errors();
        if ($errors === []) {
            return '';
        }

        $messages = array_map(
            static fn (\LibXMLError $error): string =>
                trim($error->message),
            $errors
        );

        return ' ' . implode(' | ', $messages);
    }
}
