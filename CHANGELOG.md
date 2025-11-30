# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

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
  - `wrap()` - Wrap values to fit within a range using modular arithmetic (signed/unsigned modes)
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
