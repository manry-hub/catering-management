<?php

namespace Tests\Unit;

use Tests\Support\TestCase;

class AuthenticationTest extends TestCase
{
    private string $defaultPassword = 'password123';
    private string $hashedPassword;

    public function setUp(): void
    {
        $this->hashedPassword = password_hash($this->defaultPassword, PASSWORD_BCRYPT);
    }

    // ==========================================
    // Test to Pass (Positive Testing)
    // ==========================================

    public function testValidPasswordVerificationPasses(): void
    {
        $isValid = password_verify($this->defaultPassword, $this->hashedPassword);
        $this->assertTrue($isValid, 'Kata sandi yang benar harus berhasil diverifikasi.');
    }

    public function testBcryptHashFormatIsValid(): void
    {
        $this->assertTrue(str_starts_with($this->hashedPassword, '$2y$'), 'Hash harus diawali prefix Bcrypt $2y$.');
        $this->assertEquals(60, strlen($this->hashedPassword), 'Panjang hash Bcrypt standar harus 60 karakter.');
    }

    public function testLegacyPlaintextAutoUpgradeDetection(): void
    {
        $legacyStoredPassword = 'plain123456';
        $inputPassword = 'plain123456';

        // Deteksi kecocokan plaintext warisan
        $isLegacyMatch = ($inputPassword === $legacyStoredPassword);
        $this->assertTrue($isLegacyMatch, 'Kata sandi lama harus terdeteksi jika cocok secara plaintext.');

        // Simulasi auto-upgrade ke Bcrypt
        $upgradedHash = password_hash($inputPassword, PASSWORD_BCRYPT);
        $this->assertTrue(str_starts_with($upgradedHash, '$2y$'), 'Kata sandi lama harus berhasil di-upgrade ke hash Bcrypt.');
        $this->assertTrue(password_verify($inputPassword, $upgradedHash), 'Kata sandi input harus valid terhadap hash upgrade baru.');
    }

    public function testRoleAccessAllowedForAuthorizedRole(): void
    {
        $allowedRoles = ['Owner', 'Admin'];
        $userRole = 'Admin';

        $isAuthorized = in_array($userRole, $allowedRoles, true);
        $this->assertTrue($isAuthorized, 'Pengguna dengan peran Admin harus diizinkan mengakses modul manajerial.');
    }

    public function testCsrfTokenGenerationFormat(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

        $token = $_SESSION['csrf_token'];
        $this->assertEquals(64, strlen($token), 'Token CSRF harus terdiri dari 64 karakter hexadesimal.');
        $this->assertTrue(ctype_xdigit($token), 'Token CSRF harus merupakan karakter hex yang valid.');
    }

    // ==========================================
    // Test to Fail (Negative Testing)
    // ==========================================

    public function testInvalidPasswordVerificationFails(): void
    {
        $wrongPassword = 'WrongPassword999';
        $isValid = password_verify($wrongPassword, $this->hashedPassword);
        $this->assertFalse($isValid, 'Kata sandi yang salah harus ditolak.');
    }

    public function testEmptyPasswordFails(): void
    {
        $emptyPassword = '';
        $isValid = password_verify($emptyPassword, $this->hashedPassword);
        $this->assertFalse($isValid, 'Kata sandi kosong harus ditolak oleh password_verify.');
    }

    public function testEmptyCredentialsValidationRejection(): void
    {
        $testCases = [
            ['nama' => '', 'password' => '123456'],
            ['nama' => 'Indri', 'password' => ''],
            ['nama' => '', 'password' => ''],
            ['nama' => '   ', 'password' => '   '],
        ];

        foreach ($testCases as $idx => $case) {
            $nama = trim($case['nama']);
            $pwd = trim($case['password']);
            $hasError = (empty($nama) || empty($pwd));

            $this->assertTrue($hasError, "Kasus #$idx kredensial kosong harus terdeteksi tidak valid.");
        }
    }

    public function testRoleAccessDeniedForUnauthorizedRole(): void
    {
        $allowedRoles = ['Owner', 'Admin'];
        $unauthorizedRoles = ['Pegawai Kantin', 'Pegawai Catering', 'Pegawai Dapur', 'Guest', ''];

        foreach ($unauthorizedRoles as $role) {
            $isAuthorized = in_array($role, $allowedRoles, true);
            $this->assertFalse($isAuthorized, "Peran '$role' harus ditolak dari modul khusus Owner/Admin.");
        }
    }

    public function testTamperedCsrfTokenFailsVerification(): void
    {
        $validToken = bin2hex(random_bytes(32));
        $tamperedToken = $validToken . 'attacker_payload';

        $isValid = hash_equals($validToken, $tamperedToken);
        $this->assertFalse($isValid, 'Token CSRF yang dimodifikasi harus ditolak.');

        $isEmptyValid = hash_equals($validToken, '');
        $this->assertFalse($isEmptyValid, 'Token CSRF kosong harus ditolak.');
    }
}
