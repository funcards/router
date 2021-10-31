<?php

declare(strict_types=1);

namespace FC\Router\Collector;

use FC\Router\Route;
use FC\Router\RouteInterface;
use FastRoute\RouteCollector as FastRouteCollector;

class RouteCollector extends FastRouteCollector implements RouteCollectorInterface
{
    protected const IDENTIFIER_PREFIX = 'route';

    /**
     * @var array<string, RouteInterface>
     */
    protected array $routes = [];

    /**
     * @var int
     */
    protected int $routeCounter = 0;

    /**
     * {@inheritDoc}
     */
    public function add(string $method, string $pattern, mixed $handler): RouteInterface
    {
        $this->addRoute($method, $pattern, $handler);

        return $this->routes[$this->identifier($this->routeCounter - 1)];
    }

    /**
     * {@inheritDoc}
     */
    public function group(string $prefix, callable $callback): RouteCollectorInterface
    {
        $this->addGroup($prefix, $callback);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    /**
     * {@inheritDoc}
     */
    public function addRoute($httpMethod, $route, $handler): void
    {
        $pattern = \sprintf('%s/%s', \rtrim($this->currentGroupPrefix, '/'), \ltrim($route, '/'));
        $expressions = $this->routeParser->parse($pattern);
        $data = \array_reverse($expressions);

        foreach ((array)$httpMethod as $method) {
            $route = $this->createRoute($method, $pattern, $handler, $data);
            $this->routes[$route->getIdentifier()] = $route;

            foreach ($expressions as $expression) {
                $this->dataGenerator->addRoute($method, $expression, $route->getIdentifier());
            }
        }
    }

    /**
     * @param string $method
     * @param string $pattern
     * @param string $handler
     * @param array $expressions
     * @phpstan-param array<array<string|array<string>>> $expressions
     * @return RouteInterface
     */
    protected function createRoute(string $method, string $pattern, string $handler, array $expressions): RouteInterface
    {
        return new Route(
            $this->identifier($this->routeCounter++),
            $method,
            $pattern,
            $handler,
            $expressions
        );
    }

    /**
     * @param int $routeCounter
     * @return string
     */
    protected function identifier(int $routeCounter): string
    {
        return static::IDENTIFIER_PREFIX . $routeCounter;
    }
}
