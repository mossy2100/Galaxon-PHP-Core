<?php

declare(strict_types=1);

namespace Galaxon\Core;

use DomainException;
use InvalidArgumentException;
use UnexpectedValueException;
use UnitEnum;

/**
 * This class provides a method of formatting any PHP value as a string, with a few differences from the default
 * options of var_dump(), var_export(), print_r(), json_encode(), and serialize().
 *
 * - Floats never look like integers.
 * - Strings are single-quoted.
 * - Arrays are rendered as parseable PHP code using modern square bracket syntax.
 * - Arrays that are lists will not show keys; associative arrays will show keys.
 * - Objects are rendered like arrays but with a class name and curly braces instead of square brackets.
 * - Object properties are shown with UML visibility modifiers: + (public), # (protected), and - (private).
 * - Enums are rendered as Fully\Qualified\ClassName::CaseName
 * - Resources have a unique encoding showing the type and id.
 *
 * The stringify results for objects and resources are not parseable by PHP, but they are for other types.
 *
 * The purpose of the class is to offer a somewhat more concise, readable, and informative alternative to the usual
 * options. It can be useful for exception, log, and debug messages.
 */
final class Stringify
{
    // region Constants

    /**
     * The number of spaces to indent each level.
     *
     * @var int
     */
    public const int NUM_SPACES_INDENT = 4;

    /**
     * The default maximum line length for pretty-printed output.
     */
    public const int DEFAULT_MAX_LINE_LENGTH = 120;

    // endregion

    // region Constructor

    /**
     * Private constructor to prevent instantiation.
     *
     * @codeCoverageIgnore
     */
    private function __construct()
    {
    }

    // endregion

    // region Formatting methods

    /**
     * Convert a value to a readable string representation.
     *
     * @param mixed $value The value to encode.
     * @param bool $prettyPrint Whether to use pretty printing with indentation (default false).
     * @param int $indentLevel The level of indentation for this structure (default 0).
     * @return string The string representation of the value.
     * @throws DomainException If the value cannot be stringified.
     * @throws UnexpectedValueException If the value has an unknown type.
     */
    public static function stringify(mixed $value, bool $prettyPrint = false, int $indentLevel = 0): string
    {
        // Call the relevant method.
        switch (Types::getBasicType($value)) {
            case 'null':
                return 'null';

            case 'bool':
                /** @var bool $value */
                return $value ? 'true' : 'false';

            case 'int':
                /** @var int $value */
                return (string)$value;

            case 'string':
                /** @var string $value */
                return self::stringifyString($value);

            case 'float':
                /** @var float $value */
                return self::stringifyFloat($value);

            case 'array':
                /** @var list<mixed> $value */
                return self::stringifyArray($value, $prettyPrint, $indentLevel);

            case 'resource':
                return self::stringifyResource($value);

            case 'object':
                /** @var object $value */
                return self::stringifyObject($value, $prettyPrint, $indentLevel);

            // @codeCoverageIgnoreStart
            // This should never happen, but we'll include it for completeness/robustness.
            // We can't test this, so get phpunit to ignore it for code coverage purposes.
            default:
                throw new UnexpectedValueException('Unknown type.');
            // @codeCoverageIgnoreEnd
        }
    }

    /**
     * Encode a float in such a way that it doesn't look like an integer.
     *
     * @param float $value The float value to encode.
     * @return string The string representation of the float.
     */
    public static function stringifyFloat(float $value): string
    {
        // Convert the float to a string. This will also work for ±INF and NAN.
        // The @ suppresses PHP 8.5's warning when casting NAN to string.
        $s = @(string)$value;

        // Handle non-finite values.
        if (!is_finite($value)) {
            return $s;
        }

        // If the string representation of the float value has no decimal point or exponent (i.e. nothing to distinguish
        // it from an integer), append a decimal point.
        if (!preg_match('/[.eE]/', $s)) {
            $s .= '.0';
        }

        return $s;
    }

    /**
     * Convert a string to a parseable single-quoted string.
     *
     * @param string $value The string value to encode.
     * @return string The single-quoted, escaped string representation.
     * @throws DomainException If the string is not UTF-8 and the encoding could not be detected.
     */
    public static function stringifyString(string $value): string
    {
        // Get the string as UTF-8 if not already.
        if (!mb_check_encoding($value, 'UTF-8')) {
            // Try to detect the encoding.
            $encoding = mb_detect_encoding($value, mb_detect_order(), true);
            if ($encoding === false) {
                throw new DomainException('String encoding is not UTF-8 and could not be detected.');
            }

            // Convert the string to UTF-8.
            $value = mb_convert_encoding($value, 'UTF-8', $encoding);
            if ($value === false) {
                // @codeCoverageIgnoreStart
                throw new DomainException('String was not UTF-8 and could not be converted to UTF-8.');
                // @codeCoverageIgnoreEnd
            }
        }

        // Escape backslashes and single quotes.
        $value = str_replace('\\', '\\\\', $value);
        $value = str_replace("'", "\\'", $value);

        return "'$value'";
    }

    /**
     * Stringify a PHP array as concise, parseable code.
     *
     * A list (i.e. an array with sequential integer keys starting at 0) will show values only.
     * An associative array will show keys and values. String keys will be quoted.
     *
     * If pretty printing is enabled, the result will be formatted with new lines and indentation.
     *
     * @param array<array-key, mixed> $arr The array to encode.
     * @param bool $prettyPrint Whether to use pretty printing (default false).
     * @param int $indentLevel The level of indentation for this structure (default 0).
     * @param int $maxLineLen The maximum length of the result string for a list when pretty printing is enabled.
     * @return string The string representation of the array.
     * @throws DomainException If the array contains circular references.
     */
    public static function stringifyArray(
        array $arr,
        bool $prettyPrint = false,
        int $indentLevel = 0,
        int $maxLineLen = self::DEFAULT_MAX_LINE_LENGTH
    ): string {
        // Detect circular references.
        if (Arrays::containsRecursion($arr)) {
            throw new DomainException('Cannot stringify arrays containing circular references.');
        }

        return array_is_list($arr)
            ? self::stringifyList($arr, $prettyPrint, $indentLevel, $maxLineLen)
            : self::stringifyAssociativeArray($arr, $prettyPrint, $indentLevel);
    }

    /**
     * Stringify a list (sequential integer keys starting at 0).
     *
     * Without pretty printing, values are comma-separated on one line.
     * With pretty printing, uses single-line, grid, or one-per-line format depending on content.
     *
     * @param list<mixed> $arr The list to stringify.
     * @param bool $prettyPrint Whether to use pretty printing.
     * @param int $indentLevel The level of indentation for this structure.
     * @param int $maxLineLen The maximum line length for pretty printing.
     * @return string The string representation of the list.
     */
    private static function stringifyList(
        array $arr,
        bool $prettyPrint,
        int $indentLevel,
        int $maxLineLen
    ): string {
        // Get the values as strings.
        $valueStrings = [];
        foreach ($arr as $value) {
            $valueStrings[] = self::stringify($value);
        }

        // Unpretty format. No newlines or extra spaces.
        if (!$prettyPrint) {
            return '[' . implode(', ', $valueStrings) . ']';
        }

        // Set up for pretty printing.
        $nSpacesBracketIndent = $indentLevel * self::NUM_SPACES_INDENT;
        $bracketIndent = str_repeat(' ', $nSpacesBracketIndent);
        $nSpacesItemIndent = $nSpacesBracketIndent + self::NUM_SPACES_INDENT;
        $itemIndent = str_repeat(' ', $nSpacesItemIndent);
        $nItems = count($arr);

        // Check if all items are either null, bool, int, float, or string.
        $allScalars = true;
        foreach ($arr as $value) {
            if ($value !== null && !is_scalar($value)) {
                $allScalars = false;
            }
        }

        if ($allScalars) {
            // Option 1: Format the list on one line, no trailing comma.
            $singleLineList = '[' . implode(', ', $valueStrings) . ']';

            // Check if it will fit in one line, counting the indent.
            // Note, the bracket indent may not be where the list actually starts in the output, so this isn't
            // guaranteed to fit on one line.
            if (mb_strlen($bracketIndent . $singleLineList) <= $maxLineLen) {
                return $singleLineList;
            }

            // Option 2: Format the list as a grid.
            // Get the max item width.
            $maxValueWidth = 0;
            foreach ($valueStrings as $valueString) {
                $len = mb_strlen($valueString);
                if ($len > $maxValueWidth) {
                    $maxValueWidth = $len;
                }
            }

            // Calculate the number of items per line.
            $nItemsPerLine = (int)floor(($maxLineLen + 1 - $nSpacesItemIndent) / ($maxValueWidth + 2));
            if ($nItemsPerLine > 1) {
                // Generate the grid.
                $gridList = "[\n";
                $itemCountThisLine = 0;
                foreach ($valueStrings as $i => $valueString) {
                    // Indent the first item on the line.
                    if ($itemCountThisLine === 0) {
                        $gridList .= $itemIndent;
                    }

                    // Add the value and comma.
                    $gridList .= mb_str_pad($valueString . ',', $maxValueWidth + 1);
                    $itemCountThisLine++;

                    // Add a newline or space after the value, as needed.
                    if ($itemCountThisLine === $nItemsPerLine || $i === $nItems - 1) {
                        $gridList .= "\n";
                        $itemCountThisLine = 0;
                    } else {
                        $gridList .= ' ';
                    }
                }
                return $gridList . $bracketIndent . ']';
            }
        }

        // Option 3: Format the list with one item per line. Pretty print each value. Include trailing comma.
        $multilineList = "[\n";
        foreach ($arr as $value) {
            $multilineList .= $itemIndent . self::stringify($value, true, $indentLevel + 1) .
                ",\n";
        }
        return $multilineList . $bracketIndent . ']';
    }

    /**
     * Stringify an associative array (non-sequential or string keys).
     *
     * Without pretty printing, key-value pairs are comma-separated on one line.
     * With pretty printing, uses one pair per line with aligned keys.
     *
     * @param array<array-key, mixed> $arr The associative array to stringify.
     * @param bool $prettyPrint Whether to use pretty printing.
     * @param int $indentLevel The level of indentation for this structure.
     * @return string The string representation of the associative array.
     */
    private static function stringifyAssociativeArray(
        array $arr,
        bool $prettyPrint,
        int $indentLevel
    ): string {
        // Get keys as strings.
        foreach ($arr as $key => $value) {
            $keyStrings[] = self::stringify($key);
        }

        $values = array_values($arr);
        $nItems = count($arr);

        // Unpretty format. No newlines or extra spaces.
        if (!$prettyPrint) {
            $pairs = [];
            for ($i = 0; $i < $nItems; $i++) {
                $pairs[] = $keyStrings[$i] . ' => ' . self::stringify($values[$i]);
            }
            return '[' . implode(', ', $pairs) . ']';
        }

        // Set up for pretty printing.
        $nSpacesBracketIndent = $indentLevel * self::NUM_SPACES_INDENT;
        $bracketIndent = str_repeat(' ', $nSpacesBracketIndent);
        $nSpacesItemIndent = $nSpacesBracketIndent + self::NUM_SPACES_INDENT;
        $itemIndent = str_repeat(' ', $nSpacesItemIndent);

        // Get the maximum key width.
        $maxKeyWidth = 0;
        foreach ($keyStrings as $keyString) {
            $keyStrLen = mb_strlen($keyString);
            if ($keyStrLen > $maxKeyWidth) {
                $maxKeyWidth = $keyStrLen;
            }
        }

        // Generate the result string.
        $result = "[\n";
        for ($i = 0; $i < $nItems; $i++) {
            $result .= $itemIndent . mb_str_pad($keyStrings[$i], $maxKeyWidth) . ' => ' .
                self::stringify($values[$i], true, $indentLevel + 1) . ",\n";
        }
        return $result . $bracketIndent . ']';
    }

    /**
     * Stringify a resource.
     *
     * Uses get_debug_type() which returns 'resource (stream)', 'resource (closed)', etc.
     *
     * @param mixed $value The resource to stringify.
     * @return string The string representation of the resource, e.g. 'resource (stream)'.
     * @throws InvalidArgumentException If the value is not a resource.
     */
    public static function stringifyResource(mixed $value): string
    {
        // We can't type hint for resource, and is_resource() returns false for a closed resource.
        // Check the debug type.
        $type = get_debug_type($value);
        if (!str_starts_with($type, 'resource (')) {
            throw new InvalidArgumentException('Value is not a resource.');
        }

        return $type;
    }

    /**
     * Get a string representation of an enum case in the form "Fully\Qualified\ClassName::CaseName".
     * The leading slash is removed.
     *
     * @param UnitEnum $value The enum case to stringify.
     * @return string The string representation (e.g. "UnitSystem::Financial").
     */
    public static function stringifyEnum(UnitEnum $value): string
    {
        return ltrim($value::class, '\\') . '::' . $value->name;
    }

    /**
     * Stringify an object.
     *
     * The resulting string uses a custom format.
     * - the fully qualified class name is used (i.e. with the namespace) before the opening brace
     * - property names are not quoted
     * - property-value pairs use fat arrows (=>) and are comma-separated
     * - the visibility of each property is shown using UML notation (+ for public, # for protected, - for private)
     *
     * If pretty printing is enabled, the result will be formatted with new lines and indentation.
     *
     * @param object $obj The object to encode.
     * @param bool $prettyPrint Whether to use pretty printing (default false).
     * @param int $indentLevel The level of indentation for this structure (default 0).
     * @return string The string representation of the object.
     */
    public static function stringifyObject(object $obj, bool $prettyPrint = false, int $indentLevel = 0): string
    {
        // Check for enum.
        if ($obj instanceof UnitEnum) {
            return self::stringifyEnum($obj);
        }

        // Get the object's class.
        $class = $obj::class;

        // Check for anonymous classes. We don't want null bytes in the result.
        if (str_contains($class, '@anonymous')) {
            $class = '@anonymous';
        }

        // Convert the object to an array to get its properties.
        $arr = (array)$obj;

        // Early return if no properties.
        if (count($arr) === 0) {
            return $class . ' {}';
        }

        // Generate the strings for key-value pairs. Each will be on its own line if pretty printing is enabled.
        $nSpacesBracketIndent = $indentLevel * self::NUM_SPACES_INDENT;
        $bracketIndent = $prettyPrint ? str_repeat(' ', $nSpacesBracketIndent) : '';
        $nSpacesItemIndent = $nSpacesBracketIndent + self::NUM_SPACES_INDENT;
        $itemIndent = $prettyPrint ? str_repeat(' ', $nSpacesItemIndent) : '';

        $keys = array_keys($arr);
        $values = array_values($arr);
        $propNames = [];
        $visibilitySymbols = [];
        $maxPropNameLen = 0;

        foreach ($values as $i => $value) {
            // Split the array key on null bytes to get the property name.
            $key = $keys[$i];
            $nameParts = explode("\0", $key);
            $propNames[$i] = Arrays::last($nameParts);

            // Get the property visibility symbol.
            if (count($nameParts) === 1) {
                // Property is public.
                $visibilitySymbols[$i] = '+';
            } else {
                // Property must be protected or private. If the second item in the $nameParts array is '*', the
                // property is protected; otherwise, it's private.
                $visibilitySymbols[$i] = $nameParts[1] === '*' ? '#' : '-';
            }

            // Track the maximum property name length.
            assert(is_string($propNames[$i]));
            $propNameLen = mb_strlen($propNames[$i]);
            if ($propNameLen > $maxPropNameLen) {
                $maxPropNameLen = $propNameLen;
            }
        }

        // Generate the property => value pairs.
        $pairs = [];
        foreach ($propNames as $i => $propName) {
            assert(is_string($propName));
            $paddedPropName = $prettyPrint ? mb_str_pad($propName, $maxPropNameLen) : $propName;
            $valueStr = self::stringify($values[$i], $prettyPrint, $indentLevel + 1);
            $pairs[] = $visibilitySymbols[$i] . $paddedPropName . ' => ' . $valueStr;
        }

        // If pretty print, return string formatted with new lines and indentation.
        if ($prettyPrint) {
            $result = "$class {\n";
            foreach ($pairs as $pair) {
                $result .= $itemIndent . $pair . ",\n";
            }
            return $result . $bracketIndent . '}';
        }

        // Return string without newlines or extra spaces.
        return "$class {" . implode(', ', $pairs) . '}';
    }

    /**
     * Get a short string representation of the given value for use in error messages, log messages, and the like.
     *
     * @param mixed $value The value to get the string representation for.
     * @param int $maxLen The maximum length of the result.
     * @return string The short string representation.
     * @throws DomainException If the maximum length is less than the minimum, or if the value cannot be stringified.
     */
    public static function abbrev(mixed $value, int $maxLen = 30): string
    {
        // Check the max length is reasonable.
        $minMaxLen = 10;
        if ($maxLen < $minMaxLen) {
            throw new DomainException("The maximum string length must be at least $minMaxLen.");
        }

        // Get the value as a string without newlines or indentation.
        $result = self::stringify($value);

        // Trim if necessary.
        if (mb_strlen($result) > $maxLen) {
            $result = mb_substr($result, 0, $maxLen - 3) . '...';
        }

        return $result;
    }

    /**
     * Output a stringified value directly to the output stream.
     *
     * This is a convenience method that combines stringify() with echo, useful for debugging and quick output of
     * complex values.
     *
     * @param mixed $value The value to stringify and output.
     * @param bool $prettyPrint If the result should be nicely formatted.
     * @throws DomainException If the value cannot be stringified.
     */
    public static function print(mixed $value, bool $prettyPrint = false): void
    {
        echo self::stringify($value, $prettyPrint);
    }

    /**
     * Output a stringified value directly to the output stream.
     *
     * Same as print(), but adds a new line.
     *
     * @param mixed $value The value to stringify and output.
     * @param bool $prettyPrint If the result should be nicely formatted.
     * @throws DomainException If the value cannot be stringified.
     */
    public static function println(mixed $value, bool $prettyPrint = false): void
    {
        echo self::stringify($value, $prettyPrint), "\n";
    }

    // endregion
}
