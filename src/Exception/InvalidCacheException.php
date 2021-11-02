<?php

declare(strict_types=1);

namespace FC\Router\Exception;

use JetBrains\PhpStorm\Pure;

final class InvalidCacheException extends \RuntimeException implements RouterException
{
    /**
     * @param string $cacheFile
     * @return static
     */
    #[Pure]
    public static function new(string $cacheFile): self
    {
        return new self(\sprintf('Invalid cache file "%s"', $cacheFile));
    }
}
