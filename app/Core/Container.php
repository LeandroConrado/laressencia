<?php

namespace App\Core;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;

class Container
{
    public static function makeTwig(): Environment
    {
        require_once __DIR__ . '/Helpers.php';

        $loader = new FilesystemLoader(__DIR__ . '/../../templates');
        $twig = new Environment($loader, [
            'cache' => false,
            'debug' => true
        ]);

        // Função url()
        $twig->addFunction(new TwigFunction('url', function (string $path = '') {
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $scriptName = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
            return "{$scheme}://{$host}{$scriptName}/" . ltrim($path, '/');
        }));

        // Função asset()
        $twig->addFunction(new TwigFunction('asset', function (string $path = '') {
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $scriptName = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
            return "{$scheme}://{$host}{$scriptName}/" . ltrim($path, '/');
        }));

        return $twig;
    }

    public static function make(string $class)
    {
        return new $class();
    }
}
