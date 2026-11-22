<?php

namespace App\Core;

use App\Core\Middleware\AdminMiddleware;
use App\Core\Middleware\AuthMiddleware;
use App\Core\Middleware\DoctorMiddleware;
use App\Core\Middleware\GuestMiddleware;
use App\Core\Middleware\PatientMiddleware;

class Router
{
    /** @var Route[] */
    private static array $routes = [];
    private static array $namedRoutes = [];
    private static array $groupStack = [];

    private static array $middlewareAliases = [
        'auth'       => AuthMiddleware::class,
        'admin'      => AdminMiddleware::class,
        'auth=admin' => AdminMiddleware::class,
        'guest'      => GuestMiddleware::class,
        'doctor'     => DoctorMiddleware::class,
        'patient'    => PatientMiddleware::class,
    ];

    public static function get(string $uri, mixed $action): Route
    {
        return self::addRoute('GET', $uri, $action);
    }

    public static function post(string $uri, mixed $action): Route
    {
        return self::addRoute('POST', $uri, $action);
    }

    public static function group(array $attributes, callable $callback): void
    {
        self::$groupStack[] = $attributes;
        $callback();
        array_pop(self::$groupStack);
    }

    private static function addRoute(string $method, string $uri, mixed $action): Route
    {
        $prefix = '';
        $groupMiddlewares = [];

        foreach (self::$groupStack as $group) {
            if (isset($group['prefix'])) {
                $prefix .= '/' . trim($group['prefix'], '/');
            }
            if (isset($group['middleware'])) {
                $m = is_array($group['middleware']) ? $group['middleware'] : [$group['middleware']];
                $groupMiddlewares = array_merge($groupMiddlewares, $m);
            }
        }

        $fullUri = $prefix . '/' . trim($uri, '/');
        $fullUri = '/' . trim($fullUri, '/');

        $route = new Route($method, $fullUri, $action);

        if (!empty($groupMiddlewares)) {
            $route->middleware($groupMiddlewares);
        }

        self::$routes[] = $route;
        return $route;
    }

    public static function registerNamedRoute(string $name, Route $route): void
    {
        self::$namedRoutes[$name] = $route;
    }

    public static function url(string $name, array $params = []): string
    {
        // Re-index named routes if needed
        foreach (self::$routes as $route) {
            if ($route->getName() && !isset(self::$namedRoutes[$route->getName()])) {
                self::$namedRoutes[$route->getName()] = $route;
            }
        }

        if (!isset(self::$namedRoutes[$name])) {
            return url($name);
        }

        $relativeUrl = self::$namedRoutes[$name]->generateUrl($params);
        return url($relativeUrl);
    }

    public static function dispatch(Request $request): Response
    {
        $method = $request->method();
        $uri = $request->uri();

        foreach (self::$routes as $route) {
            $params = [];
            if ($route->matches($method, $uri, $params)) {
                $request->setParams($params);

                // Run middlewares
                foreach ($route->getMiddlewares() as $middlewareKey) {
                    $middlewareClass = self::$middlewareAliases[$middlewareKey] ?? $middlewareKey;
                    if (class_exists($middlewareClass)) {
                        $middlewareInstance = new $middlewareClass();
                        $result = $middlewareInstance->handle($request);
                        if ($result instanceof Response) {
                            return $result;
                        }
                    }
                }

                // Execute action
                $action = $route->getAction();

                if (is_callable($action)) {
                    $response = call_user_func_array($action, array_merge([$request], array_values($params)));
                } elseif (is_array($action) && count($action) === 2) {
                    [$controllerClass, $methodName] = $action;
                    if (!class_exists($controllerClass)) {
                        throw new \RuntimeException("Controller not found: {$controllerClass}");
                    }
                    $controller = new $controllerClass();
                    if (!method_exists($controller, $methodName)) {
                        throw new \RuntimeException("Method {$methodName} not found in {$controllerClass}");
                    }
                    $response = call_user_func_array([$controller, $methodName], array_merge([$request], array_values($params)));
                } else {
                    throw new \RuntimeException("Invalid route action specified for {$uri}");
                }

                if ($response instanceof Response) {
                    return $response;
                }

                return new Response((string)$response);
            }
        }

        // 404 Not Found
        try {
            return View::render('errors.404', ['title' => 'Pagină negăsită - 404'], 'layouts.main')->status(404);
        } catch (\Exception $e) {
            return new Response("<h1>404 - Pagină negăsită</h1><p>Adresa solicitată nu a fost găsită pe server.</p>", 404);
        }
    }

    public static function allRoutes(): array
    {
        return self::$routes;
    }
}
