<?php

declare(strict_types=1);

namespace SbWereWolf\XmlNavigator\Convertation;

use JsonSerializable;
use SbWereWolf\JsonSerializable\JsonSerializeTrait;
use SbWereWolf\XmlNavigator\General\Notation;

/**
 * Конвертор XML документа в PHP массив
 */
class XmlConverter implements IXmlConverter, JsonSerializable
{
    use JsonSerializeTrait;

    /** @var array Структура XML документа в нормализованном виде */
    private array $xmlStructure = [];
    /** @var array  XML документа в виде удобном для чтения */
    private array $prettyXml = [];
    /** @var string Индекс для Имени */
    private string $name;
    /** @var string Индекс для Значения */
    private string $val;
    /** @var string Индекс для Атрибутов */
    private string $attribs;
    /** @var string  Индекс для вложенных элементов */
    private string $seq;
    /** @var string|null Кодировка XML Документа */
    private ?string $encoding;
    /** @var int Битовая маска из констант LIBXML_* */
    private int $flags;
    /** @var string Previous text of XML document */
    private string $previousXmlText = '';
    /** @var string Previous path or link to XML document */
    private string $previousXmlUri = '';

    /**
     * @param string $val Индекс для значения
     * @param string $attr Индекс для атрибутов
     * @param string $name Индекс для имени
     * @param string $seq Индекс для вложенных элементов
     * @param string|null $encoding
     * @param int $flags
     */
    public function __construct(
        string $val = Notation::VALUE,
        string $attr = Notation::ATTRIBUTES,
        string $name = Notation::NAME,
        string $seq = Notation::SEQUENCE,
        string $encoding = null,
        int $flags = LIBXML_BIGLINES | LIBXML_COMPACT,
    ) {
        $this->name = $name;
        $this->val = $val;
        $this->attribs = $attr;
        $this->seq = $seq;
        $this->encoding = $encoding;
        $this->flags = $flags;
    }

    /* @inheritdoc */
    public function toPrettyPrint(
        string $xmlText = '',
        string $xmlUri = '',
    ): array {
        $isPrevious = $this->isPrevious($xmlText, $xmlUri);
        if (!$isPrevious || !count($this->prettyXml)) {
            $this->prettyXml =
                FastXmlToArray::prettyPrint(
                    $xmlText,
                    $xmlUri,
                    $this->val,
                    $this->attribs,
                    $this->encoding,
                    $this->flags,
                );

            $this->previousXmlText = $xmlText;
            $this->previousXmlUri = $xmlUri;
        }

        return $this->prettyXml;
    }

    /* @inheritdoc */
    public function toHierarchyOfElements(
        string $xmlText = '',
        string $xmlUri = '',
    ): array {
        $isPrevious = $this->isPrevious($xmlText, $xmlUri);
        if (!$isPrevious || !count($this->xmlStructure)) {
            $this->xmlStructure =
                FastXmlToArray::convert(
                    $xmlText,
                    $xmlUri,
                    $this->val,
                    $this->attribs,
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
