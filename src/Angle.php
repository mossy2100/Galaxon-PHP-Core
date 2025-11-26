<?php

declare(strict_types=1);

namespace Galaxon\Core;

use DivisionByZeroError;
use Override;
use Stringable;
use TypeError;
use ValueError;

class Angle implements Stringable, Equatable
{
    use Comparable;

    // region Constants

    // Define τ = 2π.
    public const float TAU = 2 * M_PI;

    /**
     * The default unit for angles (radians).
     * This is the base unit used for internal storage.
     */
    public const string DEFAULT_UNIT = 'rad';

    /**
     * Conversion factors from each unit to radians (the base unit).
     * All internal calculations use radians.
     *
     * Supported units:
     * - 'rad' = radians (base unit)
     * - 'deg' = degrees
     * - 'arcmin' = arcminutes
     * - 'arcsec' = arcseconds
     * - 'grad' = gradians
     * - 'turn' = turns (full rotations)
     */
    private const array CONVERSION_FACTORS = [
        'rad' => 1.0,
        'deg' => M_PI / 180,
        'arcmin' => M_PI / 10800,
        'arcsec' => M_PI / 648000,
        'grad' => M_PI / 200,
        'turn' => self::TAU
    ];

    // Epsilons for comparisons.
    public const float RAD_EPSILON = 1e-9;
    public const float TRIG_EPSILON = 1e-12;

    // Constants for use as smallest unit arguments.
    public const int UNIT_DEGREE = 0;
    public const int UNIT_ARCMINUTE = 1;
    public const int UNIT_ARCSECOND = 2;

    // endregion

    // region Properties

    /**
     * Internal storage in radians.
     *
     * @var float
     */
    private float $radians;

    // endregion

    // region Constructor and factory methods

    /**
     * Constructor.
     *
     * @param float $size The size of the angle in the given unit.
     * @param string $unit The unit of the angle. Valid units are 'rad' (default), 'deg', 'arcmin', 'arcsec', 'grad', or
     * 'turn'.
     * @throws ValueError If the size is non-finite (±∞ or NaN) or if the unit is invalid.
     */
    public function __construct(float $size, string $unit = self::DEFAULT_UNIT)
    {
        // Guards.
        if (!is_finite($size)) {
            throw new ValueError('Angle size cannot be ±∞ or NaN.');
        }
        self::checkIsUnitValid($unit);

        // Calculate the value of the angle in radians.
        $this->radians = self::convert($size, $unit);
    }

    /**
     * Create an angle from degrees, arcminutes, and arcseconds.
     *
     * NB: In theory all parts SHOULD be either non-negative (i.e. 0 or positive) or non-positive (i.e. 0 or negative).
     * However, this is not enforced. Neither do any of the values have to be within a certain range (e.g. 0-60 for
     * arcminutes or arcseconds).
     * Typically you'll want to use the same sign for all parts.
     *
     * @param float $degrees The degrees part.
     * @param float $arcmin The arcminutes part (optional).
     * @param float $arcsec The arcseconds part (optional).
     * @return self A new angle with a magnitude equal to the provided angle.
     * @throws ValueError If any of the arguments are non-finite numbers.
     * @example
     * If you want to convert -12° 34′ 56″ to degrees, call fromDMS(-12, -34, -56)
     * If you want to convert -12° 56″ to degrees, call fromDMS(-12, 0, -56).
     *
     */
    public static function fromDMS(float $degrees, float $arcmin = 0.0, float $arcsec = 0.0): self
    {
        // Compute the total degrees.
        $totalDeg = $degrees
                    + self::convert($arcmin, 'arcmin', 'deg')
                    + self::convert($arcsec, 'arcsec', 'deg');

        // The call to the constructor will throw a ValueError if any of the arguments are non-finite, because if that's
        // true, $totalDeg will also be non-finite.
        return new self($totalDeg, 'deg');
    }

    /**
     * Checks that the input string, which is meant to indicate an angle, is valid.
     *
     * Different units (deg, rad, grad, turn) are supported, as used in CSS.
     * There can be spaces between the number and the unit.
     * @see https://developer.mozilla.org/en-US/docs/Web/CSS/angle
     *
     * Symbols for degrees, arcminutes, and arcseconds are also supported.
     * There cannot be any space between a number and its unit, but it's ok to have a single space
     * between two parts.
     *
     * If valid, the angle is returned; otherwise, an exception is thrown.
     *
     * @param string $value The string to parse.
     * @return self A new angle equivalent to the provided string.
     * @throws ValueError If the string does not represent a valid angle.
     */
    public static function parse(string $value): self
    {
        // Prepare an error message with the original value.
        $errMsg = "The provided string '$value' does not represent a valid angle.";

        // Reject empty input.
        $value = trim($value);
        if ($value === '') {
            throw new ValueError($errMsg);
        }

        // Check for a format containing symbols for degrees, arcminutes, and arcseconds.
        $num = '(?:\d+(?:\.\d+)?|\.\d+)';
        $pattern = "/^(?:(?P<sign>[-+]?)\s*)?"
                   . "(?:(?P<deg>$num)°\s*)?"
                   . "(?:(?P<min>$num)[′']\s*)?"
                   . "(?:(?P<sec>$num)[″\"])?$/u";
        if (preg_match($pattern, $value, $matches)) {
            // Require at least one component (deg/min/sec).
            if (empty($matches['deg']) && empty($matches['min']) && empty($matches['sec'])) {
                throw new ValueError($errMsg);
            }

            // Get the sign.
            $sign = isset($matches['sign']) && $matches['sign'] === '-' ? -1 : 1;

            // Extract the parts.
            $d = isset($matches['deg']) ? $sign * (float)$matches['deg'] : 0.0;
            $m = isset($matches['min']) ? $sign * (float)$matches['min'] : 0.0;
            $s = isset($matches['sec']) ? $sign * (float)$matches['sec'] : 0.0;

            // Convert to angle.
            return self::fromDMS($d, $m, $s);
        }

        // Check for a format with CSS angle units.
        // Whitespace between the number and unit is permitted, and the unit is case-insensitive.
        if (preg_match("/^(-?$num)\s*(rad|deg|grad|turn)$/i", $value, $m)) {
            $num = (float)$m[1];
            $unit = strtolower($m[2]);
            return new self($num, $unit);
        }

        // Invalid format.
        throw new ValueError($errMsg);
    }

    // endregion

    // region Units and conversion

    /**
     * Get a list of all valid angle units.
     *
     * @return string[] Array of valid unit strings.
     */
    public static function validUnits(): array
    {
        return array_keys(self::CONVERSION_FACTORS);
    }

    /**
     * Check if a unit string is valid.
     *
     * @param string $unit The unit to check.
     * @return bool True if the unit is valid, false otherwise.
     */
    public static function isUnitValid(string $unit): bool
    {
        return array_key_exists($unit, self::CONVERSION_FACTORS);
    }

    /**
     * Validate that a unit is valid, throwing an exception if not.
     *
     * @param string $unit The unit to validate.
     * @throws ValueError If the unit is not valid.
     */
    private static function checkIsUnitValid(string $unit): void
    {
        if (!self::isUnitValid($unit)) {
            $validUnits = implode(', ', array_map(static fn($unit) => "'$unit'", self::validUnits()));
            throw new ValueError("Invalid unit '$unit'. Valid units: $validUnits.");
        }
    }

    /**
     * Get the conversion factor from one unit to another.
     *
     * The conversion factor is the multiplier needed to convert a value from the source unit to the destination unit.
     * For example, getConversionFactor('deg', 'arcmin') returns 60.0 because 1 degree = 60 arcminutes.
     *
     * @param string $fromUnit The source unit.
     * @param string $toUnit The destination unit.
     * @return float The conversion factor.
     * @throws ValueError If either unit is invalid.
     */
    public static function getConversionFactor(string $fromUnit, string $toUnit): float
    {
        // Check the arguments.
        self::checkIsUnitValid($fromUnit);
        self::checkIsUnitValid($toUnit);

        // Shortcuts.
        if ($fromUnit === $toUnit) {
            return 1.0;
        }
        if ($toUnit === self::DEFAULT_UNIT) {
            return self::CONVERSION_FACTORS[$fromUnit];
        }

        // Calculate the conversion factor.
        return self::CONVERSION_FACTORS[$fromUnit] / self::CONVERSION_FACTORS[$toUnit];
    }

    /**
     * Convert a value from one unit to another.
     *
     * @param float $value The value to convert.
     * @param string $fromUnit The source unit (default: 'rad').
     * @param string $toUnit The destination unit (default: 'rad').
     * @return float The converted value.
     * @throws ValueError If either unit is invalid.
     */
    public static function convert(
        float $value,
        string $fromUnit = self::DEFAULT_UNIT,
        string $toUnit = self::DEFAULT_UNIT
    ): float {
        return $value * self::getConversionFactor($fromUnit, $toUnit);
    }

    // endregion

    // region Methods for getting the angle in different units

    /**
     * Get the angle size in the given units.
     *
     * @param string $unit The unit to convert the angle to. Valid units are 'rad' (default), 'deg', 'arcmin', 'arcsec',
     * 'grad', or 'turn'.
     * @return float The angle size in the specified unit.
     * @throws ValueError If the unit is not valid.
     */
    public function to(string $unit = self::DEFAULT_UNIT): float
    {
        return self::convert($this->radians, toUnit: $unit);
    }

    /**
     * Get the angle in degrees, arcminutes, and arcseconds.
     * The result will be an array with 1-3 values, depending on the requested smallest unit.
     * Only the last item may have a fractional part; others will be whole numbers.
     *
     * If the angle is positive, the resulting values will all be positive.
     * If the angle is zero, the resulting values will all be zero.
     * If the angle is negative, the resulting values will all be negative.
     *
     * For the $smallestUnit parameter, you can use the UNIT_* class constants, i.e.
     * - UNIT_DEGREE for degrees only
     * - UNIT_ARCMINUTE for degrees and arcminutes
     * - UNIT_ARCSECOND for degrees, arcminutes, and arcseconds
     *
     * @param int $smallestUnit 0 for degree, 1 for arcminute, 2 for arcsecond (default).
     * @return float[] An array of 1-3 floats with the degrees, arcminutes, and arcseconds.
     * @throws ValueError If $smallestUnit is not 0, 1, or 2.
     */
    public function toDMS(int $smallestUnit = self::UNIT_ARCSECOND): array
    {
        $totalDeg = $this->to('deg');
        $sign = Numbers::sign($totalDeg, false);
        $totalDeg = abs($totalDeg);

        switch ($smallestUnit) {
            case self::UNIT_DEGREE:
                $d = $totalDeg;

                // Apply sign and normalize -0.0 to 0.0.
                $d = Floats::normalizeZero($d * $sign);

                return [$d];

            case self::UNIT_ARCMINUTE:
                // Convert the total degrees to degrees and minutes (non-negative).
                $d = floor($totalDeg);
                $m = self::convert($totalDeg - $d, 'deg', 'arcmin');

                // Apply sign and normalize -0.0 to 0.0.
                $d = Floats::normalizeZero($d * $sign);
                $m = Floats::normalizeZero($m * $sign);

                return [$d, $m];

            case self::UNIT_ARCSECOND:
                // Convert the total degrees to degrees, minutes, and seconds (non-negative).
                $d = floor($totalDeg);
                $fMin = self::convert($totalDeg - $d, 'deg', 'arcmin');
                $m = floor($fMin);
                $s = self::convert($fMin - $m, 'arcmin', 'arcsec');

                // Apply sign and normalize -0.0 to 0.0.
                $d = Floats::normalizeZero($d * $sign);
                $m = Floats::normalizeZero($m * $sign);
                $s = Floats::normalizeZero($s * $sign);

                return [$d, $m, $s];

            default:
                throw new ValueError(
                    'The smallest unit must be 0 for degree, 1 for arcminute, or 2 for arcsecond (default).'
                );
        }
    }

    // endregion

    // region Arithmetic methods

    /**
     * Add another angle to this angle.
     *
     * @param self $other The angle to add.
     * @return self The sum as a new angle.
     */
    public function add(self $other): self
    {
        return new self($this->radians + $other->radians);
    }

    /**
     * Subtract another angle from this angle.
     *
     * @param self $other The angle to subtract.
     * @return self The difference as a new angle.
     */
    public function sub(self $other): self
    {
        return new self($this->radians - $other->radians);
    }

    /**
     * Multiply this angle by a factor.
     *
     * @param float $k The scale factor.
     * @return self The scaled angle.
     * @throws ValueError If the multiplier is a non-finite number.
     */
    public function mul(float $k): self
    {
        // Guard.
        if (!is_finite($k)) {
            throw new ValueError('Multiplier cannot be ±∞ or NaN.');
        }

        return new self($this->radians * $k);
    }

    /**
     * Divide this angle by a factor.
     *
     * @param float $k The scale factor.
     * @return self The scaled angle.
     * @throws DivisionByZeroError If the divisor is 0.
     * @throws ValueError If the divisor is a non-finite number.
     */
    public function div(float $k): self
    {
        // Guards.
        if ($k === 0.0) {
            throw new DivisionByZeroError('Divisor cannot be 0.');
        }
        if (!is_finite($k)) {
            throw new ValueError('Divisor cannot be ±∞ or NaN.');
        }

        return new self(fdiv($this->radians, $k));
    }

    /**
     * Get the absolute value of this angle.
     *
     * @return self A new angle with a non-negative magnitude.
     */
    public function abs(): self
    {
        return new self(abs($this->radians));
    }

    /**
     * Normalize an angle to a standard range.
     *
     * If $signed is true (default), the range is (-π, π]
     * If $signed is false, the range is [0, τ)
     *
     * @param bool $signed If true, wrap to the signed range; otherwise wrap to the unsigned range.
     * @return self A new angle with the wrapped value.
     *
     * @example
     * $alpha = Angle::fromRadians(M_PI * 5);
     * $wrapped = $alpha->wrap();
     */
    public function wrap(bool $signed = true): self
    {
        return new self(self::wrapRadians($this->radians, $signed));
    }

    // endregion

    // region Comparison methods

    /**
     * Compare angles by their raw numeric values.
     *
     * Compares angles as numerical values without normalization. This means 360° > 0° even though they represent the
     * same angular position.
     * If you need to compare angular positions (where 0° = 360°), normalize both angles using wrap() before comparing.
     *
     * @param mixed $other The value to compare with.
     * @return int -1 if this < other, 0 if equal, 1 if this > other.
     * @throws TypeError If the value to compare with is not an Angle.
     *
     * @example
     * $a = Angle::fromDegrees(10);
     * $b = Angle::fromDegrees(350);
     * $a->compare($b); // -1 (10 < 350)
     *
     * // To compare as positions (accounting for wraparound):
     * $a->wrap()->compare($b->wrap()); // Still -1 (10 < 350 in unsigned range)
     * $a->wrap(true)->compare($b->wrap(true)); // 1 (10 > -10 in signed range)
     */
    #[Override]
    public function compare(mixed $other): int
    {
        // Check we're comparing two Angles.
        if (!$other instanceof self) {
            throw new TypeError('Object to compare with must be an Angle.');
        }

        // Check for equality within a reasonable tolerance.
        if (Floats::approxEqual($this->radians, $other->radians, self::RAD_EPSILON)) {
            return 0;
        }

        // Check for less than or greater than.
        return $this->radians < $other->radians ? -1 : 1;
    }

    // endregion

    // region Trigonometric methods

    /**
     * Sine of the angle.
     *
     * @return float The sine value.
     */
    public function sin(): float
    {
        return sin($this->radians);
    }

    /**
     * Cosine of the angle.
     *
     * @return float The cosine value.
     */
    public function cos(): float
    {
        return cos($this->radians);
    }

    /**
     * Tangent of the angle.
     *
     * @return float The tangent value.
     */
    public function tan(): float
    {
        $s = sin($this->radians);
        $c = cos($this->radians);

        // If cos is effectively zero, return ±INF (sign chosen by the side, i.e., sign of sine).
        // The built-in tan() function normally doesn't ever return ±INF.
        if (Floats::approxEqual($c, 0, self::TRIG_EPSILON)) {
            return Numbers::copySign(INF, $s);
        }

        // Otherwise do IEEE‑754 division (no warnings/exceptions).
        return fdiv($s, $c);
    }

    /**
     * Secant of the angle (1/cos).
     *
     * @return float The secant value.
     */
    public function sec(): float
    {
        $c = cos($this->radians);

        // If cos is effectively zero, return ±INF.
        if (Floats::approxEqual($c, 0, self::TRIG_EPSILON)) {
            return Numbers::copySign(INF, $c);
        }

        return fdiv(1.0, $c);
    }

    /**
     * Cosecant of the angle (1/sin).
     *
     * @return float The cosecant value.
     */
    public function csc(): float
    {
        $s = sin($this->radians);

        // If sin is effectively zero, return ±INF.
        if (Floats::approxEqual($s, 0, self::TRIG_EPSILON)) {
            return Numbers::copySign(INF, $s);
        }

        return fdiv(1.0, $s);
    }

    /**
     * Cotangent of the angle (cos/sin).
     *
     * @return float The cotangent value.
     */
    public function cot(): float
    {
        $s = sin($this->radians);
        $c = cos($this->radians);

        // If sin is effectively zero, return ±INF.
        if (Floats::approxEqual($s, 0, self::TRIG_EPSILON)) {
            return Numbers::copySign(INF, $c);
        }

        return fdiv($c, $s);
    }

    // endregion

    // region Hyperbolic methods

    /**
     * Get the hyperbolic sine of the angle.
     *
     * @return float The hyperbolic sine value.
     */
    public function sinh(): float
    {
        return sinh($this->radians);
    }

    /**
     * Get the hyperbolic cosine of the angle.
     *
     * @return float The hyperbolic cosine value.
     */
    public function cosh(): float
    {
        return cosh($this->radians);
    }

    /**
     * Get the hyperbolic tangent of the angle.
     *
     * @return float The hyperbolic tangent value.
     */
    public function tanh(): float
    {
        return tanh($this->radians);
    }

    /**
     * Get the hyperbolic secant of the angle (1/cosh).
     *
     * @return float The hyperbolic secant value.
     */
    public function sech(): float
    {
        return fdiv(1.0, cosh($this->radians));
    }

    /**
     * Get the hyperbolic cosecant of the angle (1/sinh).
     *
     * @return float The hyperbolic cosecant value.
     */
    public function csch(): float
    {
        $sh = sinh($this->radians);

        // sinh(0) = 0, so return ±INF for values near zero.
        if (Floats::approxEqual($sh, 0, self::TRIG_EPSILON)) {
            return Numbers::copySign(INF, $sh);
        }

        return fdiv(1.0, $sh);
    }

    /**
     * Get the hyperbolic cotangent of the angle (cosh/sinh).
     *
     * @return float The hyperbolic cotangent value.
     */
    public function coth(): float
    {
        $sh = sinh($this->radians);

        // sinh(0) = 0, so return ±INF for values near zero.
        if (Floats::approxEqual($sh, 0, self::TRIG_EPSILON)) {
            return Numbers::copySign(INF, cosh($this->radians));
        }

        return fdiv(cosh($this->radians), $sh);
    }

    // endregion

    // region Static wrap methods

    /**
     * Normalize a scalar angle value into a specified half-open interval.
     *
     * This is a private method called from the public wrap[Unit]() methods.
     *
     * The range of values varies depending on the $unitsPerTurn parameter *and* the $signed flag.
     * 1. If $signed is true (default), the range is (-$unitsPerTurn/2, $unitsPerTurn/2]
     * NB: This means the minimum value is *excluded* in the range, while the maximum value is *included*.
     * 2. If $signed is false, the range is [0, $unitsPerTurn)
     * NB: This means the minimum value is *included* in the range, while the maximum value is *excluded*.
     * @see https://en.wikipedia.org/wiki/Principal_value#Complex_argument
     *
     * @param float $value The value to wrap.
     * @param float $unitsPerTurn Units per full turn (e.g., τ for radians, 360 for degrees, 400 for gradians).
     * @param bool $signed If true, wrap to the signed range; otherwise wrap to the unsigned range.
     * @return float The wrapped value.
     * @throws ValueError If the $value argument is non-finite.
     */
    private static function wrapAngle(float $value, float $unitsPerTurn, bool $signed = true): float
    {
        // Guard.
        if (!is_finite($value)) {
            throw new ValueError('Value must be finite.');
        }

        // Reduce using fmod to avoid large magnitudes.
        // $r will be in the range [0, $unitsPerTurn) if $value is positive, or (-$unitsPerTurn, 0] if negative.
        $r = fmod($value, $unitsPerTurn);

        // Adjust to fit within range bounds.
        // The value may be outside the range due to the sign of $value or the value of $signed.
        if ($signed) {
            // Signed range is (-$half, $half]
            $half = $unitsPerTurn / 2.0;
            if ($r <= -$half) {
                $r += $unitsPerTurn;
            } elseif ($r > $half) {
                $r -= $unitsPerTurn;
            }
        } else {
            // Unsigned range is [0, $unitsPerTurn)
            if ($r < 0.0) {
                $r += $unitsPerTurn;
            }
        }

        // Canonicalize -0.0 to 0.0.
        return Floats::normalizeZero($r);
    }

    /**
     * Normalize radians into [0, τ) or (-π, π].
     *
     * @param float $radians The angle in radians.
     * @param bool $signed If true, wrap to the signed range; otherwise wrap to the unsigned range.
     * @return float The normalized angle in radians.
     */
    public static function wrapRadians(float $radians, bool $signed = true): float
    {
        return self::wrapAngle($radians, self::TAU, $signed);
    }

    /**
     * Normalize degrees into [0, 360) or (-180, 180].
     *
     * @param float $degrees The angle in degrees.
     * @param bool $signed If true, wrap to the signed range; otherwise wrap to the unsigned range.
     * @return float The normalized angle in degrees.
     */
    public static function wrapDegrees(float $degrees, bool $signed = true): float
    {
        return self::wrapAngle($degrees, self::getConversionFactor('turn', 'deg'), $signed);
    }

    /**
     * Normalize gradians into [0, 400) or (-200, 200].
     *
     * @param float $gradians The angle in gradians.
     * @param bool $signed If true, wrap to the signed range; otherwise wrap to the unsigned range.
     * @return float The normalized angle in gradians.
     */
    public static function wrapGradians(float $gradians, bool $signed = true): float
    {
        return self::wrapAngle($gradians, self::getConversionFactor('turn', 'grad'), $signed);
    }

    /**
     * Normalize turns into [0, 1) or (-0.5, 0.5].
     *
     * @param float $turns The angle in turns.
     * @param bool $signed If true, wrap to the signed range; otherwise wrap to the unsigned range.
     * @return float The normalized angle in turns.
     */
    public static function wrapTurns(float $turns, bool $signed = true): float
    {
        return self::wrapAngle($turns, 1, $signed);
    }

    // endregion

    // region String-related methods

    /**
     * Format a float with an optional number of decimal places.
     *
     * NB: This is a private method called from format().
     * It will not throw an exception on invalid input, as the arguments are assumed to be already validated in calling
     * methods.
     *
     * @param float $value The value to format.
     * @param ?int $decimals Number of decimal places to show, or null for the maximum (with no trailing zeros).
     * @return string The formatted string.
     */
    private static function formatFloat(float $value, ?int $decimals = null): string
    {
        // Canonicalize -0.0 to 0.0.
        $value = Floats::normalizeZero($value);

        // If the number of decimal places is specified, format with exactly that many decimal places.
        // If the number of decimal places isn't specified, use the max float precision, then trim off any trailing
        // 0's or decimal point.
        return $decimals !== null
            ? sprintf("%.{$decimals}F", $value)
            : rtrim(sprintf('%.17F', $value), '.0');
    }

    /**
     * Format a given angle with degrees symbol, plus optional arcminutes and arcseconds.
     *
     * @param int $smallestUnit 0 for degrees, 1 for arcminutes, 2 for arcseconds (default).
     * @param ?int $decimals Optional number of decimal places for the smallest unit.
     * @return string The degrees, arcminutes, and arcseconds nicely formatted as a string.
     * @throws ValueError If the smallest unit argument is not 0, 1, or 2.
     * @example
     * $alpha = Angle::fromDegrees(12.3456789);
     * echo $alpha->formatDMS(UNIT_DEGREE);    // 12.3456789°
     * echo $alpha->formatDMS(UNIT_ARCMINUTE); // 12° 20.740734′
     * echo $alpha->formatDMS(UNIT_ARCSECOND); // 12° 20′ 44.44404″
     *
     * For the $smallestUnit parameter, you can use the UNIT class constants, i.e.
     * - UNIT_DEGREE for degrees only
     * - UNIT_ARCMINUTE for degrees and arcminutes
     * - UNIT_ARCSECOND for degrees, arcminutes, and arcseconds
     */
    public function formatDMS(int $smallestUnit = self::UNIT_ARCSECOND, ?int $decimals = null): string
    {
        // Get the sign string.
        $sign = $this->radians < 0 ? '-' : '';

        // Convert to degrees, with optional arcminutes and/or arcseconds.
        $parts = $this->abs()->toDMS($smallestUnit);

        switch ($smallestUnit) {
            case self::UNIT_DEGREE:
                [$d] = $parts;
                $strDeg = self::formatFloat($d, $decimals);
                return "$sign{$strDeg}°";

            case self::UNIT_ARCMINUTE:
                [$d, $m] = $parts;

                // Round the smallest unit if requested.
                if ($decimals !== null) {
                    $m = round($m, $decimals);

                    // Handle floating-point drift and carry.
                    if ($m >= self::getConversionFactor('deg', 'arcmin')) {
                        $m = 0.0;
                        $d += 1.0;
                    }
                }

                $strMin = self::formatFloat($m, $decimals);
                return "$sign{$d}° {$strMin}′";

            case self::UNIT_ARCSECOND:
                [$d, $m, $s] = $parts;

                // Round the smallest unit if requested.
                if ($decimals !== null) {
                    $s = round($s, $decimals);

                    // Handle floating-point drift and carry.
                    if ($s >= self::getConversionFactor('arcmin', 'arcsec')) {
                        $s = 0.0;
                        $m += 1.0;
                    }
                    if ($m >= self::getConversionFactor('deg', 'arcmin')) {
                        $m = 0.0;
                        $d += 1.0;
                    }
                }

                $strSec = self::formatFloat($s, $decimals);
                return "$sign{$d}° {$m}′ {$strSec}″";

            // @codeCoverageIgnoreStart
            default:
                throw new ValueError(
                    'The smallest unit must be 0 for degree, 1 for arcminute, or 2 for arcsecond (default).'
                );
            // @codeCoverageIgnoreEnd
        }
    }

    /**
     * Format the angle as a CSS-style angle string, with no space between, e.g. '1.23rad' or '12.5deg'.
     *
     * @param string $unit The unit of the angle.
     * @param ?int $decimals Optional number of decimal places to express.
     * @return string The angle as a string.
     * @throws ValueError If $unit is not one of the supported units or $decimals is negative.
     */
    public function format(string $unit = self::DEFAULT_UNIT, ?int $decimals = null): string
    {
        // Guard.
        if ($decimals !== null && $decimals < 0) {
            throw new ValueError('Decimals must be non-negative or null.');
        }

        // Convert the angle to the specified units. This will throw a ValueError if the unit is invalid.
        $size = $this->to($unit);

        // Return the formatted string.
        return self::formatFloat($size, $decimals) . $unit;
    }

    /**
     * Return the angle as a string, showing the units in radians using CSS notation.
     *
     * @return string The angle as a string.
     */
    #[Override]
    public function __toString(): string
    {
        return $this->format();
    }

    // endregion
}
