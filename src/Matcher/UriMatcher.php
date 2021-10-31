<?php

declare(strict_types=1);

namespace FC\Router\Matcher;

use Assert\Assert;
use FastRoute\Dispatcher;
use FC\Router\Attribute\Route;

class UriMatcher implements UriMatcherInterface
{
    protected ?Dispatcher $dispatcher = null;

    /**
     * @param array<mixed> $data
     */
    public function __construct(protected array $data)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function match(string $method, string $uri): Result
    {
        Assert::that($method)->inArray(Route::ALL);

        $uri = \rawurldecode($uri);

        if ('' === $uri || '/' !== $uri[0]) {
            $uri = '/' . $uri;
        }

        $data = $this->getDispatcher()->dispatch($method, $uri);

        return match ($data[0]) {
            Dispatcher::METHOD_NOT_ALLOWED => new Result(
                Result::METHOD_NOT_ALLOWED,
                $method,
                $uri,
                allowedMethods: $data[1]
            ),
            Dispatcher::FOUND => new Result(
                Result::FOUND,
                $method,
                $uri,
                $data[1],
                $data[2] ?? [],
            ),
            default => new Result(Result::NOT_FOUND, $method, $uri),
        };
    }

    protected function getDispatcher(): Dispatcher
    {
        if (null === $this->dispatcher) {
            $this->dispatcher = new Dispatcher\GroupCountBased($this->data);
        }

        return $this->dispatcher;
    }
}
