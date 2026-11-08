<?php
/**
 * Skrip CLI untuk migrasi kata sandi pegawai dari plaintext ke hash Bcrypt.
 * Jalankan via CLI: php sql/migrate_passwords.php
 */

if (php_sapi_name() !== 'cli') {
    die("Skrip ini hanya dapat dijalankan melalui CLI untuk alasan keamanan.\n");
}

require_once __DIR__ . '/../layouts/config.php';

echo "Memulai migrasi kata sandi pegawai ke Bcrypt...\n";

$sql = "SELECT id_pegawai, nama_pegawai, password FROM pegawai";
$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Gagal mengambil data pegawai: " . mysqli_error($conn) . "\n");
}

$migrated = 0;
$skipped = 0;

while ($row = mysqli_fetch_assoc($result)) {
    $id = $row['id_pegawai'];
    $nama = $row['nama_pegawai'];
    $pwd = $row['password'];

    // Cek apakah password sudah berformat hash Bcrypt ($2y$)
    if (str_starts_with($pwd, '$2y$')) {
        echo "- Pegawai ID $id ($nama): Sudah berformat Bcrypt hash. Dilewati.\n";
        $skipped++;
        continue;
    }

    $hashed = password_hash($pwd, PASSWORD_BCRYPT);
    $upd = mysqli_prepare($conn, "UPDATE pegawai SET password = ? WHERE id_pegawai = ?");
    mysqli_stmt_bind_param($upd, 'si', $hashed, $id);

    if (mysqli_stmt_execute($upd)) {
        echo "+ Pegawai ID $id ($nama): Berhasil di-hash ke Bcrypt.\n";
        $migrated++;
    } else {
        echo "! Pegawai ID $id ($nama): Gagal memperbarui: " . mysqli_error($conn) . "\n";
    }
}

echo "Migrasi selesai! Total dimigrasikan: $migrated, dilewati: $skipped.\n";
