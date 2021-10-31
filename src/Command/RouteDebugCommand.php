<?php

declare(strict_types=1);

namespace FC\Router\Command;

use FC\Router\RouteInterface;
use FC\Router\RouterInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class RouteDebugCommand extends Command
{
    protected static $defaultName = 'debug:route';
    protected static $defaultDescription = 'Display current routes for an application';

    public function __construct(protected RouterInterface $router)
    {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setDefinition([
                new InputArgument('name', InputArgument::OPTIONAL, 'A route name'),
            ])
            ->setDescription(self::$defaultDescription)
            ->setHelp(
                <<<'EOF'
The <info>%command.name%</info> displays the configured routes:
  <info>php %command.full_name%</info>
EOF
            );
    }

    /**
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException When route does not exist
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $name = (string)$input->getArgument('name');

        if ('' === $name) {
            [$tableHeaders, $tableRows] = $this->describeRoutes();
        } else {
            $matchingRoutes = $this->findRouteNameContaining($name);

            if (0 === \count($matchingRoutes)) {
                throw new \InvalidArgumentException(\sprintf('The route "%s" does not exist.', $name));
            }

            if (1 !== \count($matchingRoutes) || $matchingRoutes[0] !== $name) {
                $default = 1 === \count($matchingRoutes) ? $matchingRoutes[0] : null;
                $name = $io->choice('Select one of the matching routes', $matchingRoutes, $default);
            }

            $route = $this->router->getRouteCollection()->get($name);
            [$tableHeaders, $tableRows] = $this->describeRoute($route);
        }

        $table = new Table($io);
        $table->setHeaders($tableHeaders)->setRows($tableRows);
        $table->render();

        return static::SUCCESS;
    }

    /**
     * @param RouteInterface $route
     * @return array
     * @phpstan-return array<array<array<string>|string>>
     */
    protected function describeRoute(RouteInterface $route): array
    {
        $tableHeaders = ['Property', 'Value'];
        $tableRows = [
            ['Name', $route->getName()],
            ['Method', $route->getMethod()],
            ['Pattern', $route->getPattern()],
            ['Controller', $route->getHandler()],
            ['Middlewares', \implode('|', $route->getMiddlewares())],
            ['ID', $route->getIdentifier()],
            ['Class', $route::class],
        ];

        return [$tableHeaders, $tableRows];
    }

    /**
     * @return array
     * @phpstan-return array<array<array<string>|string>>
     */
    protected function describeRoutes(): array
    {
        $tableHeaders = ['Name', 'Method', 'Pattern', 'Controller'];

        $tableRows = [];
        foreach ($this->router->getRouteCollection() as $route) {
            $tableRows[] = [
                $route->getName(),
                $route->getMethod(),
                $route->getPattern(),
                $route->getHandler(),
            ];
        }

        return [$tableHeaders, $tableRows];
    }

    /**
     * @param string $name
     * @return string[]
     */
    protected function findRouteNameContaining(string $name): array
    {
        $foundRoutesNames = [];
        foreach ($this->router->getRouteCollection() as $route) {
            if (false !== \stripos($route->getName(), $name)) {
                $foundRoutesNames[] = $route->getName();
            }
        }

        return $foundRoutesNames;
    }
}
