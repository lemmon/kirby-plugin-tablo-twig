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

    public function __construct(App $kirby, string $name, string $type = 'html', string $defaultType = 'html')
    {
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

            // add default snippets dir, if exists (@snippets/... and unqualified paths)
            if (is_dir($dir = self::$kirby->root('snippets'))) {
                $loader->addPath($dir, 'snippets');
                // Default namespace so twig-jsx includes like `components/Foo.twig` resolve to
                // site/snippets/components/ (Kirby snippets, not page templates).
                $loader->addPath($dir);
            }

            // add plugin templates dirs
            foreach ($this->findDirs('templates') as $dir) {
                if (is_dir($dir)) {
                    $loader->addPath($dir);
                    $loader->addPath($dir, 'templates');
                    // Add corresponding snippets directory if it exists
                    $snippetsDir = dirname($dir) . '/snippets';
                    if (is_dir($snippetsDir)) {
                        $loader->addPath($snippetsDir, 'snippets');
                    }
                }
            }

            // add plugin snippets dir
            foreach ($this->findDirs('snippets') as $dir) {
                if (is_dir($dir)) {
                    $loader->addPath($dir, 'snippets');
                }
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

            $this->registerTwigJsx(self::$twig);
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

    /**
     * Optional JSX-like component tags via lemmon/twig-jsx (composer package installed by the site).
     *
     * @see https://github.com/lemmon/twig-jsx
     */
    private function registerTwigJsx(Twig\Environment $twig): void
    {
        if (!option('tablo.twig.jsx.enabled', false)) {
            return;
        }

        if (
            !class_exists(\Lemmon\TwigJsx\AttributeExtension::class)
            || !class_exists(\Lemmon\TwigJsx\JSXPreLexer::class)
        ) {
            throw new \LogicException(
                'tablo.twig.jsx.enabled is true, but lemmon/twig-jsx is not autoloaded. '
                . 'Require it in your project Composer root (see site/plugins/tablo-twig/README.md).'
            );
        }

        $lexer = option('tablo.twig.jsx.lexer');
        if (!is_array($lexer)) {
            $lexer = [];
        }
        $lexer = array_filter($lexer, static fn ($value) => $value !== null);

        $twig->addExtension(new \Lemmon\TwigJsx\AttributeExtension());
        $twig->setLexer(new \Lemmon\TwigJsx\JSXPreLexer($twig, $lexer));
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
        $file = $this->file();

        // render php if file is PHP template
        if ($file !== null && substr($file, -4) === '.php') {
            return parent::render($data);
        }

        // render twig
        return $this->getTwig()->render(sprintf('%s.twig', $this->name()), $data);
    }
}
