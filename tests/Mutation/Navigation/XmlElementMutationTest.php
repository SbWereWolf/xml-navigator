<?php

declare(strict_types=1);

namespace SbWereWolf\XmlNavigator\Test\Mutation\Navigation;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use SbWereWolf\XmlNavigator\Navigation\XmlElement;

final class XmlElementMutationTest extends TestCase
{
    public function testPullAcceptsMinimalInjectedChildBeforeRoundtripScenarios(): void
    {
        $element = new XmlElement(
            [
                'n' => 'root',
                's' => [
                    [
                        'n' => 'child',
                    ],
                ],
            ]
        );

        $children = iterator_to_array($element->pull(), false);

        self::assertCount(1, $children);
        self::assertSame('child', $children[0]->name());
    }

    public function testPullAcceptsInjectedChildPayloadWithMinimalShape(): void
    {
        $element = new XmlElement(
            [
                'n' => 'root',
                's' => [],
            ]
        );

        $sequence = new ReflectionProperty(XmlElement::class, 'sequenceData');
        /** @noinspection PhpExpressionResultUnusedInspection */
        $sequence->setAccessible(true);
        $sequence->setValue(
            $element,
            [
                [
                    'n' => 'child',
                ],
            ]
        );

        $children = iterator_to_array($element->pull(), false);

        self::assertCount(1, $children);
        self::assertSame('child', $children[0]->name());
        self::assertSame('', $children[0]->value());
        self::assertFalse($children[0]->hasAttribute());
        self::assertFalse($children[0]->hasElement());
    }

    public function testPullAcceptsInjectedChildPayloadWithBrokenAttributes(): void
    {
        $element = new XmlElement(
            [
                'n' => 'root',
                's' => [
                    [
                        'n' => 'child',
                        'a' => 'broken',
                    ],
                ],
            ]
        );

        $children = iterator_to_array($element->pull(), false);

        self::assertCount(1, $children);
        self::assertSame('child', $children[0]->name());
        self::assertSame('', $children[0]->value());
        self::assertFalse($children[0]->hasAttribute());
        self::assertFalse($children[0]->hasElement());
    }

    public function testPullRestoresTrustFlagAfterYieldingChild(): void
    {
        $element = new XmlElement(
            [
                'n' => 'root',
                's' => [
                    [
                        'n' => 'child',
                    ],
                ],
            ]
        );

        $trust = new ReflectionProperty(XmlElement::class, 'trustChildData');
        /** @noinspection PhpExpressionResultUnusedInspection */
        $trust->setAccessible(true);
        $trust->setValue(null, false);

        $children = iterator_to_array($element->pull(), false);

        self::assertCount(1, $children);
        self::assertSame('child', $children[0]->name());
        self::assertFalse($trust->getValue());
    }

    public function testPullRestoresTrustFlagAfterChildConstructionFailure(): void
    {
        $element = new class(['n' => 'root', 's' => [['n' => 'child']]])
            extends XmlElement {
            public function __construct(
                array $initial,
                string $name = 'n',
                string $val = 'v',
                string $attr = 'a',
                string $seq = 's',
            ) {
                parent::__construct($initial, $name, $val, $attr, $seq);

                if (($initial[$name] ?? '') === 'child') {
                    throw new InvalidArgumentException('child-construction-failed');
                }
            }
        };

        $trust = new ReflectionProperty(XmlElement::class, 'trustChildData');
        /** @noinspection PhpExpressionResultUnusedInspection */
        $trust->setAccessible(true);
        $trust->setValue(null, false);

        try {
            iterator_to_array($element->pull(), false);
            self::fail('Expected InvalidArgumentException was not thrown.');
        } catch (InvalidArgumentException $exception) {
            self::assertSame(
                'child-construction-failed',
                $exception->getMessage()
            );
            self::assertFalse($trust->getValue());
        }
    }
}
