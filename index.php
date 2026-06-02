<?php

/**
 * Tablo Twig Template Plugin for Kirby CMS
 *
 * Swaps Kirby's template component to render Twig files alongside PHP templates.
 * See README.md for installation and usage details.
 *
 * @link https://tablo.supply/ Tablo themes marketplace
 * @see README.md
 */

require __DIR__ . '/src/TwigTemplate.php';

if (!function_exists('twig')) {
    /**
     * Render a Twig string from PHP. Supports JSX component syntax when
     * tablo.twig.jsx.enabled is true.
     *
     * Usage:
     *   echo twig('<Button variant="primary" href="{{ url }}">{{ label }}</Button>', [
     *       'url'   => $url,
     *       'label' => 'Subscribe',
     *   ]);
     */
    function twig(string $source, array $data = []): string
    {
        return \Tablo\TwigTemplate::env()->createTemplate($source)->render($data);
    }
}

Kirby::plugin('tablo/twig', [
    'options' => [
        'jsx' => [
            // Opt-in; require lemmon/twig-jsx in the site Composer root.
            'enabled' => false,
            // null: do not override twig-jsx lexer defaults (see site/config for per-site options).
            'lexer' => null,
        ],
        'toolbar' => null,
    ],
    'commands' => [
        'twig:flush' => [
            'description' => 'Flush Twig cache',
            'args' => [],
            'command' => function ($cli) {
                $cacheDir = realpath(kirby()->root('cache') . '/twig');
                if (!$cacheDir) {
                    $cli->out('Twig cache already empty');
                    return;
                }
                $cli->out('Clearing Twig cache at ' . $cacheDir);
                if (\Kirby\Filesystem\Dir::remove($cacheDir)) {
                    $cli->success('Twig cache flushed');
                } else {
                    $cli->error('Failed to flush Twig cache');
                }
            },
        ],
    ],
    'components' => [
        'template' => function (Kirby $kirby, string $name, string $type = 'html', string $defaultType = 'html') {
            return new Tablo\TwigTemplate($kirby, $name, $type, $defaultType);
        },
    ],
    'snippets' => [
        'toolbar' => __DIR__ . '/snippets/toolbar.twig',
    ],
]);
