<?php

declare(strict_types=1);

ini_set('session.save_path', '/tmp');
require_once dirname(__DIR__) . '/Support/DatabaseTester.php';
\Tests\Support\DatabaseTester::loadEnv();

$payloadJson = $argv[1] ?? '{}';
$payload = json_decode($payloadJson, true) ?: [];

$method = strtoupper($payload['method'] ?? 'GET');
$script = $payload['script'] ?? 'login.php';
$postData = $payload['post'] ?? [];
$getData = $payload['get'] ?? [];
$sessionData = $payload['session'] ?? [];

// Setup Globals
$_SERVER['REQUEST_METHOD'] = $method;
$_SERVER['SCRIPT_NAME'] = '/' . ltrim($script, '/');
$_SERVER['PHP_SELF'] = $_SERVER['SCRIPT_NAME'];
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['SERVER_NAME'] = 'localhost';
$_SERVER['SERVER_PORT'] = '80';
$_SERVER['REQUEST_URI'] = $_SERVER['SCRIPT_NAME'];

if (!empty($getData)) {
    $_GET = $getData;
    $_SERVER['QUERY_STRING'] = http_build_query($getData);
    $_SERVER['REQUEST_URI'] .= '?' . $_SERVER['QUERY_STRING'];
} else {
    $_GET = [];
    $_SERVER['QUERY_STRING'] = '';
}

$_POST = $postData;

// Start and populate session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$_SESSION = $sessionData;

$targetFile = dirname(__DIR__, 2) . '/' . ltrim($script, '/');

if (!file_exists($targetFile)) {
    echo json_encode([
        'status_code' => 404,
        'headers'     => [],
        'redirect'    => null,
        'html'        => 'File not found: ' . $script,
        'session'     => [],
    ]);
    exit(0);
}

// Intercept headers and shutdown
$capturedHeaders = [];
header_register_callback(function () use (&$capturedHeaders) {
    $capturedHeaders = headers_list();
});

register_shutdown_function(function () use (&$capturedHeaders) {
    $output = ob_get_clean();
    $headers = headers_list();
    $code = http_response_code() ?: 200;
    $redirect = null;

    foreach ($headers as $h) {
        if (stripos($h, 'Location:') === 0) {
            $redirect = trim(substr($h, 9));
            if ($code === 200) $code = 302;
            break;
        }
    }

    $response = [
        'status_code' => $code,
        'headers'     => $headers,
        'redirect'    => $redirect,
        'html'        => $output ?: '',
        'session'     => $_SESSION ?? [],
    ];

    echo "\n---E2E_RESPONSE_START---\n" . json_encode($response) . "\n---E2E_RESPONSE_END---\n";
});

ob_start();

// Change working directory to the target file's directory (critical for relative includes in procedural PHP)
chdir(dirname($targetFile));
require $targetFile;
