<?php

function load_env(): void {
    $path = dirname(__DIR__) . '/.env';
    if (!file_exists($path)) return;

    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        if (!str_contains($line, '=')) continue;

        [$key, $value] = explode('=', $line, 2);
        $key   = trim($key);
        $value = trim($value, " \t\"'");

        $_ENV[$key] = $value;
        putenv("$key=$value");
    }
}

load_env();
