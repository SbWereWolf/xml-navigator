<?php

namespace SbWereWolf\XmlNavigator\Conversion;

use SbWereWolf\XmlNavigator\General\Notation;

/**
 * Converts an XML document into a PHP array
 *
 * @phpstan-import-type HierarchyNode from IFastXmlToArray
 * @phpstan-import-type PrettyNode from IFastXmlToArray
 */
class XmlConverter implements IXmlConverter
{
    /** @var HierarchyNode
     *      Normalized XML document structure */
    private $xmlStructure = [];
    /** @var PrettyNode
     *       Readable XML document representation */
    private $prettyXml = [];
    /** @var string Index for the element name */
    private $name;
    /** @var string Index for the element value */
    private $val;
    /** @var string Index for element attributes */
    private $attr;
    /** @var string  Index for child elements */
    private $seq;
    /** @var string|null XML document encoding */
    private $encoding;
    /** @var int Bitmask built from LIBXML_* constants */
    private $flags;
    /** @var string Previous text of XML document */
    private $previousXmlText = '';
    /** @var string Previous path or link to XML document */
    private $previousXmlUri = '';

    /**
     * @param string $val index for the element value
     * @param string $attr index for element attributes
     * @param string $name index for the element name
     * @param string $seq Index for child elements
     * @param string|null $encoding XML document encoding or `null`
     * @param int|null $flags
     */
    public function __construct(
        $val = Notation::VALUE,
        $attr = Notation::ATTRIBUTES,
        $name = Notation::NAME,
        $seq = Notation::SEQUENCE,
        $encoding = null,
        $flags = null
    ) {
        $this->name = $name;
        $this->val = $val;
        $this->attr = $attr;
        $this->seq = $seq;
        $this->encoding = $encoding;
        $this->flags = $flags === null ? self::defaultLibxmlFlags() : $flags;
    }

    /**
     * @return PrettyNode
     */
    public function toPrettyPrint(
        $xmlText = '',
        $xmlUri = ''
    ) {
        $isPrevious = $this->isPrevious($xmlText, $xmlUri);
        if (!$isPrevious || count($this->prettyXml) === 0) {
            $this->prettyXml =
                FastXmlToArray::prettyPrint(
                    $xmlText,
                    $xmlUri,
                    $this->val,
                    $this->attr,
                    $this->encoding,
                    $this->flags
                );

            $this->previousXmlText = $xmlText;
            $this->previousXmlUri = $xmlUri;
        }

        return $this->prettyXml;
    }

    /**
     * @return HierarchyNode
     */
    public function toHierarchyOfElements(
        $xmlText = '',
        $xmlUri = ''
    ) {
        $isPrevious = $this->isPrevious($xmlText, $xmlUri);
        if (!$isPrevious || count($this->xmlStructure) === 0) {
            $this->xmlStructure =
                FastXmlToArray::convert(
                    $xmlText,
                    $xmlUri,
                    $this->val,
                    $this->attr,
                    $this->name,
                    $this->seq,
                    $this->encoding,
                    $this->flags
                );

            $this->previousXmlText = $xmlText;
            $this->previousXmlUri = $xmlUri;
        }

        return $this->xmlStructure;
    }

    /**
     * @param string $xmlText
     * @param string $xmlUri
     * @return bool
     */
    private function isPrevious(
        $xmlText,
        $xmlUri
    ) {
        $isPrevious = true;
        if ($xmlText !== '' && $xmlText !== $this->previousXmlText) {
            $isPrevious = false;
        }
        if (
            $xmlText === '' &&
            $xmlUri !== '' &&
            $xmlUri !== $this->previousXmlUri
        ) {
            $isPrevious = false;
        }
        return $isPrevious;
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
}
