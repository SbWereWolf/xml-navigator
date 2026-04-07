<?php

declare(strict_types=1);

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
    private array $xmlStructure = [];
    /** @var PrettyNode
     *       Readable XML document representation */
    private array $prettyXml = [];
    /** @var string Index for the element name */
    private string $name;
    /** @var string Index for the element value */
    private string $val;
    /** @var string Index for element attributes */
    private string $attr;
    /** @var string  Index for child elements */
    private string $seq;
    /** @var string|null XML document encoding */
    private ?string $encoding;
    /** @var int Bitmask built from LIBXML_* constants */
    private int $flags;
    /** @var string Previous text of XML document */
    private string $previousXmlText = '';
    /** @var string Previous path or link to XML document */
    private string $previousXmlUri = '';

    /**
     * @param string $val index for the element value
     * @param string $attr index for element attributes
     * @param string $name index for the element name
     * @param string $seq Index for child elements
     * @param string|null $encoding
     * @param int $flags
     */
    public function __construct(
        string $val = Notation::VALUE,
        string $attr = Notation::ATTRIBUTES,
        string $name = Notation::NAME,
        string $seq = Notation::SEQUENCE,
        string|null $encoding = null,
        int $flags = LIBXML_BIGLINES | LIBXML_COMPACT,
    ) {
        $this->name = $name;
        $this->val = $val;
        $this->attr = $attr;
        $this->seq = $seq;
        $this->encoding = $encoding;
        $this->flags = $flags;
    }

    /**
     * @return PrettyNode
     */
    public function toPrettyPrint(
        string $xmlText = '',
        string $xmlUri = '',
    ): array {
        $isPrevious = $this->isPrevious($xmlText, $xmlUri);
        if (!$isPrevious || count($this->prettyXml) === 0) {
            $this->prettyXml =
                FastXmlToArray::prettyPrint(
                    $xmlText,
                    $xmlUri,
                    $this->val,
                    $this->attr,
                    $this->encoding,
                    $this->flags,
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
        string $xmlText = '',
        string $xmlUri = '',
    ): array {
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
                    $this->flags,
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
        string $xmlText,
        string $xmlUri
    ): bool {
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
}
