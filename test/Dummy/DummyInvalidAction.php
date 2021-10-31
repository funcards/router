<?php

declare(strict_types=1);

namespace FC\Router\Test\Dummy;

use FC\Router\Attribute\Route;
use FC\Router\Test\Dummy\Valid\DummyAction;
use FC\Router\Test\Dummy\Valid\DummyMiddleware;

class DummyInvalidAction
{
    #[Route(Route::POST, '/error', 'error', DummyMiddleware::class, DummyAction::class)]
    public function error(): void
    {
    }
}
