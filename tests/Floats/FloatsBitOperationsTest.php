<?php

declare(strict_types=1);

namespace Galaxon\Core\Tests\Floats;

use Galaxon\Core\Floats;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ValueError;

/**
 * Test class for Floats utility class - adjacent floats (next/previous) and bit-manipulation methods.
 */
#[CoversClass(Floats::class)]
final class FloatsBitOperationsTest extends TestCase
{
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

    // region ULP tests

    /**
     * Test ULP with standard values.
     */
    public function testUlpWithStandardValues(): void
    {
        // ULP of 1.0 should be PHP_FLOAT_EPSILON (1.0 is a power of 2).
        $this->assertSame(PHP_FLOAT_EPSILON, Floats::ulp(1.0));

        // ULP is defined as the gap to the next representable float.
        // For non-powers-of-2, the formula `value * PHP_FLOAT_EPSILON` is only approximate.
        $this->assertSame(Floats::next(1000.0) - 1000.0, Floats::ulp(1000.0));
        $this->assertSame(Floats::next(0.001) - 0.001, Floats::ulp(0.001));
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

        // ULP is defined as the gap to the next representable float.
        $this->assertSame(Floats::next($large) - $large, $ulp);

        // Verify it's actually the spacing.
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

        // ULP is defined as the gap to the next representable float.
        $this->assertSame(Floats::next($small) - $small, $ulp);
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
     * Test ULP with NAN returns NAN.
     */
    public function testUlpWithNan(): void
    {
        $this->assertNan(Floats::ulp(NAN));
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

    // endregion
}
