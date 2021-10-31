<?php

declare(strict_types=1);

namespace FC\Router\Test\Attribute;

use FC\Router\Attribute\Route;
use FC\Router\Test\Dummy\Valid\DummyAction;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

class RouteTest extends TestCase
{
    public function testMultipleAttributes(): void
    {
        $method = 'dummy1';

        $attributes = $this->getAttributes(DummyAction::class, $method);

        Assert::assertCount(2, $attributes);
        Assert::assertEquals([Route::GET], $attributes[0]->newInstance()->methods);
        Assert::assertEquals([Route::POST], $attributes[1]->newInstance()->methods);
    }

    public function testMultipleMiddlewares(): void
    {
        $method = 'dummy2';

        $attributes = $this->getAttributes(DummyAction::class, $method);

        Assert::assertCount(1, $attributes);

        /** @var Route $route */
        $route = $attributes[0]->newInstance();

        Assert::assertEquals([Route::DELETE], $route->methods);
        Assert::assertEquals('/'.$method, $route->pattern);
        Assert::assertEquals($method, $route->name);
        Assert::assertCount(2, $route->middlewares);
    }

    /**
     * @template T of object
     * @param class-string $class
     * @param string $method
     * @return \ReflectionAttribute<T>[]
     * @throws \ReflectionException
     */
    private function getAttributes(string $class, string $method): array
    {
        $refMethod = new \ReflectionMethod($class, $method);

        return $refMethod->getAttributes(Route::class, \ReflectionAttribute::IS_INSTANCEOF);
    }
}
