<?php
declare(strict_types=1);

namespace MM;

use MM\tokens\TokenFile;
use MM\tokens\TokenMethod;

class Executor
{
    private $lastReport;

    public function execute(string $fileName, string $directory, bool $printReport = false): string
    {
        // Load the file
        $fileName     .= '.php';
        $fileContents = file_get_contents($directory . $fileName);

        // Convert it to tokens
        try {
            $tokenFile        = TokenFile::create($fileContents);
            $this->lastReport = $tokenFile->optimize();
        } catch (\Exception $e) {
            if ($e->getCode() == TokenMethod::CLASS_IS_INTERFACE) {
                $this->lastReport = [
                    $e->getCode() => [
                        $fileName,
                    ],
                ];
            }

            return $fileContents;
        }

        if ($printReport) {
            foreach ($this->lastReport as $code => $methodList) {
                echo count($methodList) . " :: " . TokenMethod::CODE_TO_STRING[$code] . PHP_EOL;
            }
        }

        // Convert the tokens to code
        return $tokenFile->export();
    }

    public function executeDirectory(string $directory): array
    {
        $files = $this->getDirContents($directory);

        $max   = 300;
        $count = 0;

        $summaries = [];
        foreach ($files as $file) {
            $pieces         = explode('/', $file);
            $fileName       = array_pop($pieces);
            $parsedFileName = str_replace('.php', '', $fileName);
            if ($parsedFileName == $fileName) {
                // not a php file
                continue;
            }
            $directory = implode('/', $pieces) . '/';

            $outputCode           = $this->execute($parsedFileName, $directory);
            $summaries[$fileName] = $this->lastReport;
            file_put_contents(MM_OUTPUT_DIR . "foe/" . $parsedFileName . ".php", $outputCode);
            $count++;
            if ($count >= $max) {
                break;
            }
        }

        return $summaries;
    }

    private function getDirContents($dir, &$results = []): array
    {
        $files = scandir($dir);

        foreach ($files as $key => $value) {
            $path = realpath($dir . DIRECTORY_SEPARATOR . $value);
            if (!is_dir($path)) {
                $results[] = $path;
            } else {
                if ($value != "." && $value != "..") {
                    $this->getDirContents($path, $results);
                    $results[] = $path;
                }
            }
        }

        return $results;
    }
}

