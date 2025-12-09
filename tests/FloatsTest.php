<?php

declare(strict_types=1);

namespace Galaxon\Core\Tests;

use Galaxon\Core\Floats;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ValueError;

/**
 * Test class for Floats utility class.
 */
#[CoversClass(Floats::class)]
final class FloatsTest extends TestCase
{
    // region approxEqual tests

    /**
     * Test approxEqual uses both relative and absolute tolerance.
     */
    public function testApproxEqualWithBothTolerances(): void
    {
        // Large values: relative tolerance handles scale
        $large = 1e20;
        $this->assertTrue(Floats::approxEqual($large, $large + 1e9));

        // Small values near zero: absolute tolerance handles them
        $this->assertTrue(Floats::approxEqual(0.0, PHP_FLOAT_EPSILON / 2));
        $this->assertFalse(Floats::approxEqual(0.0, PHP_FLOAT_EPSILON * 2));
    }

    /**
     * Test approxEqual with custom tolerances.
     */
    public function testApproxEqualWithCustomTolerances(): void
    {
        // 10% relative tolerance, 1.0 absolute tolerance
        $this->assertTrue(Floats::approxEqual(100.0, 105.0, 0.1, 1.0));
        $this->assertFalse(Floats::approxEqual(100.0, 115.0, 0.1, 1.0));

        // Absolute tolerance catches values near zero
        $this->assertTrue(Floats::approxEqual(0.0, 0.5, 1e-9, 1.0));
    }

    /**
     * Test approxEqual with negative tolerances throws ValueError.
     */
    public function testApproxEqualWithNegativeTolerancesThrows(): void
    {
        $this->expectException(ValueError::class);
        $this->expectExceptionMessage('Tolerances must be non-negative');
        Floats::approxEqual(1.0, 1.0, -0.1, 0.0);
    }

    /**
     * Test approxEqual with zero values.
     */
    public function testApproxEqualWithZeros(): void
    {
        $this->assertTrue(Floats::approxEqual(0.0, 0.0));
        $this->assertTrue(Floats::approxEqual(0.0, -0.0));
        $this->assertTrue(Floats::approxEqual(-0.0, 0.0));
        $this->assertTrue(Floats::approxEqual(-0.0, -0.0));
    }

    /**
     * Test approxEqual with same infinities returns true.
     */
    public function testApproxEqualWithSameInfinity(): void
    {
        // INF and -INF are only considered close to themselves (matching Python's isclose behavior)
        $this->assertTrue(Floats::approxEqual(INF, INF));
        $this->assertTrue(Floats::approxEqual(-INF, -INF));
    }

    /**
     * Test approxEqual with infinity and finite value returns false.
     */
    public function testApproxEqualWithInfinityAndFinite(): void
    {
        // Infinity with finite value returns false
        $this->assertFalse(Floats::approxEqual(INF, 1.0));
        $this->assertFalse(Floats::approxEqual(1.0, INF));
        $this->assertFalse(Floats::approxEqual(-INF, 1.0));
        $this->assertFalse(Floats::approxEqual(1.0, -INF));
    }

    /**
     * Test approxEqual with opposite infinities returns false.
     */
    public function testApproxEqualWithOppositeInfinities(): void
    {
        // Opposite infinities are not close to each other
        $this->assertFalse(Floats::approxEqual(INF, -INF));
        $this->assertFalse(Floats::approxEqual(-INF, INF));
    }

    /**
     * Test approxEqual with NAN returns false.
     */
    public function testApproxEqualWithNan(): void
    {
        // NAN is never equal to anything, including itself
        $this->assertFalse(Floats::approxEqual(NAN, NAN));
    }

    /**
     * Test approxEqual with NAN and finite value returns false.
     */
    public function testApproxEqualWithNanAndFinite(): void
    {
        // NAN with any finite value returns false
        $this->assertFalse(Floats::approxEqual(NAN, 0.0));
        $this->assertFalse(Floats::approxEqual(0.0, NAN));
    }

    // endregion

    // region compare tests

    /**
     * Test compare with equal values.
     */
    public function testApproxCompareWithEqualValues(): void
    {
        $this->assertSame(0, Floats::approxCompare(1.0, 1.0));
        $this->assertSame(0, Floats::approxCompare(0.0, 0.0));
        $this->assertSame(0, Floats::approxCompare(-5.5, -5.5));
    }

    /**
     * Test compare with approximately equal values.
     */
    public function testApproxCompareWithApproximatelyEqual(): void
    {
        // Uses combined relative and absolute tolerance
        $large = 1e20;
        $this->assertSame(0, Floats::approxCompare($large, $large + 1e9));

        // Absolute tolerance handles values near zero
        $this->assertSame(0, Floats::approxCompare(0.0, PHP_FLOAT_EPSILON / 2));
    }

    /**
     * Test compare with less than.
     */
    public function testApproxCompareWithLessThan(): void
    {
        $this->assertSame(-1, Floats::approxCompare(1.0, 2.0));
        $this->assertSame(-1, Floats::approxCompare(-5.0, -4.0));
        $this->assertSame(-1, Floats::approxCompare(0.0, 1.0));
    }

    /**
     * Test compare with greater than.
     */
    public function testApproxCompareWithGreaterThan(): void
    {
        $this->assertSame(1, Floats::approxCompare(2.0, 1.0));
        $this->assertSame(1, Floats::approxCompare(-4.0, -5.0));
        $this->assertSame(1, Floats::approxCompare(1.0, 0.0));
    }

    /**
     * Test compare with custom tolerances.
     */
    public function testApproxCompareWithCustomTolerances(): void
    {
        // 10% relative, 1.0 absolute
        $this->assertSame(0, Floats::approxCompare(100.0, 105.0, 0.1, 1.0));
        $this->assertSame(-1, Floats::approxCompare(100.0, 115.0, 0.1, 1.0));
    }

    /**
     * Test compare with negative tolerance throws ValueError.
     */
    public function testApproxCompareWithNegativeToleranceThrows(): void
    {
        $this->expectException(ValueError::class);
        $this->expectExceptionMessage('Tolerances must be non-negative');
        Floats::approxCompare(1.0, 1.0, -0.1, 0.0);
    }

    // endregion

    // region Transformation method tests

    /**
     * Test normalization of zero values.
     */
    public function testNormalizeZero(): void
    {
        // Test that negative zero is normalized to positive zero.
        $this->assertSame(0.0, Floats::normalizeZero(-0.0));

        // Test that positive zero remains positive zero.
        $this->assertSame(0.0, Floats::normalizeZero(0.0));

        // Test that positive values are unchanged.
        $this->assertSame(1.5, Floats::normalizeZero(1.5));

        // Test that negative values are unchanged.
        $this->assertSame(-2.5, Floats::normalizeZero(-2.5));

        // Test that infinity values are unchanged.
        $this->assertSame(INF, Floats::normalizeZero(INF));
        $this->assertSame(-INF, Floats::normalizeZero(-INF));

        // Test that NAN is unchanged (NAN !== NAN, so use is_nan).
        $this->assertTrue(is_nan(Floats::normalizeZero(NAN)));
    }

    /**
     * Test conversion of floats to hexadecimal strings.
     */
    public function testToHex(): void
    {
        // Test that positive zero produces a consistent hex string.
        $hexZero = Floats::toHex(0.0);
        $this->assertSame(16, strlen($hexZero));

        // Test that negative zero produces a different hex string than positive zero.
        $hexNegZero = Floats::toHex(-0.0);
        $this->assertSame(16, strlen($hexNegZero));
        $this->assertNotSame($hexZero, $hexNegZero);

        // Test that a regular value produces a 16-character hex string.
        $hex1 = Floats::toHex(1.0);
        $this->assertSame(16, strlen($hex1));

        // Test that different values produce different hex strings.
        $hex2 = Floats::toHex(2.0);
        $this->assertNotSame($hex1, $hex2);

        // Test that special values produce valid hex strings.
        $this->assertSame(16, strlen(Floats::toHex(INF)));
        $this->assertSame(16, strlen(Floats::toHex(-INF)));
        $this->assertSame(16, strlen(Floats::toHex(NAN)));

        // Test that very close but different values produce different hex strings.
        $this->assertNotSame(Floats::toHex(1.0), Floats::toHex(1.0 + PHP_FLOAT_EPSILON));
    }

    /**
     * Test tryConvertToInt with floats that equal whole numbers.
     */
    public function testTryConvertToIntWithWholeNumbers(): void
    {
        $this->assertSame(5, Floats::tryConvertToInt(5.0));
        $this->assertSame(-10, Floats::tryConvertToInt(-10.0));
        $this->assertSame(0, Floats::tryConvertToInt(0.0));
        $this->assertSame(1000000, Floats::tryConvertToInt(1000000.0));
    }

    /**
     * Test tryConvertToInt with floats that have fractional parts.
     */
    public function testTryConvertToIntWithFractionalNumbers(): void
    {
        $this->assertNull(Floats::tryConvertToInt(5.5));
        $this->assertNull(Floats::tryConvertToInt(1.001));
        $this->assertNull(Floats::tryConvertToInt(-3.14));
    }

    /**
     * Test tryConvertToInt with edge case floats.
     */
    public function testTryConvertToIntEdgeCases(): void
    {
        // Very small positive number (not zero)
        $this->assertNull(Floats::tryConvertToInt(0.1));

        // Very small negative number (not zero)
        $this->assertNull(Floats::tryConvertToInt(-0.1));

        // Negative zero
        $this->assertSame(0, Floats::tryConvertToInt(-0.0));
    }

    /**
     * Test tryConvertToInt with large integers that can be exactly represented as floats.
     */
    public function testTryConvertToIntWithLargeIntegers(): void
    {
        // Use powers of 2 up to 2^53, which can be exactly represented as floats
        $this->assertSame(1 << 50, Floats::tryConvertToInt((float)(1 << 50)));

        // Negative large integer
        $this->assertSame(-(1 << 50), Floats::tryConvertToInt((float)(-(1 << 50))));

        // PHP_INT_MIN is -2^63, which is a power of 2 and CAN be exactly represented as a float
        $this->assertSame(PHP_INT_MIN, Floats::tryConvertToInt((float)PHP_INT_MIN));

        // Note: PHP_INT_MAX (2^63 - 1) cannot be exactly represented as a float
        // because it has many bits set and exceeds the 53-bit mantissa precision
    }

    /**
     * Test tryConvertToInt with floats that lose precision when cast to int.
     */
    public function testTryConvertToIntOutOfRange(): void
    {
        // Float larger than PHP_INT_MAX (loses precision)
        $f = (float)PHP_INT_MAX * 2;
        // Verify it doesn't crash and returns int or null
        /** @var null|int $result */
        $result = Floats::tryConvertToInt($f);
        $this->assertTrue($result === null || is_int($result));
    }

    /**
     * Test tryConvertToInt with various representable integers.
     */
    public function testTryConvertToIntWithVariousIntegers(): void
    {
        $testCases = [
            [1.0, 1],
            [-1.0, -1],
            [100.0, 100],
            [-100.0, -100],
            [0.0, 0],
            [-0.0, 0],
            [42.0, 42],
            [-42.0, -42],
        ];

        foreach ($testCases as [$float, $expectedInt]) {
            $this->assertSame($expectedInt, Floats::tryConvertToInt($float), "Wrong conversion for $float");
        }
    }

    /**
     * Test tryConvertToInt with various non-convertible floats.
     */
    public function testTryConvertToIntWithNonConvertibleFloats(): void
    {
        $testCases = [
            0.1,
            0.5,
            0.999,
            1.1,
            -0.5,
            -1.5,
            3.14159,
            -2.71828,
        ];

        foreach ($testCases as $float) {
            $this->assertNull(Floats::tryConvertToInt($float), "Should return null for $float");
        }
    }

    /**
     * Test tryConvertToInt with non-finite floats.
     */
    public function testTryConvertToIntWithNonFiniteFloats(): void
    {
        $this->assertNull(Floats::tryConvertToInt(NAN));
        $this->assertNull(Floats::tryConvertToInt(INF));
        $this->assertNull(Floats::tryConvertToInt(-INF));
    }

    // endregion

    // region Precision method tests

    /**
     * Test ULP with standard values.
     */
    public function testUlpWithStandardValues(): void
    {
        // ULP of 1.0 should be PHP_FLOAT_EPSILON
        $this->assertSame(PHP_FLOAT_EPSILON, Floats::ulp(1.0));

        // ULP scales with magnitude
        $this->assertSame(1000.0 * PHP_FLOAT_EPSILON, Floats::ulp(1000.0));
        $this->assertSame(0.001 * PHP_FLOAT_EPSILON, Floats::ulp(0.001));
    }

    /**
     * Test ULP with positive zero.
     */
    public function testUlpWithPositiveZero(): void
    {
        $expected = PHP_FLOAT_EPSILON * PHP_FLOAT_MIN;
        $this->assertSame($expected, Floats::ulp(0.0));
    }

    /**
     * Test ULP with negative zero.
     */
    public function testUlpWithNegativeZero(): void
    {
        $expected = PHP_FLOAT_EPSILON * PHP_FLOAT_MIN;
        $this->assertSame($expected, Floats::ulp(-0.0));
    }

    /**
     * Test ULP with negative values uses absolute value.
     */
    public function testUlpWithNegativeValues(): void
    {
        // ULP is the same for positive and negative values
        $this->assertSame(Floats::ulp(100.0), Floats::ulp(-100.0));
        $this->assertSame(Floats::ulp(1.0), Floats::ulp(-1.0));
    }

    /**
     * Test ULP with large values.
     */
    public function testUlpWithLargeValues(): void
    {
        $large = 1e20;
        $ulp = Floats::ulp($large);

        // ULP should be proportional to the magnitude
        $this->assertSame($large * PHP_FLOAT_EPSILON, $ulp);

        // Verify it's actually the spacing
        $next = $large + $ulp;
        $this->assertGreaterThan($large, $next);
    }

    /**
     * Test ULP with small values.
     */
    public function testUlpWithSmallValues(): void
    {
        $small = 1e-100;
        $ulp = Floats::ulp($small);

        $this->assertSame($small * PHP_FLOAT_EPSILON, $ulp);
    }

    /**
     * Test ULP with infinity returns INF.
     */
    public function testUlpWithInfinity(): void
    {
        $this->assertSame(INF, Floats::ulp(INF));
        $this->assertSame(INF, Floats::ulp(-INF));
    }

    /**
     * Test ULP with NAN returns INF.
     */
    public function testUlpWithNan(): void
    {
        $this->assertSame(INF, Floats::ulp(NAN));
    }

    /**
     * Test ULP relationship with next().
     */
    public function testUlpRelationshipWithNext(): void
    {
        $value = 42.0;
        $ulp = Floats::ulp($value);
        $next = Floats::next($value);

        // The difference should approximately equal the ULP
        // (may have rounding in floating-point subtraction)
        $diff = $next - $value;
        $this->assertGreaterThan(0, $diff);
        $this->assertLessThanOrEqual($ulp * 2, $diff);
    }

    /**
     * Test isExactInt with whole number floats.
     */
    public function testIsExactIntWithWholeNumbers(): void
    {
        $this->assertTrue(Floats::isExactInt(0.0));
        $this->assertTrue(Floats::isExactInt(1.0));
        $this->assertTrue(Floats::isExactInt(-1.0));
        $this->assertTrue(Floats::isExactInt(42.0));
        $this->assertTrue(Floats::isExactInt(-99.0));
        $this->assertTrue(Floats::isExactInt(1000000.0));
    }

    /**
     * Test isExactInt with fractional floats.
     */
    public function testIsExactIntWithFractionalNumbers(): void
    {
        $this->assertFalse(Floats::isExactInt(0.5));
        $this->assertFalse(Floats::isExactInt(1.1));
        $this->assertFalse(Floats::isExactInt(-3.14));
        $this->assertFalse(Floats::isExactInt(0.001));
        $this->assertFalse(Floats::isExactInt(99.999));
    }

    /**
     * Test isExactInt with negative zero.
     */
    public function testIsExactIntWithNegativeZero(): void
    {
        $this->assertTrue(Floats::isExactInt(-0.0));
    }

    /**
     * Test isExactInt at the boundary of exact representation (2^53).
     */
    public function testIsExactIntAtExactBoundary(): void
    {
        // 2^53 is the largest consecutive integer exactly representable
        $boundary = 1 << 53; // 9007199254740992
        $this->assertTrue(Floats::isExactInt((float)$boundary));
        $this->assertTrue(Floats::isExactInt((float)-$boundary));
    }

    /**
     * Test isExactInt beyond exact representation boundary.
     */
    public function testIsExactIntBeyondBoundary(): void
    {
        // 2^54 is beyond our ±2^53 range
        $this->assertFalse(Floats::isExactInt((float)(1 << 54)));
        $this->assertFalse(Floats::isExactInt((float)(-(1 << 54))));

        // Very large values are beyond the range
        $this->assertFalse(Floats::isExactInt((float)PHP_INT_MAX));
        $this->assertFalse(Floats::isExactInt(1e20));
    }

    /**
     * Test isExactInt with large integers within exact range.
     */
    public function testIsExactIntWithLargeIntegers(): void
    {
        // Powers of 2 up to 2^53
        $this->assertTrue(Floats::isExactInt((float)(1 << 40)));
        $this->assertTrue(Floats::isExactInt((float)(1 << 50)));
        $this->assertTrue(Floats::isExactInt((float)(1 << 52)));
    }

    /**
     * Test isExactInt with non-finite values.
     */
    public function testIsExactIntWithNonFinite(): void
    {
        $this->assertFalse(Floats::isExactInt(INF));
        $this->assertFalse(Floats::isExactInt(-INF));
        $this->assertFalse(Floats::isExactInt(NAN));
    }

    /**
     * Test isExactInt vs tryConvertToInt relationship.
     */
    public function testIsExactIntVsTryConvertToIntRelationship(): void
    {
        // isExactInt checks for exact integer representation within ±2^53
        // tryConvertToInt checks for lossless conversion to PHP int (±2^63-1)

        // Both should agree for small integers
        $testValues = [0.0, 1.0, -1.0, 42.0, -99.0, 1000.0];
        foreach ($testValues as $value) {
            $isExact = Floats::isExactInt($value);
            $canConvert = Floats::tryConvertToInt($value) !== null;
            $this->assertSame($isExact, $canConvert, "Mismatch for $value");
        }

        // Fractional values fail both
        $this->assertFalse(Floats::isExactInt(1.5));
        $this->assertNull(Floats::tryConvertToInt(1.5));
    }

    /**
     * Test isExactInt comprehensive coverage.
     */
    public function testIsExactIntComprehensive(): void
    {
        // Test various integer values within range
        $testValues = [
            [0.0, true],
            [1.0, true],
            [-1.0, true],
            [100.0, true],
            [-100.0, true],
            [(float)(1 << 52), true], // 2^52 is within range
            [(float)(1 << 53), true], // 2^53 is the boundary
            [(float)(1 << 54), false], // 2^54 is beyond range
            [0.5, false], // Fractional
            [1.1, false], // Fractional
            [1e20, false], // Too large
        ];

        foreach ($testValues as [$value, $expected]) {
            $result = Floats::isExactInt($value);
            $this->assertSame(
                $expected,
                $result,
                sprintf('isExactInt(%s) should be %s', $value, $expected ? 'true' : 'false')
            );
        }
    }

    // endregion

    // region Inspection method tests

    /**
     * Test detection of negative zero.
     */
    public function testIsNegativeZero(): void
    {
        // Test that -0.0 is correctly identified as negative zero.
        $this->assertTrue(Floats::isNegativeZero(-0.0));

        // Test that positive zero is not negative zero.
        $this->assertFalse(Floats::isNegativeZero(0.0));

        // Test that positive values are not negative zero.
        $this->assertFalse(Floats::isNegativeZero(1.0));

        // Test that negative values are not negative zero.
        $this->assertFalse(Floats::isNegativeZero(-1.0));

        // Test that infinity values are not negative zero.
        $this->assertFalse(Floats::isNegativeZero(INF));
        $this->assertFalse(Floats::isNegativeZero(-INF));

        // Test that NAN is not negative zero.
        $this->assertFalse(Floats::isNegativeZero(NAN));
    }

    /**
     * Test detection of positive zero.
     */
    public function testIsPositiveZero(): void
    {
        // Test that +0.0 is correctly identified as positive zero.
        $this->assertTrue(Floats::isPositiveZero(0.0));

        // Test that negative zero is not positive zero.
        $this->assertFalse(Floats::isPositiveZero(-0.0));

        // Test that positive values are not positive zero.
        $this->assertFalse(Floats::isPositiveZero(1.0));

        // Test that negative values are not positive zero.
        $this->assertFalse(Floats::isPositiveZero(-1.0));

        // Test that infinity values are not positive zero.
        $this->assertFalse(Floats::isPositiveZero(INF));
        $this->assertFalse(Floats::isPositiveZero(-INF));

        // Test that NAN is not positive zero.
        $this->assertFalse(Floats::isPositiveZero(NAN));
    }

    /**
     * Test detection of negative values.
     */
    public function testIsNegative(): void
    {
        // Test that negative values are correctly identified.
        $this->assertTrue(Floats::isNegative(-1.0));
        $this->assertTrue(Floats::isNegative(-0.5));
        $this->assertTrue(Floats::isNegative(-100.0));

        // Test that negative zero is identified as negative.
        $this->assertTrue(Floats::isNegative(-0.0));

        // Test that negative infinity is identified as negative.
        $this->assertTrue(Floats::isNegative(-INF));

        // Test that positive values are not negative.
        $this->assertFalse(Floats::isNegative(1.0));
        $this->assertFalse(Floats::isNegative(0.5));

        // Test that positive zero is not negative.
        $this->assertFalse(Floats::isNegative(0.0));

        // Test that positive infinity is not negative.
        $this->assertFalse(Floats::isNegative(INF));

        // Test that NAN is not negative.
        $this->assertFalse(Floats::isNegative(NAN));
    }

    /**
     * Test detection of positive values.
     */
    public function testIsPositive(): void
    {
        // Test that positive values are correctly identified.
        $this->assertTrue(Floats::isPositive(1.0));
        $this->assertTrue(Floats::isPositive(0.5));
        $this->assertTrue(Floats::isPositive(100.0));

        // Test that positive zero is identified as positive.
        $this->assertTrue(Floats::isPositive(0.0));

        // Test that positive infinity is identified as positive.
        $this->assertTrue(Floats::isPositive(INF));

        // Test that negative values are not positive.
        $this->assertFalse(Floats::isPositive(-1.0));
        $this->assertFalse(Floats::isPositive(-0.5));

        // Test that negative zero is not positive.
        $this->assertFalse(Floats::isPositive(-0.0));

        // Test that negative infinity is not positive.
        $this->assertFalse(Floats::isPositive(-INF));

        // Test that NAN is not positive.
        $this->assertFalse(Floats::isPositive(NAN));
    }

    /**
     * Test detection of special float values.
     */
    public function testIsSpecial(): void
    {
        // Test that NAN is identified as special.
        $this->assertTrue(Floats::isSpecial(NAN));

        // Test that negative zero is identified as special.
        $this->assertTrue(Floats::isSpecial(-0.0));

        // Test that positive infinity is identified as special.
        $this->assertTrue(Floats::isSpecial(INF));

        // Test that negative infinity is identified as special.
        $this->assertTrue(Floats::isSpecial(-INF));

        // Test that positive zero is not special.
        $this->assertFalse(Floats::isSpecial(0.0));

        // Test that regular positive values are not special.
        $this->assertFalse(Floats::isSpecial(1.0));
        $this->assertFalse(Floats::isSpecial(42.5));

        // Test that regular negative values are not special.
        $this->assertFalse(Floats::isSpecial(-1.0));
        $this->assertFalse(Floats::isSpecial(-42.5));
    }

    // endregion

    // region Adjacent floats method tests

    /**
     * Test next with regular positive numbers.
     */
    public function testNextWithPositiveNumbers(): void
    {
        $f = 1.0;
        $next = Floats::next($f);
        $this->assertGreaterThan($f, $next);
        $this->assertNotSame($f, $next);
    }

    /**
     * Test next with regular negative numbers.
     */
    public function testNextWithNegativeNumbers(): void
    {
        $f = -1.0;
        $next = Floats::next($f);
        $this->assertGreaterThan($f, $next);
        $this->assertLessThan(0.0, $next);
    }

    /**
     * Test next with positive zero.
     */
    public function testNextWithPositiveZero(): void
    {
        $f = 0.0;
        $next = Floats::next($f);
        $this->assertGreaterThan(0.0, $next);
        $this->assertTrue(Floats::isPositive($next));
    }

    /**
     * Test next with negative zero returns positive zero.
     */
    public function testNextWithNegativeZero(): void
    {
        $f = -0.0;
        $next = Floats::next($f);
        $this->assertSame(0.0, $next);
        $this->assertTrue(Floats::isPositiveZero($next));
    }

    /**
     * Test next with NAN returns NAN.
     */
    public function testNextWithNan(): void
    {
        $next = Floats::next(NAN);
        $this->assertTrue(is_nan($next));
    }

    /**
     * Test next with PHP_FLOAT_MAX returns INF.
     */
    public function testNextWithMaxFloat(): void
    {
        $next = Floats::next(PHP_FLOAT_MAX);
        $this->assertSame(INF, $next);
    }

    /**
     * Test next with INF returns INF.
     */
    public function testNextWithInf(): void
    {
        $next = Floats::next(INF);
        $this->assertSame(INF, $next);
    }

    /**
     * Test next with -INF returns -PHP_FLOAT_MAX.
     */
    public function testNextWithNegativeInf(): void
    {
        $next = Floats::next(-INF);
        $this->assertSame(-PHP_FLOAT_MAX, $next);
    }

    /**
     * Test next with very small positive number.
     */
    public function testNextWithSmallPositiveNumber(): void
    {
        $f = 1e-100;
        $next = Floats::next($f);
        $this->assertGreaterThan($f, $next);
    }

    /**
     * Test previous with regular positive numbers.
     */
    public function testPreviousWithPositiveNumbers(): void
    {
        $f = 1.0;
        $prev = Floats::previous($f);
        $this->assertLessThan($f, $prev);
        $this->assertGreaterThan(0.0, $prev);
    }

    /**
     * Test previous with regular negative numbers.
     */
    public function testPreviousWithNegativeNumbers(): void
    {
        $f = -1.0;
        $prev = Floats::previous($f);
        $this->assertLessThan($f, $prev);
        $this->assertNotSame($f, $prev);
    }

    /**
     * Test previous with positive zero returns negative zero.
     */
    public function testPreviousWithPositiveZero(): void
    {
        $f = 0.0;
        $prev = Floats::previous($f);
        $this->assertSame(-0.0, $prev);
        $this->assertTrue(Floats::isNegativeZero($prev));
    }

    /**
     * Test previous with negative zero.
     */
    public function testPreviousWithNegativeZero(): void
    {
        $f = -0.0;
        $prev = Floats::previous($f);
        $this->assertLessThan(0.0, $prev);
        $this->assertTrue(Floats::isNegative($prev));
    }

    /**
     * Test previous with NAN returns NAN.
     */
    public function testPreviousWithNan(): void
    {
        $prev = Floats::previous(NAN);
        $this->assertTrue(is_nan($prev));
    }

    /**
     * Test previous with -PHP_FLOAT_MAX returns -INF.
     */
    public function testPreviousWithMinFloat(): void
    {
        $prev = Floats::previous(-PHP_FLOAT_MAX);
        $this->assertSame(-INF, $prev);
    }

    /**
     * Test previous with -INF returns -INF.
     */
    public function testPreviousWithNegativeInf(): void
    {
        $prev = Floats::previous(-INF);
        $this->assertSame(-INF, $prev);
    }

    /**
     * Test previous with INF returns PHP_FLOAT_MAX.
     */
    public function testPreviousWithInf(): void
    {
        $prev = Floats::previous(INF);
        $this->assertSame(PHP_FLOAT_MAX, $prev);
    }

    /**
     * Test round-trip: next(previous(x)) should equal x for regular floats.
     */
    public function testNextPreviousRoundTrip(): void
    {
        $testValues = [1.0, -1.0, 42.5, -99.9, 1e10, -1e-10];

        foreach ($testValues as $value) {
            $result = Floats::next(Floats::previous($value));
            $this->assertSame($value, $result, "Round trip failed for $value");
        }
    }

    /**
     * Test round-trip: previous(next(x)) should equal x for regular floats.
     */
    public function testPreviousNextRoundTrip(): void
    {
        $testValues = [1.0, -1.0, 42.5, -99.9, 1e10, -1e-10];

        foreach ($testValues as $value) {
            $result = Floats::previous(Floats::next($value));
            $this->assertSame($value, $result, "Round trip failed for $value");
        }
    }

    /**
     * Test that next produces unique hex values.
     */
    public function testNextProducesUniqueHexValues(): void
    {
        $f = 1.0;
        $next = Floats::next($f);

        $hex1 = Floats::toHex($f);
        $hex2 = Floats::toHex($next);

        $this->assertNotSame($hex1, $hex2, 'next() should produce different binary representation');
    }

    /**
     * Test that previous produces unique hex values.
     */
    public function testPreviousProducesUniqueHexValues(): void
    {
        $f = 1.0;
        $prev = Floats::previous($f);

        $hex1 = Floats::toHex($f);
        $hex2 = Floats::toHex($prev);

        $this->assertNotSame($hex1, $hex2, 'previous() should produce different binary representation');
    }

    /**
     * Test next across zero boundary.
     */
    public function testNextAcrossZero(): void
    {
        // Start with negative zero
        $f = -0.0;
        $next = Floats::next($f);

        // Should get positive zero
        $this->assertSame(0.0, $next);
        $this->assertTrue(Floats::isPositiveZero($next));

        // Next from positive zero should be smallest positive number
        $next2 = Floats::next($next);
        $this->assertGreaterThan(0.0, $next2);
    }

    /**
     * Test previous across zero boundary.
     */
    public function testPreviousAcrossZero(): void
    {
        // Start with positive zero
        $f = 0.0;
        $prev = Floats::previous($f);

        // Should get negative zero
        $this->assertSame(-0.0, $prev);
        $this->assertTrue(Floats::isNegativeZero($prev));

        // Previous from negative zero should be smallest negative number
        $prev2 = Floats::previous($prev);
        $this->assertLessThan(0.0, $prev2);
    }

    // endregion

    // region Bit-manipulation methods tests

    /**
     * Test disassemble with positive one.
     */
    public function testDisassemblePositiveOne(): void
    {
        $result = Floats::disassemble(1.0);

        $this->assertSame(0, $result['sign']);
        $this->assertSame(1023, $result['exponent']); // Bias is 1023, so 1.0 has exponent 0 + 1023
        $this->assertSame(0, $result['fraction']); // 1.0 has implicit 1, no fraction bits set
    }

    /**
     * Test disassemble with negative one.
     */
    public function testDisassembleNegativeOne(): void
    {
        $result = Floats::disassemble(-1.0);

        $this->assertSame(1, $result['sign']);
        $this->assertSame(1023, $result['exponent']);
        $this->assertSame(0, $result['fraction']);
    }

    /**
     * Test disassemble with positive zero.
     */
    public function testDisassemblePositiveZero(): void
    {
        $result = Floats::disassemble(0.0);

        $this->assertSame(0, $result['sign']);
        $this->assertSame(0, $result['exponent']);
        $this->assertSame(0, $result['fraction']);
    }

    /**
     * Test disassemble with negative zero.
     */
    public function testDisassembleNegativeZero(): void
    {
        $result = Floats::disassemble(-0.0);

        $this->assertSame(1, $result['sign']);
        $this->assertSame(0, $result['exponent']);
        $this->assertSame(0, $result['fraction']);
    }

    /**
     * Test disassemble with two (2^1).
     */
    public function testDisassembleTwo(): void
    {
        $result = Floats::disassemble(2.0);

        $this->assertSame(0, $result['sign']);
        $this->assertSame(1024, $result['exponent']); // Exponent 1 + bias 1023
        $this->assertSame(0, $result['fraction']);
    }

    /**
     * Test disassemble with 1.5 (1 + 0.5).
     */
    public function testDisassembleOnePointFive(): void
    {
        $result = Floats::disassemble(1.5);

        $this->assertSame(0, $result['sign']);
        $this->assertSame(1023, $result['exponent']);
        // 1.5 = 1.1 in binary, so fraction has MSB set
        $this->assertSame(1 << 51, $result['fraction']);
    }

    /**
     * Test disassemble with infinity.
     */
    public function testDisassembleInfinity(): void
    {
        $result = Floats::disassemble(INF);

        $this->assertSame(0, $result['sign']);
        $this->assertSame(2047, $result['exponent']); // All 11 bits set
        $this->assertSame(0, $result['fraction']);
    }

    /**
     * Test disassemble with negative infinity.
     */
    public function testDisassembleNegativeInfinity(): void
    {
        $result = Floats::disassemble(-INF);

        $this->assertSame(1, $result['sign']);
        $this->assertSame(2047, $result['exponent']);
        $this->assertSame(0, $result['fraction']);
    }

    /**
     * Test disassemble with NAN.
     */
    public function testDisassembleNan(): void
    {
        $result = Floats::disassemble(NAN);

        // NAN has exponent all 1s and non-zero fraction
        $this->assertSame(2047, $result['exponent']);
        $this->assertGreaterThan(0, $result['fraction']);
    }

    /**
     * Test assemble with positive one.
     */
    public function testAssemblePositiveOne(): void
    {
        $result = Floats::assemble(0, 1023, 0);
        $this->assertSame(1.0, $result);
    }

    /**
     * Test assemble with negative one.
     */
    public function testAssembleNegativeOne(): void
    {
        $result = Floats::assemble(1, 1023, 0);
        $this->assertSame(-1.0, $result);
    }

    /**
     * Test assemble with positive zero.
     */
    public function testAssemblePositiveZero(): void
    {
        $result = Floats::assemble(0, 0, 0);
        $this->assertSame(0.0, $result);
        $this->assertTrue(Floats::isPositiveZero($result));
    }

    /**
     * Test assemble with negative zero.
     */
    public function testAssembleNegativeZero(): void
    {
        $result = Floats::assemble(1, 0, 0);
        $this->assertSame(-0.0, $result);
        $this->assertTrue(Floats::isNegativeZero($result));
    }

    /**
     * Test assemble with two.
     */
    public function testAssembleTwo(): void
    {
        $result = Floats::assemble(0, 1024, 0);
        $this->assertSame(2.0, $result);
    }

    /**
     * Test assemble with 1.5.
     */
    public function testAssembleOnePointFive(): void
    {
        $result = Floats::assemble(0, 1023, 1 << 51);
        $this->assertSame(1.5, $result);
    }

    /**
     * Test assemble with infinity.
     */
    public function testAssembleInfinity(): void
    {
        $result = Floats::assemble(0, 2047, 0);
        $this->assertSame(INF, $result);
    }

    /**
     * Test assemble with negative infinity.
     */
    public function testAssembleNegativeInfinity(): void
    {
        $result = Floats::assemble(1, 2047, 0);
        $this->assertSame(-INF, $result);
    }

    /**
     * Test assemble with NAN (exponent 2047, non-zero fraction).
     */
    public function testAssembleNan(): void
    {
        $result = Floats::assemble(0, 2047, 1);
        $this->assertTrue(is_nan($result));
    }

    /**
     * Test assemble round-trip with disassemble.
     */
    public function testAssembleDisassembleRoundTrip(): void
    {
        $testValues = [1.0, -1.0, 2.0, 0.5, 1.5, -42.25, 1e10, 1e-10, PHP_FLOAT_MAX];

        foreach ($testValues as $value) {
            $parts = Floats::disassemble($value);
            $result = Floats::assemble($parts['sign'], $parts['exponent'], $parts['fraction']);
            $this->assertSame($value, $result, "Round trip failed for $value");
        }
    }

    /**
     * Test assemble with invalid sign throws ValueError.
     */
    public function testAssembleInvalidSignThrows(): void
    {
        $this->expectException(ValueError::class);
        $this->expectExceptionMessage('Sign must be 0 or 1');
        Floats::assemble(2, 1023, 0);
    }

    /**
     * Test assemble with negative sign throws ValueError.
     */
    public function testAssembleNegativeSignThrows(): void
    {
        $this->expectException(ValueError::class);
        $this->expectExceptionMessage('Sign must be 0 or 1');
        Floats::assemble(-1, 1023, 0);
    }

    /**
     * Test assemble with invalid exponent throws ValueError.
     */
    public function testAssembleInvalidExponentThrows(): void
    {
        $this->expectException(ValueError::class);
        $this->expectExceptionMessage('Exponent must be in the range [0, 2047]');
        Floats::assemble(0, 2048, 0);
    }

    /**
     * Test assemble with negative exponent throws ValueError.
     */
    public function testAssembleNegativeExponentThrows(): void
    {
        $this->expectException(ValueError::class);
        $this->expectExceptionMessage('Exponent must be in the range [0, 2047]');
        Floats::assemble(0, -1, 0);
    }

    /**
     * Test assemble with invalid fraction throws ValueError.
     */
    public function testAssembleInvalidFractionThrows(): void
    {
        $this->expectException(ValueError::class);
        $this->expectExceptionMessage('Fraction must be in the range');
        Floats::assemble(0, 1023, 0x10000000000000); // 2^52, one too large
    }

    /**
     * Test assemble with negative fraction throws ValueError.
     */
    public function testAssembleNegativeFractionThrows(): void
    {
        $this->expectException(ValueError::class);
        $this->expectExceptionMessage('Fraction must be in the range');
        Floats::assemble(0, 1023, -1);
    }

    // endregion

    // region Random methods tests

    /**
     * Test rand returns finite floats.
     */
    public function testRandReturnsFiniteFloats(): void
    {
        // Generate multiple random floats and verify they're all finite
        for ($i = 0; $i < 100; $i++) {
            $f = Floats::rand();
            $this->assertTrue(is_finite($f), 'Random float should be finite');
            $this->assertFalse(is_nan($f), 'Random float should not be NAN');
            $this->assertFalse(Floats::isSpecial($f), 'Random float should not be special');
        }
    }

    /**
     * Test rand returns different values.
     */
    public function testRandReturnsDifferentValues(): void
    {
        // Generate multiple random floats and verify they're not all the same
        $values = [];
        for ($i = 0; $i < 10; $i++) {
            $values[] = Floats::rand();
        }

        // Check that we got at least 2 different values (extremely unlikely to fail)
        $unique = array_unique($values);
        $this->assertGreaterThan(1, count($unique), 'Should generate different random values');
    }

    /**
     * Test randUniform with valid range.
     */
    public function testRandUniformWithValidRange(): void
    {
        $min = 10.0;
        $max = 20.0;

        // Generate multiple values and verify they're all in range
        for ($i = 0; $i < 100; $i++) {
            $f = Floats::randUniform($min, $max);
            $this->assertGreaterThanOrEqual($min, $f, 'Value should be >= min');
            $this->assertLessThanOrEqual($max, $f, 'Value should be <= max');
            $this->assertTrue(is_finite($f), 'Value should be finite');
        }
    }

    /**
     * Test randUniform with negative range.
     */
    public function testRandUniformWithNegativeRange(): void
    {
        $min = -50.0;
        $max = -10.0;

        $f = Floats::randUniform($min, $max);
        $this->assertGreaterThanOrEqual($min, $f);
        $this->assertLessThanOrEqual($max, $f);
    }

    /**
     * Test randUniform with range crossing zero.
     */
    public function testRandUniformWithRangeCrossingZero(): void
    {
        $min = -10.0;
        $max = 10.0;

        $f = Floats::randUniform($min, $max);
        $this->assertGreaterThanOrEqual($min, $f);
        $this->assertLessThanOrEqual($max, $f);
    }

    /**
     * Test randUniform with min >= max throws ValueError.
     */
    public function testRandUniformWithMinEqualToMax(): void
    {
        $f = Floats::randUniform(42.5, 42.5);
        $this->assertEquals(42.5, $f);
    }

    /**
     * Test randUniform with min > max throws ValueError.
     */
    public function testRandUniformWithMinGreaterThanMaxThrows(): void
    {
        $this->expectException(ValueError::class);
        $this->expectExceptionMessage('Min must be less than or equal to max');
        Floats::randUniform(20.0, 10.0);
    }

    /**
     * Test randUniform with NAN min throws ValueError.
     */
    public function testRandUniformWithNanMinThrows(): void
    {
        $this->expectException(ValueError::class);
        $this->expectExceptionMessage('Min and max must be finite');
        Floats::randUniform(NAN, 10.0);
    }

    /**
     * Test randUniform with NAN max throws ValueError.
     */
    public function testRandUniformWithNanMaxThrows(): void
    {
        $this->expectException(ValueError::class);
        $this->expectExceptionMessage('Min and max must be finite');
        Floats::randUniform(0.0, NAN);
    }

    /**
     * Test randUniform with positive infinity min throws ValueError.
     */
    public function testRandUniformWithInfMinThrows(): void
    {
        $this->expectException(ValueError::class);
        $this->expectExceptionMessage('Min and max must be finite');
        Floats::randUniform(INF, 10.0);
    }

    /**
     * Test randUniform with negative infinity max throws ValueError.
     */
    public function testRandUniformWithNegativeInfMaxThrows(): void
    {
        $this->expectException(ValueError::class);
        $this->expectExceptionMessage('Min and max must be finite');
        Floats::randUniform(0.0, -INF);
    }

    /**
     * Test randUniform with negative zero as min or max normalizes to positive zero.
     */
    public function testRandUniformWithNegativeZero(): void
    {
        // -0.0 should be treated as 0.0, so this creates a valid range [0.0, 10.0]
        $f = Floats::randUniform(-0.0, 10.0);
        $this->assertGreaterThanOrEqual(0.0, $f);
        $this->assertLessThanOrEqual(10.0, $f);
    }

    /**
     * Test randUniform generates no duplicates.
     * This test creates a range of exactly 10 adjacent floats and samples it many times, to ensure an even distribution
     * across all possible results from the method.
     * With optimal step calculation, we should never get duplicates.
     */
    public function testRandUniformNoCollisions(): void
    {
        // Build a range of exactly 10 adjacent floats starting from 1.0
        $nValues = 10;
        $min = 1.0;
        $max = $min;
        $counts = [Floats::toHex($min) => 0];
        for ($i = 0; $i < $nValues - 1; $i++) {
            $f = Floats::next($max);
            $counts[Floats::toHex($f)] = 0;
            $max = $f;
        }

        // Sample the range many times, and count how many times each value appears.
        $nIters = 100000;
        for ($i = 0; $i < $nIters; $i++) {
            $f = Floats::randUniform($min, $max);
            $counts[Floats::toHex($f)]++;
        }

//        echo Floats::toHex($min) . ' - ' . Floats::toHex($max) . "\n";
//        var_dump($counts);

        // Check we got the right number of results.
        $this->assertEquals($nValues, count($counts));

        // Check we got a reasonably even distribution across the possible values.
        $avg = $nIters / $nValues;
        foreach ($counts as $count) {
            $this->assertGreaterThanOrEqual($avg * 0.9, $count);
            $this->assertLessThanOrEqual($avg * 1.1, $count);
        }
    }

    /**
     * Test rand with valid positive range.
     */
    public function testRandWithRangeWithPositiveRange(): void
    {
        $min = 10.0;
        $max = 20.0;

        // Generate multiple values and verify they're all in range
        for ($i = 0; $i < 100; $i++) {
            $f = Floats::rand($min, $max);
            $this->assertGreaterThanOrEqual($min, $f, 'Value should be >= min');
            $this->assertLessThanOrEqual($max, $f, 'Value should be <= max');
            $this->assertTrue(is_finite($f), 'Value should be finite');
        }
    }

    /**
     * Test rand with negative range.
     */
    public function testRandWithRangeWithNegativeRange(): void
    {
        $min = -50.0;
        $max = -10.0;

        for ($i = 0; $i < 50; $i++) {
            $f = Floats::rand($min, $max);
            $this->assertGreaterThanOrEqual($min, $f);
            $this->assertLessThanOrEqual($max, $f);
        }
    }

    /**
     * Test rand with range crossing zero.
     */
    public function testRandWithRangeWithRangeCrossingZero(): void
    {
        $min = -10.0;
        $max = 10.0;

        for ($i = 0; $i < 50; $i++) {
            $f = Floats::rand($min, $max);
            $this->assertGreaterThanOrEqual($min, $f);
            $this->assertLessThanOrEqual($max, $f);
        }
    }

    /**
     * Test rand with narrow range (e.g., [0, 1]).
     */
    public function testRandWithRangeWithNarrowRange(): void
    {
        $min = 0.0;
        $max = 1.0;

        for ($i = 0; $i < 50; $i++) {
            $f = Floats::rand($min, $max);
            $this->assertGreaterThanOrEqual($min, $f);
            $this->assertLessThanOrEqual($max, $f);
        }
    }

    /**
     * Test rand with min equal to max returns that value.
     */
    public function testRandWithRangeWithMinEqualToMax(): void
    {
        $value = 42.5;
        $f = Floats::rand($value, $value);
        $this->assertSame($value, $f);
    }

    /**
     * Test rand with min > max throws ValueError.
     */
    public function testRandWithRangeWithMinGreaterThanMaxThrows(): void
    {
        $this->expectException(ValueError::class);
        $this->expectExceptionMessage('Min must be less than or equal to max');
        Floats::rand(20.0, 10.0);
    }

    /**
     * Test rand with NAN throws ValueError.
     */
    public function testRandWithRangeWithNanThrows(): void
    {
        $this->expectException(ValueError::class);
        $this->expectExceptionMessage('Min and max must be finite');
        Floats::rand(NAN, 10.0);
    }

    /**
     * Test rand with INF throws ValueError.
     */
    public function testRandWithRangeWithInfThrows(): void
    {
        $this->expectException(ValueError::class);
        $this->expectExceptionMessage('Min and max must be finite');
        Floats::rand(0.0, INF);
    }

    /**
     * Test rand returns different values (statistical test).
     */
    public function testRandWithRangeReturnsDifferentValues(): void
    {
        $min = 0.0;
        $max = 100.0;
        $values = [];

        for ($i = 0; $i < 20; $i++) {
            $values[] = Floats::rand($min, $max);
        }

        // Should have at least 2 different values
        $unique = array_unique($values);
        $this->assertGreaterThan(1, count($unique), 'Should generate different random values');
    }

    /**
     * Test rand with very small range.
     */
    public function testRandWithRangeWithVerySmallRange(): void
    {
        $min = 1.0;
        $max = 1.0 + 1e-10;

        for ($i = 0; $i < 20; $i++) {
            $f = Floats::rand($min, $max);
//            echo Stringify::stringifyFloat($f), PHP_EOL;
            $this->assertGreaterThanOrEqual($min, $f);
            $this->assertLessThanOrEqual($max, $f);
        }
    }

    /**
     * Test rand with large range.
     */
    public function testRandWithRangeWithLargeRange(): void
    {
        $min = -1e100;
        $max = 1e100;

        for ($i = 0; $i < 20; $i++) {
            $f = Floats::rand($min, $max);
            $this->assertGreaterThanOrEqual($min, $f);
            $this->assertLessThanOrEqual($max, $f);
            $this->assertTrue(is_finite($f));
        }
    }

    // endregion
}
