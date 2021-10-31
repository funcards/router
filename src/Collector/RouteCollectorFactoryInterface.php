<?php

declare(strict_types=1);

namespace FC\Router\Collector;

interface RouteCollectorFactoryInterface
{
    /**
     * @return RouteCollectorInterface
     */
    public function create(): RouteCollectorInterface;
}
