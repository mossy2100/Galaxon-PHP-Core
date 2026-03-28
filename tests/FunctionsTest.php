<?php

declare(strict_types=1);

namespace Galaxon\Core\Tests;

use PHPUnit\Framework\TestCase;
use stdClass;

use function Galaxon\Core\is_number;
use function Galaxon\Core\println;

/**
 * Tests for the convenience functions in functions.php.
 */
final class FunctionsTest extends TestCase
{
    // region println() tests

    /**
     * Test println outputs a string with a newline.
     */
    public function testPrintlnWithString(): void
    {
        $this->expectOutputString('Hello' . PHP_EOL);
        println('Hello');
    }

    /**
     * Test println outputs an integer with a newline.
     */
    public function testPrintlnWithInt(): void
    {
        $this->expectOutputString('42' . PHP_EOL);
        println(42);
    }

    /**
     * Test println outputs a float with a newline.
     */
    public function testPrintlnWithFloat(): void
    {
        $this->expectOutputString('3.14' . PHP_EOL);
        println(3.14);
    }

    /**
     * Test println with no argument outputs just a newline.
     */
    public function testPrintlnWithNoArgument(): void
    {
        $this->expectOutputString(PHP_EOL);
        println();
    }

    /**
     * Test println with an empty string outputs just a newline.
     */
    public function testPrintlnWithEmptyString(): void
    {
        $this->expectOutputString(PHP_EOL);
        println('');
    }

    /**
     * Test println with a boolean true.
     */
    public function testPrintlnWithTrue(): void
    {
        $this->expectOutputString('true' . PHP_EOL);
        println(true);
    }

    /**
     * Test println with a boolean false.
     */
    public function testPrintlnWithFalse(): void
    {
        $this->expectOutputString('false' . PHP_EOL);
        println(false);
    }

    /**
     * Test println with null.
     */
    public function testPrintlnWithNull(): void
    {
        $this->expectOutputString('null' . PHP_EOL);
        println(null);
    }

    /**
     * Test println with an object that has a __toString() method.
     */
    public function testPrintlnWithStringableObject(): void
    {
        $obj = new class {
            public function __toString(): string
            {
                return 'stringable object';
            }
        };

        $this->expectOutputString('stringable object' . PHP_EOL);
        println($obj);
    }

    // endregion

    // region is_number() tests

    /**
     * Test is_number returns true for integers.
     */
    public function testIsNumberWithIntegers(): void
    {
        $this->assertTrue(is_number(0));
        $this->assertTrue(is_number(42));
        $this->assertTrue(is_number(-99));
        $this->assertTrue(is_number(PHP_INT_MAX));
        $this->assertTrue(is_number(PHP_INT_MIN));
    }

    /**
     * Test is_number returns true for floats.
     */
    public function testIsNumberWithFloats(): void
    {
        $this->assertTrue(is_number(0.0));
        $this->assertTrue(is_number(3.14));
        $this->assertTrue(is_number(-2.5));
        $this->assertTrue(is_number(1e10));
        $this->assertTrue(is_number(PHP_FLOAT_MAX));
        $this->assertTrue(is_number(PHP_FLOAT_MIN));
        $this->assertTrue(is_number(PHP_FLOAT_EPSILON));
    }

    /**
     * Test is_number returns true for special float values.
     */
    public function testIsNumberWithSpecialFloats(): void
    {
        $this->assertTrue(is_number(INF));
        $this->assertTrue(is_number(-INF));
        $this->assertTrue(is_number(NAN));
        $this->assertTrue(is_number(-0.0));
    }

    /**
     * Test is_number returns false for numeric strings.
     */
    public function testIsNumberWithNumericStrings(): void
    {
        $this->assertFalse(is_number('42'));
        $this->assertFalse(is_number('3.14'));
        $this->assertFalse(is_number('-99'));
        $this->assertFalse(is_number('1e10'));
        $this->assertFalse(is_number('0x1A'));
    }

    /**
     * Test is_number returns false for non-numeric types.
     */
    public function testIsNumberWithNonNumericTypes(): void
    {
        $this->assertFalse(is_number('hello'));
        $this->assertFalse(is_number(''));
        $this->assertFalse(is_number(true));
        $this->assertFalse(is_number(false));
        $this->assertFalse(is_number(null));
        $this->assertFalse(is_number([]));
        $this->assertFalse(is_number([1, 2]));
        $this->assertFalse(is_number(new stdClass()));
    }

    // endregion
}
