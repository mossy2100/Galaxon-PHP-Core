<?php

/**
 * Convenience functions that work better as plain functions than methods.
 */

declare(strict_types=1);

namespace Galaxon\Core;

/**
 * Echo a value and append a newline character.
 *
 * @param mixed $value The value to echo.
 */
function println(mixed $value): void
{
    echo $value, PHP_EOL;
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

/**
 * Dump a value to the output, typically for debugging purposes.
 *
 * @param mixed $value The value to dump.
 */
function dump(mixed $value): void {
    Stringify::println($value, true);
}
