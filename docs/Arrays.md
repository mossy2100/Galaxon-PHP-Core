# Arrays

Static utility class containing useful array-related methods.

## Overview

The `Arrays` class provides helper methods for working with PHP arrays. This is a static utility class and cannot be instantiated.

Methods are organized into:
- **Inspection methods** - Analyze array properties (e.g., detect circular references)
- **Transformation methods** - Transform array values (e.g., quote strings)
- **Extraction methods** - Extract values from arrays (e.g., first/last element)

## Inspection Methods

### containsRecursion()

```php
public static function containsRecursion(array $arr): bool
```

Checks if an array contains circular references (recursion). This occurs when an array contains a reference to itself, either directly or indirectly through nested arrays.

**Parameters:**
- `$arr` (array) - The array to check for circular references

**Returns:**
- `bool` - Returns `true` if recursion is detected, `false` otherwise

**Examples:**

Direct recursion:
```php
$arr = ['foo' => 'bar'];
$arr['self'] = &$arr;
Arrays::containsRecursion($arr); // true
```

Indirect recursion:
```php
$arr1 = ['name' => 'array1'];
$arr2 = ['name' => 'array2'];
$arr1['child'] = &$arr2;
$arr2['parent'] = &$arr1;
Arrays::containsRecursion($arr1); // true
```

No recursion:
```php
$arr = [[1, 2], [3, 4]];
Arrays::containsRecursion($arr); // false
```

**Note:** This method uses `json_encode()` internally to detect recursion, as circular references cannot be JSON-encoded.

## Transformation Methods

### quoteValues()

```php
public static function quoteValues(array $arr, bool $doubleQuotes = false): array
```

Wrap each string value in the array with quotes for formatting purposes. Useful for creating quoted lists in error messages, output, or documentation.

**Parameters:**
- `$arr` (array<string>) - Array of strings to quote
- `$doubleQuotes` (bool) - Use double quotes instead of single quotes (default: `false`)

**Returns:**
- `array<string>` - Array with each value wrapped in quotes, preserving array keys

**Throws:**
- `TypeError` - If any array value is not a string

**Examples:**

Basic usage with single quotes (default):
```php
$fruits = ['apple', 'banana', 'cherry'];
$quoted = Arrays::quoteValues($fruits);
// ["'apple'", "'banana'", "'cherry'"]
```

Using double quotes:
```php
$names = ['Alice', 'Bob', 'Charlie'];
$quoted = Arrays::quoteValues($names, true);
// ['"Alice"', '"Bob"', '"Charlie"']
```

Preserves array keys:
```php
$config = ['host' => 'localhost', 'port' => '5432'];
$quoted = Arrays::quoteValues($config);
// ['host' => "'localhost'", 'port' => "'5432'"]
```

Values containing quotes are not escaped:
```php
$phrases = ["it's", 'say "hello"'];
$quoted = Arrays::quoteValues($phrases);
// ["'it's'", "'say \"hello\"'"]
```

Type validation:
```php
$mixed = ['string', 42, 'another'];
Arrays::quoteValues($mixed); // throws TypeError
```

**Use Cases:**
- Formatting error messages with lists of valid values
- Creating CSV-like output with quoted strings
- Generating SQL value lists
- Displaying configuration options in documentation
- Building command-line argument strings

**Example in Error Messages:**

```php
$validUnits = ['kg', 'g', 'mg'];
$quotedUnits = Arrays::quoteValues($validUnits);
throw new ValueError('Invalid unit. Valid units: ' . implode(', ', $quotedUnits));
// "Invalid unit. Valid units: 'kg', 'g', 'mg'"
```

**Note:** This method does not perform escaping. If the values contain the quote character, they will not be escaped. For proper escaping, use appropriate functions like `addslashes()` or context-specific escaping functions.

## Extraction Methods

### first()

```php
public static function first(array $arr): mixed
```

Get the first value in an array. This is a polyfill for PHP versions prior to 8.5, which provides the native `array_first()` function.

This method doesn't behave exactly the same as `array_first()`, as it will throw a `LengthException` instead of returning `null` for empty arrays.

This is because the first value in an array might actually be null.

**Parameters:**
- `$arr` (non-empty-array) - The array to extract from

**Returns:**
- `mixed` - The first value in the array

**Throws:**
- `LengthException` - If the array is empty

**Examples:**

List array:
```php
Arrays::first([1, 2, 3]); // 1
Arrays::first(['apple', 'banana', 'cherry']); // 'apple'
```

Associative array:
```php
$config = ['host' => 'localhost', 'port' => 5432, 'db' => 'myapp'];
Arrays::first($config); // 'localhost'
```

Single element:
```php
Arrays::first([42]); // 42
Arrays::first(['only' => 'value']); // 'value'
```

Empty array throws exception:
```php
Arrays::first([]); // throws LengthException
```

**Note:** Unlike `reset()`, this method does not modify the array's internal pointer and throws an exception for empty arrays rather than returning `false`.

### last()

```php
public static function last(array $arr): mixed
```

Get the last value in an array. This is a polyfill for PHP versions prior to 8.5, which provides the native `array_last()` function.

This method doesn't behave exactly the same as `array_last()`, as it will throw a `LengthException` instead of returning `null` for empty arrays.

This is because the last value in an array might actually be null.

**Parameters:**
- `$arr` (non-empty-array) - The array to extract from

**Returns:**
- `mixed` - The last value in the array

**Throws:**
- `LengthException` - If the array is empty

**Examples:**

List array:
```php
Arrays::last([1, 2, 3]); // 3
Arrays::last(['apple', 'banana', 'cherry']); // 'cherry'
```

Associative array:
```php
$config = ['host' => 'localhost', 'port' => 5432, 'db' => 'myapp'];
Arrays::last($config); // 'myapp'
```

Single element:
```php
Arrays::last([42]); // 42
Arrays::last(['only' => 'value']); // 'value'
```

Empty array throws exception:
```php
Arrays::last([]); // throws LengthException
```

**Note:** Unlike `end()`, this method does not modify the array's internal pointer and throws an exception for empty arrays rather than returning `false`.

## See Also

- **[Strings](Strings.md)** - String utility methods
- **[Types](Types.md)** - Type checking and conversion utilities
