<?php

declare(strict_types=1);

namespace FC\Router;

use Assert\Assert;
use Psr\Http\Server\MiddlewareInterface;

class Route implements RouteInterface
{
    /**
     * @var string|null
     */
    protected ?string $name = null;

    /**
     * @var array<class-string<MiddlewareInterface>>
     */
    protected array $middlewares = [];

    /**
     * @param string $identifier
     * @param string $method
     * @param string $pattern
     * @param string $handler
     * @param array $expressions
     * @phpstan-param array<array<string|array<string>>> $expressions
     */
    public function __construct(
        protected string $identifier,
        protected string $method,
        protected string $pattern,
        protected string $handler,
        protected array $expressions = []
    ) {
        Assert::that($identifier)->notEmpty('Identifier should not be empty');
        Assert::that($this->method)->notEmpty('Method should not be empty');
        Assert::that($pattern)->notEmpty('Pattern should not be empty');
        Assert::that($handler)->notEmpty('Handler should not be empty');

        $this->name = $identifier;
    }

    /**
     * {@inheritDoc}
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * {@inheritDoc}
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return $this->name ?? $this->identifier;
    }

    /**
     * {@inheritDoc}
     */
    public function setName(?string $name): RouteInterface
    {
        Assert::that($name)->nullOr()->notEmpty();

        $this->name = $name;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getPattern(): string
    {
        return $this->pattern;
    }

    /**
     * {@inheritDoc}
     */
    public function getExpressions(): array
    {
        return $this->expressions;
    }

    /**
     * {@inheritDoc}
     */
    public function getHandler(): string
    {
        return $this->handler;
    }

    /**
     * {@inheritDoc}
     */
    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

    /**
     * {@inheritDoc}
     */
    public function setMiddlewares(array $middlewares): RouteInterface
    {
        Assert::thatAll($middlewares)->implementsInterface(MiddlewareInterface::class);

        $this->middlewares = $middlewares;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function addMiddleware(string $middleware): RouteInterface
    {
        Assert::that($middleware)->implementsInterface(MiddlewareInterface::class);

        $this->middlewares[] = $middleware;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function __serialize(): array
    {
        return [
            $this->name,
            $this->identifier,
            $this->method,
            $this->pattern,
            $this->handler,
            $this->expressions,
            $this->middlewares,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function __unserialize(array $data): void
    {
        [
            $this->name,
            $this->identifier,
            $this->method,
            $this->pattern,
            $this->handler,
            $this->expressions,
            $this->middlewares,
        ] = $data;
    }
}
