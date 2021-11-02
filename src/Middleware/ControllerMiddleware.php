<?php

declare(strict_types=1);

namespace FC\Router\Middleware;

use FC\Router\Exception\ControllerMethodNotFoundException;
use FC\Router\Exception\ControllerNotFoundException;
use FC\Router\Exception\ResolveMethodArgumentException;
use FC\Router\Matcher\Result;
use FC\Router\RouteInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ControllerMiddleware implements MiddlewareInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $attributeName
     */
    public function __construct(protected ContainerInterface $container, protected string $attributeName)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $request = $this->resolveController($request);
        return $handler->handle($request);
    }

    /**
     * @param ServerRequestInterface $request
     * @return ServerRequestInterface
     */
    protected function resolveController(ServerRequestInterface $request): ServerRequestInterface
    {
        /** @var RouteInterface $route */
        $route = $request->getAttribute(RouteInterface::class);

        [$serviceId, $method] = \explode('::', $route->getHandler());

        if (!$this->container->has($serviceId)) {
            throw ControllerNotFoundException::new(\sprintf('Controller "%s" not found', $serviceId));
        }

        $service = $this->container->get($serviceId);

        try {
            $reflectionMethod = new \ReflectionMethod($service, $method);
            $arguments = $this->resolveArguments($reflectionMethod, $request);
            return $request->withAttribute($this->attributeName, [$service, $method, $arguments]);
        } catch (\ReflectionException $e) {
            throw ControllerMethodNotFoundException::new(
                \sprintf('Method "%s" for controller "%s" not found', $method, $service::class),
                $e,
            );
        }
    }

    /**
     * @param \ReflectionMethod $reflectionMethod
     * @param ServerRequestInterface $request
     * @return array<string, mixed>
     * @throws \ReflectionException
     */
    protected function resolveArguments(\ReflectionMethod $reflectionMethod, ServerRequestInterface $request): array
    {
        /** @var Result $result */
        $result = $request->getAttribute(Result::class);

        $routeArgs = $result->getArguments();
        $arguments = [];

        foreach ($reflectionMethod->getParameters() as $parameter) {
            $name = $parameter->getName();

            if (\array_key_exists($name, $routeArgs) && '' !== $routeArgs[$name]) {
                $arguments[$name] = $routeArgs[$name];
                continue;
            }

            if ($parameter->hasType() && $parameter->getType() instanceof \ReflectionNamedType) {
                $typeName = $parameter->getType()->getName();

                if (\is_a($typeName, ServerRequestInterface::class, true)) {
                    $arguments[$name] = $request;
                    continue;
                }

                if ($this->container->has($typeName)) {
                    $arguments[$name] = $this->container->get($typeName);
                    continue;
                }
            }

            if ($parameter->isOptional()) {
                if ($parameter->isDefaultValueAvailable()) {
                    $arguments[$name] = $parameter->getDefaultValue();
                    continue;
                }

                if ($parameter->isDefaultValueConstant()) {
                    $arguments[$name] = $parameter->getDefaultValueConstantName();
                    continue;
                }
            }

            if ($parameter->allowsNull()) {
                $arguments[$name] = null;
                continue;
            }

            throw ResolveMethodArgumentException::new(
                \sprintf(
                    'Parameter "%s" value for "%s::%s" not found',
                    $name,
                    $reflectionMethod->getDeclaringClass()->getName(),
                    $reflectionMethod->getName(),
                )
            );
        }

        return $arguments;
    }
}
