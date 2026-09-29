<?php

declare(strict_types=1);

namespace App\Http;

use Closure;

final class Router
{
    /** @var array<string, array<string, Closure>> */
    private array $routes = [];

    public function get(string $path, Closure $handler): void
    {
        $this->add('GET', $path, $handler);
    }

    public function post(string $path, Closure $handler): void
    {
        $this->add('POST', $path, $handler);
    }

    public function dispatch(string $method, string $uri): void
    {
        // Query strings are irrelevant when deciding which route handles a request.
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $handler = $this->routes[$method][$path] ?? null;

        if ($handler !== null) {
            // Route handlers send their own response or redirect when finished.
            $handler();
            return;
        }

        // A known URL used with the wrong HTTP verb is a 405, not a 404.
        if ($this->pathExists($path)) {
            http_response_code(405);
            header('Allow: ' . implode(', ', $this->methodsFor($path)));
            echo 'Method not allowed';
            return;
        }

        http_response_code(404);
        echo 'Page not found';
    }

    private function add(string $method, string $path, Closure $handler): void
    {
        $this->routes[$method][$path] = $handler;
    }

    private function pathExists(string $path): bool
    {
        foreach ($this->routes as $routes) {
            if (isset($routes[$path])) {
                return true;
            }
        }

        return false;
    }

    /** @return list<string> */
    private function methodsFor(string $path): array
    {
        $methods = [];

        foreach ($this->routes as $method => $routes) {
            if (isset($routes[$path])) {
                $methods[] = $method;
            }
        }

        return $methods;
    }
}

