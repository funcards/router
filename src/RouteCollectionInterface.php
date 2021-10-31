<?php

declare(strict_types=1);

namespace FC\Router;

/**
 * @template-extends \IteratorAggregate<string, RouteInterface>
 */
interface RouteCollectionInterface extends \IteratorAggregate, \Countable
{
    /**
     * @return array<string, RouteInterface>
     */
    public function all(): array;

    /**
     * @param RouteInterface $route
     */
    public function add(RouteInterface $route): void;

    /**
     * @param string $name
     * @return RouteInterface|null
     */
    public function get(string $name): ?RouteInterface;

    /**
     * @param array<string>|string $name
     */
    public function remove(array|string $name): void;

    /**
     * Returns all the necessary state of the object for serialization purposes.
     *
     * @return array<mixed>
     */
    public function __serialize(): array;

    /**
     * Restores the object state from an array given by __serialize().
     *
     * @param array<mixed> $data
     */
    public function __unserialize(array $data): void;
}
