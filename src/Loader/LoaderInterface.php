<?php

declare(strict_types=1);

namespace FC\Router\Loader;

use FC\Router\Collector\RouteCollectorInterface;

interface LoaderInterface
{
    /**
     * @param mixed $resource
     * @return RouteCollectorInterface
     */
    public function load(mixed $resource): RouteCollectorInterface;
}
