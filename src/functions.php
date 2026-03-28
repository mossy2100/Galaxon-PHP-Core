<?php

/**
 * Convenience functions that work better as plain functions than methods.
 */

declare(strict_types=1);

namespace Galaxon\Core;

/**
 * Print a value and append a newline character.
 *
 * Strings are output as-is.
 * Objects with a __toString() method are converted to strings via that method.
 * Otherwise, the value is converted to a string using Stringify::stringify().
 *
 * This method makes it easier to distinguish null, bool, int, float, and string values, and provides a nice output
 * for arrays, objects, and resources.
 *
 * @param mixed $value The value to echo.
 */
function println(mixed $value = ''): void
{
    $str = is_string($value)
        ? $value
        : (is_object($value) && method_exists($value, '__toString')
            ? (string)$value
            : Stringify::stringify($value));
    echo $str . PHP_EOL;
}

/**
 * Check if a value is a number.
 *
 * This varies from is_numeric(), which also returns true for numeric strings.
 *
 * @param mixed $value The value to check.
 * @return bool True if the value is a number, false otherwise.
 */
function is_number(mixed $value): bool
{
    return is_int($value) || is_float($value);
}
