# ApproxComparable

Trait providing complete comparison operations with both exact and approximate equality for objects with natural ordering and floating-point precision concerns.

## Overview

The `ApproxComparable` trait combines `Comparable` and `ApproxEquatable` to provide a complete set of comparison operations including approximate equality. This is ideal for types with natural ordering that contain floating-point values (e.g., Rational numbers).

The trait provides:
- `equal()` - Exact equality (from Equatable via Comparable)
- `approxEqual()` - Approximate equality with configurable tolerances (from ApproxEquatable)
- `compare()` - Exact ordering comparison (from Comparable)
- `approxCompare()` - Approximate ordering comparison with tolerance
- `lessThan()`, `greaterThan()`, etc. - Ordering methods (from Comparable)

## Methods

#### approxCompare()

```php
public function approxCompare(
    mixed $other,
    float $relTol = Floats::DEFAULT_RELATIVE_TOLERANCE,
    float $absTol = PHP_FLOAT_EPSILON
): int
```

Compare with approximate equality awareness. Returns 0 if values are approximately equal within tolerances, otherwise performs exact comparison.

**Parameters:**
- `$other` (mixed) - The value to compare with
- `$relTol` (float) - Relative tolerance (default: 1e-9)
- `$absTol` (float) - Absolute tolerance (default: PHP_FLOAT_EPSILON ≈ 2.22e-16)

**Returns:**
- `int` - Exactly `-1`, `0`, or `1`

**Behavior:**
- If `approxEqual()` returns `true`, returns `0`
- Otherwise, returns result of exact `compare()`

**Use Cases:**
- Sorting with approximate equality "buckets"
- Implementing approximate ordering algorithms
- Range queries with tolerance

## Examples

### Using ApproxComparable for Rational Numbers

```php
use Galaxon\Core\Floats;
use Galaxon\Core\Numbers;
use Galaxon\Core\Traits\ApproxComparable;

class Rational
{
    use ApproxComparable;

    public function __construct(
        private int $numerator,
        private int $denominator
    ) {}

    public function toFloat(): float
    {
        return $this->numerator / $this->denominator;
    }

    public function compare(mixed $other): int
    {
        if (!$other instanceof self) {
            throw new TypeError('Can only compare with another Rational');
        }

        // Use cross-multiplication for exact comparison
        $left = $this->numerator * $other->denominator;
        $right = $other->numerator * $this->denominator;

        return Numbers::sign($left <=> $right);
    }

    public function approxEqual(
        mixed $other,
        float $relTol = Floats::DEFAULT_RELATIVE_TOLERANCE,
        float $absTol = PHP_FLOAT_EPSILON
    ): bool {
        if (!$other instanceof self) {
            return false;
        }

        // Convert to floats and use tolerance-based comparison
        return Floats::approxEqual(
            $this->toFloat(),
            $other->toFloat(),
            $relTol,
            $absTol
        );
    }
}

$r1 = new Rational(1, 3);
$r2 = new Rational(333333, 1000000);

// Exact comparison
var_dump($r1->equal($r2));                // false (not exactly equal)
var_dump($r1->compare($r2));              // 1 (1/3 > 333333/1000000)

// Approximate comparison
var_dump($r1->approxEqual($r2, 1e-5, 1e-5)); // true (close enough)
var_dump($r1->approxCompare($r2, 1e-5, 1e-5)); // 0 (approximately equal)
```

### Sorting with Approximate Equality

```php
use Galaxon\Core\Floats;
use Galaxon\Core\Traits\ApproxComparable;

class Score
{
    use ApproxComparable;

    private const TOLERANCE = 0.01; // 1% tolerance

    public function __construct(
        private float $value
    ) {}

    public function compare(mixed $other): int
    {
        if (!$other instanceof self) {
            throw new TypeError('Can only compare with another Score');
        }

        if ($this->value < $other->value) {
            return -1;
        }
        if ($this->value > $other->value) {
            return 1;
        }
        return 0;
    }

    public function approxEqual(
        mixed $other,
        float $relTol = self::TOLERANCE,
        float $absTol = PHP_FLOAT_EPSILON
    ): bool {
        if (!$other instanceof self) {
            return false;
        }

        return Floats::approxEqual($this->value, $other->value, $relTol, $absTol);
    }
}

$scores = [
    new Score(95.0),
    new Score(95.5),  // Within 1% of 95.0
    new Score(90.0),
    new Score(85.0),
];

// Sort with approximate comparison
usort($scores, fn($a, $b) => $a->approxCompare($b, 0.01));
// Scores within 1% are considered equal and maintain relative order
```

### Vector Comparison with Magnitude

```php
use Galaxon\Core\Floats;
use Galaxon\Core\Numbers;
use Galaxon\Core\Traits\ApproxComparable;

class Vector2D
{
    use ApproxComparable;

    public function __construct(
        private float $x,
        private float $y
    ) {}

    public function magnitude(): float
    {
        return sqrt($this->x ** 2 + $this->y ** 2);
    }

    public function compare(mixed $other): int
    {
        if (!$other instanceof self) {
            throw new TypeError('Can only compare with another Vector2D');
        }

        // Compare by magnitude
        return Numbers::sign($this->magnitude() <=> $other->magnitude());
    }

    public function approxEqual(
        mixed $other,
        float $relTol = Floats::DEFAULT_RELATIVE_TOLERANCE,
        float $absTol = PHP_FLOAT_EPSILON
    ): bool {
        if (!$other instanceof self) {
            return false;
        }

        // Both components must be within tolerance
        return Floats::approxEqual($this->x, $other->x, $relTol, $absTol)
            && Floats::approxEqual($this->y, $other->y, $relTol, $absTol);
    }
}

$v1 = new Vector2D(3.0, 4.0);  // magnitude: 5.0
$v2 = new Vector2D(3.00001, 4.00001);
$v3 = new Vector2D(0.0, 6.0);  // magnitude: 6.0

var_dump($v1->equal($v2));        // false (exact components differ)
var_dump($v1->approxEqual($v2));  // true (components within tolerance)
var_dump($v1->lessThan($v3));     // true (5.0 < 6.0 by magnitude)
var_dump($v1->approxCompare($v2)); // 0 (approximately equal)
```

## Relationship with Other Traits

ApproxComparable combines Comparable and ApproxEquatable, providing the complete comparison suite for ordered types with floating-point components.

See [Traits.md](Traits.md) for complete hierarchy and usage guide.

## Classes Using ApproxComparable

- `Galaxon\Math\Rational` - Rational numbers with exact and approximate comparison

## Best Practices

1. **Implement Both**: Provide both exact (`compare()`) and approximate (`approxEqual()`) implementations
2. **Consistent Semantics**: Ensure approximate equality aligns with your ordering semantics
3. **Don't Override approxCompare()**: Let the trait provide it based on `approxEqual()` and `compare()`
4. **Document Precision**: Clearly document when to use exact vs approximate comparison
5. **Type Safety**: Throw `TypeError` in `compare()` for incompatible types, return `false` in `approxEqual()`
6. **Use Floats Utilities**: Leverage `Floats::approxEqual()` and `Floats::compare()` for float comparisons
7. **Sensible Defaults**: Choose default tolerances appropriate for your type's typical use cases
8. **Test Thoroughly**: Test edge cases like zero, very large values, and very small values

## When to Use Each Method

### Use `equal()` when:
- You need exact equality
- Comparing integer-only types
- Working with canonical forms (e.g., reduced fractions)

### Use `approxEqual()` when:
- Comparing floating-point results
- Dealing with accumulated rounding errors
- Checking if values are "close enough" for practical purposes

### Use `compare()` when:
- Sorting with strict ordering
- Finding exact min/max
- Binary search with exact matching

### Use `approxCompare()` when:
- Sorting with tolerance "buckets"
- Finding approximate min/max
- Range queries with tolerance

## Common Patterns

### Exact Comparison in compare()

```php
public function compare(mixed $other): int
{
    if (!$other instanceof self) {
        throw new TypeError('Type mismatch');
    }

    // Use integer arithmetic for exact comparison
    $left = $this->numerator * $other->denominator;
    $right = $other->numerator * $this->denominator;

    return Numbers::sign($left <=> $right);
}
```

### Float-Based Approximate Equality

```php
public function approxEqual(
    mixed $other,
    float $relTol = Floats::DEFAULT_RELATIVE_TOLERANCE,
    float $absTol = PHP_FLOAT_EPSILON
): bool {
    if (!$other instanceof self) {
        return false;
    }

    return Floats::approxEqual(
        $this->toFloat(),
        $other->toFloat(),
        $relTol,
        $absTol
    );
}
```

### Component-Wise Approximate Equality

```php
public function approxEqual(
    mixed $other,
    float $relTol = Floats::DEFAULT_RELATIVE_TOLERANCE,
    float $absTol = PHP_FLOAT_EPSILON
): bool {
    if (!$other instanceof self) {
        return false;
    }

    // All components must be within tolerance
    return Floats::approxEqual($this->x, $other->x, $relTol, $absTol)
        && Floats::approxEqual($this->y, $other->y, $relTol, $absTol)
        && Floats::approxEqual($this->z, $other->z, $relTol, $absTol);
}
```
