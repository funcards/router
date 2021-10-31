<?php

declare(strict_types=1);

namespace FC\Router\Attribute;

use Assert\Assert;
use Psr\Http\Server\MiddlewareInterface;

#[\Attribute(\Attribute::IS_REPEATABLE | \Attribute::TARGET_CLASS)]
final class Prefix implements RouteAttribute
{
    /**
     * @var string[]
     */
    public array $middlewares;

    /**
     * @param string $prefix
     * @param string ...$middlewares
     */
    public function __construct(public string $prefix, string ...$middlewares)
    {
        Assert::that($prefix)->notEmpty('Prefix should not be empty');
        Assert::thatAll($middlewares)->implementsInterface(MiddlewareInterface::class);

        $this->middlewares = $middlewares;
    }
}
