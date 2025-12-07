# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [0.4.0] - 2025-02-08

### Breaking Changes

- **Equatable converted from interface to trait**
  - Changed from `interface Equatable` to `trait Equatable`
  - Classes must now use `use Equatable;` instead of `implements Equatable`
  - Provides better composition with other comparison traits
  - Namespace unchanged: `Galaxon\Core\Traits\Equatable`

- **Comparable namespace changed and converted to abstract trait**
  - Moved from `Galaxon\Core\Comparable` to `Galaxon\Core\Traits\Comparable`
  - No longer provides default `compare()` implementation
  - Now requires implementing class to provide `compare()` method
  - Uses Equatable trait via composition
  - Method `equals()` renamed to `equal()` for consistency

- **Floats::approxEqual() signature changed**
  - Now mimics Python's `math.isclose()` behavior
  - Parameters changed from `($f1, $f2, $epsilon, $relative)` to `($a, $b, $relTol, $absTol)`
  - Default relative tolerance: `1e-9` (new constant `DEFAULT_RELATIVE_TOLERANCE`)
  - Default absolute tolerance: `PHP_FLOAT_EPSILON` (new constant `DEFAULT_ABSOLUTE_TOLERANCE`)
  - Removed `$relative` parameter - now uses combined relative and absolute tolerance
  - IEEE-754 special value handling: `INF === INF`, `-INF === -INF`, `NaN` never equals anything
  - Removed `approxEqualAbsolute()` and `approxEqualRelative()` methods

- **Comparison method names standardized**
  - `equals()` → `equal()` across all traits
  - `isLessThan()` → `lessThan()`
  - `isGreaterThan()` → `greaterThan()`
  - `isLessThanOrEqual()` → `lessThanOrEqual()`
  - `isGreaterThanOrEqual()` → `greaterThanOrEqual()`

### Added

- **ApproxEquatable trait** (`Galaxon\Core\Traits\ApproxEquatable`)
  - Extends Equatable with tolerance-based comparison
  - Abstract `approxEqual()` method for floating-point equality within tolerances
  - For types without natural ordering (e.g., Complex numbers)

- **ApproxComparable trait** (`Galaxon\Core\Traits\ApproxComparable`)
  - Combines Comparable and ApproxEquatable for complete comparison suite
  - Provides `approxCompare()` method for approximate ordering comparison
  - For types with natural ordering that contain floating-point values (e.g., Rational numbers)

- **Floats constants**
  - `DEFAULT_RELATIVE_TOLERANCE` (1e-9) - Default relative tolerance for `approxEqual()`
  - `DEFAULT_ABSOLUTE_TOLERANCE` (PHP_FLOAT_EPSILON) - Default absolute tolerance
  - `MAX_EXACT_INT` (2^53) - Maximum integer exactly representable as float

- **Types::haveSameType()** - Check if two values have the same type using `get_debug_type()`

- **Comparable::checkSameType()** - Public method to verify type compatibility, throws `TypeError` if types don't match

### Changed

- **Floats class reorganized**
  - Methods grouped by functionality: comparison, conversion, precision, rounding, random
  - Improved PHPDoc comments throughout
  - Better separation of concerns

- **Numbers::approxEqual()** - Updated to use new Floats::approxEqual() signature

- **Traits now use composition**
  - Comparable uses Equatable
  - ApproxEquatable uses Equatable
  - ApproxComparable uses both Comparable and ApproxEquatable
  - PHP automatically resolves diamond inheritance of Equatable

### Documentation

- **New comprehensive trait documentation**:
  - `docs/Traits/Traits.md` - Complete overview with hierarchy diagram and usage guide
  - `docs/Traits/Equatable.md` - Rewritten for trait (previously interface)
  - `docs/Traits/Comparable.md` - Updated for new namespace and behavior
  - `docs/Traits/ApproxEquatable.md` - New documentation for approximate equality
  - `docs/Traits/ApproxComparable.md` - New documentation for approximate comparison

- **Updated class documentation**:
  - `docs/Floats.md` - Completely reorganized to match new class structure
  - `docs/Numbers.md` - Updated for new comparison method signatures
  - `docs/Types.md` - Added `haveSameType()` documentation

- **README.md** - Added Traits section with links to all four traits and overview

### Tests

- **FloatsTest** - Comprehensive rewrite for new `approxEqual()` behavior
  - Tests for relative and absolute tolerance
  - Tests for IEEE-754 special values (INF, -INF, NaN)
  - Tests for combined tolerance behavior
  - Reduced from 478 to ~280 lines (test consolidation)

- **NumbersTest** - Updated for new `approxEqual()` signature
- **TypesTest** - Added tests for `haveSameType()`


## [0.3.0] - 2025-01-15

### Added

- **Arrays::quoteValues()** - Wrap string array values in quotes for formatting
  - Supports both single quotes (default) and double quotes
  - Useful for formatting lists in error messages or output
  - Throws `TypeError` if array contains non-string values
  - Preserves array keys

- **Floats::ulp()** - Calculate Unit in Last Place (ULP) for floating-point precision analysis
  - Returns the spacing between adjacent representable floats at a given magnitude
  - Useful for understanding floating-point precision limits and calculating error bounds
  - Moved from `NumberWithError` in Units package to provide general-purpose float utility

- **Floats::isExactInt()** - Check if a float represents an exact integer without rounding error
  - Validates integers are within IEEE-754 double's exact integer range (±2^53)
  - Returns `true` for whole numbers that can be exactly represented as floats
  - Moved from `NumberWithError::isExactFloat()` in Units package with improved naming

### Tests

- Added 19 comprehensive tests for `Arrays::quoteValues()`:
  - Both single and double quote modes
  - Empty arrays and strings
  - Special characters, whitespace, and unicode
  - Type validation (TypeError for non-string values)
  - Key preservation and immutability

- Added 18 comprehensive tests for Floats precision methods:
  - `ulp()` tests: standard values, zero handling, negative values, large/small magnitudes, non-finite values, relationship with `next()`
  - `isExactInt()` tests: whole numbers, fractional values, boundary cases (±2^53), non-finite values, comparison with `tryConvertToInt()`

### Documentation

- **Arrays.md** - Added documentation for `quoteValues()`
- **Floats.md** - Added documentation for `ulp()` and `isExactInt()`

## [0.2.0] - 2025-01-29

### Breaking Changes

- **Angle class removed** - Moved to `galaxon/units` package
  - Use `Galaxon\Units\Angle` instead of `Galaxon\Core\Angle`
  - All Angle functionality now available in the separate Units package

- **Floats::approxEqual() behavior changed**
  - Now uses relative comparison by default instead of absolute comparison
  - New 4th parameter `$relative` (defaults to `true`)
  - Relative comparison scales epsilon with magnitude, better for comparing values across different scales
  - To maintain old behavior, pass `false` for the `$relative` parameter

### Added

- **Floats** - New constants and methods for float operations
  - `TAU` constant - The mathematical constant τ (tau) = 2π, useful for angular calculations
  - `EPSILON` constant - Default epsilon value (1e-10) for approximate comparisons
  - `approxEqualAbsolute()` - Explicit absolute epsilon comparison
  - `approxEqualRelative()` - Explicit relative epsilon comparison (scales with magnitude)
  - `compare()` - Three-way comparison with approximate equality support

- **Numbers** - New comparison methods
  - `equal()` - Exact equality check for int|float values
  - `approxEqual()` - Approximate equality with relative/absolute mode selection

### Changed

- **Floats::compare()** - Now uses `Numbers::sign()` to guarantee exactly -1, 0, or 1 return values
- **Floats::approxEqual()** - Added 4th parameter `$relative` (defaults to `true`)
- **Numbers::approxEqual()** - Added 3rd parameter `$epsilon` and 4th parameter `$relative`

### Documentation

- **Enhanced PHPDoc comments** in `Equatable` and `Comparable` with detailed implementation guidelines
- **Comprehensive documentation updates**:
  - `docs/Floats.md` - Added TAU, wrap(), compare(), and all three approxEqual variants
  - `docs/Numbers.md` - Added equal() and updated approxEqual() documentation
  - `docs/Equatable.md` - Updated epsilon examples and best practices
  - `docs/Comparable.md` - Corrected implementation details and added TypeError documentation
  - Removed `docs/Angle.md` (moved to Units package)

### Tests

- Added comprehensive tests for new Floats methods (approxEqualAbsolute, approxEqualRelative, compare, wrap)
- Added comprehensive tests for Numbers methods (equal, approxEqual)
- All tests passing with 100% code coverage maintained

## [0.1.0] - 2025-01-16

### Added

- **Angle** - Class for working with angles in radians and degrees
  - `wrapRadians()`, `wrapDegrees()` - Normalize angles to standard ranges
  - `fromDegrees()`, `fromRadians()` - Factory methods
  - Conversion between radians and degrees

- **Floats** - Utility methods for floating-point operations
  - `approxEqual()` - Compare floats with epsilon tolerance
  - `sign()` - Get sign of a float (-1, 0, or 1)
  - Constants for common epsilon values

- **Integers** - Utility methods for integer operations
  - `sign()` - Get sign of an integer
  - `gcd()` - Greatest common divisor
  - `lcm()` - Least common multiple
  - `absExact()` - Absolute value with overflow detection
  - `mulExact()`, `addExact()` - Arithmetic with overflow detection

- **Numbers** - Utility methods for numeric operations
  - Common operations that work with both int and float

- **Arrays** - Utility methods for array operations

- **Stringify** - Utilities for converting values to strings
  - `value()` - Convert any PHP value to a readable string representation

- **Types** - Utility methods for type checking and manipulation
  - `isNumber()` - Check if value is int or float
  - `isUint()` - Check if value is unsigned integer
  - `getBasicType()` - Get canonical type name
  - `getUniqueString()` - Convert any value to unique string
  - `createError()` - Create TypeError with helpful message
  - `usesTrait()` - Check if class/object uses a trait
  - `getTraits()` - Get all traits used by class/interface/trait

- **Equatable** - Interface for value equality comparison
  - `equals(mixed $other): bool` - Check equality with another value

- **Comparable** - Trait providing comparison methods
  - `equals()`, `isLessThan()`, `isGreaterThan()`
  - `isLessThanOrEqual()`, `isGreaterThanOrEqual()`
  - Requires implementing class to provide `compare()` method

### Requirements
- PHP ^8.4

### Development
- PSR-12 coding standards
- PHPStan level 9 static analysis
- PHPUnit test coverage
- Comprehensive test suite with 100% code coverage
