<?php

namespace Tests\E2E;

use Tests\Support\TestCase;
use Tests\Support\BrowserClient;
use Tests\Fixtures\DatabaseSeeder;

class AuthenticationE2ETest extends TestCase
{
    private BrowserClient $browser;

    public function setUp(): void
    {
        parent::setUp();
        DatabaseSeeder::resetAndSeed();
        $this->browser = new BrowserClient();
    }

    // ==========================================
    // Test to Pass (Positive Scenarios)
    // ==========================================

    public function testUserCanViewLoginPageAndLoginSuccessfully(): void
    {
        // 1. Buka Halaman Login
        $resp = $this->browser->get('login.php');
        $resp->assertStatus(200);
        $resp->assertSee('Portal Admin');
        $resp->assertSee('Sign In');

        $csrfToken = $this->browser->extractCsrfToken($resp->getHtml());
        $this->assertNotNull($csrfToken, 'Halaman login harus menyertakan token CSRF.');

        // 2. Kirim Form Login dengan Kredensial Valid
        $loginResp = $this->browser->post('login.php', [
            'nama_pegawai' => 'Indri',
            'password'     => '123456',
            'csrf_token'   => $csrfToken,
        ]);

        // Ekspektasi: Redirect 302 dan sesi terbentuk
        $loginResp->assertStatus(302);
        $session = $this->browser->getSession();
        $this->assertEquals(1, $session['id_pegawai'] ?? null, 'Sesi id_pegawai harus bernilai 1.');
        $this->assertEquals('Admin', $session['jabatan'] ?? null, 'Sesi jabatan harus bernilai Admin.');

        // 3. Akses Halaman Dashboard dengan Sesi Aktif
        $dashResp = $this->browser->get('dashboard/index.php');
        $dashResp->assertStatus(200);
        $dashResp->assertSee('Dashboard');
        $dashResp->assertSee('Total Order');

        // 4. Logout Pengguna
        $logoutResp = $this->browser->get('logout.php');
        $logoutResp->assertStatus(302);
        $this->assertNull($this->browser->getSession()['id_pegawai'] ?? null, 'Sesi harus bersih pasca logout.');
    }

    // ==========================================
    // Test to Fail (Negative Scenarios)
    // ==========================================

    public function testLoginFailsWithWrongPassword(): void
    {
        $resp = $this->browser->get('login.php');
        $csrfToken = $this->browser->extractCsrfToken($resp->getHtml());

        $loginResp = $this->browser->post('login.php', [
            'nama_pegawai' => 'Indri',
            'password'     => 'PasswordSalahTotal',
            'csrf_token'   => $csrfToken,
        ]);

        $loginResp->assertStatus(200);
        $loginResp->assertSee('Nama atau password salah.');
        $this->assertNull($this->browser->getSession()['id_pegawai'] ?? null, 'Sesi id_pegawai tidak boleh terbentuk.');
    }

    public function testLoginFailsWithEmptyCredentials(): void
    {
        $resp = $this->browser->get('login.php');
        $csrfToken = $this->browser->extractCsrfToken($resp->getHtml());

        $loginResp = $this->browser->post('login.php', [
            'nama_pegawai' => '',
            'password'     => '',
            'csrf_token'   => $csrfToken,
        ]);

        $loginResp->assertStatus(200);
        $loginResp->assertSee('Nama atau password salah.');
    }

    public function testLoginFailsWithInvalidCsrfToken(): void
    {
        $loginResp = $this->browser->post('login.php', [
            'nama_pegawai' => 'Indri',
            'password'     => '123456',
            'csrf_token'   => 'invalid_tampered_csrf_token_1234567890abcdef1234567890abcdef12345678',
        ]);

        $loginResp->assertStatus(302);
        $session = $this->browser->getSession();
        $this->assertNull($session['id_pegawai'] ?? null);
        $this->assertContains('CSRF Token', $session['flash_error'] ?? '');
    }

    public function testUnauthenticatedUserCannotAccessDashboard(): void
    {
        // Langsung akses dashboard tanpa login
        $dashResp = $this->browser->get('dashboard/index.php');
        $dashResp->assertStatus(302);
        $dashResp->assertRedirect('login.php');
    }
}
