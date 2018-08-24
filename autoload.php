<?php
declare(strict_types=1);

function mmAutoload(string $class): void
{
    $pieces = explode('\\', $class);

    $projectNamespace = array_shift($pieces);
    if ($projectNamespace !== "MM") {
        throw new Exception("Trying to load class $class which isn't in the project namespace!");
    }

    $path = MM_INCLUDES_DIR . implode('/', $pieces) . '.php';

    require_once $path;
}

spl_autoload_register('mmAutoload');
