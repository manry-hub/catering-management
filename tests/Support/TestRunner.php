<?php

namespace Tests\Support;

class TestRunner
{
    private array $results = [];
    private int $totalAssertions = 0;
    private float $startTime = 0;

    public function run(array $files, string $filter = ''): int
    {
        $this->startTime = microtime(true);
        $this->results = [];
        $this->totalAssertions = 0;

        echo "\n\033[1;36m========================================================\033[0m\n";
        echo "\033[1;36m  Kantin Gorjes Automated Test Suite (Native PHP 8.x)  \033[0m\n";
        echo "\033[1;36m========================================================\033[0m\n\n";

        foreach ($files as $file) {
            if (!file_exists($file)) continue;

            require_once $file;
            $classes = $this->getClassesInFile($file);

            foreach ($classes as $className) {
                if (!is_subclass_of($className, TestCase::class)) {
                    continue;
                }

                if ($filter && !str_contains($className, $filter)) {
                    continue;
                }

                $this->runTestClass($className, $filter);
            }
        }

        $elapsed = round((microtime(true) - $this->startTime) * 1000, 2);
        return $this->printSummary($elapsed);
    }

    private function runTestClass(string $className, string $filter): void
    {
        $reflection = new \ReflectionClass($className);
        $shortName = $reflection->getShortName();
        $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);

        echo "\033[1;34m" . $shortName . "\033[0m\n";

        foreach ($methods as $method) {
            $name = $method->getName();
            if (!str_starts_with($name, 'test')) {
                continue;
            }

            if ($filter && !str_contains($name, $filter) && !str_contains($className, $filter)) {
                continue;
            }

            /** @var TestCase $testInstance */
            $testInstance = new $className();
            $status = 'PASS';
            $errorMsg = null;
            $trace = null;
            $methodStart = microtime(true);

            try {
                $testInstance->setUp();
                $testInstance->$name();
                $testInstance->tearDown();
            } catch (AssertionException $e) {
                $status = 'FAIL';
                $errorMsg = $e->getMessage();
                $trace = $e->getFile() . ':' . $e->getLine();
            } catch (\Throwable $e) {
                $status = 'ERROR';
                $errorMsg = get_class($e) . ': ' . $e->getMessage();
                $trace = $e->getFile() . ':' . $e->getLine();
            }

            $methodElapsed = round((microtime(true) - $methodStart) * 1000, 1);
            $assertions = $testInstance->getAssertionCount();
            $this->totalAssertions += $assertions;

            $this->results[] = [
                'class'      => $shortName,
                'method'     => $name,
                'status'     => $status,
                'error'      => $errorMsg,
                'trace'      => $trace,
                'assertions' => $assertions,
                'time_ms'    => $methodElapsed,
            ];

            if ($status === 'PASS') {
                echo "  \033[32m✔ PASS\033[0m  {$name} \033[90m({$methodElapsed}ms, {$assertions} assertions)\033[0m\n";
            } elseif ($status === 'FAIL') {
                echo "  \033[31m✘ FAIL\033[0m  {$name} \033[90m({$methodElapsed}ms)\033[0m\n";
                echo "         \033[31mError: {$errorMsg}\033[0m\n";
                echo "         \033[90mAt: {$trace}\033[0m\n";
            } else {
                echo "  \033[33m⚠ ERROR\033[0m {$name} \033[90m({$methodElapsed}ms)\033[0m\n";
                echo "         \033[33mError: {$errorMsg}\033[0m\n";
                echo "         \033[90mAt: {$trace}\033[0m\n";
            }
        }

        echo "\n";
    }

    private function printSummary(float $elapsedMs): int
    {
        $total = count($this->results);
        $passed = count(array_filter($this->results, fn($r) => $r['status'] === 'PASS'));
        $failed = count(array_filter($this->results, fn($r) => $r['status'] === 'FAIL'));
        $errors = count(array_filter($this->results, fn($r) => $r['status'] === 'ERROR'));

        echo "\033[1;36m--------------------------------------------------------\033[0m\n";
        echo "Results: Total: $total | ";
        echo "\033[32mPassed: $passed\033[0m | ";
        if ($failed > 0) {
            echo "\033[31mFailed: $failed\033[0m | ";
        } else {
            echo "Failed: 0 | ";
        }
        if ($errors > 0) {
            echo "\033[33mErrors: $errors\033[0m | ";
        } else {
            echo "Errors: 0 | ";
        }
        echo "Assertions: {$this->totalAssertions} | Time: {$elapsedMs}ms\n";
        echo "\033[1;36m========================================================\033[0m\n\n";

        // Save JSON report for audit records
        $resultDir = dirname(__DIR__, 2) . '/test-results';
        if (!is_dir($resultDir)) {
            mkdir($resultDir, 0777, true);
        }
        $reportFile = $resultDir . '/latest_run.json';
        file_put_contents($reportFile, json_encode([
            'timestamp'  => date('c'),
            'summary'    => [
                'total'      => $total,
                'passed'     => $passed,
                'failed'     => $failed,
                'errors'     => $errors,
                'assertions' => $this->totalAssertions,
                'time_ms'    => $elapsedMs,
            ],
            'results'    => $this->results,
        ], JSON_PRETTY_PRINT));

        return ($failed > 0 || $errors > 0) ? 1 : 0;
    }

    private function getClassesInFile(string $file): array
    {
        $phpCode = file_get_contents($file);
        $classes = [];
        $namespace = '';

        $tokens = token_get_all($phpCode);
        $count = count($tokens);

        for ($i = 0; $i < $count; $i++) {
            if ($tokens[$i][0] === T_NAMESPACE) {
                $ns = '';
                for ($j = $i + 1; $j < $count; $j++) {
                    if ($tokens[$j] === '{' || $tokens[$j] === ';') break;
                    if (is_array($tokens[$j])) $ns .= $tokens[$j][1];
                }
                $namespace = trim($ns);
            }

            if ($tokens[$i][0] === T_CLASS) {
                if ($i > 1 && $tokens[$i - 2][0] === T_DOUBLE_COLON) continue;
                for ($j = $i + 1; $j < $count; $j++) {
                    if ($tokens[$j][0] === T_STRING) {
                        $className = $tokens[$j][1];
                        $fullClass = $namespace ? "$namespace\\$className" : $className;
                        $classes[] = $fullClass;
                        break;
                    }
                }
            }
        }

        return $classes;
    }
}
