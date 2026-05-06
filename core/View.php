<?php

namespace Core;

class View
{
    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function render(string $template, array $data = [], ?string $layout = null): string
    {
        $viewPath = rtrim($this->config['view_path'], '/\\') . DIRECTORY_SEPARATOR . $template . '.php';
        if (!file_exists($viewPath)) {
            throw new \RuntimeException('View not found: ' . $template);
        }

        extract($data, EXTR_SKIP);
        $config = $this->config;
        $flash = $_SESSION['_flash'] ?? [];
        unset($_SESSION['_flash']);
        $csrfToken = Security::csrfToken();

        ob_start();
        require $viewPath;
        $content = ob_get_clean();

        $layout = $layout ?? ($this->config['layout'] ?? null);
        if (!$layout) {
            return $content;
        }

        $layoutPath = rtrim($this->config['view_path'], '/\\') . DIRECTORY_SEPARATOR . $layout . '.php';
        if (!file_exists($layoutPath)) {
            return $content;
        }

        ob_start();
        require $layoutPath;
        return ob_get_clean();
    }
}
