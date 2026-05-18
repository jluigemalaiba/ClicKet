<?php

// Example PHP script
function greet(string $name): string
{
    return 'Hello, ' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '!';
}

if (php_sapi_name() === 'cli') {
    $name = $argv[1] ?? 'World';
    echo greet($name) . PHP_EOL;
} else {
    $name = $_GET['name'] ?? 'World';
    echo '<p>' . greet($name) . '</p>';
}
