<?php

declare(strict_types=1);

namespace FC\Router;

use Psr\Http\Server\MiddlewareInterface;

interface RouteInterface
{
    /**
     * @return string
     */
    public function getIdentifier(): string;

    /**
     * @return string
     */
    public function getMethod(): string;

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @param string|null $name
     * @return RouteInterface
     */
    public function setName(?string $name): RouteInterface;

    /**
     * @return string
     */
    public function getPattern(): string;

    /**
     * @return array
     * @phpstan-return array<array<string|array<string>>>
     */
    public function getExpressions(): array;

    /**
     * @return string
     */
    public function getHandler(): string;

    /**
     * @return class-string<MiddlewareInterface>[]
     */
    public function getMiddlewares(): array;

    /**
     * @param array<class-string<MiddlewareInterface>> $middlewares
     * @return RouteInterface
     */
    public function setMiddlewares(array $middlewares): RouteInterface;

    /**
     * @param class-string<MiddlewareInterface> $middleware
     * @return RouteInterface
     */
    public function addMiddleware(string $middleware): RouteInterface;

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
