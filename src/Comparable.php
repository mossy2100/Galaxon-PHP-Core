<?php

declare(strict_types=1);

namespace Galaxon\Core;

use TypeError;

/**
 * Trait providing a complete set of comparison operations based on a single compare() method.
 *
 * This trait follows the Template Method Pattern: you implement the abstract compare() method that returns -1, 0, or 1,
 * and the trait provides all other comparison methods (equals, isLessThan, isGreaterThan, etc.).
 *
 * Classes using this trait should typically also implement the Equatable interface, as this trait provides an equals()
 * method that satisfies the interface contract.
 *
 * The trait includes type checking to ensure comparisons are only performed between compatible types, throwing
 * TypeError for incompatible types (except for equals(), which returns false gracefully).
 *
 * @see Equatable For the equality interface this trait satisfies.
 * @codeCoverageIgnore
 * @phpstan-ignore trait.unused
 */
trait Comparable
{
    /**
     * Compare this object with another and return an integer indicating the ordering relationship.
     *
     * Implementations must return exactly -1, 0, or 1 (not just negative/zero/positive):
     *     -1 if this object is less than the other object
     *      0 if this object equals the other object
     *      1 if this object is greater than the other object
     *
     * Important: Return values must be exactly -1, 0, or 1 because the convenience methods (isLessThan, etc.) use
     * strict equality checks. Use Numbers::sign() to normalize spaceship operator results.
     *
     * Implementation guidelines:
     * - May throw TypeError for incompatible types (this is expected behavior)
     * - Should use epsilon tolerance for floating-point comparisons
     * - Must be consistent (same inputs always produce same result)
     * - Must be transitive (if A < B and B < C, then A < C)
     *
     * @param mixed $other The value to compare with.
     * @return int Exactly -1, 0, or 1 indicating the ordering relationship.
     * @throws TypeError If the types are incompatible for comparison.
     */
    abstract public function compare(mixed $other): int;

    /**
     * Check if another value has the same type as this object.
     *
     * Uses get_debug_type() for type comparison, which provides more accurate type information than instanceof,
     * especially for distinguishing between different class instances.
     *
     * @param mixed $other The value to compare with.
     * @return bool True if the types are the same, false otherwise.
     */
    private function hasSameType(mixed $other): bool
    {
        return get_debug_type($this) === get_debug_type($other);
    }

    /**
     * Verify that another value has the same type as this object, throwing TypeError if not.
     *
     * This method is used by the comparison methods (isLessThan, isGreaterThan, etc.) to ensure type safety before
     * delegating to the compare() method.
     *
     * @param mixed $other The value to compare with.
     * @return void
     * @throws TypeError If the types are not the same.
     */
    private function checkSameType(mixed $other): void
    {
        if (!$this->hasSameType($other)) {
            throw new TypeError("Cannot compare values of different types.");
        }
    }

    /**
     * Check if this object is equal to another value.
     *
     * This method satisfies the Equatable interface contract. Unlike the other comparison methods, equals() returns
     * false gracefully for incompatible types instead of throwing TypeError.
     *
     * The method first checks type compatibility using get_debug_type(), and only calls compare() if the types match.
     *
     * @param mixed $other The value to compare with.
     * @return bool True if the values are equal, false otherwise (including for incompatible types).
     */
    public function equals(mixed $other): bool
    {
        // Check if the types are the same, and if so, compare the values.
        return $this->hasSameType($other) && $this->compare($other) === 0;
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
    public function isLessThan(mixed $other): bool
    {
        $this->checkSameType($other);
        return $this->compare($other) === -1;
    }

    /**
     * Check if this object is less than or equal to another object.
     *
     * Implemented as the negation of isGreaterThan() to maintain consistency. Throws TypeError for incompatible types.
     *
     * @param mixed $other The value to compare with.
     * @return bool True if this object is less than or equal to the other object, false otherwise.
     * @throws TypeError If the types are not the same.
     */
    public function isLessThanOrEqual(mixed $other): bool
    {
        $this->checkSameType($other);
        return !$this->isGreaterThan($other);
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
    public function isGreaterThan(mixed $other): bool
    {
        $this->checkSameType($other);
        return $this->compare($other) === 1;
    }

    /**
     * Check if this object is greater than or equal to another object.
     *
     * Implemented as the negation of isLessThan() to maintain consistency. Throws TypeError for incompatible types.
     *
     * @param mixed $other The value to compare with.
     * @return bool True if this object is greater than or equal to the other object, false otherwise.
     * @throws TypeError If the types are not the same.
     */
    public function isGreaterThanOrEqual(mixed $other): bool
    {
        $this->checkSameType($other);
        return !$this->isLessThan($other);
    }
}
