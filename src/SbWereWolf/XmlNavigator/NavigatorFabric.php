<?php

declare(strict_types=1);

namespace SbWereWolf\XmlNavigator;

use RuntimeException;
use XMLReader;

class NavigatorFabric implements INavigatorFabric
{
    private string $xmlString = '';
    private string $xmlUri = '';
    /**
     * @var null
     */
    private $enc;
    private int $flags;

    /**
     * @param $encoding
     * @param int $flags
     */
    public function __construct($encoding = null, int $flags = 0,)
    {
        $this->enc = $encoding;
        $this->flags = $flags;
    }

    /* @inheritdoc */
    public function makeFromXmlString(string $xml): INavigatorFabric
    {
        $this->xmlString = $xml;
        $this->xmlUri = '';

        return $this;
    }

    /* @inheritdoc */
    public function makeFromXmlUri(string $uri): INavigatorFabric
    {
        $this->xmlString = '';
        $this->xmlUri = $uri;

        return $this;
    }

    /** Make instance of Converter
     * @return IConverter
     */
    public function makeConverter(): IConverter
    {
        $reader = new XMLReader();
        if ($this->xmlString !== '') {
            $reader = XMLReader::XML(
                $this->xmlString,
                $this->enc,
                $this->flags,
            );
        }
        if ($this->xmlUri !== '') {
            $reader = XMLReader::open(
                $this->xmlUri,
                $this->enc,
                $this->flags,
            );
        }

        return new Converter($reader);
    }

    /** Make instance of XmlNavigator
     * @return IXmlNavigator
     */
    public function makeNavigator(): IXmlNavigator
    {
        $converter = $this->makeConverter();
        $data = $converter->toNormalizedArray();

        if (!count($data[IConverter::ELEMS])) {
            throw new RuntimeException('XML do not has any elements');
        }

        return new XmlNavigator($data[IConverter::ELEMS][0]);
    }
}