<?php

declare(strict_types=1);

namespace SbWereWolf\XmlNavigator;

/**
 * Parent class for composers
 */
class ElementComposer
{
    /**
     * @param array $elems
     * @return int
     */
    protected static function extractBaseDepth(array $elems): int
    {
        $first = [];
        if ($elems) {
            reset($elems);
            $first = current($elems);
        }
        $base = 0;
        if ($first) {
            $base = current($first)[ElementExtractor::DEPTH];
        }

        return $base;
    }
}