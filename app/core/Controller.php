<?php

namespace App\Core;

class Controller
{
    protected function view(string $view, array $data = []): string
    {
        $viewFile = view_path(str_replace('.', DIRECTORY_SEPARATOR, $view) . '.php');

        if (! is_file($viewFile)) {
            throw new \RuntimeException("View [{$view}] not found.");
        }

        extract($data, EXTR_SKIP);

        ob_start();
        require $viewFile;
        return (string) ob_get_clean();
    }

    protected function layout(string $view, array $data = [], string $layout = 'layouts.main'): string
    {
        $content = $this->view($view, $data);

        return $this->view($layout, array_merge($data, [
            'content' => $content,
            'appName' => $data['appName'] ?? config('app.name'),
        ]));
    }
}
