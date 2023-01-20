<?php

declare(strict_types=1);

namespace SbWereWolf\XmlNavigator;

class Converter implements IConverter
{
    private array $xmlStructure = [];
    private array $prettyXml = [];
    private string $name;
    private string $val;
    private string $attribs;
    private string $elems;
    private ?string $encoding;
    private int $flags;

    /**
     * @param string $name
     * @param string $val
     * @param string $attribs
     * @param string $elems
     * @param string|null $encoding
     * @param int $flags
     */
    public function __construct(
        string $name = IFastXmlToArray::NAME,
        string $val = IFastXmlToArray::VAL,
        string $attribs = IFastXmlToArray::ATTRIBS,
        string $elems = IFastXmlToArray::ELEMS,
        string $encoding = null,
        int $flags = LIBXML_BIGLINES | LIBXML_COMPACT,
    ) {
        $this->name = $name;
        $this->val = $val;
        $this->attribs = $attribs;
        $this->elems = $elems;
        $this->encoding = $encoding;
        $this->flags = $flags;
    }

    /* @inheritdoc */
    public function prettyPrint(
        string $xmlText = '',
        string $xmlUri = '',
    ): array {
        if (!count($this->prettyXml)) {
            $this->prettyXml =
                FastXmlToArray::prettyPrint(
                    $xmlText,
                    $xmlUri,
                    $this->val,
                    $this->attribs,
                    $this->encoding,
                    $this->flags,
                );
        }

        return $this->prettyXml;
    }

    /* @inheritdoc */
    public function xmlStructure(
        string $xmlText = '',
        string $xmlUri = '',
    ): array {
        if (!count($this->xmlStructure)) {
            $this->xmlStructure =
                FastXmlToArray::convert(
                    $xmlText,
                    $xmlUri,
                    $this->name,
                    $this->val,
                    $this->attribs,
                    $this->elems,
                    $this->encoding,
                    $this->flags,
                );
        }

        return $this->xmlStructure;
    }
}
