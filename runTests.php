<?php
declare(strict_types=1);
require_once 'init.php';

use MM\Executor;

function getDirContents($dir, &$results = [])
{
    $files = scandir($dir);

    foreach ($files as $key => $value) {
        $path = realpath($dir . DIRECTORY_SEPARATOR . $value);
        if (!is_dir($path)) {
            $results[] = $path;
        } else {
            if ($value != "." && $value != "..") {
                getDirContents($path, $results);
                $results[] = $path;
            }
        }
    }

    return $results;
}

function getCleanFileList(string $directory): array
{
    $files = getDirContents($directory);
    foreach ($files as $index => $inputFile) {
        if (strpos($inputFile, '.php') === false) {
            unset($files[$index]);
            continue;
        }
        $files[$index] = str_replace($directory, '', $inputFile);
        $files[$index] = str_replace('.php', '', $files[$index]);
    }
    return array_values($files);
}

echo PHP_EOL;
$inputFiles  = getCleanFileList(MM_TESTS_INPUT_DIR);
$testResults = getCleanFileList(MM_TESTS_OUTPUT_DIR);
$test        = new Executor();
$failures    = [];

foreach ($inputFiles as $index => $file) {
    $actual   = $test->execute($file, MM_TESTS_INPUT_DIR);
    $expected = file_get_contents(MM_TESTS_OUTPUT_DIR . $testResults[$index] . '.php');

    if ($actual == $expected) {
        echo ".";
    } else {
        echo "X";
        $failures[] = $file;
    }
}

echo PHP_EOL . PHP_EOL;
if (empty($failures)) {
    echo "No failures, all good!";
} else {
    echo "Failures on Tests: " . PHP_EOL;
    echo implode(PHP_EOL, $failures);
}

echo PHP_EOL . PHP_EOL;
