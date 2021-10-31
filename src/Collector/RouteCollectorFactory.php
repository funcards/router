<?php

declare(strict_types=1);

namespace FC\Router\Collector;

use FastRoute\DataGenerator\GroupCountBased;
use FastRoute\RouteParser\Std;
use JetBrains\PhpStorm\Pure;

class RouteCollectorFactory implements RouteCollectorFactoryInterface
{
    /**
     * {@inheritDoc}
     */
    #[Pure]
    public function create(): RouteCollectorInterface
    {
        return new RouteCollector(new Std(), new GroupCountBased());
    }
}
