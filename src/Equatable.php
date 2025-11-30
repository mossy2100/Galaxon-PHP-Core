<?php

declare(strict_types=1);

namespace Galaxon\Core;

/**
 * Interface for objects that can be compared for equality.
 *
 * This interface defines a contract for objects that support equality comparison. Implementations should check for type
 * compatibility and return false for incompatible types rather than throwing exceptions.
 *
 * For types with a natural ordering, consider using the Comparable trait instead, which provides equals() along with
 * ordering methods like isLessThan(), isGreaterThan(), etc.
 *
 * @see Comparable For types with natural ordering.
 */
interface Equatable
{
    /**
     * Compare this object with another value and determine if they are equal.
     *
     * Implementations should:
     * - Return false for incompatible types (do not throw exceptions)
     * - Handle type checking gracefully
     * - Consider using epsilon-based comparison for floating-point values
     * - Be reflexive (x.equals(x) is always true)
     * - Be symmetric (if x.equals(y) then y.equals(x))
     * - Be transitive (if x.equals(y) and y.equals(z) then x.equals(z))
     *
     * @param mixed $other The value to compare with (can be any type).
     * @return bool True if the values are equal, false otherwise.
     */
    public function equals(mixed $other): bool;
}
