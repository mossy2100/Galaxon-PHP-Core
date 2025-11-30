<?php

declare(strict_types=1);

namespace Galaxon\Core;

use ValueError;

/**
 * Container for general number-related utility methods.
 */
final class Numbers
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
     * Check if two numbers are equal.
     *
     * The purpose of this function is to:
     * - Avoid numeric strings accidentally being converted to numbers and compared as such.
     * - Avoid IDE warnings about using == vs === or != vs !==.
     *
     * The string problem is solved by `declare(strict_types=1);` at the top of this file.
     * The comparison problem is solved by using === and casting the operands to float when necessary.
     *
     * @param int|float $a The first number.
     * @param int|float $b The second number.
     * @return bool True if the two numbers are equal, false otherwise.
     */
    public static function equal(int|float $a, int|float $b): bool
    {
        // If they're both ints, no need to cast.
        if (is_int($a) && is_int($b)) {
            return $a === $b;
        }

        // Compare as floats.
        return (float)$a === (float)$b;
    }

    /**
     * Check if two numbers are approximately equal within a given epsilon.
     *
     * If both are integers, they are compared as exactly equal.
     * If one or both is a float, they are compared as within epsilon of each other.
     *
     * @param int|float $a The first number.
     * @param int|float $b The second number.
     * @param float $epsilon The maximum allowed difference (absolute or relative depending on $relative).
     * @param bool $relative If true, use relative comparison; if false, use absolute comparison.
     * @return bool True if the two numbers are approximately equal, false otherwise.
     * @throws ValueError If epsilon is negative.
     *
     * @see Floats::approxEqual()
     */
    public static function approxEqual(
        int|float $a,
        int|float $b,
        float $epsilon = Floats::EPSILON,
        bool $relative = true
    ): bool {
        // If they're both ints, check for exact equality.
        if (is_int($a) && is_int($b)) {
            return $a === $b;
        }

        // Compare as floats.
        return Floats::approxEqual((float)$a, (float)$b, $epsilon, $relative);
    }

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
            throw new ValueError('NaN is not allowed for either parameter.');
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
}
