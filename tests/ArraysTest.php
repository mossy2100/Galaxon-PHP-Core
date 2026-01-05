<?php

declare(strict_types=1);

namespace Galaxon\Core\Tests;

use Galaxon\Core\Arrays;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * Test class for Arrays utility class.
 */
#[CoversClass(Arrays::class)]
final class ArraysTest extends TestCase
{
    /**
     * Test that simple arrays without recursion return false.
     */
    public function testContainsRecursionSimpleArray(): void
    {
        // Test empty array.
        $this->assertFalse(Arrays::containsRecursion([]));

        // Test simple flat array.
        $this->assertFalse(Arrays::containsRecursion([1, 2, 3]));

        // Test associative array.
        $this->assertFalse(Arrays::containsRecursion([
            'name' => 'John',
            'age'  => 30,
        ]));

        // Test array with mixed types.
        $this->assertFalse(Arrays::containsRecursion([1, 'hello', true, null, 3.14]));
    }

    /**
     * Test that nested arrays without recursion return false.
     */
    public function testContainsRecursionNestedArray(): void
    {
        // Test nested array without recursion.
        $this->assertFalse(Arrays::containsRecursion([
            [1, 2],
            [3, 4],
        ]));

        // Test deeply nested array without recursion.
        $this->assertFalse(Arrays::containsRecursion([
            'level1' => [
                'level2' => [
                    'level3' => [
                        'value' => 42,
                    ],
                ],
            ],

        ]));

        // Test array containing objects without recursion.
        $obj = new stdClass();
        $obj->name = 'test';
        $this->assertFalse(Arrays::containsRecursion([
            'object' => $obj,
        ]));
    }

    /**
     * Test that arrays with direct self-reference return true.
     */
    public function testContainsRecursionDirectReference(): void
    {
        // Create array with direct self-reference.
        $arr = [
            'foo' => 'bar',
        ];
        $arr['self'] = &$arr;

        // Test that recursion is detected.
        $this->assertTrue(Arrays::containsRecursion($arr));
    }

    /**
     * Test that arrays with indirect recursion return true.
     */
    public function testContainsRecursionIndirectReference(): void
    {
        // Create array with indirect recursion.
        $arr1 = [
            'name' => 'array1',
        ];
        $arr2 = [
            'name' => 'array2',
        ];
        $arr1['child'] = &$arr2;
        $arr2['parent'] = &$arr1;

        // Test that recursion is detected in first array.
        $this->assertTrue(Arrays::containsRecursion($arr1));

        // Test that recursion is detected in second array.
        $this->assertTrue(Arrays::containsRecursion($arr2));
    }

    /**
     * Test that arrays with nested recursion return true.
     */
    public function testContainsRecursionNestedReference(): void
    {
        // Create array with recursion at a nested level.
        $arr = [
            'level1' => [
                'level2' => [
                    'level3' => [],
                ],
            ],

        ];
        $arr['level1']['level2']['level3']['back'] = &$arr;

        // Test that nested recursion is detected.
        $this->assertTrue(Arrays::containsRecursion($arr));
    }

    /**
     * Test that arrays with self-reference at different positions return true.
     */
    public function testContainsRecursionMultipleReferences(): void
    {
        // Create array with multiple references to itself.
        $arr = [
            'a' => 1,
            'b' => 2,
        ];
        $arr['ref1'] = &$arr;
        $arr['ref2'] = &$arr;

        // Test that recursion is detected even with multiple references.
        $this->assertTrue(Arrays::containsRecursion($arr));
    }

    /**
     * Test that arrays containing references to sub-arrays don't cause false positives.
     */
    public function testContainsRecursionSubArrayReference(): void
    {
        // Create array with reference to a sub-array (not recursion).
        $subArray = [
            'x' => 1,
            'y' => 2,
        ];
        $arr = [
            'original'  => $subArray,
            'reference' => &$subArray,

        ];

        // Test that this is not detected as recursion (it's just a reference to a sub-array).
        $result = Arrays::containsRecursion($arr);

        $this->assertFalse($result);
    }

    /**
     * Test with array containing various data types and no recursion.
     */
    public function testContainsRecursionComplexNonRecursive(): void
    {
        // Create complex array without recursion.
        $arr = [
            'null'   => null,
            'bool'   => true,
            'int'    => 42,
            'float'  => 3.14,
            'string' => 'hello',
            'array'  => [1, 2, 3],
            'nested' => [
                'deep' => [
                    'value' => 'test',
                ],

            ],
            'object' => new stdClass(),

        ];

        // Test that no recursion is detected.
        $this->assertFalse(Arrays::containsRecursion($arr));
    }

    /**
     * Test quoteValues with single quotes (default).
     */
    public function testQuoteValuesWithSingleQuotes(): void
    {
        $input = ['foo', 'bar', 'baz'];
        $expected = ["'foo'", "'bar'", "'baz'"];
        $result = Arrays::quoteValues($input);

        $this->assertEquals($expected, $result);
    }

    /**
     * Test quoteValues with double quotes.
     */
    public function testQuoteValuesWithDoubleQuotes(): void
    {
        $input = ['foo', 'bar', 'baz'];
        $expected = ['"foo"', '"bar"', '"baz"'];
        $result = Arrays::quoteValues($input, true);

        $this->assertEquals($expected, $result);
    }

    /**
     * Test quoteValues with empty array.
     */
    public function testQuoteValuesWithEmptyArray(): void
    {
        $result = Arrays::quoteValues([]);

        $this->assertEquals([], $result);
    }

    /**
     * Test quoteValues with single element.
     */
    public function testQuoteValuesWithSingleElement(): void
    {
        $input = ['hello'];
        $expected = ["'hello'"];
        $result = Arrays::quoteValues($input);

        $this->assertEquals($expected, $result);
    }

    /**
     * Test quoteValues preserves array keys.
     */
    public function testQuoteValuesPreservesKeys(): void
    {
        $input = [
            'first'  => 'apple',
            'second' => 'banana',
            'third'  => 'cherry',
        ];
        $expected = [
            'first'  => "'apple'",
            'second' => "'banana'",
            'third'  => "'cherry'",
        ];
        $result = Arrays::quoteValues($input);

        $this->assertEquals($expected, $result);
    }

    /**
     * Test quoteValues with values containing quotes.
     */
    public function testQuoteValuesWithQuotesInValues(): void
    {
        // Single quotes in values with single quote wrapping.
        $input = ["it's", "can't", "won't"];
        $expected = ["'it's'", "'can't'", "'won't'"];
        $result = Arrays::quoteValues($input);

        $this->assertEquals($expected, $result);
    }

    /**
     * Test quoteValues with values containing double quotes.
     */
    public function testQuoteValuesWithDoubleQuotesInValues(): void
    {
        // Double quotes in values with double quote wrapping.
        $input = ['say "hello"', 'the "word"'];
        $expected = ['"say "hello""', '"the "word""'];
        $result = Arrays::quoteValues($input, true);

        $this->assertEquals($expected, $result);
    }

    /**
     * Test quoteValues with empty strings.
     */
    public function testQuoteValuesWithEmptyStrings(): void
    {
        $input = ['', 'foo', '', 'bar'];
        $expected = ["''", "'foo'", "''", "'bar'"];
        $result = Arrays::quoteValues($input);

        $this->assertEquals($expected, $result);
    }

    /**
     * Test quoteValues with whitespace strings.
     */
    public function testQuoteValuesWithWhitespace(): void
    {
        $input = [' ', '  spaces  ', "\t", "\n"];
        $expected = ["' '", "'  spaces  '", "'\t'", "'\n'"];
        $result = Arrays::quoteValues($input);

        $this->assertEquals($expected, $result);
    }

    /**
     * Test quoteValues with special characters.
     */
    public function testQuoteValuesWithSpecialCharacters(): void
    {
        $input = ["hello\nworld", "tab\there", 'back\\slash'];
        $expected = ["'hello\nworld'", "'tab\there'", "'back\\slash'"];
        $result = Arrays::quoteValues($input);

        $this->assertEquals($expected, $result);
    }

    /**
     * Test quoteValues with numeric strings.
     */
    public function testQuoteValuesWithNumericStrings(): void
    {
        $input = ['123', '45.67', '0', '-999'];
        $expected = ["'123'", "'45.67'", "'0'", "'-999'"];
        $result = Arrays::quoteValues($input);

        $this->assertEquals($expected, $result);
    }

    /**
     * Test quoteValues throws InvalidArgumentException for non-string values (integers).
     */
    public function testQuoteValuesThrowsExceptionForIntegers(): void
    {
        $input = ['foo', 123, 'bar'];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The array values must be strings.');
        // @phpstan-ignore argument.type
        Arrays::quoteValues($input);
    }

    /**
     * Test quoteValues throws InvalidArgumentException for non-string values (floats).
     */
    public function testQuoteValuesThrowsExceptionForFloats(): void
    {
        $input = ['foo', 3.14, 'bar'];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The array values must be strings.');
        // @phpstan-ignore argument.type
        Arrays::quoteValues($input);
    }

    /**
     * Test quoteValues throws InvalidArgumentException for non-string values (booleans).
     */
    public function testQuoteValuesThrowsExceptionForBooleans(): void
    {
        $input = ['foo', true, 'bar'];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The array values must be strings.');
        // @phpstan-ignore argument.type
        Arrays::quoteValues($input);
    }

    /**
     * Test quoteValues throws InvalidArgumentException for non-string values (null).
     */
    public function testQuoteValuesThrowsExceptionForNull(): void
    {
        $input = ['foo', null, 'bar'];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The array values must be strings.');
        // @phpstan-ignore argument.type
        Arrays::quoteValues($input);
    }

    /**
     * Test quoteValues throws InvalidArgumentException for non-string values (arrays).
     */
    public function testQuoteValuesThrowsExceptionForArrays(): void
    {
        $input = [
            'foo',
            ['nested'],
            'bar',
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The array values must be strings.');
        // @phpstan-ignore argument.type
        Arrays::quoteValues($input);
    }

    /**
     * Test quoteValues throws InvalidArgumentException for non-string values (objects).
     */
    public function testQuoteValuesThrowsExceptionForObjects(): void
    {
        $input = ['foo', new stdClass(), 'bar'];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The array values must be strings.');
        // @phpstan-ignore argument.type
        Arrays::quoteValues($input);
    }

    /**
     * Test quoteValues with unicode strings.
     */
    public function testQuoteValuesWithUnicode(): void
    {
        $input = ['hello', '世界', 'emoji 😀', 'Ñoño'];
        $expected = ["'hello'", "'世界'", "'emoji 😀'", "'Ñoño'"];
        $result = Arrays::quoteValues($input);

        $this->assertEquals($expected, $result);
    }

    /**
     * Test quoteValues does not modify original array.
     */
    public function testQuoteValuesDoesNotModifyOriginal(): void
    {
        $input = ['foo', 'bar', 'baz'];
        $original = $input;
        Arrays::quoteValues($input);

        $this->assertEquals($original, $input);
    }
}
