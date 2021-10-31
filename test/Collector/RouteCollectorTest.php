<?php

declare(strict_types=1);

namespace FC\Router\Test\Collector;

use FC\Router\Collector\RouteCollector;
use FC\Router\RouteInterface;
use FC\Router\Test\Dummy\Valid\DummyMiddleware;
use FastRoute\DataGenerator\GroupCountBased;
use FastRoute\RouteParser\Std;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

class RouteCollectorTest extends TestCase
{
    public function testAddRoute(): void
    {
        $collector = new RouteCollector(new Std(), new GroupCountBased());

        $data = [
            [['POST', '/login', 'Auth::login'], 'login', []],
            [['GET', '/logout', 'Auth::logout'], 'logout', [DummyMiddleware::class]],
        ];

        foreach ($data as $item) {
            $collector->add(...$item[0])->setName($item[1])->setMiddlewares($item[2]);
        }

        Assert::assertCount(2, $collector->getRoutes());
        Assert::assertContainsOnlyInstancesOf(RouteInterface::class, $collector->getRoutes());

        foreach ($data as $i => $item) {
            $route = $collector->getRoutes()['route'.$i];

            Assert::assertNotEmpty($route->getExpressions());
            Assert::assertEquals($item[0][0], $route->getMethod());
            Assert::assertEquals($item[0][1], $route->getPattern());
            Assert::assertEquals($item[0][2], $route->getHandler());
            Assert::assertEquals($item[1], $route->getName());
            Assert::assertEquals($item[2], $route->getMiddlewares());
        }
    }
}
