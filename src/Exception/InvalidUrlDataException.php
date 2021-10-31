<?php

declare(strict_types=1);

namespace FC\Router\Exception;

use JetBrains\PhpStorm\Pure;

final class InvalidUrlDataException extends \InvalidArgumentException implements RouteException
{
    /**
     * @param string $message
     * @return static
     */
    #[Pure]
    public static function new(string $message): self
    {
        return new self($message);
    }
}
