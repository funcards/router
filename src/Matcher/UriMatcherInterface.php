<?php

declare(strict_types=1);

namespace FC\Router\Matcher;

interface UriMatcherInterface
{
    /**
     * @param string $method
     * @param string $uri
     * @return Result
     */
    public function match(string $method, string $uri): Result;
}
