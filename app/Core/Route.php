<?php

namespace App\Core;

class Route
{
    private string $method;
    private string $uri;
    private mixed $action;
    private ?string $name = null;
    private array $middlewares = [];

    public function __construct(string $method, string $uri, mixed $action)
    {
        $this->method = strtoupper($method);
        $this->uri = '/' . trim($uri, '/');
        $this->action = $action;
    }

    public function name(string $name): self
    {
        $this->name = $name;
        Router::registerNamedRoute($name, $this);
        return $this;
    }

    public function middleware(string|array $middleware): self
    {
        if (is_array($middleware)) {
            $this->middlewares = array_merge($this->middlewares, $middleware);
        } else {
            $this->middlewares[] = $middleware;
        }
        return $this;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function getAction(): mixed
    {
        return $this->action;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

    public function matches(string $method, string $uri, array &$params = []): bool
    {
        if ($this->method !== $method) {
            return false;
        }

        $pattern = preg_replace_callback('/\{([a-zA-Z0-9_]+)\}/', function ($matches) {
            $paramName = $matches[1];
            if ($paramName === 'id' || str_ends_with($paramName, '_id')) {
                return '(?P<' . $paramName . '>\d+)';
            }
            return '(?P<' . $paramName . '>[^/]+)';
        }, $this->uri);
        $pattern = "#^{$pattern}$#";

        if (preg_match($pattern, $uri, $matches)) {
            $params = [];
            foreach ($matches as $key => $value) {
                if (!is_numeric($key)) {
                    $params[$key] = $value;
                }
            }
            return true;
        }

        return false;
    }

    public function generateUrl(array $params = []): string
    {
        $url = $this->uri;
        foreach ($params as $key => $value) {
            $url = str_replace('{' . $key . '}', (string)$value, $url);
        }
        return $url;
    }
}
