<?php

declare(strict_types=1);

namespace FC\Router;

use FC\Router\Generator\UrlGenerator;
use FC\Router\Generator\UrlGeneratorInterface;
use FC\Router\Loader\LoaderInterface;
use FC\Router\Matcher\Result;
use FC\Router\Matcher\UriMatcher;
use FC\Router\Matcher\UriMatcherInterface;
use Psr\Http\Message\UriInterface;
use Psr\SimpleCache\CacheInterface;

class Router implements RouterInterface
{
    protected const ROUTER_COLLECTION = '__fast_routes_collection__';
    protected const ROUTER_DATA = '__fast_routes_data__';

    protected ?RouteCollectionInterface $routeCollection = null;
    protected ?UrlGeneratorInterface $urlGenerator = null;
    protected ?UriMatcherInterface $uriMatcher = null;

    public function __construct(
        protected LoaderInterface $loader,
        protected mixed $resource,
        protected bool $debug,
        protected ?CacheInterface $cache = null,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function getRouteCollection(): RouteCollectionInterface
    {
        return $this->routeCollection;
    }

    /**
     * {@inheritDoc}
     */
    public function match(string $method, string $uri): Result
    {
        $this->load();
        $result = $this->uriMatcher->match($method, $uri);

        if (null !== $result->getIdentifier()) {
            $result->setRoute($this->routeCollection->get($result->getIdentifier()));
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function generate(string $routeName, array $data = [], array $queryParams = []): string
    {
        $this->load();
        return $this->urlGenerator->generate($routeName, $data, $queryParams);
    }

    /**
     * {@inheritDoc}
     */
    public function absolute(UriInterface $uri, string $routeName, array $data = [], array $queryParams = []): string
    {
        $this->load();
        return $this->urlGenerator->absolute($uri, $routeName, $data, $queryParams);
    }

    /**
     * @return bool
     */
    protected function useCache(): bool
    {
        return !$this->debug && null !== $this->cache;
    }

    protected function load(): void
    {
        if ($this->useCache()) {
            if (null !== $this->routeCollection) {
                return;
            }

            $collection = $this->cache->get(static::ROUTER_COLLECTION);
            $data = $this->cache->get(static::ROUTER_DATA);

            if (null !== $collection && null !== $data) {
                $this->routeCollection = $collection;
                $this->urlGenerator = new UrlGenerator($this->routeCollection);
                $this->uriMatcher = new UriMatcher($data);

                return;
            }
        }

        $routeCollector = $this->loader->load($this->resource);
        $this->routeCollection = new RouteCollection();

        foreach ($routeCollector->getRoutes() as $route) {
            $this->routeCollection->add($route);
        }

        $this->urlGenerator = new UrlGenerator($this->routeCollection);
        $this->uriMatcher = new UriMatcher($routeCollector->getData());

        if ($this->useCache()) {
            $this->cache->set(static::ROUTER_COLLECTION, $this->routeCollection);
            $this->cache->set(static::ROUTER_DATA, $routeCollector->getData());
        }
    }
}
