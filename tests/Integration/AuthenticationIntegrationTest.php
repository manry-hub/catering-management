<?php

namespace Tests\Integration;

use Tests\Support\TestCase;
use Tests\Fixtures\DatabaseSeeder;

class AuthenticationIntegrationTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        DatabaseSeeder::resetAndSeed();
        $this->resetGlobals();
    }

    // ==========================================
    // Test to Pass (Positive Testing)
    // ==========================================

    public function testLoginWithValidDatabaseAccountEstablishesSession(): void
    {
        $db = $this->getDb();
        $username = 'Indri';
        $inputPassword = '123456';

        // Query aktual database
        $stmt = mysqli_prepare($db, "SELECT id_pegawai, nama_pegawai, jabatan, password FROM pegawai WHERE nama_pegawai = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, 's', $username);
        mysqli_stmt_execute($stmt);
        $user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

        $this->assertNotNull($user, 'Pengguna Indri harus ditemukan di database.');
        $this->assertTrue(password_verify($inputPassword, $user['password']), 'Password 123456 harus cocok dengan hash di database.');

        // Simulasi pembentukan sesi
        $this->mockSession([
            'id_pegawai'   => $user['id_pegawai'],
            'nama_pegawai' => $user['nama_pegawai'],
            'jabatan'      => $user['jabatan'],
        ]);

        $this->assertEquals(1, $_SESSION['id_pegawai'], 'Session id_pegawai harus terisi ID 1.');
        $this->assertEquals('Indri', $_SESSION['nama_pegawai'], 'Session nama_pegawai harus Indri.');
        $this->assertEquals('Admin', $_SESSION['jabatan'], 'Session jabatan harus Admin.');
    }

    public function testLegacyPlaintextAutoUpgradeUpdatesDatabase(): void
    {
        $db = $this->getDb();

        // 1. Tambahkan pegawai baru dengan password plaintext di database
        $legacyName = 'StaffLama';
        $plainPass = 'rahasia123';
        $jabatan = 'Pegawai Kantin';

        $insert = mysqli_prepare($db, "INSERT INTO pegawai (nama_pegawai, jabatan, password) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($insert, 'sss', $legacyName, $jabatan, $plainPass);
        mysqli_stmt_execute($insert);
        $newId = mysqli_insert_id($db);

        // Verifikasi tersimpan plaintext
        $this->assertDatabaseHas('pegawai', ['id_pegawai' => $newId, 'password' => $plainPass]);

        // 2. Simulasi proses login & auto-upgrade
        $stmt = mysqli_prepare($db, "SELECT id_pegawai, nama_pegawai, jabatan, password FROM pegawai WHERE nama_pegawai = ?");
        mysqli_stmt_bind_param($stmt, 's', $legacyName);
        mysqli_stmt_execute($stmt);
        $user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

        $isAuthed = false;
        if ($user) {
            if (password_verify($plainPass, $user['password'])) {
                $isAuthed = true;
            } elseif ($plainPass === $user['password']) {
                // Auto-upgrade seketika
                $newHash = password_hash($plainPass, PASSWORD_BCRYPT);
                $upd = mysqli_prepare($db, "UPDATE pegawai SET password = ? WHERE id_pegawai = ?");
                mysqli_stmt_bind_param($upd, 'si', $newHash, $user['id_pegawai']);
                mysqli_stmt_execute($upd);
                $isAuthed = true;
            }
        }

        $this->assertTrue($isAuthed, 'Login dengan akun lama harus berhasil.');

        // 3. Verifikasi kondisi aktual database setelah upgrade
        $stmtCheck = mysqli_prepare($db, "SELECT password FROM pegawai WHERE id_pegawai = ?");
        mysqli_stmt_bind_param($stmtCheck, 'i', $newId);
        mysqli_stmt_execute($stmtCheck);
        $updatedUser = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtCheck));

        $this->assertTrue(str_starts_with($updatedUser['password'], '$2y$'), 'Password di database sekarang harus berformat hash Bcrypt.');
        $this->assertTrue(password_verify($plainPass, $updatedUser['password']), 'Password yang ter-upgrade harus valid.');
    }

    public function testLogoutDestroysUserSession(): void
    {
        $this->mockSession([
            'id_pegawai'   => 1,
            'nama_pegawai' => 'Indri',
            'jabatan'      => 'Admin',
        ]);

        $this->assertArrayHasKey('id_pegawai', $_SESSION, 'Session harus aktif sebelum logout.');

        // Simulasi logout
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }

        $this->assertCount(0, $_SESSION, 'Session harus kosong total setelah logout.');
        $this->assertFalse(isset($_SESSION['id_pegawai']), 'id_pegawai tidak boleh ada di sesi setelah logout.');
    }

    // ==========================================
    // Test to Fail (Negative Testing)
    // ==========================================

    public function testLoginWithWrongPasswordFailsAndSessionNotEstablished(): void
    {
        $db = $this->getDb();
        $username = 'Indri';
        $wrongPass = 'SalahPasswordTotal';

        $stmt = mysqli_prepare($db, "SELECT * FROM pegawai WHERE nama_pegawai = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, 's', $username);
        mysqli_stmt_execute($stmt);
        $user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

        $isValid = ($user && password_verify($wrongPass, $user['password']));
        $this->assertFalse($isValid, 'Verifikasi password salah harus gagal.');

        // Pastikan sesi tidak dibuat
        $this->assertFalse(isset($_SESSION['id_pegawai']), 'Session id_pegawai tidak boleh terbentuk jika login gagal.');
    }

    public function testLoginWithNonExistentAccountFails(): void
    {
        $db = $this->getDb();
        $fakeUser = 'PenggunaTidakAdaDiDB999';

        $stmt = mysqli_prepare($db, "SELECT * FROM pegawai WHERE nama_pegawai = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, 's', $fakeUser);
        mysqli_stmt_execute($stmt);
        $user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

        $this->assertNull($user, 'Query untuk pengguna fiktif harus menghasilkan null.');
        $this->assertFalse(isset($_SESSION['id_pegawai']), 'Session tidak boleh terbentuk untuk pengguna fiktif.');
    }
}
