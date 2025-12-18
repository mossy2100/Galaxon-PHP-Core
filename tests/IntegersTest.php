<?php

declare(strict_types=1);

namespace Galaxon\Core\Tests;

use ArgumentCountError;
use Galaxon\Core\Integers;
use OverflowException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use RangeException;
use UnderflowException;

/**
 * Test class for Integers utility class.
 */
#[CoversClass(Integers::class)]
final class IntegersTest extends TestCase
{
    /**
     * Test addition of integers without overflow.
     */
    public function testAdd(): void
    {
        // Test basic addition.
        $this->assertSame(5, Integers::add(2, 3));
        $this->assertSame(0, Integers::add(0, 0));

        // Test addition with negative numbers.
        $this->assertSame(-5, Integers::add(-2, -3));
        $this->assertSame(1, Integers::add(-2, 3));
        $this->assertSame(-1, Integers::add(2, -3));

        // Test addition with zero.
        $this->assertSame(10, Integers::add(10, 0));
        $this->assertSame(-10, Integers::add(0, -10));

        // Test large numbers that don't overflow.
        $this->assertSame(1000000, Integers::add(500000, 500000));
    }

    /**
     * Test addition overflow detection.
     */
    public function testAddOverflow(): void
    {
        // Test positive overflow.
        $this->expectException(OverflowException::class);
        $this->expectExceptionMessage('Overflow in integer addition.');
        Integers::add(PHP_INT_MAX, 1);
    }

    /**
     * Test addition negative overflow detection.
     */
    public function testAddNegativeOverflow(): void
    {
        // Test negative overflow.
        $this->expectException(OverflowException::class);
        $this->expectExceptionMessage('Overflow in integer addition.');
        Integers::add(PHP_INT_MIN, -1);
    }

    /**
     * Test subtraction of integers without overflow.
     */
    public function testSub(): void
    {
        // Test basic subtraction.
        $this->assertSame(1, Integers::sub(3, 2));
        $this->assertSame(0, Integers::sub(0, 0));

        // Test subtraction with negative numbers.
        $this->assertSame(1, Integers::sub(-2, -3));
        $this->assertSame(-5, Integers::sub(-2, 3));
        $this->assertSame(5, Integers::sub(2, -3));

        // Test subtraction with zero.
        $this->assertSame(10, Integers::sub(10, 0));
        $this->assertSame(-10, Integers::sub(0, 10));

        // Test large numbers that don't overflow.
        $this->assertSame(0, Integers::sub(500000, 500000));
    }

    /**
     * Test subtraction overflow detection.
     */
    public function testSubOverflow(): void
    {
        // Test positive overflow (subtracting a large negative number).
        $this->expectException(OverflowException::class);
        $this->expectExceptionMessage('Overflow in integer subtraction.');
        Integers::sub(PHP_INT_MAX, -1);
    }

    /**
     * Test subtraction negative overflow detection.
     */
    public function testSubNegativeOverflow(): void
    {
        // Test negative overflow (subtracting a large positive number from minimum).
        $this->expectException(OverflowException::class);
        $this->expectExceptionMessage('Overflow in integer subtraction.');
        Integers::sub(PHP_INT_MIN, 1);
    }

    /**
     * Test multiplication of integers without overflow.
     */
    public function testMul(): void
    {
        // Test basic multiplication.
        $this->assertSame(6, Integers::mul(2, 3));
        $this->assertSame(0, Integers::mul(0, 0));

        // Test multiplication with negative numbers.
        $this->assertSame(6, Integers::mul(-2, -3));
        $this->assertSame(-6, Integers::mul(-2, 3));
        $this->assertSame(-6, Integers::mul(2, -3));

        // Test multiplication with zero.
        $this->assertSame(0, Integers::mul(10, 0));
        $this->assertSame(0, Integers::mul(0, 10));

        // Test multiplication with one.
        $this->assertSame(10, Integers::mul(10, 1));
        $this->assertSame(-10, Integers::mul(-10, 1));

        // Test large numbers that don't overflow.
        $this->assertSame(1000000, Integers::mul(1000, 1000));
    }

    /**
     * Test multiplication overflow detection.
     */
    public function testMulOverflow(): void
    {
        // Test positive overflow.
        $this->expectException(OverflowException::class);
        $this->expectExceptionMessage('Overflow in integer multiplication.');
        Integers::mul(PHP_INT_MAX, 2);
    }

    /**
     * Test multiplication negative overflow detection.
     */
    public function testMulNegativeOverflow(): void
    {
        // Test negative overflow.
        $this->expectException(OverflowException::class);
        $this->expectExceptionMessage('Overflow in integer multiplication.');
        Integers::mul(PHP_INT_MAX, -2);
    }

    /**
     * Test exponentiation of integers without overflow.
     */
    public function testPow(): void
    {
        // Test basic exponentiation.
        $this->assertSame(8, Integers::pow(2, 3));
        $this->assertSame(1, Integers::pow(5, 0));
        $this->assertSame(5, Integers::pow(5, 1));

        // Test with zero base.
        $this->assertSame(0, Integers::pow(0, 5));
        $this->assertSame(1, Integers::pow(0, 0));

        // Test with negative base.
        $this->assertSame(-8, Integers::pow(-2, 3));
        $this->assertSame(16, Integers::pow(-2, 4));
        $this->assertSame(1, Integers::pow(-5, 0));

        // Test with one.
        $this->assertSame(1, Integers::pow(1, 100));
        $this->assertSame(1, Integers::pow(-1, 0));
        $this->assertSame(-1, Integers::pow(-1, 1));

        // Test larger calculations that don't overflow.
        $this->assertSame(1024, Integers::pow(2, 10));
    }

    /**
     * Test exponentiation with negative exponent causing underflow.
     */
    public function testPowNegativeExponentUnderflow(): void
    {
        // Test that negative exponents throw UnderflowException.
        $this->expectException(UnderflowException::class);
        $this->expectExceptionMessage('Underflow in exponentiation.');
        Integers::pow(2, -1);
    }

    /**
     * Test exponentiation overflow detection.
     */
    public function testPowOverflow(): void
    {
        // Test positive overflow.
        $this->expectException(OverflowException::class);
        $this->expectExceptionMessage('Overflow in exponentiation.');
        Integers::pow(PHP_INT_MAX, 2);
    }

    /**
     * Test exponentiation with large exponent causing overflow.
     */
    public function testPowLargeExponentOverflow(): void
    {
        // Test overflow with large exponent.
        $this->expectException(OverflowException::class);
        $this->expectExceptionMessage('Overflow in exponentiation.');
        Integers::pow(10, 100);
    }

    /**
     * Test GCD calculation with two integers.
     */
    public function testGcdTwoIntegers(): void
    {
        // Test basic GCD.
        $this->assertSame(6, Integers::gcd(12, 18));
        $this->assertSame(1, Integers::gcd(17, 19));
        $this->assertSame(5, Integers::gcd(5, 10));

        // Test with same numbers.
        $this->assertSame(7, Integers::gcd(7, 7));

        // Test with one being zero.
        $this->assertSame(5, Integers::gcd(5, 0));
        $this->assertSame(5, Integers::gcd(0, 5));

        // Test with both being zero.
        $this->assertSame(0, Integers::gcd(0, 0));

        // Test with negative numbers (GCD uses absolute values).
        $this->assertSame(6, Integers::gcd(-12, 18));
        $this->assertSame(6, Integers::gcd(12, -18));
        $this->assertSame(6, Integers::gcd(-12, -18));

        // Test with one being one.
        $this->assertSame(1, Integers::gcd(1, 100));
    }

    /**
     * Test GCD calculation with multiple integers.
     */
    public function testGcdMultipleIntegers(): void
    {
        // Test with three integers.
        $this->assertSame(6, Integers::gcd(12, 18, 24));
        $this->assertSame(1, Integers::gcd(10, 15, 22));

        // Test with four integers.
        $this->assertSame(4, Integers::gcd(8, 12, 16, 20));

        // Test with five integers.
        $this->assertSame(5, Integers::gcd(10, 15, 20, 25, 30));

        // Test with mixed positive and negative.
        $this->assertSame(3, Integers::gcd(-9, 12, -15));
    }

    /**
     * Test GCD calculation with single integer.
     */
    public function testGcdSingleInteger(): void
    {
        // Test with single positive integer.
        $this->assertSame(42, Integers::gcd(42));

        // Test with single negative integer.
        $this->assertSame(42, Integers::gcd(-42));

        // Test with zero.
        $this->assertSame(0, Integers::gcd(0));
    }

    /**
     * Test GCD with no arguments throws error.
     */
    public function testGcdNoArguments(): void
    {
        // Test that calling GCD with no arguments throws ArgumentCountError.
        $this->expectException(ArgumentCountError::class);
        $this->expectExceptionMessage('At least one integer is required.');
        Integers::gcd();
    }

    /**
     * Test GCD with large numbers.
     */
    public function testGcdLargeNumbers(): void
    {
        // Test GCD with large coprime numbers.
        $this->assertSame(1, Integers::gcd(1000000007, 1000000009));

        // Test GCD with large numbers having common factors.
        $this->assertSame(3000, Integers::gcd(123000, 456000));
    }

    /**
     * Test GCD with PHP_INT_MIN as first argument throws RangeException.
     */
    public function testGcdWithPhpIntMinFirstArgThrows(): void
    {
        $this->expectException(RangeException::class);
        $this->expectExceptionMessage('Arguments must be greater than PHP_INT_MIN');
        Integers::gcd(PHP_INT_MIN, 5);
    }

    /**
     * Test GCD with PHP_INT_MIN as second argument throws RangeException.
     */
    public function testGcdWithPhpIntMinSecondArgThrows(): void
    {
        $this->expectException(RangeException::class);
        $this->expectExceptionMessage('Arguments must be greater than PHP_INT_MIN');
        Integers::gcd(5, PHP_INT_MIN);
    }

    /**
     * Test GCD with PHP_INT_MIN as only argument throws RangeException.
     */
    public function testGcdWithPhpIntMinSingleArgThrows(): void
    {
        $this->expectException(RangeException::class);
        $this->expectExceptionMessage('Arguments must be greater than PHP_INT_MIN');
        Integers::gcd(PHP_INT_MIN);
    }

    /**
     * Test toSubscript with positive integer.
     */
    public function testToSubscriptPositive(): void
    {
        $this->assertSame('₁₂₃', Integers::toSubscript(123));
        $this->assertSame('₀', Integers::toSubscript(0));
        $this->assertSame('₉₈₇₆₅₄₃₂₁₀', Integers::toSubscript(9876543210));
    }

    /**
     * Test toSubscript with negative integer.
     */
    public function testToSubscriptNegative(): void
    {
        $this->assertSame('₋₁₂₃', Integers::toSubscript(-123));
        $this->assertSame('₋₁', Integers::toSubscript(-1));
    }

    /**
     * Test toSuperscript with positive integer.
     */
    public function testToSuperscriptPositive(): void
    {
        $this->assertSame('¹²³', Integers::toSuperscript(123));
        $this->assertSame('⁰', Integers::toSuperscript(0));
        $this->assertSame('⁹⁸⁷⁶⁵⁴³²¹⁰', Integers::toSuperscript(9876543210));
    }

    /**
     * Test toSuperscript with negative integer.
     */
    public function testToSuperscriptNegative(): void
    {
        $this->assertSame('⁻¹²³', Integers::toSuperscript(-123));
        $this->assertSame('⁻¹', Integers::toSuperscript(-1));
    }
}
