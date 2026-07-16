<?php

declare(strict_types=1);

namespace PIS\Controllers;

class Controller
{
    protected function json(mixed $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function view(string $view, array $data = []): void
    {
        extract($data);
        $viewPath = BASE_PATH . '/src/Views/' . str_replace('.', '/', $view) . '.php';

        if (!file_exists($viewPath)) {
            throw new \RuntimeException("View {$view} not found");
        }

        ob_start();
        require $viewPath;
        $content = ob_get_clean();

        require BASE_PATH . '/src/Views/layouts/main.php';
    }

    protected function redirect(string $url): void
    {
        header("Location: {$url}");
        exit;
    }

    protected function getInput(): array
    {
        $input = json_decode(file_get_contents('php://input'), true);
        return $input ?? $_POST;
    }
}
