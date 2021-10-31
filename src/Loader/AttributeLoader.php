<?php

declare(strict_types=1);

namespace FC\Router\Loader;

use FC\Router\Attribute\Prefix;
use FC\Router\Attribute\Route;
use FC\Router\Collector\RouteCollectorFactoryInterface;
use FC\Router\Collector\RouteCollectorInterface;
use Symfony\Component\Finder\Finder as SfFinder;

class AttributeLoader implements LoaderInterface
{
    public function __construct(protected RouteCollectorFactoryInterface $routeCollectorFactory)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function load(mixed $resource): RouteCollectorInterface
    {
        $collector = $this->routeCollectorFactory->create();

        foreach (SfFinder::create()->files()->name('*.php')->in($resource) as $file) {
            if (false !== ($class = $this->findClass((string)$file))) {
                $refl = new \ReflectionClass($class);
                if ($refl->isAbstract()) {
                    continue;
                }

                /**
                 * @var Route $attribute
                 * @var string $handler
                 */
                foreach ($this->findClassAttributes($refl) as [$attribute, $handler]) {
                    $middlewares = $attribute->prefix?->middlewares ?? [];
                    $prefix = $attribute->prefix?->prefix ?? '';
                    $name = null;

                    $methods = \array_intersect(Route::ALL, $attribute->methods);

                    foreach ($methods as $method) {
                        if (null !== $attribute->name) {
                            $name = null === $name
                                ? $attribute->name
                                : \sprintf(
                                    '%s_%s',
                                    $attribute->name,
                                    \strtolower($method)
                                );
                        }

                        $collector
                            ->add($method, $prefix . $attribute->pattern, $handler)
                            ->setName($name)
                            ->setMiddlewares([...$middlewares, ...$attribute->middlewares]);
                    }
                }
            }
        }

        \gc_mem_caches();

        return $collector;
    }

    /**
     * @param \ReflectionClass<object> $refl
     * @return array<int, array{Route, non-empty-string}>
     */
    protected function findClassAttributes(\ReflectionClass $refl): array
    {
        $prefixes = $this->findPrefixes($refl);

        $data = [];

        foreach ($refl->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->isStatic()) {
                continue;
            }

            $attributes = $method->getAttributes(Route::class, \ReflectionAttribute::IS_INSTANCEOF);

            foreach ($attributes as $attribute) {
                /** @var Route $route */
                $instance = $attribute->newInstance();

                $handler = \sprintf('%s::%s', $refl->getName(), $method->getName());

                if (0 === \count($prefixes)) {
                    $data[] = [$instance, $handler];
                    continue;
                }

                foreach ($prefixes as $prefix) {
                    $route = clone $instance;
                    $route->prefix = $prefix;
                    $data[] = [$route, $handler];
                }
            }
        }

        return $data;
    }

    /**
     * @template T of object
     * @param \ReflectionClass<T> $class
     * @return Prefix[]
     */
    protected function findPrefixes(\ReflectionClass $class): array
    {
        return \array_map(
            fn(\ReflectionAttribute $attribute) => $attribute->newInstance(),
            $class->getAttributes(Prefix::class)
        );
    }

    /**
     * Returns the full class name for the first class in the file.
     */
    protected function findClass(string $file): string|false
    {
        $class = false;
        $namespace = false;
        $tokens = \token_get_all(\file_get_contents($file));

        if (1 === \count($tokens) && \T_INLINE_HTML === $tokens[0][0]) {
            throw new \InvalidArgumentException(
                \sprintf(
                    'The file "%s" does not contain PHP code. Did you forgot to add the "<?php" start tag at the beginning of the file?',
                    $file
                )
            );
        }

        $nsTokens = [\T_NS_SEPARATOR => true, \T_STRING => true];
        if (\defined('T_NAME_QUALIFIED')) {
            $nsTokens[\T_NAME_QUALIFIED] = true;
        }
        for ($i = 0; isset($tokens[$i]); ++$i) {
            $token = $tokens[$i];
            if (!isset($token[1])) {
                continue;
            }

            if (true === $class && \T_STRING === $token[0]) {
                return $namespace . '\\' . $token[1];
            }

            if (true === $namespace && isset($nsTokens[$token[0]])) {
                $namespace = $token[1];
                while (isset($tokens[++$i][1], $nsTokens[$tokens[$i][0]])) {
                    $namespace .= $tokens[$i][1];
                }
                $token = $tokens[$i];
            }

            if (\T_CLASS === $token[0]) {
                // Skip usage of ::class constant and anonymous classes
                $skipClassToken = false;
                for ($j = $i - 1; $j > 0; --$j) {
                    if (!isset($tokens[$j][1])) {
                        if ('(' === $tokens[$j] || ',' === $tokens[$j]) {
                            $skipClassToken = true;
                        }
                        break;
                    }

                    if (\T_DOUBLE_COLON === $tokens[$j][0] || \T_NEW === $tokens[$j][0]) {
                        $skipClassToken = true;
                        break;
                    } elseif (!\in_array((int)$tokens[$j][0], [\T_WHITESPACE, \T_DOC_COMMENT, \T_COMMENT], true)) {
                        break;
                    }
                }

                if (!$skipClassToken) {
                    $class = true;
                }
            }

            if (\T_NAMESPACE === $token[0]) {
                $namespace = true;
            }
        }

        return false;
    }
}
