<?php

declare(strict_types=1);

namespace FC\Router\Collector;

use FC\Router\RouteInterface;

interface RouteCollectorInterface
{
    /**
     * @param string $method
     * @param string $pattern
     * @param mixed $handler
     * @return RouteInterface
     */
    public function add(string $method, string $pattern, mixed $handler): RouteInterface;

    /**
     * @param string $prefix
     * @param callable $callback
     * @return RouteCollectorInterface
     */
    public function group(string $prefix, callable $callback): RouteCollectorInterface;

    /**
     * @return array<string, RouteInterface>
     */
    public function getRoutes(): array;

    /**
     * Returns the collected route data, as provided by the data generator.
     *
     * @return array<mixed>
     */
    public function getData();
}
