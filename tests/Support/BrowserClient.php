<?php

namespace Tests\Support;

class BrowserResponse
{
    private int $statusCode;
    private array $headers;
    private ?string $redirect;
    private string $html;
    private array $session;

    public function __construct(array $data)
    {
        $this->statusCode = (int)($data['status_code'] ?? 200);
        $this->headers = $data['headers'] ?? [];
        $this->redirect = $data['redirect'] ?? null;
        $this->html = $data['html'] ?? '';
        $this->session = $data['session'] ?? [];
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getRedirect(): ?string
    {
        return $this->redirect;
    }

    public function getHtml(): string
    {
        return $this->html;
    }

    public function getSession(): array
    {
        return $this->session;
    }

    public function assertStatus(int $expectedCode, string $message = ''): void
    {
        if ($this->statusCode !== $expectedCode) {
            $msg = $message ?: "Expected status code $expectedCode, but got {$this->statusCode}. Response HTML: " . substr($this->html, 0, 300);
            throw new AssertionException($msg);
        }
    }

    public function assertRedirect(?string $expectedSubstring = null, string $message = ''): void
    {
        if ($this->statusCode !== 302 && $this->statusCode !== 301) {
            $msg = $message ?: "Expected redirect (301/302), but got status code {$this->statusCode}.";
            throw new AssertionException($msg);
        }

        if ($expectedSubstring !== null && $this->redirect !== null && !str_contains($this->redirect, $expectedSubstring)) {
            $msg = $message ?: "Expected redirect URL to contain '$expectedSubstring', but got '{$this->redirect}'.";
            throw new AssertionException($msg);
        }
    }

    public function assertSee(string $text, string $message = ''): void
    {
        if (!str_contains($this->html, $text)) {
            $msg = $message ?: "Expected response HTML to contain text '$text'.";
            throw new AssertionException($msg);
        }
    }

    public function assertDontSee(string $text, string $message = ''): void
    {
        if (str_contains($this->html, $text)) {
            $msg = $message ?: "Expected response HTML NOT to contain text '$text'.";
            throw new AssertionException($msg);
        }
    }
}

class BrowserClient
{
    private array $session = [];

    public function __construct(array $initialSession = [])
    {
        $this->session = $initialSession;
    }

    public function getSession(): array
    {
        return $this->session;
    }

    public function setSession(array $session): void
    {
        $this->session = $session;
    }

    public function clearSession(): void
    {
        $this->session = [];
    }

    public function get(string $script, array $query = []): BrowserResponse
    {
        return $this->execute('GET', $script, [], $query);
    }

    public function post(string $script, array $data = [], array $query = []): BrowserResponse
    {
        return $this->execute('POST', $script, $data, $query);
    }

    public function extractCsrfToken(string $html): ?string
    {
        if (preg_match('/name=["\']csrf_token["\']\s+value=["\']([a-f0-9]{64})["\']/i', $html, $matches)) {
            return $matches[1];
        }
        if (preg_match('/value=["\']([a-f0-9]{64})["\']\s+name=["\']csrf_token["\']/i', $html, $matches)) {
            return $matches[1];
        }
        return null;
    }

    private function execute(string $method, string $script, array $postData = [], array $query = []): BrowserResponse
    {
        $payload = [
            'method'  => $method,
            'script'  => $script,
            'post'    => $postData,
            'get'     => $query,
            'session' => $this->session,
        ];

        $workerPath = __DIR__ . '/browser_worker.php';
        $cmd = 'php -d session.save_path=/tmp ' . escapeshellarg($workerPath) . ' ' . escapeshellarg(json_encode($payload));

        $output = shell_exec($cmd) ?: '';

        // Extract JSON marker
        if (preg_match('/---E2E_RESPONSE_START---\s*(.*?)\s*---E2E_RESPONSE_END---/s', $output, $m)) {
            $responseData = json_decode($m[1], true);
            if (is_array($responseData)) {
                // Update persistent session
                if (isset($responseData['session']) && is_array($responseData['session'])) {
                    $this->session = $responseData['session'];
                }
                return new BrowserResponse($responseData);
            }
        }

        throw new \RuntimeException("E2E worker failed to produce valid response output: " . substr($output, 0, 500));
    }
}
