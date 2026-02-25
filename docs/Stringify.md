# Stringify

Static utility class for converting PHP values to readable string representations.

## Overview

The `Stringify` class provides an alternative to PHP's built-in functions for converting values to strings (viz. `var_dump()`, `var_export()`, `print_r()`, `json_encode()`, and `serialize()`). This is a static utility class and cannot be instantiated.

### Key Features

- **Single-quoted strings**: Strings are wrapped in single quotes with backslash and single-quote escaping. Unicode characters are preserved as-is.
- **Clearer float representation**: Floats are always made distinguishable from integers by appending `.0` if no decimal point or 'e' is present (e.g., `5.0` instead of `5`). Special values (`NAN`, `INF`, `-INF`) are handled correctly.
- **PHP-style array formatting**: Both lists and associative arrays use square brackets (`[...]`). Lists omit keys; associative arrays show keys with thick arrows (`=>`).
- **Smart pretty printing**: Scalar lists use single-line, grid, or one-per-line layout depending on length. Associative arrays and objects align keys/property names.
- **UML-style visibility notation**: Objects use `ClassName {...}` with visibility symbols (`+` public, `#` protected, `-` private).
- **Enum support**: Enums are rendered as `Fully\Qualified\ClassName::CaseName`.
- **Resource formatting**: Resources use `get_debug_type()` output, e.g. `resource (stream)` or `resource (closed)`.

The output for scalars, strings, and arrays is parseable PHP code. Object and resource output is not parseable but is designed for readability.

## Constants

| Constant | Value | Description |
|---|---|---|
| `NUM_SPACES_INDENT` | `4` | Number of spaces per indentation level in pretty-printed output. |
| `DEFAULT_MAX_LINE_LENGTH` | `120` | Maximum line length before pretty-printed lists wrap to grid or multiline format. |

## Formatting Methods

### stringify()

```php
public static function stringify(mixed $value, bool $prettyPrint = false, int $indentLevel = 0): string
```

Convert any PHP value to a readable string representation. This is the main entry point that dispatches to the appropriate type-specific method.

**Parameters:**
- `$value` (mixed) - The value to encode.
- `$prettyPrint` (bool) - Whether to use pretty printing with indentation (default: `false`).
- `$indentLevel` (int) - The level of indentation for nested structures (default: `0`).

**Returns:**
- `string` - The string representation of the value.

**Throws:**
- `DomainException` - If the value cannot be stringified (e.g., arrays with circular references).
- `UnexpectedValueException` - If the value has an unknown type.

**Examples:**

Basic types:
```php
Stringify::stringify(null);          // 'null'
Stringify::stringify(true);          // 'true'
Stringify::stringify(42);            // '42'
Stringify::stringify('hello');       // "'hello'"
Stringify::stringify(3.14);          // '3.14'
Stringify::stringify(5.0);           // '5.0' (not '5')
```

Arrays:
```php
Stringify::stringify([1, 2, 3]);                       // '[1, 2, 3]'
Stringify::stringify(['name' => 'John', 'age' => 30]); // "['name' => 'John', 'age' => 30]"
```

Enums:
```php
Stringify::stringify(Suit::Hearts); // 'App\Enums\Suit::Hearts'
```

### stringifyFloat()

```php
public static function stringifyFloat(float $value): string
```

Format a float value as a string, ensuring it doesn't look like an integer. Non-finite values (`NAN`, `INF`, `-INF`) are returned as-is.

**Parameters:**
- `$value` (float) - The float value to encode.

**Returns:**
- `string` - The string representation of the float.

**Examples:**

```php
Stringify::stringifyFloat(3.14);    // '3.14'
Stringify::stringifyFloat(5.0);     // '5.0' (ensures decimal point)
Stringify::stringifyFloat(1.5e100); // '1.5E+100'
Stringify::stringifyFloat(-0.0);    // '-0.0'
Stringify::stringifyFloat(NAN);     // 'NAN'
Stringify::stringifyFloat(INF);     // 'INF'
Stringify::stringifyFloat(-INF);    // '-INF'
```

### stringifyString()

```php
public static function stringifyString(string $value): string
```

Convert a string to a parseable single-quoted string representation. Backslashes and single quotes are escaped. Non-UTF-8 input is converted to UTF-8. Unicode characters are preserved as-is (not escaped to `\uXXXX`).

**Parameters:**
- `$value` (string) - The string value to encode.

**Returns:**
- `string` - The single-quoted, escaped string representation.

**Examples:**

```php
Stringify::stringifyString('hello');      // "'hello'"
Stringify::stringifyString("it's");       // "'it\\'s'"
Stringify::stringifyString('foo\\bar');   // "'foo\\\\bar'"
Stringify::stringifyString('café');       // "'café'"
```

### stringifyArray()

```php
public static function stringifyArray(
    array $arr,
    bool $prettyPrint = false,
    int $indentLevel = 0,
    int $maxLineLen = self::DEFAULT_MAX_LINE_LENGTH
): string
```

Stringify a PHP array as concise, parseable code. Lists (sequential integer keys starting at 0) show values only. Associative arrays show keys and values with fat arrows (`=>`).

When pretty printing is enabled, three layout strategies are used for lists of scalars:
1. **Single line** - if the result fits within `$maxLineLen`.
2. **Grid** - items padded to equal width and arranged in columns.
3. **One per line** - for lists containing non-scalar values.

Associative arrays are always one pair per line with aligned keys when pretty printing.

**Parameters:**
- `$arr` (array) - The array to encode.
- `$prettyPrint` (bool) - Whether to use pretty printing (default: `false`).
- `$indentLevel` (int) - The level of indentation (default: `0`).
- `$maxLineLen` (int) - Maximum line length for pretty printing (default: `120`).

**Returns:**
- `string` - The string representation of the array.

**Throws:**
- `DomainException` - If the array contains circular references.

**Examples:**

Lists:
```php
Stringify::stringifyArray([1, 2, 3]);           // '[1, 2, 3]'
Stringify::stringifyArray([]);                  // '[]'
```

Associative arrays:
```php
Stringify::stringifyArray(['a' => 1, 'b' => 2]); // "['a' => 1, 'b' => 2]"
```

Pretty-printed grid (scalar list exceeding max line length):
```php
Stringify::stringifyArray(range(1, 50), true);
// [
//     1,  2,  3,  4,  5,  6,  7,  8,  9,  10,
//     11, 12, 13, 14, 15, 16, 17, 18, 19, 20,
//     ...
// ]
```

Pretty-printed associative array (aligned keys):
```php
Stringify::stringifyArray(['name' => 'John', 'age' => 30], true);
// [
//     'name' => 'John',
//     'age'  => 30,
// ]
```

### stringifyResource()

```php
public static function stringifyResource(mixed $value): string
```

Stringify a resource using `get_debug_type()`. Works for both open and closed resources.

**Parameters:**
- `$value` (mixed) - The resource to stringify.

**Returns:**
- `string` - The string representation of the resource, e.g. `'resource (stream)'`.

**Throws:**
- `InvalidArgumentException` - If the value is not a resource.

**Examples:**

```php
$file = fopen('php://memory', 'r');
Stringify::stringifyResource($file);  // 'resource (stream)'

fclose($file);
Stringify::stringifyResource($file);  // 'resource (closed)'
```

### stringifyEnum()

```php
public static function stringifyEnum(UnitEnum $value): string
```

Get a string representation of an enum case in the form `Fully\Qualified\ClassName::CaseName`. The leading backslash is removed if present.

**Parameters:**
- `$value` (UnitEnum) - The enum case to stringify.

**Returns:**
- `string` - The string representation.

**Examples:**

```php
Stringify::stringifyEnum(Suit::Hearts);  // 'App\Enums\Suit::Hearts'
```

**Note:** `stringifyObject()` and `stringify()` automatically detect enum instances and delegate to this method.

### stringifyObject()

```php
public static function stringifyObject(object $obj, bool $prettyPrint = false, int $indentLevel = 0): string
```

Stringify an object using a custom format with the class name, curly braces, and UML visibility symbols.

If the object is an enum, it is automatically delegated to `stringifyEnum()`.

**Parameters:**
- `$obj` (object) - The object to encode.
- `$prettyPrint` (bool) - Whether to use pretty printing (default: `false`).
- `$indentLevel` (int) - The level of indentation (default: `0`).

**Returns:**
- `string` - The string representation of the object.

**Visibility Symbols (UML notation):**
- `+` - Public property
- `#` - Protected property
- `-` - Private property

**Examples:**

Simple object:
```php
class User {
    public string $name = 'John';
    protected int $age = 30;
    private string $id = 'abc123';
}

$user = new User();
Stringify::stringifyObject($user);
// "User {+name => 'John', #age => 30, -id => 'abc123'}"
```

Empty object:
```php
$obj = new stdClass();
Stringify::stringifyObject($obj);  // 'stdClass {}'
```

With pretty printing (property names are aligned):
```php
Stringify::stringifyObject($user, true);
// User {
//     +name => 'John',
//     +age  => 30,
//     -id   => 'abc123',
// }
```

Anonymous class:
```php
$anon = new class { public int $x = 1; };
Stringify::stringifyObject($anon);  // '@anonymous {+x => 1}'
```

### abbrev()

```php
public static function abbrev(mixed $value, int $maxLen = 30): string
```

Get a short string representation of a value, truncated to a maximum length. Uses multibyte-safe truncation. Useful for error messages and logs where space is limited.

**Parameters:**
- `$value` (mixed) - The value to get the string representation for.
- `$maxLen` (int) - The maximum length of the result (default: `30`, minimum: `10`).

**Returns:**
- `string` - The abbreviated string representation.

**Throws:**
- `DomainException` - If the maximum length is less than 10, or if the value cannot be stringified.

**Examples:**

```php
Stringify::abbrev('hello');                           // "'hello'"
Stringify::abbrev('this is a very long string', 15); // "'this is a v..."
Stringify::abbrev([1, 2, 3, 4, 5, 6, 7], 15);       // '[1, 2, 3, 4,...'
```

### print()

```php
public static function print(mixed $value, bool $prettyPrint = false): void
```

Output a stringified value directly to the output stream. A convenience method that combines `stringify()` with `echo`.

**Parameters:**
- `$value` (mixed) - The value to stringify and output.
- `$prettyPrint` (bool) - Whether to use pretty printing (default: `false`).

**Throws:**
- `DomainException` - If the value cannot be stringified.

### println()

```php
public static function println(mixed $value, bool $prettyPrint = false): void
```

Same as `print()`, but appends a trailing newline.

**Parameters:**
- `$value` (mixed) - The value to stringify and output.
- `$prettyPrint` (bool) - Whether to use pretty printing (default: `false`).

**Throws:**
- `DomainException` - If the value cannot be stringified.

## See Also

- **[Types](Types.md)** - Type checking and inspection utilities.
- **[Arrays](Arrays.md)** - Array utility methods including `containsRecursion()`.
