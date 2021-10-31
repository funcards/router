<?php

declare(strict_types=1);

namespace FC\Router\Matcher;

use Assert\Assert;
use FC\Router\RouteInterface;
use JetBrains\PhpStorm\ExpectedValues;

class Result
{
    public const NOT_FOUND = 0;
    public const FOUND = 1;
    public const METHOD_NOT_ALLOWED = 2;

    /**
     * @param int $status
     * @param string $method
     * @param string $uri
     * @param string|null $identifier
     * @param array<int|string, string> $arguments
     * @param RouteInterface|null $route
     * @param array<string> $allowedMethods
     */
    public function __construct(
        #[ExpectedValues(valuesFromClass: self::class)] protected int $status,
        protected string $method,
        protected string $uri,
        protected ?string $identifier = null,
        protected array $arguments = [],
        protected ?RouteInterface $route = null,
        protected array $allowedMethods = [],
    ) {
        Assert::that($status)->inArray([static::FOUND, static::METHOD_NOT_ALLOWED, static::NOT_FOUND]);
        Assert::that($method)->notEmpty('Method should not be empty');
        Assert::that($uri)->notEmpty('Uri should not be empty');
        Assert::that($identifier)->nullOr()->notEmpty('Identifier should not be empty');
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @return string
     */
    public function getUri(): string
    {
        return $this->uri;
    }

    /**
     * @return string|null
     */
    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    /**
     * @param bool $urlDecode
     * @return array<int|string, string>
     */
    public function getArguments(bool $urlDecode = true): array
    {
        if (!$urlDecode) {
            return $this->arguments;
        }

        $arguments = [];

        foreach ($this->arguments as $key => $value) {
            $arguments[$key] = \rawurldecode($value);
        }

        return $arguments;
    }

    /**
     * @param RouteInterface $route
     */
    public function setRoute(RouteInterface $route): void
    {
        $this->route = $route;
    }

    /**
     * @return RouteInterface|null
     */
    public function getRoute(): ?RouteInterface
    {
        return $this->route;
    }

    /**
     * @return array<string>
     */
    public function getAllowedMethods(): array
    {
        return $this->allowedMethods;
    }
}
