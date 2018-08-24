<?php
declare(strict_types=1);
require_once 'init.php';

// https://github.com/nikic/PHP-Parser - this eventually may be helpful
use MM\Executor;

$fileName   = $argv[1] ?? 'simpleTest';
$executor   = new Executor();
$outputCode = $executor->execute($fileName, MM_INPUT_DIR, true);
file_put_contents(MM_OUTPUT_DIR . $fileName . ".php", $outputCode);

