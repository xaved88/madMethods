<?php
declare(strict_types=1);

require 'output/heavyMethodTest.php';

$obj = new HeavyMethodTest();

echo "One :: " . $obj->run(['a' => 1, 'b' => 2, 'c' => 3]) . PHP_EOL;
echo "Two :: " . $obj->run(['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4]) . PHP_EOL;
echo "Three :: " . $obj->run(['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5]) . PHP_EOL;

echo PHP_EOL;

