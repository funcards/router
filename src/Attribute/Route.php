<?php

declare(strict_types=1);

namespace FC\Router\Attribute;

use Assert\Assert;
use JetBrains\PhpStorm\ExpectedValues;
use Psr\Http\Server\MiddlewareInterface;

#[\Attribute(\Attribute::IS_REPEATABLE | \Attribute::TARGET_METHOD)]
class Route implements RouteAttribute
{
    public const GET = 'GET';
    public const HEAD = 'HEAD';
    public const PUT = 'PUT';
    public const POST = 'POST';
    public const PATCH = 'PATCH';
    public const DELETE = 'DELETE';

    public const ALL = [self::GET, self::POST, self::PUT, self::PATCH, self::DELETE, self::HEAD];

    /**
     * @var array<string>
     */
    public array $methods;

    /**
     * @var string
     */
    public string $pattern;

    /**
     * @var string|null
     */
    public ?string $name;

    /**
     * @var class-string<MiddlewareInterface>[]
     */
    public array $middlewares;

    /**
     * @var Prefix|null
     */
    public ?Prefix $prefix = null;

    /**
     * @param array<string>|string $methods
     * @param string $pattern
     * @param string|null $name
     * @param string ...$middlewares
     */
    public function __construct(
        #[ExpectedValues(valuesFromClass: Route::class)] array|string $methods = self::ALL,
        string $pattern = '',
        ?string $name = null,
        string ...$middlewares,
    ) {
        $methods = (array)$methods;

        Assert::thatAll($methods)->inArray(static::ALL, 'Method not valid');
        Assert::that($name)->nullOr()->notEmpty('Name should not be empty');
        Assert::thatAll($middlewares)->implementsInterface(MiddlewareInterface::class);

        $this->methods = $methods;
        $this->pattern = $pattern;
        $this->name = $name;
        $this->middlewares = $middlewares;
    }
}
