<?php
/**
 * Load key=value pairs from a .env file into $_ENV and putenv().
 */
function loadEnv(string $path): void
{
    if (!file_exists($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        if (!str_contains($line, '=')) {
            continue;
        }
        [$name, $value] = explode('=', $line, 2);
        $name  = trim($name);
        $value = trim($value, " \t\"'");
        $_ENV[$name] = $value;
        putenv("{$name}={$value}");
    }
}

loadEnv(__DIR__ . '/.env');
