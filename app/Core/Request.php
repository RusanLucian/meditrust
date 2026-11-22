<?php

namespace App\Core;

class Request
{
    private string $method;
    private string $uri;
    private array $data;
    private array $params = [];

    public function __construct()
    {
        $this->method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        
        $requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        
        // Compute base directory (e.g. /meditrust)
        $scriptDir = trim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
        if (!empty($scriptDir) && strpos($requestUri, '/' . $scriptDir) === 0) {
            $requestUri = substr($requestUri, strlen('/' . $scriptDir));
        }

        $requestUri = '/' . trim($requestUri, '/');
        if ($requestUri !== '/' && str_ends_with($requestUri, '/')) {
            $requestUri = rtrim($requestUri, '/');
        }
        
        // Remove index.php if requested directly
        if ($requestUri === '/index.php') {
            $requestUri = '/';
        }

        $this->uri = $requestUri;

        $this->data = array_merge($_GET, $_POST);
        
        // Handle JSON body if Content-Type is application/json
        if (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
            $raw = file_get_contents('php://input');
            $json = json_decode($raw, true);
            if (is_array($json)) {
                $this->data = array_merge($this->data, $json);
            }
        }
    }

    public function method(): string
    {
        return $this->method;
    }

    public function isGet(): bool
    {
        return $this->method === 'GET';
    }

    public function isPost(): bool
    {
        return $this->method === 'POST';
    }

    public function uri(): string
    {
        return $this->uri;
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }

    public function all(): array
    {
        return $this->data;
    }

    public function setParams(array $params): void
    {
        $this->params = $params;
    }

    public function param(string $key, mixed $default = null): mixed
    {
        return $this->params[$key] ?? $default;
    }

    public function params(): array
    {
        return $this->params;
    }
}
