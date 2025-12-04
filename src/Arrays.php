<?php

declare(strict_types=1);

namespace Galaxon\Core;

use JsonException;
use TypeError;

/**
 * Container for useful array-related methods.
 */
final class Arrays
{
    /**
     * Private constructor to prevent instantiation.
     *
     * @codeCoverageIgnore
     */
    private function __construct()
    {
    }

    /**
     * Checks if an array contains recursion.
     *
     * @param mixed[] $arr The array to check.
     * @return bool True if the array contains recursion, false otherwise.
     */
    public static function containsRecursion(array $arr): bool
    {
        try {
            json_encode($arr, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            if ($e->getCode() === JSON_ERROR_RECURSION) {
                return true;
            }
        }

        return false;
    }

    /**
     * Wrap each string value in the array with quotes.
     *
     * Useful for formatting lists in error messages or output.
     * Does not perform escaping - values containing quotes will not be escaped.
     *
     * @param array<string> $arr Array of strings to quote.
     * @param bool $doubleQuotes Use double quotes instead of single quotes.
     * @return array<string> Array with each value wrapped in quotes.
     * @throws TypeError If any array value is not a string.
     */
    public static function quoteValues(array $arr, bool $doubleQuotes = false): array
    {
        $quoteChar = $doubleQuotes ? '"' : "'";
        return array_map(static function ($value) use ($quoteChar) {
            // Type check.
            // @phpstan-ignore function.alreadyNarrowedType
            if (!is_string($value)) {
                throw new TypeError('The array values must be strings.');
            }

            // Wrap the value in quotes.
            return $quoteChar . $value . $quoteChar;
        }, $arr);
    }
}
