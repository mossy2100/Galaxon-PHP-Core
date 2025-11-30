# Numbers

General number-related utility methods for working with signs and magnitudes.

## Background

This class provides utilities for working with the signs of numbers (both integers and floats), including support for IEEE-754 signed zeros (-0.0 vs +0.0). These methods are useful for mathematical operations, comparisons, and algorithms that need precise control over signs.

## Methods

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
- `ValueError` - If NaN is passed as either parameter (NaN doesn't have a defined sign)

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

**Note:** Similar to C's `copysign()` function, but with explicit NaN rejection for clarity.

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
Numbers::equal(NAN, NAN);    // false (NaN is never equal to itself)
Numbers::equal(0.0, -0.0);   // true
```

**Behavior:**
- Uses strict equality (`===`) for comparison
- Handles mixed int/float types correctly
- NaN is never equal to anything, including itself
- Positive and negative zero (0.0 and -0.0) are considered equal

**Use Cases:**
- Exact integer comparisons
- Cases where strict float equality is required
- Checking for specific values like zero or infinity

**Note:** For float comparisons where precision issues may occur, use `approxEqual()` instead.

### approxEqual()

```php
public static function approxEqual(
    int|float $a,
    int|float $b,
    float $epsilon = 1e-10,
    bool $relative = true
): bool
```

Check if two numbers (integers or floats) are approximately equal. For integer comparisons, this performs exact equality checking. For float comparisons, this uses approximate equality with configurable tolerance and comparison mode.

**Parameters:**
- `$a` (int|float) - The first number
- `$b` (int|float) - The second number
- `$epsilon` (float) - The tolerance for comparison (default: `1e-10`)
- `$relative` (bool) - If `true` (default), use relative comparison; if `false`, use absolute comparison

**Returns:**
- `bool` - Returns `true` if the numbers are approximately equal, `false` otherwise

**Throws:**
- `ValueError` - If epsilon is negative

**Examples:**

Integer comparisons (always exact):
```php
Numbers::approxEqual(5, 5);     // true
Numbers::approxEqual(5, 6);     // false
Numbers::approxEqual(0, 0);     // true
```

Float comparisons with default relative mode:
```php
// Handles precision issues
Numbers::approxEqual(0.1 + 0.2, 0.3);  // true

// Scales with magnitude
Numbers::approxEqual(1000000.0, 1000000.1, 1e-6);  // true
Numbers::approxEqual(1.0, 1.0000001, 1e-6);  // true
```

Float comparisons with absolute mode:
```php
Numbers::approxEqual(1.0, 1.0 + 1e-11, 1e-10, false);  // true
Numbers::approxEqual(1.0, 1.0 + 1e-9, 1e-10, false);   // false
```

Mixed type comparisons:
```php
// Integer with float (converts to float comparison)
Numbers::approxEqual(5, 5.0);       // true
Numbers::approxEqual(5, 5.000001);  // true (within tolerance)
Numbers::approxEqual(1, 2.0);       // false
```

Custom epsilon:
```php
// Looser tolerance
Numbers::approxEqual(1.0, 1.1, 0.2);  // true
Numbers::approxEqual(1.0, 1.3, 0.2);  // false

// Tighter tolerance
Numbers::approxEqual(1.0, 1.0 + 1e-15, 1e-14);  // true
```

**Behavior:**
- **Both integers**: Uses exact equality (`===`)
- **At least one float**: Delegates to `Floats::approxEqual()`
- **Relative mode** (default): Epsilon scales with magnitude
- **Absolute mode**: Fixed epsilon threshold
- **Symmetric**: `approxEqual($a, $b)` equals `approxEqual($b, $a)`

**Choosing Comparison Mode:**
- **Relative** (default): Best for comparing values of varying magnitudes
- **Absolute**: Best for values near zero or when tolerance should be fixed

**Choosing Epsilon:**
- `1e-10` (default): Good for most general-purpose comparisons
- `1e-6` to `1e-9`: Suitable for results of moderate computation
- `1e-14` to `1e-15`: Near machine precision for doubles
- Domain-specific: Use tolerances appropriate to your application

**Use Cases:**
- Comparing calculation results that may include both ints and floats
- Unit testing numerical code
- Checking convergence in iterative algorithms
- Any scenario where approximate equality is needed

**See Also:**
- `equal()` - Exact equality comparison
- `Floats::approxEqual()` - Float-specific approximate comparison
- `Floats::approxEqualAbsolute()` - Explicit absolute comparison
- `Floats::approxEqualRelative()` - Explicit relative comparison
