<?php

declare(strict_types=1);

namespace FC\Router;

use FC\Router\Generator\UrlGeneratorInterface;
use FC\Router\Matcher\UriMatcherInterface;

interface RouterInterface extends UriMatcherInterface, UrlGeneratorInterface
{
    /**
     * @return RouteCollectionInterface
     */
    public function getRouteCollection(): RouteCollectionInterface;
}
