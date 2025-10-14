<?php

namespace Tablo;

use Kirby\Cms\App;
use Kirby\Cms\Template;
use Twig;
use Symfony\Component\VarDumper\VarDumper;

class TwigTemplate extends Template
{
    private static $kirby;
    private static $twig;

    public function __construct(App $kirby, string $name, string $type = 'html', string $defaultType = 'html')    {
        parent::__construct($name, $type, $defaultType);
        self::$kirby ??= $kirby;
    }

    private function getTwig()
    {
        if (!isset(self::$twig)) {
            $loader = new Twig\Loader\FilesystemLoader([]);

            // add default templates dir, if exists
            if (is_dir($dir = self::$kirby->root('templates'))) {
                $loader->addPath($dir);
                $loader->addPath($dir, 'templates');
            }

            // add default snippets dir, if exists
            if (is_dir($dir = self::$kirby->root('snippets'))) {
                $loader->addPath($dir, 'snippets');
            }

            // add plugin templates dirs
            foreach ($this->findDirs('templates') as $dir) if (is_dir($dir)) {
                $loader->addPath($dir);
                $loader->addPath($dir, 'templates');
                $loader->addPath(substr($dir, 0, -10) . '/snippets', 'snippets');
            }

            // add plugin snippets dir
            foreach ($this->findDirs('snippets') as $dir) if (is_dir($dir)) {
                $loader->addPath($dir, 'snippets');
            }

            self::$twig = new Twig\Environment($loader, [
                'cache' => self::$kirby->root('cache') . '/twig',
                'debug' => option('debug'),
            ]);

            self::$twig->addFunction(new Twig\TwigFunction('dump', function (...$args) {
                VarDumper::dump(...$args);
            }));

            self::$twig->addFunction(new Twig\TwigFunction('*', function ($name, ...$arguments) {
                return call_user_func_array($name, $arguments);
            }));

            self::$twig->addGlobal('kirby', self::$kirby);
            self::$twig->addGlobal('site', self::$kirby->site());
            self::$twig->addGlobal('pages', self::$kirby->site()->pages());
            self::$twig->addGlobal('page', self::$kirby->site()->page());
            self::$twig->addGlobal('user', self::$kirby->user());
            self::$twig->addGlobal('users', self::$kirby->users());
        }

        return self::$twig;
    }

    public function findDirs(string $extension): array
    {
        $paths = self::$kirby->extensions($extension);
        $paths = array_filter($paths, fn ($x) => substr($x, -5) === '.twig');
        $paths = array_map(fn ($x) => explode("/{$extension}/", $x)[0] . "/{$extension}", $paths);
        $paths = array_unique($paths);
        $paths = array_values($paths);
        return $paths;
    }

    public function extension(): string
    {
        return 'twig';
    }

    public function file(): string|null
    {
        $fileBase = $this->root() . '/' . $this->name();

        // check if we are looking for type-specific template
        if (!$this->hasDefaultType() && $this->type() !== $this->defaultType()) {
            $fileBase .= '.' . $this->type();
        }

        // possible extensions
        $phpFile = $fileBase . '.php';
        $twigFile = $fileBase . '.twig';

        // check for both php and twig template, prefer php if both exist
        if (is_file($phpFile)) {
            return realpath($phpFile);
        } elseif (is_file($twigFile)) {
            return realpath($twigFile);
        }

        // fallback to default function
        return parent::file();
    }

    public function render(array $data = []): string
    {
        // render php
        if (substr($this->file(), -4) === '.php') {
            return parent::render($data);
        }
        // render twig
        return $this->getTwig()->render(sprintf('%s.twig', $this->name), $data);
    }
}
