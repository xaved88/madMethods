<?php
declare(strict_types=1);
require_once 'init.php';

// https://github.com/nikic/PHP-Parser - this eventually may be helpful
use MM\Executor;
use MM\tokens\TokenMethod;

$directory = $argv[1];
$executor  = new Executor();
$summaries = $executor->executeDirectory($directory);

$totalReplaced = 0;
$total         = 0;
foreach ($summaries as $fileName => $summary) {
    echo $fileName . ":: " . PHP_EOL;
    foreach ($summary as $code => $methodList) {
        $count = count($methodList);
        $total += $count;
        if ($code == TokenMethod::REPLACEABLE) {
            $totalReplaced += $count;
        }
        echo $count . " :: " . TokenMethod::CODE_TO_STRING[$code] . PHP_EOL;
    }
    echo PHP_EOL;
}

echo "TOTAL REPLACED METHODS: " . $totalReplaced . PHP_EOL;
echo "TOTOL METHODS: " . $total . PHP_EOL;

