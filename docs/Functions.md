# Functions

Convenience functions in the `Galaxon\Core` namespace that work better as plain functions than static methods.

---

## Overview

The `functions.php` file provides a small set of utility functions that are more natural to call as plain functions than as static class methods. These are namespaced under `Galaxon\Core`.

---

## Autoloading

Since these are functions rather than classes, PSR-4 autoloading won't discover them automatically. The Core package's `composer.json` includes a `files` autoload entry:

```json
"autoload": {
    "psr-4": {
        "Galaxon\\Core\\": "src/"
    },
    "files": [
        "src/functions.php"
    ]
}
```

This means the functions are loaded automatically in any project that requires `galaxon/core`. To use them, add a `use function` import:

```php
use function Galaxon\Core\println;
use function Galaxon\Core\is_number;
```

---

## Functions

### println()

```php
function println(mixed $value): void
```

Echo a value and append a newline character.

**Parameters:**
- `$value` (mixed) - The value to echo.

**Examples:**

```php
use function Galaxon\Core\println;

println('Hello, world!');  // Outputs: Hello, world!\n
println(42);               // Outputs: 42\n
println(3.14);             // Outputs: 3.14\n
```

**Notes:**
- Uses `PHP_EOL` for the newline, so the line ending is platform-appropriate.
- The value is output using `echo`, so it follows PHP's standard string conversion rules.

### is_number()

```php
function is_number(mixed $value): bool
```

Check if a value is a number (int or float).

This differs from PHP's built-in `is_numeric()`, which also returns `true` for numeric strings like `"42"` or `"3.14"`.

**Parameters:**
- `$value` (mixed) - The value to check.

**Returns:**
- `bool` - `true` if the value is an `int` or `float`, `false` otherwise.

**Examples:**

```php
use function Galaxon\Core\is_number;

is_number(42);        // true
is_number(3.14);      // true
is_number('42');      // false (numeric string)
is_number('hello');   // false
is_number(true);      // false
is_number(null);      // false
is_number([1, 2]);    // false
```

**Comparison with `is_numeric()`:**

| Value | `is_number()` | `is_numeric()` |
|-------|---------------|-----------------|
| `42` | `true` | `true` |
| `3.14` | `true` | `true` |
| `'42'` | `false` | `true` |
| `'3.14'` | `false` | `true` |
| `'0x1A'` | `false` | `true` |
| `true` | `false` | `false` |
| `null` | `false` | `false` |

---

## See Also

- **[Types](Types.md)** - Static utility class for type checking and inspection
- **[Numbers](Numbers.md)** - General number-related utility methods
