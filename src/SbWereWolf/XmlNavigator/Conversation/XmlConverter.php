<?php

declare(strict_types=1);

namespace SbWereWolf\XmlNavigator\Conversation;

use JsonSerializable;
use SbWereWolf\JsonSerializable\JsonSerializeTrait;
use SbWereWolf\XmlNavigator\General\Notation;

/**
 * Конвертер XML документа в PHP массив
 *
 * @phpstan-import-type HierarchyNode from IFastXmlToArray
 * @phpstan-import-type PrettyNode from IFastXmlToArray
 */
class XmlConverter implements IXmlConverter, JsonSerializable
{
    use JsonSerializeTrait;

    /** @var HierarchyNode
     *      Структура XML документа в нормализованном виде */
    private array $xmlStructure = [];
    /** @var PrettyNode
     *       XML документа в виде удобном для чтения */
    private array $prettyXml = [];
    /** @var string Индекс для Имени */
    private string $name;
    /** @var string Индекс для Значения */
    private string $val;
    /** @var string Индекс для Атрибутов */
    private string $attr;
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
