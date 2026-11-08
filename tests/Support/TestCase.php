<?php

namespace Tests\Support;

class AssertionException extends \Exception {}

abstract class TestCase
{
    protected ?\mysqli $db = null;
    protected int $assertionCount = 0;

    public function setUp(): void
    {
        // Hook for subclasses
    }

    public function tearDown(): void
    {
        // Hook for subclasses
    }

    public function getAssertionCount(): int
    {
        return $this->assertionCount;
    }

    /**
     * Get isolated test database connection
     */
    protected function getDb(): \mysqli
    {
        if ($this->db === null) {
            $this->db = DatabaseTester::getConnection();
        }
        return $this->db;
    }

    // ==========================================
    // Core Assertions
    // ==========================================

    public function assertTrue(mixed $condition, string $message = ''): void
    {
        $this->assertionCount++;
        if ($condition !== true) {
            $msg = $message ?: 'Failed asserting that condition is true.';
            throw new AssertionException($msg);
        }
    }

    public function assertFalse(mixed $condition, string $message = ''): void
    {
        $this->assertionCount++;
        if ($condition !== false) {
            $msg = $message ?: 'Failed asserting that condition is false.';
            throw new AssertionException($msg);
        }
    }

    public function assertEquals(mixed $expected, mixed $actual, string $message = ''): void
    {
        $this->assertionCount++;
        if ($expected != $actual) {
            $expStr = is_scalar($expected) ? var_export($expected, true) : json_encode($expected);
            $actStr = is_scalar($actual) ? var_export($actual, true) : json_encode($actual);
            $msg = $message ?: "Failed asserting that actual value $actStr matches expected $expStr.";
            throw new AssertionException($msg);
        }
    }

    public function assertSame(mixed $expected, mixed $actual, string $message = ''): void
    {
        $this->assertionCount++;
        if ($expected !== $actual) {
            $expStr = is_scalar($expected) ? var_export($expected, true) : json_encode($expected);
            $actStr = is_scalar($actual) ? var_export($actual, true) : json_encode($actual);
            $msg = $message ?: "Failed asserting that actual $actStr is identical to expected $expStr.";
            throw new AssertionException($msg);
        }
    }

    public function assertNotEquals(mixed $expected, mixed $actual, string $message = ''): void
    {
        $this->assertionCount++;
        if ($expected == $actual) {
            $expStr = is_scalar($expected) ? var_export($expected, true) : json_encode($expected);
            $msg = $message ?: "Failed asserting that actual value does not equal $expStr.";
            throw new AssertionException($msg);
        }
    }

    public function assertNull(mixed $actual, string $message = ''): void
    {
        $this->assertionCount++;
        if ($actual !== null) {
            $actStr = var_export($actual, true);
            $msg = $message ?: "Failed asserting that value $actStr is null.";
            throw new AssertionException($msg);
        }
    }

    public function assertNotNull(mixed $actual, string $message = ''): void
    {
        $this->assertionCount++;
        if ($actual === null) {
            $msg = $message ?: "Failed asserting that value is not null.";
            throw new AssertionException($msg);
        }
    }

    public function assertGreaterThan(float|int $expected, float|int $actual, string $message = ''): void
    {
        $this->assertionCount++;
        if (!($actual > $expected)) {
            $msg = $message ?: "Failed asserting that $actual is greater than $expected.";
            throw new AssertionException($msg);
        }
    }

    public function assertLessThan(float|int $expected, float|int $actual, string $message = ''): void
    {
        $this->assertionCount++;
        if (!($actual < $expected)) {
            $msg = $message ?: "Failed asserting that $actual is less than $expected.";
            throw new AssertionException($msg);
        }
    }

    public function assertContains(string $needle, string $haystack, string $message = ''): void
    {
        $this->assertionCount++;
        if (!str_contains($haystack, $needle)) {
            $msg = $message ?: "Failed asserting that '$haystack' contains '$needle'.";
            throw new AssertionException($msg);
        }
    }

    public function assertArrayHasKey(string|int $key, array $array, string $message = ''): void
    {
        $this->assertionCount++;
        if (!array_key_exists($key, $array)) {
            $msg = $message ?: "Failed asserting that array contains key '$key'.";
            throw new AssertionException($msg);
        }
    }

    public function assertCount(int $expectedCount, countable|array $countable, string $message = ''): void
    {
        $this->assertionCount++;
        $actualCount = count($countable);
        if ($expectedCount !== $actualCount) {
            $msg = $message ?: "Failed asserting that count is $expectedCount (actual: $actualCount).";
            throw new AssertionException($msg);
        }
    }

    public function assertThrows(string $expectedException, callable $callback, string $message = ''): void
    {
        $this->assertionCount++;
        $thrown = false;
        try {
            $callback();
        } catch (\Throwable $e) {
            if ($e instanceof $expectedException) {
                $thrown = true;
            } else {
                $actualClass = get_class($e);
                throw new AssertionException("Expected exception $expectedException, but caught $actualClass: " . $e->getMessage());
            }
        }

        if (!$thrown) {
            $msg = $message ?: "Failed asserting that exception $expectedException was thrown.";
            throw new AssertionException($msg);
        }
    }

    // ==========================================
    // Database Assertions
    // ==========================================

    public function assertDatabaseHas(string $table, array $criteria, string $message = ''): void
    {
        $this->assertionCount++;
        $db = $this->getDb();

        $whereClauses = [];
        $types = '';
        $values = [];

        foreach ($criteria as $col => $val) {
            $whereClauses[] = "`$col` = ?";
            if (is_int($val)) {
                $types .= 'i';
            } elseif (is_float($val)) {
                $types .= 'd';
            } else {
                $types .= 's';
                $val = (string)$val;
            }
            $values[] = $val;
        }

        $sql = "SELECT COUNT(*) AS c FROM `$table` WHERE " . implode(' AND ', $whereClauses);
        $stmt = mysqli_prepare($db, $sql);
        if (!$stmt) {
            throw new AssertionException("Database error in assertDatabaseHas: " . mysqli_error($db));
        }

        if (!empty($values)) {
            mysqli_stmt_bind_param($stmt, $types, ...$values);
        }
        mysqli_stmt_execute($stmt);
        $res = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        $count = (int)($res['c'] ?? 0);

        if ($count === 0) {
            $critStr = json_encode($criteria);
            $msg = $message ?: "Failed asserting that table `$table` contains record matching $critStr.";
            throw new AssertionException($msg);
        }
    }

    public function assertDatabaseMissing(string $table, array $criteria, string $message = ''): void
    {
        $this->assertionCount++;
        $db = $this->getDb();

        $whereClauses = [];
        $types = '';
        $values = [];

        foreach ($criteria as $col => $val) {
            $whereClauses[] = "`$col` = ?";
            if (is_int($val)) {
                $types .= 'i';
            } elseif (is_float($val)) {
                $types .= 'd';
            } else {
                $types .= 's';
                $val = (string)$val;
            }
            $values[] = $val;
        }

        $sql = "SELECT COUNT(*) AS c FROM `$table` WHERE " . implode(' AND ', $whereClauses);
        $stmt = mysqli_prepare($db, $sql);
        if (!$stmt) {
            throw new AssertionException("Database error in assertDatabaseMissing: " . mysqli_error($db));
        }

        if (!empty($values)) {
            mysqli_stmt_bind_param($stmt, $types, ...$values);
        }
        mysqli_stmt_execute($stmt);
        $res = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        $count = (int)($res['c'] ?? 0);

        if ($count > 0) {
            $critStr = json_encode($criteria);
            $msg = $message ?: "Failed asserting that table `$table` does not contain record matching $critStr (found $count).";
            throw new AssertionException($msg);
        }
    }

    // ==========================================
    // Simulation / Mock Helpers
    // ==========================================

    protected function mockSession(array $data): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        $_SESSION = array_merge($_SESSION, $data);
    }

    protected function mockPost(array $data): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = $data;
    }

    protected function mockGet(array $data): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET = $data;
    }

    protected function resetGlobals(): void
    {
        $_POST = [];
        $_GET = [];
        $_SESSION = [];
        $_SERVER['REQUEST_METHOD'] = 'GET';
    }
}
