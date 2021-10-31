<?php

declare(strict_types=1);

namespace FC\Router\Test\Dummy\Valid;

use FC\Router\Attribute\Get;
use FC\Router\Attribute\Post;
use FC\Router\Attribute\Prefix;
use FC\Router\Attribute\Route;

#[Prefix('/prefix', DummyMiddleware::class)]
class DummyAction
{
    #[Get('/dummy1-get', 'dummy1-get')]
    #[Route(Route::POST, '/dummy1-post', 'dummy1-post')]
    public function dummy1(): void
    {
    }

    #[Route(Route::DELETE, '/dummy2', 'dummy2', DummyMiddleware::class, DummyMiddleware::class)]
    public function dummy2(): void
    {
    }

    #[Route(Route::ALL, '/dummy1-any', 'dummy1-any')]
    public function any(): void
    {
    }

    #[Route([Route::GET, Route::POST], '/dummy1-get-and-post', 'dummy1-get-and-post')]
    public function getAndPost(): void
    {
    }

    #[Post('/no-private')]
    private function noPrivate(): void
    {}

    #[Post('/no-protected')]
    protected function noProtected(): void
    {}
}
