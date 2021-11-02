<?php

declare(strict_types=1);

namespace FC\Router\Generator;

use FC\Router\Exception\RouterNotFoundException;
use Psr\Http\Message\UriInterface;

interface UrlGeneratorInterface
{
    /**
     * @param string $routeName
     * @param array<string, mixed> $data
     * @param array<string, mixed> $queryParams
     * @return string
     * @throws RouterNotFoundException
     */
    public function generate(string $routeName, array $data = [], array $queryParams = []): string;

    /**
     * @param UriInterface $uri
     * @param string $routeName
     * @param array<string, mixed> $data
     * @param array<string, mixed> $queryParams
     * @return string
     * @throws RouterNotFoundException
     */
    public function absolute(UriInterface $uri, string $routeName, array $data = [], array $queryParams = []): string;
}
