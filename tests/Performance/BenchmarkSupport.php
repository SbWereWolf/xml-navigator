<?php

declare(strict_types=1);

namespace SbWereWolf\XmlNavigator\Bench;

use InvalidArgumentException;
use RuntimeException;
use SbWereWolf\XmlNavigator\Conversion\FastXmlToArray;
use SbWereWolf\XmlNavigator\Conversion\XmlConverter;
use SbWereWolf\XmlNavigator\Extraction\PrettyPrintComposer;
use SbWereWolf\XmlNavigator\Navigation\XmlElement;
use SbWereWolf\XmlNavigator\Parsing\FastXmlParser;
use XMLReader;

const MANIFEST_FILE = 'manifest.json';

/**
 * @return array<string,mixed>
 */
function benchmarkEnvironment(): array
{
    return [
        'php_version' => PHP_VERSION,
        'sapi' => PHP_SAPI,
        'os' => php_uname('s'),
        'os_release' => php_uname('r'),
        'opcache' => (bool) ini_get('opcache.enable_cli'),
        'opcache_jit' => (string) (ini_get('opcache.jit') ?: ''),
        'opcache_jit_buffer_size' =>
            (string) (ini_get('opcache.jit_buffer_size') ?: ''),
    ];
}

function ensureDirectory(string $path): void
{
    if (is_dir($path)) {
        return;
    }

    if (!mkdir($path, 0777, true) && !is_dir($path)) {
        throw new RuntimeException("Cannot create directory `$path`");
    }
}

function writeText(string $path, string $content): void
{
    ensureDirectory(dirname($path));

    if (file_put_contents($path, $content) === false) {
        throw new RuntimeException("Cannot write `$path`");
    }
}

/**
 * @return array<string,string>
 */
function parseArgs(array $argv): array
{
    $result = [];
    foreach ($argv as $argument) {
        if (!str_starts_with($argument, '--')) {
            continue;
        }

        $pair = explode('=', substr($argument, 2), 2);
        $result[$pair[0]] = $pair[1] ?? '1';
    }

    return $result;
}

function buildRepeatedCollection(string $fragment, int $repeat): string
{
    return '<Collection>' . str_repeat($fragment, $repeat) . '</Collection>';
}

/**
 * @return array{small:string,medium:string,large:string}
 */
function generateReadmeSizedFiles(string $dir, array $limits): array
{
    $xml = '<SomeElement key="123">value</SomeElement>' . PHP_EOL;
    $fragments = str_repeat($xml, 10);

    $result = [];
    foreach ($limits as $name => $limit) {
        $path = $dir . DIRECTORY_SEPARATOR . "readme-$name.xml";
        $file = fopen($path, 'wb');
        if ($file === false) {
            throw new RuntimeException("Cannot open `$path`");
        }

        fwrite($file, '<Collection>');
        for ($i = 0; $i < $limit; $i++) {
            fwrite($file, $fragments);
        }
        fwrite($file, '</Collection>');
        fclose($file);

        $result[$name] = $path;
    }

    /** @var array{small:string,medium:string,large:string} $result */
    return $result;
}

function businessDocumentXml(int $repeatItems): string
{
    $items = [];
    for ($i = 0; $i < $repeatItems; $i++) {
        $sku = sprintf('SKU-%06d', $i);
        $name = "Item $i";
        $price = 10 + ($i % 25);
        $items[] = <<<XML
<Item sku="$sku">
    <Name>$name</Name>
    <Price currency="EUR">$price</Price>
    <Tags>
        <Tag>new</Tag>
        <Tag>featured</Tag>
    </Tags>
</Item>
XML;
    }

    $joined = implode(PHP_EOL, $items);

    return <<<XML
<Doc id="42" kind="demo">
    <Meta>
        <Title>Quarter report</Title>
        <Flags>
            <Flag code="A">true</Flag>
            <Flag code="B">false</Flag>
        </Flags>
    </Meta>
    <Items>
$joined
    </Items>
    <Summary total="$repeatItems">ok</Summary>
</Doc>
XML;
}

function xmlElementFixture(): string
{
    return <<<'XML'
<doc attrib="a" option="o">
    <base/>
    <valuable>element value</valuable>
    <complex>
        <a empty=""/>
        <b val="x"/>
        <b val="y"/>
        <b val="z"/>
        <c>0</c>
        <c v="o"/>
        <c/>
        <different/>
    </complex>
</doc>
XML;
}

/**
 * @return array<string,mixed>
 */
function fixturePackProfile(string $profile): array
{
    return match ($profile) {
        'smoke' => [
            'readme' => [
                'small' => 1,
                'medium' => 10,
                'large' => 100,
            ],
            'stream_repeat' => 50,
            'business_small' => 5,
            'business_large' => 25,
        ],
        'acceptance' => [
            'readme' => [
                'small' => 1,
                'medium' => 1_000,
                'large' => 1_000_000,
            ],
            'stream_repeat' => 50_000,
            'business_small' => 250,
            'business_large' => 5_000,
        ],
        default => throw new InvalidArgumentException(
            "Unknown fixture profile `$profile`"
        ),
    };
}

/**
 * @return array<string,mixed>
 */
function generateFixturePack(
    string $fixturesDir,
    bool $force = false,
    string $profile = 'acceptance'
): array
{
    ensureDirectory($fixturesDir);

    $manifestPath = $fixturesDir . DIRECTORY_SEPARATOR . MANIFEST_FILE;
    if (!$force && is_file($manifestPath)) {
        return loadManifest($fixturesDir);
    }

    $config = fixturePackProfile($profile);
    $readme = generateReadmeSizedFiles($fixturesDir, $config['readme']);

    $pair = '<One attr="val">text</One><Other attr1="" attr2=""/>' . PHP_EOL;
    $streamPath = $fixturesDir . DIRECTORY_SEPARATOR . 'stream-large.xml';
    writeText(
        $streamPath,
        buildRepeatedCollection($pair, $config['stream_repeat'])
    );

    $businessSmallPath = $fixturesDir . DIRECTORY_SEPARATOR . 'business-small.xml';
    $businessLargePath = $fixturesDir . DIRECTORY_SEPARATOR . 'business-large.xml';
    writeText($businessSmallPath, businessDocumentXml($config['business_small']));
    writeText($businessLargePath, businessDocumentXml($config['business_large']));

    $xmlElementPath = $fixturesDir . DIRECTORY_SEPARATOR . 'xml-element.xml';
    writeText($xmlElementPath, xmlElementFixture());

    $files = [
        'readme_small' => fileDescriptor($readme['small']),
        'readme_medium' => fileDescriptor($readme['medium']),
        'readme_large' => fileDescriptor($readme['large']),
        'stream_large' => fileDescriptor($streamPath),
        'business_small' => fileDescriptor($businessSmallPath),
        'business_large' => fileDescriptor($businessLargePath),
        'xml_element' => fileDescriptor($xmlElementPath),
    ];

    $manifest = [
        'version' => 1,
        'profile' => $profile,
        'generated_at' => gmdate(DATE_ATOM),
        'environment' => benchmarkEnvironment(),
        'files' => $files,
    ];

    $encoded = json_encode(
        $manifest,
        JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
    );
    if ($encoded === false) {
        throw new RuntimeException('Cannot encode manifest');
    }

    writeText($manifestPath, $encoded . PHP_EOL);

    return $manifest;
}

/**
 * @return array<string,mixed>
 */
function loadManifest(string $fixturesDir): array
{
    $path = $fixturesDir . DIRECTORY_SEPARATOR . MANIFEST_FILE;
    if (!is_file($path)) {
        throw new InvalidArgumentException("Manifest `$path` not found");
    }

    $decoded = json_decode((string) file_get_contents($path), true);
    if (!is_array($decoded)) {
        throw new RuntimeException("Manifest `$path` is invalid");
    }

    return $decoded;
}

/**
 * @return array<string,int|string>
 */
function fileDescriptor(string $path): array
{
    return [
        'path' => $path,
        'bytes' => filesize($path) ?: 0,
        'sha256' => hash_file('sha256', $path) ?: '',
    ];
}

/**
 * @param list<int> $values
 */
function median(array $values): int
{
    sort($values);
    $count = count($values);
    $mid = intdiv($count, 2);

    if ($count % 2 === 1) {
        return $values[$mid];
    }

    return (int) floor(($values[$mid - 1] + $values[$mid]) / 2);
}

/**
 * @return array<string,int|string>
 */
function benchmarkCase(
    string $label,
    int $iterations,
    callable $callable,
    int $warmup = 3
): array {
    $samples = [];

    for ($i = 0; $i < $warmup; $i++) {
        $callable();
    }

    for ($i = 0; $i < $iterations; $i++) {
        $start = hrtime(true);
        $callable();
        $samples[] = hrtime(true) - $start;
    }

    return [
        'label' => $label,
        'iterations' => $iterations,
        'median_ns' => median($samples),
        'avg_ns' => (int) floor(array_sum($samples) / count($samples)),
        'min_ns' => min($samples),
        'max_ns' => max($samples),
    ];
}

function checksum(mixed $value): string
{
    $json = json_encode(
        $value,
        JSON_UNESCAPED_UNICODE
        | JSON_UNESCAPED_SLASHES
        | JSON_PRESERVE_ZERO_FRACTION
    );

    return hash('sha256', (string) $json);
}

function loadFile(string $path): string
{
    $content = file_get_contents($path);
    if ($content === false) {
        throw new RuntimeException("Cannot read `$path`");
    }

    return $content;
}

/**
 * @return array<string,mixed>
 */
function correctnessChecks(array $manifest): array
{
    /** @var array<string,array<string,int|string>> $files */
    $files = $manifest['files'];

    $streamFile = (string) $files['stream_large']['path'];
    $readmeLarge = (string) $files['readme_large']['path'];
    $businessSmall = loadFile((string) $files['business_small']['path']);
    $businessLarge = loadFile((string) $files['business_large']['path']);
    $xmlElementXml = loadFile((string) $files['xml_element']['path']);

    $reader = XMLReader::open($streamFile);
    if ($reader === false) {
        throw new RuntimeException("Cannot open `$streamFile`");
    }
    $extractor = FastXmlParser::extractHierarchy(
        $reader,
        static fn (XMLReader $cursor): bool =>
            $cursor->nodeType === XMLReader::ELEMENT && $cursor->name === 'One'
    );
    $streamElements = iterator_to_array($extractor, false);
    $reader->close();

    $readmeReader = XMLReader::open($readmeLarge);
    if ($readmeReader === false) {
        throw new RuntimeException("Cannot open `$readmeLarge`");
    }
    $mayRead = true;
    while ($mayRead && $readmeReader->name !== 'SomeElement') {
        $mayRead = $readmeReader->read();
    }
    $firstElement = PrettyPrintComposer::compose($readmeReader);
    $readmeReader->close();

    $converter = new XmlConverter();
    $hierarchy = FastXmlToArray::convert($businessSmall);
    $pretty = FastXmlToArray::prettyPrint($businessSmall);
    $hierarchyFromConverter = $converter->toHierarchyOfElements($businessLarge);
    $xmlElement = new XmlElement(FastXmlToArray::convert($xmlElementXml));
    $complex = $xmlElement->pull('complex')->current();

    return [
        'uc1_count' => count($streamElements),
        'uc1_hash' => checksum($streamElements),
        'uc2_hash_large' => checksum($firstElement),
        'uc3_hash_small' => checksum($hierarchy),
        'uc3_hash_large_converter' => checksum($hierarchyFromConverter),
        'uc4_hash_small' => checksum($pretty),
        'uc5_checks' => [
            'name' => $xmlElement->name(),
            'value' => $xmlElement->value(),
            'attr_count' => count($xmlElement->attributes()),
            'has_complex' => $xmlElement->hasElement('complex'),
            'complex_hash' =>
                checksum($complex instanceof XmlElement ? $complex->serialize() : null),
        ],
    ];
}

/**
 * @return array<string,int|string>
 */
function benchmarkFirstElement(
    string $file,
    string $label,
    int $iterations = 30
): array {
    return benchmarkCase(
        $label,
        $iterations,
        static function () use ($file): void {
            $reader = XMLReader::open($file);
            if ($reader === false) {
                throw new RuntimeException("Cannot open `$file`");
            }
            $mayRead = true;
            while ($mayRead && $reader->name !== 'SomeElement') {
                $mayRead = $reader->read();
            }
            PrettyPrintComposer::compose($reader);
            $reader->close();
        }
    );
}

/**
 * @return array<string,mixed>
 */
function benchmarkIterationProfile(array $manifest): array
{
    $profile = (string) ($manifest['profile'] ?? 'acceptance');

    return match ($profile) {
        'smoke' => [
            'macro' => 5,
            'micro' => 500,
        ],
        default => [
            'macro' => 30,
            'micro' => 100_000,
        ],
    };
}

/**
 * @return array<string,mixed>
 */
function runAcceptanceBenchmark(array $manifest): array
{
    /** @var array<string,array<string,int|string>> $files */
    $files = $manifest['files'];

    $streamFile = (string) $files['stream_large']['path'];
    $businessSmall = loadFile((string) $files['business_small']['path']);
    $businessLarge = loadFile((string) $files['business_large']['path']);
    $xmlElementXml = loadFile((string) $files['xml_element']['path']);
    $xmlElement = new XmlElement(FastXmlToArray::convert($xmlElementXml));
    $complex = $xmlElement->pull('complex')->current();
    $iterations = benchmarkIterationProfile($manifest);

    $results = [];
    $results[] = benchmarkCase(
        'UC-1 FastXmlParser::extractHierarchy stream-large',
        $iterations['macro'],
        static function () use ($streamFile): void {
            $reader = XMLReader::open($streamFile);
            if ($reader === false) {
                throw new RuntimeException("Cannot open `$streamFile`");
            }
            $extractor = FastXmlParser::extractHierarchy(
                $reader,
                static fn (XMLReader $cursor): bool =>
                    $cursor->nodeType === XMLReader::ELEMENT
                    && $cursor->name === 'One'
            );
            iterator_to_array($extractor, false);
            $reader->close();
        }
    );
    $results[] = benchmarkFirstElement(
        (string) $files['readme_small']['path'],
        'UC-2 PrettyPrintComposer::compose first-element small',
        $iterations['macro'],
    );
    $results[] = benchmarkFirstElement(
        (string) $files['readme_medium']['path'],
        'UC-2 PrettyPrintComposer::compose first-element medium',
        $iterations['macro'],
    );
    $results[] = benchmarkFirstElement(
        (string) $files['readme_large']['path'],
        'UC-2 PrettyPrintComposer::compose first-element large',
        $iterations['macro'],
    );
    $results[] = benchmarkCase(
        'UC-3 FastXmlToArray::convert business-small',
        $iterations['macro'],
        static fn (): array => FastXmlToArray::convert($businessSmall)
    );
    $results[] = benchmarkCase(
        'UC-3 XmlConverter::toHierarchyOfElements business-large',
        $iterations['macro'],
        static function () use ($businessLarge): array {
            $converter = new XmlConverter();
            return $converter->toHierarchyOfElements($businessLarge);
        }
    );
    $results[] = benchmarkCase(
        'UC-4 FastXmlToArray::prettyPrint business-small',
        $iterations['macro'],
        static fn (): array => FastXmlToArray::prettyPrint($businessSmall)
    );
    $results[] = benchmarkCase(
        'UC-4 PrettyPrintComposer::compose business-large root',
        $iterations['macro'],
        static function () use ($files): array {
            $path = (string) $files['business_large']['path'];
            $reader = XMLReader::open($path);
            if ($reader === false) {
                throw new RuntimeException("Cannot open `$path`");
            }
            while ($reader->nodeType !== XMLReader::ELEMENT && $reader->read()) {
            }
            $result = PrettyPrintComposer::compose($reader);
            $reader->close();
            return $result;
        }
    );
    $results[] = benchmarkCase(
        'UC-5 XmlElement::name',
        $iterations['micro'],
        static fn (): string => $xmlElement->name()
    );
    $results[] = benchmarkCase(
        'UC-5 XmlElement::value',
        $iterations['micro'],
        static fn (): string => $xmlElement->value()
    );
    $results[] = benchmarkCase(
        'UC-5 XmlElement::get attrib',
        $iterations['micro'],
        static fn (): string => $xmlElement->get('attrib')
    );
    $results[] = benchmarkCase(
        'UC-5 XmlElement::hasAttribute attrib',
        $iterations['micro'],
        static fn (): bool => $xmlElement->hasAttribute('attrib')
    );
    $results[] = benchmarkCase(
        'UC-5 XmlElement::attributes',
        $iterations['micro'],
        static fn (): array => $xmlElement->attributes()
    );
    $results[] = benchmarkCase(
        'UC-5 XmlElement::hasElement complex',
        $iterations['micro'],
        static fn (): bool => $xmlElement->hasElement('complex')
    );
    $results[] = benchmarkCase(
        'UC-5 XmlElement::elements',
        $iterations['micro'],
        static fn (): array => $xmlElement->elements()
    );
    $results[] = benchmarkCase(
        'UC-5 XmlElement::pull b count',
        $iterations['micro'],
        static function () use ($complex): int {
            if (!$complex instanceof XmlElement) {
                return 0;
            }
            return iterator_count($complex->pull('b'));
        }
    );

    return [
        'environment' => benchmarkEnvironment(),
        'fixtures' => $manifest['files'],
        'correctness' => correctnessChecks($manifest),
        'results' => $results,
    ];
}
