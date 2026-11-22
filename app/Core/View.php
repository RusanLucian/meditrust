<?php

namespace App\Core;

class View
{
    public static function render(string $view, array $data = [], ?string $layout = 'layouts/main'): Response
    {
        $content = self::renderPartial($view, $data);

        if ($layout !== null) {
            $layoutPath = dirname(__DIR__) . '/Views/' . str_replace('.', '/', $layout) . '.php';
            if (file_exists($layoutPath)) {
                $layoutData = array_merge($data, ['content' => $content]);
                $content = self::renderFile($layoutPath, $layoutData);
            }
        }

        return new Response($content);
    }

    public static function renderPartial(string $view, array $data = []): string
    {
        $viewPath = dirname(__DIR__) . '/Views/' . str_replace('.', '/', $view) . '.php';

        if (!file_exists($viewPath)) {
            throw new \RuntimeException("View file not found: {$viewPath}");
        }

        return self::renderFile($viewPath, $data);
    }

    private static function renderFile(string $filePath, array $data = []): string
    {
        extract($data, EXTR_SKIP);

        ob_start();
        include $filePath;
        return ob_get_clean() ?: '';
    }
}
