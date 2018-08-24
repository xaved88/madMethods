<?php
declare(strict_types=1);

list($scriptPath) = get_included_files();
define("MM_ROOT_DIR", substr($scriptPath, 0, strrpos($scriptPath, '/') + 1));
define("MM_INCLUDES_DIR", MM_ROOT_DIR . "includes/");
define("MM_INPUT_DIR", MM_ROOT_DIR . "input/");
define("MM_OUTPUT_DIR", MM_ROOT_DIR . "output/");
define("MM_TESTS_INPUT_DIR", MM_ROOT_DIR . "tests/input/");
define("MM_TESTS_OUTPUT_DIR", MM_ROOT_DIR . "tests/output/");

require_once MM_ROOT_DIR . 'autoload.php';
