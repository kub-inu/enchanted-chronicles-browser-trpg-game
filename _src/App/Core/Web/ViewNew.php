<?php
class View
{
    public static function render( string $view, array $data = [], string|false|null $layout = null): string 
    {
        return (new self())->renderInternal($view, $data, $layout);
    }

    protected function renderInternal(string $view, array $data, string|false|null $layout): string 
    {
        $viewPath = ROOT_DIR . '/views/' . $view . '.php';

        if (!is_file($viewPath)) {
            Response::html('404 - View not found', 404);
        }

        extract($data, EXTR_SKIP);

        // dostupné vo view
        $auth = \App\Core\Auth\Auth::class;
        $csrf = \App\Core\Security\Csrf::class;

        ob_start();
        require $viewPath;
        $content = ob_get_clean();

        if ($layout === false) {
            return $content;
        }

        $layoutName = is_string($layout) ? $layout : 'publicWeb';
        $layoutPath = ROOT_DIR . '/views/layouts/' . $layoutName . '.php';

        if (!is_file($layoutPath)) {
            Response::html('500 - Layout not found', 500);
        }

        ob_start();
        require $layoutPath;
        return ob_get_clean();
    }
}