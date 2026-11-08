<?php

/**
 * Kantin Gorjes Test Runner CLI
 * Usage:
 *   php tests/runner.php                  # Run all tests (Unit + Integration)
 *   php tests/runner.php --unit           # Run only Unit tests
 *   php tests/runner.php --integration    # Run only Integration tests
 *   php tests/runner.php --filter=Name    # Run tests matching Name
 */

declare(strict_types=1);

require_once __DIR__ . '/Support/TestCase.php';
require_once __DIR__ . '/Support/DatabaseTester.php';
require_once __DIR__ . '/Support/TestRunner.php';
require_once __DIR__ . '/Fixtures/DatabaseSeeder.php';

// Simple PSR-4 style autoloader for tests
spl_autoload_register(function ($class) {
    if (str_starts_with($class, 'Tests\\')) {
        $path = __DIR__ . '/' . str_replace('\\', '/', substr($class, 6)) . '.php';
        if (file_exists($path)) {
            require_once $path;
        }
    }
});

// Load test environment
\Tests\Support\DatabaseTester::loadEnv();

// Parse CLI options
$args = $argv ?? [];
$isUnitOnly = in_array('--unit', $args, true);
$isIntegrationOnly = in_array('--integration', $args, true);
$isE2EOnly = in_array('--e2e', $args, true);
$filter = '';

foreach ($args as $arg) {
    if (str_starts_with($arg, '--filter=')) {
        $filter = substr($arg, 9);
    }
}

// Find files
$files = [];

if ($isUnitOnly) {
    $files = glob(__DIR__ . '/Unit/*Test.php') ?: [];
} elseif ($isIntegrationOnly) {
    // Reset test database before integration tests
    echo "\033[90mResetting and seeding isolated test database...\033[0m\n";
    \Tests\Fixtures\DatabaseSeeder::resetAndSeed();
    $files = glob(__DIR__ . '/Integration/*Test.php') ?: [];
} elseif ($isE2EOnly) {
    echo "\033[90mResetting and seeding isolated test database for E2E...\033[0m\n";
    \Tests\Fixtures\DatabaseSeeder::resetAndSeed();
    $files = glob(__DIR__ . '/E2E/*Test.php') ?: [];
} else {
    // Full suite
    $unitFiles = glob(__DIR__ . '/Unit/*Test.php') ?: [];
    $intFiles = glob(__DIR__ . '/Integration/*Test.php') ?: [];
    $e2eFiles = glob(__DIR__ . '/E2E/*Test.php') ?: [];
    if (!empty($intFiles) || !empty($e2eFiles)) {
        echo "\033[90mResetting and seeding isolated test database...\033[0m\n";
        \Tests\Fixtures\DatabaseSeeder::resetAndSeed();
    }
    $files = array_merge($unitFiles, $intFiles, $e2eFiles);
}

if (empty($files)) {
    echo "\033[33mTidak ada file pengujian yang ditemukan untuk dieksekusi.\033[0m\n";
    exit(0);
}

$runner = new \Tests\Support\TestRunner();
$exitCode = $runner->run($files, $filter);
exit($exitCode);
