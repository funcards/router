<?php

declare(strict_types=1);

namespace FC\Router\Exception;

use JetBrains\PhpStorm\Pure;

final class ControllerMethodNotFoundException extends \InvalidArgumentException implements RouterException
{
    /**
     * @param string $message
     * @param \Throwable|null $previous
     * @return static
     */
    #[Pure]
    public static function new(string $message, ?\Throwable $previous = null): self
    {
        return new self($message, previous: $previous);
    }
}
