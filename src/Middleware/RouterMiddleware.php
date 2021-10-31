<?php

declare(strict_types=1);

namespace FC\Router\Middleware;

use FC\Router\Exception\RouteMethodNotAllowedException;
use FC\Router\Exception\RouteNotFoundException;
use FC\Router\Matcher\Result;
use FC\Router\RouteInterface;
use FC\Router\RouterInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RouterMiddleware implements MiddlewareInterface
{
    /**
     * @param RouterInterface $router
     */
    public function __construct(protected RouterInterface $router)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $request = $this->withResult($request);
        $request = $this->withRoute($request);

        return $handler->handle($request);
    }

    /**
     * @param ServerRequestInterface $request
     * @return ServerRequestInterface
     */
    protected function withResult(ServerRequestInterface $request): ServerRequestInterface
    {
        $result = $this->router->match($request->getMethod(), $request->getUri()->getPath());
        return $request->withAttribute(Result::class, $result);
    }

    /**
     * @param ServerRequestInterface $request
     * @return ServerRequestInterface
     */
    protected function withRoute(ServerRequestInterface $request): ServerRequestInterface
    {
        /** @var Result $result */
        $result = $request->getAttribute(Result::class);

        switch ($result->getStatus()) {
            case Result::NOT_FOUND:
                throw RouteNotFoundException::new(\sprintf('Route for "%s" not found', $request->getUri()->getPath()));
            case Result::METHOD_NOT_ALLOWED:
                $message = \sprintf(
                    'Method "%s" not allowed, allowed methods: %s',
                    $result->getMethod(),
                    \implode(', ', $result->getAllowedMethods())
                );
                throw RouteMethodNotAllowedException::new($message);
        }

        return $request->withAttribute(RouteInterface::class, $result->getRoute());
    }
}
