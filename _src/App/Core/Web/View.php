<?php

namespace App\Core\Web;

use App\Core\Auth\Auth;
use App\Core\Security\Csrf;

class View
{
    protected string $view;
    protected array $data = [];
    protected ?string $layout = null;

    public function __construct(string $view, array $data = [], ?string $layout = null)
    {
        $this->view = $view;
        $this->data = $data;
        $this->layout = $layout;
    }

    public function render(): void
    {
        extract($this->data, EXTR_SKIP);

        //Import tried do view
        $auth = Auth::class;
        $csrf = Csrf::class;
        
        // Obsah samotného view
        ob_start();
        require ROOT_DIR . '/views/' . $this->view . '.php';
        $content = ob_get_clean();

    
        if ($this->layout === false) {
            echo $content;
            return;
        }

        if(is_string($this->layout)){
            require ROOT_DIR . '/views/layouts/' . $this->layout . '.php';
            return;
        }

        require ROOT_DIR . '/views/layouts/publicWeb.php';
    }


    // Statická helper funkcia na rýchle použitie
    public static function make(string $view, array $data = [], ?string $layout = null): self
    {
        return new self($view, $data, $layout);
    }
}
