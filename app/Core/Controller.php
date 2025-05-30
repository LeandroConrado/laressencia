<?php

namespace App\Core;

use Twig\Environment;

class Controller
{
    protected Environment $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * Renderiza uma view com dados
     */
    protected function view(string $template, array $dados = []): void
    {
        echo $this->twig->render($template, $dados);
    }

    /**
     * Redireciona para uma URL
     */
    protected function redirecionar(string $url): void
    {
        header("Location: {$url}");
        exit;
    }
}
