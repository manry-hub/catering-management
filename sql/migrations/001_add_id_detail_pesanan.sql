-- Migration 001: Menambahkan id_detail_pesanan sebagai surrogate primary key (opsional)
-- Skrip ini dapat dijalankan jika administrator database menginginkan ID tunggal per baris item pesanan.

ALTER TABLE detail_pesanan 
    ADD COLUMN id_detail_pesanan INT AUTO_INCREMENT FIRST,
    DROP PRIMARY KEY,
    ADD PRIMARY KEY (id_detail_pesanan),
    ADD UNIQUE KEY uq_pesanan_produk (id_pesanan, id_produk);
