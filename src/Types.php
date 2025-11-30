<?php

declare(strict_types=1);

namespace Galaxon\Core;

use TypeError;
use ValueError;

/**
 * Convenience methods for working with types.
 */
final class Types
{
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

    // region Type inspection

    /**
     * Check if a value is a number, i.e. an integer or a float.
     * This varies from is_numeric(), which also returns true for numeric strings.
     *
     * @param mixed $value The value to check.
     * @return bool True if the value is a number, false otherwise.
     */
    public static function isNumber(mixed $value): bool
    {
        return is_int($value) || is_float($value);
    }

    /**
     * Check if a value is an unsigned integer.
     *
     * @param mixed $value The value to check.
     * @return bool True if the value is an unsigned integer, false otherwise.
     */
    public static function isUint(mixed $value): bool
    {
        return is_int($value) && $value >= 0;
    }

    // endregion

    // region Miscellaneous

    /**
     * Get the basic type of a value.
     *
     * Result will be a string, one of:
     * - null
     * - bool
     * - int
     * - float
     * - string
     * - array
     * - object
     * - resource
     * - unknown
     *
     * @param mixed $value The value to get the type of.
     * @return string The basic type of the value.
     */
    public static function getBasicType(mixed $value): string
    {
        // Try get_debug_type() first as this returns the new, canonical type names.
        $type = get_debug_type($value);
        if (in_array($type, ['null', 'bool', 'int', 'float', 'string', 'array'], true)) {
            return $type;
        }

        // Call gettype() and return the first word, which should be "object", "resource", or "unknown".
        // NB: The documentation for get_debug_type() has no equivalent for "unknown type", so this may never occur.
        $type = gettype($value);
        return explode(' ', $type)[0];
    }

    /**
     * Convert any PHP value into a unique string.
     *
     * The intended use case is a key in a collection like Set or Dictionary.
     *
     * @param mixed $value The value to convert.
     * @return string The unique string key.
     */
    public static function getUniqueString(mixed $value): string
    {
        switch (self::getBasicType($value)) {
            case 'null':
                return 'n';

            case 'bool':
                return 'b:' . ($value ? 'T' : 'F');

            case 'int':
                /** @var int $value */
                return 'i:' . $value;

            case 'float':
                /** @var float $value */
                return 'f:' . Floats::toHex($value);

            case 'string':
                /** @var string $value */
                return 's:' . strlen($value) . ":$value";

            case 'array':
                /** @var mixed[] $value */
                return 'a:' . count($value) . ':' . Stringify::stringifyArray($value);

            case 'object':
                /** @var object $value */
                return 'o:' . spl_object_id($value);

            case 'resource':
                /** @var resource $value */
                return 'r:' . get_resource_id($value);

            // @codeCoverageIgnoreStart
            default:
                return throw new TypeError('Value has unknown type.');
            // @codeCoverageIgnoreEnd
        }
    }

    // endregion

    // region Errors

    /**
     * Create a new TypeError using information about the parameter and expected type.
     *
     * @param string $varName The name of the argument or variable that failed validation, e.g. 'index'.
     * @param string $expectedType The expected type (e.g., 'int', 'string', 'callable').
     * @param mixed $value The actual value that was provided (optional).
     */
    public static function createError(string $varName, string $expectedType, mixed $value = null): TypeError
    {
        $message = "Variable '$varName' must be of type $expectedType";

        if (func_num_args() > 2) {
            $actualType = get_debug_type($value);
            $message .= ", $actualType given.";
        } else {
            $message .= '.';
        }

        return new TypeError($message);
    }

    // endregion

    // region Traits

    /**
     * Check if an object or class uses a given trait.
     * Handle both class names and objects, including trait inheritance.
     *
     * @param object|string $objOrClass The object or class to inspect.
     * @param string $trait The trait to check for.
     * @return bool True if the object or class uses the trait, false otherwise.
     */
    public static function usesTrait(object|string $objOrClass, string $trait): bool
    {
        $allTraits = self::getTraits($objOrClass);
        return in_array($trait, $allTraits, true);
    }

    /**
     * Get all traits used by an object, class, interface, or trait, including those inherited from parent classes and
     * other traits.
     *
     * @param object|string $objOrClass The object or class (or interface or trait) to inspect.
     * @return string[] The list of traits used by the object or class.
     * @throws ValueError If the provided class name is invalid.
     */
    public static function getTraits(object|string $objOrClass): array
    {
        // Get class, interface, or trait name.
        if (is_object($objOrClass)) {
            $class = get_class($objOrClass);
        } elseif (class_exists($objOrClass) || interface_exists($objOrClass) || trait_exists($objOrClass)) {
            $class = (string)$objOrClass;
        } else {
            throw new ValueError("Invalid class name: $objOrClass");
        }

        return self::getTraitsRecursive($class);
    }

    /**
     * Get all traits used by a class, interface, or trait, including parent classes and trait inheritance.
     *
     * @param string $class The class, interface, or trait to inspect.
     * @return string[] The list of traits used by the type.
     */
    private static function getTraitsRecursive(string $class): array
    {
        // Collection for traits.
        $traits = [];

        // Get traits from current class and all parent classes.
        do {
            // Get traits used by the current class.
            $classTraits = class_uses($class);

            // Check for class not found. Should be never, but having this check satisfies phpstan.
            if ($classTraits === false) {
                break; // @codeCoverageIgnore
            }

            // Add traits from current class.
            $traits = array_merge($traits, $classTraits);

            // Also get traits used by the traits themselves.
            foreach ($classTraits as $trait) {
                $traitTraits = self::getTraitsRecursive($trait);
                $traits = array_merge($traits, $traitTraits);
            }
        } while ($class = get_parent_class($class));

        return array_unique($traits);
    }

    // endregion
}
