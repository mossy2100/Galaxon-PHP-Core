<?php

declare(strict_types=1);

namespace Galaxon\Core\Traits;

use Galaxon\Core\Types;
use Override;
use TypeError;

/**
 * Trait providing a complete set of comparison operations based on a single compare() method.
 *
 * This trait follows the Template Method Pattern: you implement the abstract compare() method that returns exactly
 * -1, 0, or 1, and the trait provides all other comparison methods automatically: equal(), lessThan(),
 * lessThanOrEqual(), greaterThan(), and greaterThanOrEqual().
 *
 * The trait uses Equatable via composition, providing an equal() method that returns false gracefully for incompatible
 * types (rather than throwing TypeError like the ordering methods do).
 *
 * Type safety is enforced through the checkSameType() method, which uses Types::haveSameType() to verify that both
 * objects being compared have the same type (using get_debug_type()).
 *
 * Example usage:
 * <code>
 * class Score
 * {
 *     use Comparable;
 *
 *     public function __construct(private int $value) {}
 *
 *     #[Override]
 *     public function compare(mixed $other): int
 *     {
 *         $this->checkSameType($other);
 *         return Numbers::sign($this->value <=> $other->value);
 *     }
 * }
 * </code>
 *
 * @see Equatable The base equality trait this includes.
 * @see ApproxComparable For types needing approximate comparison with tolerance.
 *
 * @codeCoverageIgnore
 * @phpstan-ignore trait.unused
 */
trait Comparable
{
    use Equatable;

    /**
     * Compare this object with another and return an integer indicating the ordering relationship.
     *
     * Implementations must return exactly -1, 0, or 1 (not just negative/zero/positive):
     *   -1 if this object is less than the other object
     *    0 if this object equals the other object
     *    1 if this object is greater than the other object
     *
     * Important: Return values must be exactly -1, 0, or 1 because the convenience methods (lessThan, etc.) use
     * strict equality checks. Use Numbers::sign() to normalize spaceship operator results.
     *
     * Implementation guidelines:
     * - May throw TypeError for incompatible types (this is expected behavior)
     * - Must be consistent (same inputs always produce same result)
     * - Should be transitive (if A < B and B < C, then A < C)
     *
     * @param mixed $other The value to compare with.
     * @return int Exactly -1, 0, or 1 indicating the ordering relationship.
     * @throws TypeError If the types are incompatible for comparison.
     */
    abstract public function compare(mixed $other): int;

    /**
     * Check if this object is equal to another value.
     *
     * This method overrides the abstract equal() method from the Equatable trait. Unlike the other comparison methods
     * in this trait (lessThan, greaterThan, etc.), equal() returns false gracefully for incompatible types instead
     * of throwing TypeError.
     *
     * The method first checks type compatibility using Types::haveSameType(), and only calls compare() if the types
     * match.
     *
     * @param mixed $other The value to compare with.
     * @return bool True if the values are equal, false otherwise (including for incompatible types).
     */
    #[Override]
    public function equal(mixed $other): bool
    {
        // Check if the types are the same, and if so, compare the values.
        return Types::haveSameType($this, $other) && $this->compare($other) === 0;
    }

    /**
     * Check if this object is less than another object.
     *
     * Verifies type compatibility before delegating to compare(). Throws TypeError for incompatible types.
     *
     * @param mixed $other The value to compare with.
     * @return bool True if this object is less than the other object, false otherwise.
     * @throws TypeError If the types are not the same.
     */
    public function lessThan(mixed $other): bool
    {
        $this->checkSameType($other);
        return $this->compare($other) === -1;
    }

    /**
     * Check if this object is less than or equal to another object.
     *
     * Implemented as the negation of greaterThan() to maintain consistency. Throws TypeError for incompatible types.
     *
     * @param mixed $other The value to compare with.
     * @return bool True if this object is less than or equal to the other object, false otherwise.
     * @throws TypeError If the types are not the same.
     */
    public function lessThanOrEqual(mixed $other): bool
    {
        $this->checkSameType($other);
        return !$this->greaterThan($other);
    }

    /**
     * Check if this object is greater than another object.
     *
     * Verifies type compatibility before delegating to compare(). Throws TypeError for incompatible types.
     *
     * @param mixed $other The value to compare with.
     * @return bool True if this object is greater than the other object, false otherwise.
     * @throws TypeError If the types are not the same.
     */
    public function greaterThan(mixed $other): bool
    {
        $this->checkSameType($other);
        return $this->compare($other) === 1;
    }

    /**
     * Check if this object is greater than or equal to another object.
     *
     * Implemented as the negation of lessThan() to maintain consistency. Throws TypeError for incompatible types.
     *
     * @param mixed $other The value to compare with.
     * @return bool True if this object is greater than or equal to the other object, false otherwise.
     * @throws TypeError If the types are not the same.
     */
    public function greaterThanOrEqual(mixed $other): bool
    {
        $this->checkSameType($other);
        return !$this->lessThan($other);
    }

    /**
     * Verify that another value has the same type as this object, throwing TypeError if not.
     *
     * This method is used by the comparison methods (lessThan, greaterThan, etc.) to ensure type safety before
     * delegating to the compare() method.
     *
     * @param mixed $other The value to compare with.
     * @return void
     * @throws TypeError If the types are not the same.
     */
    public function checkSameType(mixed $other): void
    {
        if (!Types::haveSameType($this, $other)) {
            throw new TypeError('Cannot compare values of different types.');
        }
    }
}
