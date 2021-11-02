<?php

declare(strict_types=1);

namespace FC\Router\Exception;

final class RouterMethodNotAllowedException extends \RuntimeException implements RouterException
{
    /**
     * @var array<string>
     */
    private array $allowedMethods;

    /**
     * @param string $message
     * @param array<string> $allowedMethods
     */
    private function __construct(string $message, array $allowedMethods = [])
    {
        parent::__construct($message, 405);

        $this->allowedMethods = $allowedMethods;
    }

    /**
     * @return string[]
     */
    public function getAllowedMethods(): array
    {
        return $this->allowedMethods;
    }

    /**
     * @param string $message
     * @param array<string> $allowedMethods
     * @return static
     */
    public static function new(string $message, array $allowedMethods): self
    {
        return new self($message, $allowedMethods);
    }
}
