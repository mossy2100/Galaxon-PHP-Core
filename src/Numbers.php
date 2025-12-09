<?php

declare(strict_types=1);

namespace Galaxon\Core;

use ValueError;

/**
 * Container for general number-related utility methods.
 */
final class Numbers
{
    // region Constructor

    /**
     * Private constructor to prevent instantiation.
     *
     * @codeCoverageIgnore
     */
    private function __construct()
    {
    }

    // endregion

    // region Type inspection

    /**
     * Check if a value is a number, i.e. an integer or a float.
     * This varies from is_numeric(), which also returns true for numeric strings.
     *
     * @param mixed $value The value to check.
     * @return bool True if the value is a number, false otherwise.
     * @phpstan-assert-if-true int|float $value
     */
    public static function isNumber(mixed $value): bool
    {
        return is_int($value) || is_float($value);
    }

    // endregion

    // region Comparison methods

    /**
     * Check if two numbers are equal.
     *
     * This method is useful for equality comparison when working with values that can be ints or floats.
     *
     * It serves several purposes:
     * 1. Avoids numeric strings being converted to numbers and compared as such.
     * 2. Silences IDE warnings about using == vs === or != vs !==
     * 3. Avoids integers being compared as equal that aren't. Because integers have 64 bits of precision and floats
     * only have 53, a comparison like (float)$a === (float)$b can return true for integers that are different but
     * convert to the same float.
     *
     * @param int|float $a The first number.
     * @param int|float $b The second number.
     * @return bool True if the two numbers are equal, false otherwise.
     */
    public static function equal(int|float $a, int|float $b): bool
    {
        // If they're both ints, don't convert to float.
        if (is_int($a) && is_int($b)) {
            return $a === $b;
        }

        // Compare as floats.
        return (float)$a === (float)$b;
    }

    // endregion

    // region Sign methods

    /**
     * Copy the sign of one number to another.
     *
     * @param int|float $num The number whose magnitude to use.
     * @param int|float $signSource The number whose sign to copy.
     * @return int|float The magnitude of $num with the sign of $signSource.
     * @throws ValueError If NaN is passed as either parameter.
     */
    public static function copySign(int|float $num, int|float $signSource): int|float
    {
        // Guard. This method won't work for NaN, which doesn't have a sign.
        if (is_nan($num) || is_nan($signSource)) {
            throw new ValueError('NAN is not allowed for either parameter.');
        }

        return abs($num) * self::sign($signSource, false);
    }

    /**
     * Get the sign of a number.
     *
     * This method has two modes of operation, determined by the $zeroForZero parameter.
     * In either mode, the method will return 1 for positive numbers and -1 for negative numbers.
     * 1. The default mode (when $zeroForZero is true) will return 0 when $value equals 0.
     * 2. The alternate mode (when $zeroForZero is false) will return -1 for the special float value -0.0, or 1 for
     *    int 0 or float +0.0.
     *
     * @param int|float $value The number to check.
     * @param bool $zeroForZero If true, return 0 when $value equals 0. If false, return 1 or -1, indicating the sign
     * of the zero.
     * @return int The sign of the $value argument (-1, 0, or 1).
     */
    public static function sign(int|float $value, bool $zeroForZero = true): int
    {
        // Check for positive.
        if ($value > 0) {
            return 1;
        }

        // Check for negative.
        if ($value < 0) {
            return -1;
        }

        // Value is 0. Return the default result if requested.
        if ($zeroForZero) {
            return 0;
        }

        // Return the sign of the zero.
        return is_float($value) && Floats::isNegativeZero($value) ? -1 : 1;
    }

    // endregion
}
