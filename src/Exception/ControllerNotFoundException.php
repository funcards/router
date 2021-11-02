<?php

declare(strict_types=1);

namespace FC\Router\Exception;

use JetBrains\PhpStorm\Pure;
use Psr\Container\NotFoundExceptionInterface;

final class ControllerNotFoundException extends \InvalidArgumentException implements RouterException,
                                                                                     NotFoundExceptionInterface
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
