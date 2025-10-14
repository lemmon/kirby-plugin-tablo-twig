<?php

require __DIR__ . '/src/TwigTemplate.php';

Kirby::plugin('tablo/twig', [
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
                if (Dir::remove($cacheDir)) {
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
]);
