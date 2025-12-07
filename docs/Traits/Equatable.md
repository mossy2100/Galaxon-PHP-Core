# Equatable

Trait providing equality comparison functionality for objects.

## Overview

The `Equatable` trait provides a foundation for objects that support equality comparison. It defines an abstract `equal()` method that must be implemented by classes using the trait.

This trait is designed to be composed with other traits in a hierarchy. It's separate from the `Comparable` trait to follow the Interface Segregation Principle - some types can check equality but don't have a natural ordering (e.g., Complex numbers can be equal but don't have a meaningful less-than/greater-than relationship).

## Method

### equal()

```php
public function equal(mixed $other): bool
```

Compare this object with another value and determine if they are equal.

**Parameters:**
- `$other` (mixed) - The value to compare with (can be any type)

**Returns:**
- `bool` - `true` if the values are equal, `false` otherwise

**Implementation Guidelines:**
- Should return `false` for incompatible types (not throw exceptions)
- Should handle type checking gracefully
- May use epsilon-based comparison for floating-point types
- Should be consistent with the object's equality semantics

## Examples

### Using Equatable for Value Objects

```php
use Galaxon\Core\Traits\Equatable;

class Point
{
    use Equatable;

    public function __construct(
        private float $x,
        private float $y
    ) {}

    public function equal(mixed $other): bool
    {
        if (!$other instanceof self) {
            return false;
        }

        return $this->x === $other->x
            && $this->y === $other->y;
    }
}

$p1 = new Point(3.0, 4.0);
$p2 = new Point(3.0, 4.0);
$p3 = new Point(5.0, 6.0);

var_dump($p1->equal($p2)); // true
var_dump($p1->equal($p3)); // false
var_dump($p1->equal("string")); // false (gracefully handles wrong type)
```

### Epsilon-Based Equality for Floating-Point Types

```php
use Galaxon\Core\Traits\Equatable;

class Temperature
{
    use Equatable;

    private const EPSILON = 0.01; // 0.01° tolerance

    public function __construct(
        private float $celsius
    ) {}

    public function equal(mixed $other): bool
    {
        if (!$other instanceof self) {
            return false;
        }

        return abs($this->celsius - $other->celsius) <= self::EPSILON;
    }
}

$t1 = new Temperature(20.00);
$t2 = new Temperature(20.005); // Within tolerance
$t3 = new Temperature(20.02);  // Outside tolerance

var_dump($t1->equal($t2)); // true (within epsilon)
var_dump($t1->equal($t3)); // false (outside epsilon)
```

### Complex Equality with Custom Logic

```php
use Galaxon\Core\Traits\Equatable;

class Money
{
    use Equatable;

    public function __construct(
        private float $amount,
        private string $currency
    ) {}

    public function equal(mixed $other): bool
    {
        if (!$other instanceof self) {
            return false;
        }

        // Must have same currency AND same amount
        return $this->currency === $other->currency
            && abs($this->amount - $other->amount) < 0.01;
    }
}

$usd1 = new Money(100.00, 'USD');
$usd2 = new Money(100.00, 'USD');
$eur = new Money(100.00, 'EUR');

var_dump($usd1->equal($usd2)); // true (same currency and amount)
var_dump($usd1->equal($eur));  // false (different currency)
```

## Relationship with Other Traits

Equatable is the base trait in the comparison hierarchy. Other traits extend it:
- **Comparable** adds ordering operations
- **ApproxEquatable** adds approximate equality
- **ApproxComparable** combines both

See [Traits.md](Traits.md) for complete hierarchy and usage guide.

## Classes Using Equatable

- `Galaxon\Math\Complex` - Uses `ApproxEquatable` (equality with approximate comparison, no ordering)
- `Galaxon\Math\Rational` - Uses `ApproxComparable` (full ordering with exact and approximate comparison)
- `Galaxon\Units\Measurement` - Uses `Comparable` (full ordering with exact comparison)
- `Galaxon\Collections\Collection` and derived types - Use `Equatable` directly (structural equality, no ordering)
- `Galaxon\Color\Color` - Uses `Equatable` directly (exact comparison, no ordering)

## Best Practices

1. **Type Safety**: Always check the type of `$other` before comparing
2. **No Exceptions**: `equal()` should never throw exceptions - return `false` for incompatible types
3. **Reflexive**: `x.equal(x)` should always be `true`
4. **Symmetric**: If `x.equal(y)` is `true`, then `y.equal(x)` must also be `true`
5. **Transitive**: If `x.equal(y)` and `y.equal(z)` are both `true`, then `x.equal(z)` must be `true`
6. **Consistent**: Multiple calls to `equal()` with the same arguments should return the same result
7. **Null/Type Handling**: `x.equal($other)` where `$other` is a different type should return `false`
8. **Float Comparison**: For types containing floats, consider epsilon-based comparison to handle precision issues
