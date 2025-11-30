<?php

declare(strict_types=1);

namespace Galaxon\Core\Tests;

use Galaxon\Core\Numbers;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ValueError;

/**
 * Test class for Numbers utility class.
 */
#[CoversClass(Numbers::class)]
final class NumbersTest extends TestCase
{
    /**
     * Test sign detection with default behavior (zero for zero).
     */
    public function testSignDefault(): void
    {
        // Test positive numbers return 1.
        $this->assertSame(1, Numbers::sign(1));
        $this->assertSame(1, Numbers::sign(42));
        $this->assertSame(1, Numbers::sign(1.5));
        $this->assertSame(1, Numbers::sign(0.001));

        // Test negative numbers return -1.
        $this->assertSame(-1, Numbers::sign(-1));
        $this->assertSame(-1, Numbers::sign(-42));
        $this->assertSame(-1, Numbers::sign(-1.5));
        $this->assertSame(-1, Numbers::sign(-0.001));

        // Test zero returns 0 (default behavior).
        $this->assertSame(0, Numbers::sign(0));
        $this->assertSame(0, Numbers::sign(0.0));
        $this->assertSame(0, Numbers::sign(-0.0));

        // Test infinity values.
        $this->assertSame(1, Numbers::sign(INF));
        $this->assertSame(-1, Numbers::sign(-INF));
    }

    /**
     * Test sign detection with zeroForZero set to false.
     */
    public function testSignNoZeroForZero(): void
    {
        // Test positive numbers return 1.
        $this->assertSame(1, Numbers::sign(1, false));
        $this->assertSame(1, Numbers::sign(42.5, false));

        // Test negative numbers return -1.
        $this->assertSame(-1, Numbers::sign(-1, false));
        $this->assertSame(-1, Numbers::sign(-42.5, false));

        // Test integer zero returns 1 (positive zero).
        $this->assertSame(1, Numbers::sign(0, false));

        // Test positive float zero returns 1.
        $this->assertSame(1, Numbers::sign(0.0, false));

        // Test negative float zero returns -1.
        $this->assertSame(-1, Numbers::sign(-0.0, false));

        // Test infinity values.
        $this->assertSame(1, Numbers::sign(INF, false));
        $this->assertSame(-1, Numbers::sign(-INF, false));
    }

    /**
     * Test copying sign to positive numbers.
     */
    public function testCopySignToPositive(): void
    {
        // Test copying positive sign to positive number.
        $this->assertSame(5, Numbers::copySign(5, 10));
        $this->assertSame(5.0, Numbers::copySign(5.0, 10.0));

        // Test copying negative sign to positive number.
        $this->assertSame(-5, Numbers::copySign(5, -10));
        $this->assertSame(-5.0, Numbers::copySign(5.0, -10.0));

        // Test copying sign from zero to positive number.
        $this->assertSame(5, Numbers::copySign(5, 0));
        $this->assertSame(5.0, Numbers::copySign(5.0, 0.0));
        $this->assertSame(-5, Numbers::copySign(5, -0.0));

        // Test copying sign from infinity.
        $this->assertSame(5, Numbers::copySign(5, INF));
        $this->assertSame(-5, Numbers::copySign(5, -INF));
    }

    /**
     * Test copying sign to negative numbers.
     */
    public function testCopySignToNegative(): void
    {
        // Test copying positive sign to negative number.
        $this->assertSame(5, Numbers::copySign(-5, 10));
        $this->assertSame(5.0, Numbers::copySign(-5.0, 10.0));

        // Test copying negative sign to negative number.
        $this->assertSame(-5, Numbers::copySign(-5, -10));
        $this->assertSame(-5.0, Numbers::copySign(-5.0, -10.0));

        // Test copying sign from zero to negative number.
        $this->assertSame(5, Numbers::copySign(-5, 0));
        $this->assertSame(5.0, Numbers::copySign(-5.0, 0.0));
        $this->assertSame(-5, Numbers::copySign(-5, -0.0));
    }

    /**
     * Test copying sign to and from zero.
     */
    public function testCopySignWithZero(): void
    {
        // Test copying positive sign to zero.
        $this->assertSame(0, Numbers::copySign(0, 10));
        $this->assertSame(0.0, Numbers::copySign(0.0, 10));

        // Test copying negative sign to zero.
        $this->assertSame(0, Numbers::copySign(0, -10));
        $this->assertSame(-0.0, Numbers::copySign(0.0, -10));

        // Test copying sign from positive zero.
        $this->assertSame(5, Numbers::copySign(5, 0.0));

        // Test copying sign from negative zero.
        $this->assertSame(-5, Numbers::copySign(5, -0.0));
    }

    /**
     * Test copying sign with infinity values.
     */
    public function testCopySignWithInfinity(): void
    {
        // Test copying sign to infinity.
        $this->assertSame(INF, Numbers::copySign(INF, 10));
        $this->assertSame(-INF, Numbers::copySign(INF, -10));
        $this->assertSame(INF, Numbers::copySign(-INF, 10));
        $this->assertSame(-INF, Numbers::copySign(-INF, -10));

        // Test copying sign from infinity.
        $this->assertSame(5, Numbers::copySign(5, INF));
        $this->assertSame(-5, Numbers::copySign(5, -INF));
    }

    /**
     * Test that copySign throws ValueError when num is NaN.
     */
    public function testCopySignWithNanAsNum(): void
    {
        // Test that NaN as first parameter throws ValueError.
        $this->expectException(ValueError::class);
        $this->expectExceptionMessage('NaN is not allowed for either parameter.');
        Numbers::copySign(NAN, 5);
    }

    /**
     * Test that copySign throws ValueError when sign_source is NaN.
     */
    public function testCopySignWithNanAsSignSource(): void
    {
        // Test that NaN as second parameter throws ValueError.
        $this->expectException(ValueError::class);
        $this->expectExceptionMessage('NaN is not allowed for either parameter.');
        Numbers::copySign(5, NAN);
    }

    /**
     * Test that copySign throws ValueError when both parameters are NaN.
     */
    public function testCopySignWithBothNan(): void
    {
        // Test that NaN as both parameters throws ValueError.
        $this->expectException(ValueError::class);
        $this->expectExceptionMessage('NaN is not allowed for either parameter.');
        Numbers::copySign(NAN, NAN);
    }

    /**
     * Test copySign preserves the type relationship.
     */
    public function testCopySignReturnType(): void
    {
        // Test that copySign with int parameter returns int.
        $result = Numbers::copySign(5, 10);
        $this->assertIsInt($result);

        $result = Numbers::copySign(5, -10);
        $this->assertIsInt($result);

        // Test that copySign with float parameter returns float.
        $result = Numbers::copySign(5.0, 10);
        $this->assertIsFloat($result);

        $result = Numbers::copySign(5.0, -10.0);
        $this->assertIsFloat($result);
    }

    // region equal tests

    /**
     * Test equal with two equal integers.
     */
    public function testEqualWithEqualIntegers(): void
    {
        $this->assertTrue(Numbers::equal(5, 5));
        $this->assertTrue(Numbers::equal(0, 0));
        $this->assertTrue(Numbers::equal(-42, -42));
        $this->assertTrue(Numbers::equal(1000000, 1000000));
    }

    /**
     * Test equal with two different integers.
     */
    public function testEqualWithDifferentIntegers(): void
    {
        $this->assertFalse(Numbers::equal(5, 6));
        $this->assertFalse(Numbers::equal(0, 1));
        $this->assertFalse(Numbers::equal(-42, 42));
        $this->assertFalse(Numbers::equal(1000000, 1000001));
    }

    /**
     * Test equal with two equal floats.
     */
    public function testEqualWithEqualFloats(): void
    {
        $this->assertTrue(Numbers::equal(5.0, 5.0));
        $this->assertTrue(Numbers::equal(0.0, 0.0));
        $this->assertTrue(Numbers::equal(-42.5, -42.5));
        $this->assertTrue(Numbers::equal(1.23456789, 1.23456789));
    }

    /**
     * Test equal with two different floats.
     */
    public function testEqualWithDifferentFloats(): void
    {
        $this->assertFalse(Numbers::equal(5.0, 5.1));
        $this->assertFalse(Numbers::equal(0.0, 0.1));
        $this->assertFalse(Numbers::equal(-42.5, -42.6));
        $this->assertFalse(Numbers::equal(1.0, 1.0 + PHP_FLOAT_EPSILON));
    }

    /**
     * Test equal with mixed int and float (equal values).
     */
    public function testEqualWithMixedIntFloatEqual(): void
    {
        $this->assertTrue(Numbers::equal(5, 5.0));
        $this->assertTrue(Numbers::equal(5.0, 5));
        $this->assertTrue(Numbers::equal(0, 0.0));
        $this->assertTrue(Numbers::equal(0.0, 0));
        $this->assertTrue(Numbers::equal(-42, -42.0));
        $this->assertTrue(Numbers::equal(-42.0, -42));
    }

    /**
     * Test equal with mixed int and float (different values).
     */
    public function testEqualWithMixedIntFloatDifferent(): void
    {
        $this->assertFalse(Numbers::equal(5, 5.1));
        $this->assertFalse(Numbers::equal(5.1, 5));
        $this->assertFalse(Numbers::equal(0, 0.1));
        $this->assertFalse(Numbers::equal(0.1, 0));
    }

    /**
     * Test equal with positive and negative zero.
     */
    public function testEqualWithZeros(): void
    {
        $this->assertTrue(Numbers::equal(0, 0));
        $this->assertTrue(Numbers::equal(0.0, 0.0));
        $this->assertTrue(Numbers::equal(0, 0.0));
        $this->assertTrue(Numbers::equal(0.0, 0));
        $this->assertTrue(Numbers::equal(0.0, -0.0));
        $this->assertTrue(Numbers::equal(-0.0, 0.0));
    }

    /**
     * Test equal with special float values.
     */
    public function testEqualWithSpecialFloats(): void
    {
        // INF
        $this->assertTrue(Numbers::equal(INF, INF));
        $this->assertFalse(Numbers::equal(INF, -INF));
        $this->assertFalse(Numbers::equal(INF, 1.0));

        // -INF
        $this->assertTrue(Numbers::equal(-INF, -INF));
        $this->assertFalse(Numbers::equal(-INF, INF));
        $this->assertFalse(Numbers::equal(-INF, -1.0));

        // NAN (NAN !== NAN by IEEE 754)
        $this->assertFalse(Numbers::equal(NAN, NAN));
        $this->assertFalse(Numbers::equal(NAN, 1.0));
    }

    // endregion

    // region approxEqual tests

    /**
     * Test approxEqual with two equal integers.
     */
    public function testApproxEqualWithEqualIntegers(): void
    {
        $this->assertTrue(Numbers::approxEqual(5, 5));
        $this->assertTrue(Numbers::approxEqual(0, 0));
        $this->assertTrue(Numbers::approxEqual(-42, -42));
        $this->assertTrue(Numbers::approxEqual(1000000, 1000000));
    }

    /**
     * Test approxEqual with two different integers (never approximately equal).
     */
    public function testApproxEqualWithDifferentIntegers(): void
    {
        $this->assertFalse(Numbers::approxEqual(5, 6));
        $this->assertFalse(Numbers::approxEqual(0, 1));
        $this->assertFalse(Numbers::approxEqual(-42, -41));
        // Even with large epsilon, ints are compared exactly
        $this->assertFalse(Numbers::approxEqual(100, 101, 10.0));
    }

    /**
     * Test approxEqual with two equal floats.
     */
    public function testApproxEqualWithEqualFloats(): void
    {
        $this->assertTrue(Numbers::approxEqual(5.0, 5.0));
        $this->assertTrue(Numbers::approxEqual(0.0, 0.0));
        $this->assertTrue(Numbers::approxEqual(-42.5, -42.5));
    }

    /**
     * Test approxEqual with floats within tolerance (relative by default).
     */
    public function testApproxEqualWithFloatsWithinTolerance(): void
    {
        // Default relative tolerance (1e-10)
        $this->assertTrue(Numbers::approxEqual(1.0, 1.0 + 1e-11));
        $this->assertTrue(Numbers::approxEqual(100.0, 100.0 + 1e-9));

        // Custom tolerance
        $this->assertTrue(Numbers::approxEqual(100.0, 105.0, 0.1, true));
        $this->assertTrue(Numbers::approxEqual(1.0, 1.05, 0.1, false));
    }

    /**
     * Test approxEqual with floats outside tolerance.
     */
    public function testApproxEqualWithFloatsOutsideTolerance(): void
    {
        // Default relative tolerance (1e-10)
        $this->assertFalse(Numbers::approxEqual(1.0, 1.0 + 1e-9));
        $this->assertFalse(Numbers::approxEqual(100.0, 100.0 + 1e-7));

        // Custom tolerance
        $this->assertFalse(Numbers::approxEqual(100.0, 115.0, 0.1, true));
        $this->assertFalse(Numbers::approxEqual(1.0, 1.15, 0.1, false));
    }

    /**
     * Test approxEqual with mixed int and float (equal values).
     */
    public function testApproxEqualWithMixedIntFloatEqual(): void
    {
        $this->assertTrue(Numbers::approxEqual(5, 5.0));
        $this->assertTrue(Numbers::approxEqual(5.0, 5));
        $this->assertTrue(Numbers::approxEqual(0, 0.0));
        $this->assertTrue(Numbers::approxEqual(-42, -42.0));
    }

    /**
     * Test approxEqual with mixed int and float (approximately equal).
     */
    public function testApproxEqualWithMixedIntFloatApproximate(): void
    {
        // When one is float, uses float comparison
        $this->assertTrue(Numbers::approxEqual(5, 5.0 + 1e-11));
        $this->assertTrue(Numbers::approxEqual(5.0 + 1e-11, 5));
        $this->assertTrue(Numbers::approxEqual(100, 100.0 + 1e-9));
    }

    /**
     * Test approxEqual with mixed int and float (not approximately equal).
     */
    public function testApproxEqualWithMixedIntFloatNotApproximate(): void
    {
        $this->assertFalse(Numbers::approxEqual(5, 5.1));
        $this->assertFalse(Numbers::approxEqual(5.1, 5));
        $this->assertFalse(Numbers::approxEqual(0, 0.1));
    }

    /**
     * Test approxEqual defaults to relative comparison.
     */
    public function testApproxEqualDefaultsToRelative(): void
    {
        // Large values: relative works
        $large = 1e20;
        $this->assertTrue(Numbers::approxEqual($large, $large + 1e9));
        // Verify it's using relative by checking with explicit absolute
        $this->assertFalse(Numbers::approxEqual($large, $large + 1e9, 1e-10, false));
    }

    /**
     * Test approxEqual with absolute comparison.
     */
    public function testApproxEqualWithAbsolute(): void
    {
        $this->assertTrue(Numbers::approxEqual(1.0, 1.05, 0.1, false));
        $this->assertFalse(Numbers::approxEqual(1.0, 1.15, 0.1, false));
    }

    /**
     * Test approxEqual with zero values.
     */
    public function testApproxEqualWithZeros(): void
    {
        $this->assertTrue(Numbers::approxEqual(0, 0));
        $this->assertTrue(Numbers::approxEqual(0.0, 0.0));
        $this->assertTrue(Numbers::approxEqual(0, 0.0));
        $this->assertTrue(Numbers::approxEqual(0.0, -0.0));
    }

    /**
     * Test approxEqual with negative epsilon throws ValueError.
     */
    public function testApproxEqualWithNegativeEpsilonThrows(): void
    {
        $this->expectException(ValueError::class);
        $this->expectExceptionMessage('Epsilon must be non-negative');
        Numbers::approxEqual(1.0, 1.0, -0.1);
    }

    /**
     * Test approxEqual with special float values.
     */
    public function testApproxEqualWithSpecialFloats(): void
    {
        // NAN (NAN !== NAN, and NAN comparisons always return false)
        $this->assertFalse(Numbers::approxEqual(NAN, NAN));
        $this->assertFalse(Numbers::approxEqual(NAN, 1.0));
        $this->assertFalse(Numbers::approxEqual(1.0, NAN));

        // Note: approxEqual with infinities can give unexpected results
        // due to INF arithmetic. Use equal() for exact infinity comparisons.
    }

    // endregion
}
