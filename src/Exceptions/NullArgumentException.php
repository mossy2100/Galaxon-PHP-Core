<?php

declare(strict_types=1);

namespace Galaxon\Core\Exceptions;

use InvalidArgumentException;
use Throwable;

/**
 * Exception thrown when a required argument is null.
 *
 * This exception is used when a nullable parameter receives null but the method cannot proceed
 * without a value. It signals a programming error at the call site rather than a domain logic issue.
 */
class NullArgumentException extends InvalidArgumentException
{
    /**
     * Create a new NullArgumentException.
     *
     * @param string $paramName The name of the parameter that was null.
     * @param string $message Optional custom message. If empty, a default message is generated.
     * @param int $code The exception code.
     * @param Throwable|null $previous The previous throwable used for exception chaining.
     */
    public function __construct(
        public readonly string $paramName,
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null
    ) {
        if ($message === '') {
            $message = "Argument '$paramName' must not be null.";
        }

        parent::__construct($message, $code, $previous);
    }
}
