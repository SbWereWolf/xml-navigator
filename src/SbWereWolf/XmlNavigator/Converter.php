<?php

declare(strict_types=1);

namespace SbWereWolf\XmlNavigator;

use JsonSerializable;
use XMLReader;

/**
 * Конвертор XML документа в PHP массив
 */
class Converter implements IConverter, JsonSerializable
{
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

    /**
     * @param string $val Индекс для значения
     * @param string $attr Индекс для атрибутов
     * @param string $name Индекс для имени
     * @param string $seq Индекс для вложенных элементов
     * @param string|null $encoding
     * @param int $flags
     */
    public function __construct(
        string $val = IFastXmlToArray::VALUE,
        string $attr = IFastXmlToArray::ATTRIBUTES,
        string $name = IFastXmlToArray::NAME,
        string $seq = IFastXmlToArray::SEQUENCE,
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
                    $this->seq,
                    $this->encoding,
                    $this->flags,
                );
        }

        return $this->xmlStructure;
    }

    /* @inheritdoc */
    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }

    public function extractElements(XMLReader $reader): array
    {
        $result = FastXmlToArray::extractElements(
            $reader,
            $this->val,
            $this->attribs,
        );

        return $result;
    }

    public function createTheHierarchyOfElements(array $elems): array
    {
        $result = FastXmlToArray::createTheHierarchyOfElements(
            $elems,
            $this->name,
            $this->val,
            $this->attribs,
            $this->seq,
        );

        return $result;
    }

    public function composePrettyPrintByXmlElements(array $elems): array
    {
        $result = FastXmlToArray::composePrettyPrintByXmlElements(
            $elems,
            $this->val,
            $this->attribs,
        );

        return $result;
    }
}
