<?php
namespace MyPHPServer\Server;

class Router {
    private $routes = [];

    public function addRoute(string $method, string $path, callable $handler): void {
        $this->routes[$method][$path] = $handler;
    }

    public function hasRoute(string $path): bool {
        foreach ($this->routes as $methodRoutes) {
            if (isset($methodRoutes[$path])) {
                return true;
            }
        }
        return false;
    }

    public function dispatch(Request $request): Response {
        $method = $request->getMethod();
        $path = $request->getPath();

        if (!isset($this->routes[$method][$path])) {
            return (new Response())->setStatusCode(404);
        }

        try {
            return call_user_func($this->routes[$method][$path], $request);
        } catch (\Throwable $e) {
            error_log($e->getMessage());
            return (new Response())->setStatusCode(500);
        }
    }
}