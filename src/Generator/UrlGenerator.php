<?php

declare(strict_types=1);

namespace FC\Router\Generator;

use Assert\Assert;
use FC\Router\Exception\InvalidUrlDataException;
use FC\Router\Exception\RouterNotFoundException;
use FC\Router\RouteCollectionInterface;
use Psr\Http\Message\UriInterface;

class UrlGenerator implements UrlGeneratorInterface
{
    /**
     * @param RouteCollectionInterface $routeCollection
     */
    public function __construct(protected RouteCollectionInterface $routeCollection)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function generate(string $routeName, array $data = [], array $queryParams = []): string
    {
        Assert::that($routeName)->notEmpty('RouteName should not be empty');

        if (null === ($route = $this->routeCollection->get($routeName))) {
            throw RouterNotFoundException::new(\sprintf('Route "%s" not found.', $routeName));
        }

        $segments = [];
        $segmentName = '';

        /*
         * $routes is an associative array of expressions representing a route as multiple segments
         * There is an expression for each optional parameter plus one without the optional parameters
         * The most specific is last, hence why we reverse the array before iterating over it
         */
        foreach ($route->getExpressions() as $expression) {
            foreach ($expression as $segment) {
                /*
                 * Each $segment is either a string or an array of strings
                 * containing optional parameters of an expression
                 */
                if (\is_string($segment)) {
                    $segments[] = $segment;
                    continue;
                }

                /*
                 * If we don't have a data element for this segment in the provided $data
                 * we cancel testing to move onto the next expression with a less specific item
                 */
                if (!\array_key_exists($segment[0], $data)) {
                    $segments = [];
                    $segmentName = $segment[0];
                    break;
                }

                $segments[] = $data[$segment[0]];
            }

            /*
             * If we get to this logic block we have found all the parameters
             * for the provided $data which means we don't need to continue testing
             * less specific expressions
             */
            if (0 < \count($segments)) {
                break;
            }
        }

        if (0 === \count($segments)) {
            throw InvalidUrlDataException::new('Missing data for URL segment: ' . $segmentName);
        }

        $url = \implode('', $segments);

        if (0 < \count($queryParams)) {
            $url .= '?' . \http_build_query($queryParams);
        }

        return $url;
    }

    /**
     * {@inheritDoc}
     */
    public function absolute(UriInterface $uri, string $routeName, array $data = [], array $queryParams = []): string
    {
        Assert::that($routeName)->notEmpty('RouteName should not be empty');

        $path = $this->generate($routeName, $data, $queryParams);
        $scheme = $uri->getScheme();
        $authority = $uri->getAuthority();
        $protocol = ('' === $scheme ? '' : $scheme . ':') . ('' === $authority ? '' : '//' . $authority);

        return $protocol . $path;
    }
}
