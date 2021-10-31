<?php

declare(strict_types=1);

namespace FC\Router;

class RouteCollection implements RouteCollectionInterface
{
    /**
     * @var array<string, RouteInterface>
     */
    private array $routes = [];

    /**
     * @var array<string, string>
     */
    private array $identifiers = [];

    public function __clone(): void
    {
        foreach ($this->routes as $name => $route) {
            $this->routes[$name] = clone $route;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->all());
    }

    /**
     * {@inheritDoc}
     */
    public function count(): int
    {
        return \count($this->routes);
    }

    /**
     * {@inheritDoc}
     */
    public function all(): array
    {
        return $this->routes;
    }

    /**
     * {@inheritDoc}
     */
    public function add(RouteInterface $route): void
    {
        $this->routes[$route->getName()] = $route;
        $this->identifiers[$route->getIdentifier()] = $route->getName();
    }

    /**
     * {@inheritDoc}
     */
    public function get(string $name): ?RouteInterface
    {
        return $this->routes[$name] ?? $this->routes[$this->identifiers[$name] ?? ''] ?? null;
    }

    /**
     * {@inheritDoc}
     */
    public function remove(array|string $name): void
    {
        foreach ((array)$name as $n) {
            unset($this->routes[$n], $this->identifiers[$n]);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function __serialize(): array
    {
        return [$this->routes, $this->identifiers];
    }

    /**
     * {@inheritDoc}
     */
    public function __unserialize(array $data): void
    {
        [$this->routes, $this->identifiers] = $data;
    }
}
