# Numbers

General number-related utility methods for working with signs and magnitudes.

## Background

This class provides utilities for working with the signs of numbers (both integers and floats), including support for IEEE-754 signed zeros (-0.0 vs +0.0). These methods are useful for mathematical operations, comparisons, and algorithms that need precise control over signs.

## Methods

### isNumber()

```php
public static function isNumber(mixed $value): bool
```

Check if a value is a number (int or float). This differs from PHP's `is_numeric()` function, which also returns `true` for numeric strings.

**Parameters:**
- `$value` (mixed) - The value to check

**Returns:**
- `bool` - Returns `true` if the value is an int or float, `false` otherwise

**Examples:**

```php
Numbers::isNumber(42);         // true
Numbers::isNumber(3.14);       // true
Numbers::isNumber(INF);        // true
Numbers::isNumber(NAN);        // true
Numbers::isNumber("42");       // false (numeric string)
Numbers::isNumber("3.14");     // false (numeric string)
Numbers::isNumber(true);       // false
Numbers::isNumber(null);       // false
```

**Use Case:** When you need strict type checking that distinguishes actual numbers from numeric strings.

### equal()

```php
public static function equal(int|float $a, int|float $b): bool
```

Check if two numbers (integers or floats) are exactly equal. For float comparisons, this uses strict equality (`===`). For approximate float comparison, use `approxEqual()` instead.

**Parameters:**
- `$a` (int|float) - The first number
- `$b` (int|float) - The second number

**Returns:**
- `bool` - Returns `true` if the numbers are exactly equal, `false` otherwise

**Examples:**

Integer comparisons:
```php
Numbers::equal(5, 5);    // true
Numbers::equal(5, -5);   // false
Numbers::equal(0, 0);    // true
```

Float comparisons (exact):
```php
Numbers::equal(1.0, 1.0);  // true
Numbers::equal(1.0, 2.0);  // false

// Precision issues with floats
Numbers::equal(0.1 + 0.2, 0.3);  // false (!)
```

Mixed type comparisons:
```php
Numbers::equal(5, 5.0);    // true
Numbers::equal(5, 5.1);    // false
```

Special float values:
```php
Numbers::equal(INF, INF);    // true
Numbers::equal(INF, -INF);   // false
Numbers::equal(NAN, NAN);    // false (NAN is never equal to itself)
Numbers::equal(0.0, -0.0);   // true
```

**Behavior:**
- Uses strict equality (`===`) for comparison
- Handles mixed int/float types correctly
- NAN is never equal to anything, including itself
- Positive and negative zero (0.0 and -0.0) are considered equal

**Use Cases:**
- Exact integer comparisons
- Cases where strict float equality is required
- Checking for specific values like zero or infinity

**Note:** For float comparisons where precision issues may occur, use `approxEqual()` instead.

### sign()

```php
public static function sign(int|float $value, bool $zeroForZero = true): int
```

Get the sign of a number. This method supports two modes of operation depending on how you want zero values to be handled.

**Parameters:**
- `$value` (int|float) - The number to check
- `$zeroForZero` (bool) - If `true` (default), return 0 for zero; if `false`, return the sign of zero (-1 for -0.0, 1 otherwise)

**Returns:**
- `int` - Returns 1 for positive, -1 for negative, or 0 for zero (if `$zeroForZero` is `true`)

**Examples:**

Default behavior (return 0 for zero):
```php
Numbers::sign(42);      // 1
Numbers::sign(-42);     // -1
Numbers::sign(0);       // 0
Numbers::sign(0.0);     // 0
Numbers::sign(-0.0);    // 0
Numbers::sign(INF);     // 1
Numbers::sign(-INF);    // -1
```

With `$zeroForZero = false` (distinguish between -0.0 and +0.0):
```php
Numbers::sign(42, false);      // 1
Numbers::sign(-42, false);     // -1
Numbers::sign(0, false);       // 1 (integer 0 is considered positive)
Numbers::sign(0.0, false);     // 1
Numbers::sign(-0.0, false);    // -1
```

**Use Cases:**
- Mathematical algorithms requiring signum function
- Comparisons where sign matters
- Working with IEEE-754 operations that distinguish -0.0 from +0.0

### copySign()

```php
public static function copySign(int|float $num, int|float $signSource): int|float
```

Copy the sign of one number to another. Returns a value with the magnitude of the first parameter and the sign of the second parameter.

**Parameters:**
- `$num` (int|float) - The number whose magnitude to use
- `$signSource` (int|float) - The number whose sign to copy

**Returns:**
- `int|float` - The magnitude of `$num` with the sign of `$signSource`

**Throws:**
- `ValueError` - If NAN is passed as either parameter (NAN doesn't have a defined sign)

**Examples:**

Basic usage:
```php
Numbers::copySign(5, 10);      // 5 (positive magnitude, positive sign)
Numbers::copySign(5, -10);     // -5 (positive magnitude, negative sign)
Numbers::copySign(-5, 10);     // 5 (negative magnitude, positive sign)
Numbers::copySign(-5, -10);    // -5 (negative magnitude, negative sign)
```

With zero:
```php
Numbers::copySign(5, 0.0);     // 5 (sign of +0.0 is positive)
Numbers::copySign(5, -0.0);    // -5 (sign of -0.0 is negative)
Numbers::copySign(0.0, -10);   // -0.0 (zero with negative sign)
```

With infinity:
```php
Numbers::copySign(5, INF);     // 5
Numbers::copySign(5, -INF);    // -5
Numbers::copySign(INF, -10);   // -INF
```

Error cases:
```php
Numbers::copySign(NAN, 5);     // throws ValueError
Numbers::copySign(5, NAN);     // throws ValueError
```

**Use Cases:**
- Implementing mathematical functions that need to preserve sign relationships
- Working with algorithms that require specific sign control (e.g., coordinate transformations)
- Ensuring consistent sign handling across calculations

**Note:** Similar to C's `copysign()` function, but with explicit NAN rejection for clarity.
