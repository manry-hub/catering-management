-- CABANG
INSERT INTO cabang_kantin (nama_cabang, alamat_cabang) VALUES
                                                           ('SMP Islam Al Azhar 2 Jakarta Selatan','Jakarta Selatan'),
                                                           ('SMP Islam Al Azhar 1 Kebayoran','Kebayoran');

-- PEGAWAI
INSERT INTO pegawai (nama_pegawai,jabatan,password) VALUES
                                                        ('Indri','Admin','123456'),
                                                        ('Rina Putri','Pegawai Catering','123456'),
                                                        ('Dewi Lestari','Pegawai Kantin','123456'),
                                                        ('Siska Nur','Pegawai Dapur','123456');


-- PELANGGAN
INSERT INTO pelanggan (nama_pelanggan,no_telp,alamat_pelanggan) VALUES
                                                                    ('Ahmad Fauzi','081234567890','Jakarta Selatan'),
                                                                    ('Siti Aminah','081234567891','Kebayoran'),
                                                                    ('Rizky Maulana','081234567892','Depok'),
                                                                    ('Dina Maharani','081234567893','Jakarta Selatan'),
                                                                    ('Andi Saputra','081234567894','Tangerang');


-- PRODUK
INSERT INTO produk
(nama_produk, harga_jual, kategori, harga_pokok)
VALUES

-- MAKANAN (KANTIN)
('Dimsum Keju Lumer', 8000, 'Makanan (Kantin)', 5000),
('Fettuccine Carbonara', 20000, 'Makanan (Kantin)', 13000),
('Brulee', 25000, 'Makanan (Kantin)', 17000),
('Siomay', 15000, 'Makanan (Kantin)', 9000),
('Batagor', 10000, 'Makanan (Kantin)', 6000),
('Cimol Keju', 10000, 'Makanan (Kantin)', 6500),
('Samyang Keju', 20000, 'Makanan (Kantin)', 14000),
('Dimsum Mentai Small', 10000, 'Makanan (Kantin)', 6500),
('Dimsum Mentai Medium', 15dml000, 'Makanan (Kantin)', 10000),
('Mini Cordon Bleu', 10000, 'Makanan (Kantin)', 7000),

-- DESSERT (KANTIN)
('Cheese Cake', 18000, 'Dessert (Kantin)', 12000),
('Puding Dark Choco', 12000, 'Dessert (Kantin)', 7000),
('Silky Puding', 8000, 'Dessert (Kantin)', 5000),
('Slice Fruit Melon', 10000, 'Dessert (Kantin)', 6000),
('Slice Fruit Semangka', 10000, 'Dessert (Kantin)', 6000),
('Sago Mango', 15000, 'Dessert (Kantin)', 9000),

-- SNACK (KANTIN)
('Soft Cookies', 12000, 'Snack (Kantin)', 7000),
('Croffle', 8000, 'Snack (Kantin)', 5000),
('Aneka Risoles', 7000, 'Snack (Kantin)', 4000),

-- PAKET NASI BOX
('Paket Nasi Box 1', 25000, 'Paket Nasi Box', 18000),
('Paket Nasi Box 2', 25000, 'Paket Nasi Box', 18000),
('Paket Nasi Box 3', 25000, 'Paket Nasi Box', 18000),
('Paket Nasi Box 4', 25000, 'Paket Nasi Box', 18000),

-- MENU CATERING HARIAN
('Chicken Basil Thailand', 22000, 'Catering Harian', 15000),
('Ayam Fillet Crispy', 22000, 'Catering Harian', 15000),
('Fish and Chips', 22000, 'Catering Harian', 15000),
('Sate Lilit', 22000, 'Catering Harian', 15000),
('Ebi Furai', 22000, 'Catering Harian', 15000);

-- =========================================
-- BAHAN BAKU LENGKAP + PACKAGING
-- =========================================

INSERT INTO bahan_baku
(nama_bahan, satuan, harga_satuan_bahan, stok, stok_kritis, status_ketersediaan)
VALUES

-- =========================================
-- BAHAN DASAR
-- =========================================
('Tepung Terigu', 'Kg', 13000, 20, 5, 'Tersedia'),
('Tepung Tapioka', 'Kg', 12000, 15, 5, 'Tersedia'),
('Tepung Panir', 'Kg', 18000, 10, 3, 'Tersedia'),
('Tepung Maizena', 'Kg', 15000, 5, 2, 'Tersedia'),
('Tepung Custard', 'Kg', 35000, 3, 1, 'Tersedia'),
('Beras', 'Kg', 16000, 50, 10, 'Tersedia'),
('Mie Samyang', 'Pack', 15000, 20, 5, 'Tersedia'),
('Fettuccine Pasta', 'Pack', 18000, 15, 3, 'Tersedia'),

-- =========================================
-- PROTEIN
-- =========================================
('Ayam Fillet', 'Kg', 50000, 15, 3, 'Tersedia'),
('Daging Ayam Giling', 'Kg', 45000, 10, 3, 'Tersedia'),
('Ikan Tenggiri', 'Kg', 70000, 8, 2, 'Tersedia'),
('Ikan Dori Fillet', 'Kg', 65000, 8, 2, 'Tersedia'),
('Udang', 'Kg', 85000, 8, 2, 'Tersedia'),
('Sosis', 'Pack', 25000, 10, 3, 'Tersedia'),
('Smoked Beef', 'Pack', 35000, 10, 2, 'Tersedia'),
('Telur', 'Butir', 2500, 100, 20, 'Tersedia'),

-- =========================================
-- SUSU & KEJU
-- =========================================
('Keju Mozzarella', 'Kg', 95000, 5, 1, 'Tersedia'),
('Keju Cheddar', 'Kg', 85000, 5, 1, 'Tersedia'),
('Keju Bubuk', 'Kg', 90000, 3, 1, 'Tersedia'),
('Susu Cair', 'Liter', 18000, 10, 2, 'Tersedia'),
('Whipping Cream', 'Liter', 45000, 5, 1, 'Tersedia'),
('Butter', 'Kg', 90000, 5, 1, 'Tersedia'),
('Cream Cheese', 'Kg', 120000, 3, 1, 'Tersedia'),
('Susu Kental Manis', 'Kaleng', 13000, 20, 5, 'Tersedia'),

-- =========================================
-- SAYUR & BUAH
-- =========================================
('Bawang Putih', 'Kg', 35000, 5, 1, 'Tersedia'),
('Bawang Merah', 'Kg', 40000, 5, 1, 'Tersedia'),
('Bawang Bombay', 'Kg', 30000, 5, 1, 'Tersedia'),
('Daun Bawang', 'Ikat', 8000, 10, 2, 'Tersedia'),
('Seledri', 'Ikat', 7000, 10, 2, 'Tersedia'),
('Kol/Kubis', 'Kg', 12000, 10, 2, 'Tersedia'),
('Wortel', 'Kg', 15000, 10, 2, 'Tersedia'),
('Cabai', 'Kg', 40000, 3, 1, 'Tersedia'),
('Daun Basil', 'Ikat', 10000, 10, 2, 'Tersedia'),
('Kentang', 'Kg', 18000, 10, 2, 'Tersedia'),
('Timun', 'Kg', 12000, 5, 1, 'Tersedia'),
('Selada', 'Kg', 18000, 5, 1, 'Tersedia'),
('Melon', 'Buah', 25000, 10, 2, 'Tersedia'),
('Semangka', 'Buah', 30000, 10, 2, 'Tersedia'),
('Mangga', 'Kg', 35000, 10, 2, 'Tersedia'),
('Lemon', 'Kg', 40000, 3, 1, 'Tersedia'),

-- =========================================
-- DESSERT & BAKERY
-- =========================================
('Biskuit Marie', 'Pack', 12000, 10, 2, 'Tersedia'),
('Coklat Bubuk', 'Kg', 70000, 3, 1, 'Tersedia'),
('Dark Chocolate', 'Kg', 95000, 3, 1, 'Tersedia'),
('Chocolate Chips', 'Kg', 75000, 5, 1, 'Tersedia'),
('Agar-Agar', 'Pack', 6000, 20, 5, 'Tersedia'),
('Gelatin', 'Pack', 18000, 10, 2, 'Tersedia'),
('Vanilla Essence', 'Botol', 25000, 5, 1, 'Tersedia'),
('Santan', 'Liter', 18000, 10, 2, 'Tersedia'),
('Sagu Mutiara', 'Kg', 22000, 5, 1, 'Tersedia'),
('Croissant Dough', 'Pack', 35000, 10, 2, 'Tersedia'),
('Kulit Lumpia', 'Pack', 15000, 10, 2, 'Tersedia'),

-- =========================================
-- GULA & BAHAN KUE
-- =========================================
('Gula Pasir', 'Kg', 17000, 15, 5, 'Tersedia'),
('Gula Halus', 'Kg', 20000, 10, 2, 'Tersedia'),
('Gula Merah', 'Kg', 22000, 5, 1, 'Tersedia'),
('Baking Powder', 'Pack', 12000, 10, 2, 'Tersedia'),

-- =========================================
-- BUMBU & SEASONING
-- =========================================
('Garam', 'Kg', 10000, 5, 1, 'Tersedia'),
('Merica', 'Kg', 80000, 2, 1, 'Tersedia'),
('Kaldu Bubuk', 'Pack', 12000, 10, 2, 'Tersedia'),
('MSG/Penyedap', 'Pack', 9000, 10, 2, 'Tersedia'),
('Bawang Bubuk', 'Pack', 15000, 5, 1, 'Tersedia'),
('Cabai Bubuk', 'Pack', 18000, 5, 1, 'Tersedia'),
('Parsley/Oregano', 'Pack', 25000, 5, 1, 'Tersedia'),

-- =========================================
-- SAUCE & PELENGKAP
-- =========================================
('Minyak Goreng', 'Liter', 18000, 20, 5, 'Tersedia'),
('Mayonnaise', 'Botol', 25000, 10, 2, 'Tersedia'),
('Saus Mentai', 'Botol', 35000, 8, 2, 'Tersedia'),
('Saus Tomat', 'Botol', 15000, 10, 2, 'Tersedia'),
('Saus Sambal', 'Botol', 16000, 10, 2, 'Tersedia'),
('Kecap', 'Botol', 18000, 10, 2, 'Tersedia'),
('Saus Tiram', 'Botol', 22000, 8, 2, 'Tersedia'),
('Fish Sauce', 'Botol', 30000, 5, 1, 'Tersedia'),
('Tartar Sauce', 'Botol', 28000, 5, 1, 'Tersedia'),

-- =========================================
-- PACKAGING
-- =========================================
('Box Nasi', 'Pcs', 1500, 300, 50, 'Tersedia'),
('Box Dessert', 'Pcs', 1000, 300, 50, 'Tersedia'),
('Cup Sauce', 'Pcs', 300, 500, 100, 'Tersedia'),
('Cup Minuman', 'Pcs', 800, 300, 50, 'Tersedia'),
('Tutup Cup', 'Pcs', 300, 300, 50, 'Tersedia'),
('Sendok Plastik', 'Pcs', 250, 500, 100, 'Tersedia'),
('Garpu Plastik', 'Pcs', 250, 500, 100, 'Tersedia'),
('Sedotan', 'Pcs', 150, 500, 100, 'Tersedia'),
('Tisu', 'Pack', 12000, 50, 10, 'Tersedia'),
('Plastik Kemasan', 'Pcs', 500, 500, 100, 'Tersedia'),
('Paper Bag', 'Pcs', 1200, 200, 30, 'Tersedia'),
('Stiker Label', 'Pcs', 250, 500, 100, 'Tersedia');

-- =========================================
-- BOM : MAKANAN (KANTIN)
-- =========================================

INSERT INTO bom
(id_produk, id_bahan_baku, qty, subtotal)
VALUES

-- =====================================================
-- 1. Dimsum Keju Lumer
-- =====================================================
(1, 2, 0.03, 360),      -- Tepung Tapioka
(1, 9, 0.05, 2250),     -- Ayam Giling
(1, 18, 0.02, 1900),    -- Mozzarella
(1, 17, 1, 2500),       -- Telur
(1, 27, 0.01, 350),     -- Bawang Putih
(1, 30, 0.02, 160),     -- Daun Bawang
(1, 60, 0.005, 50),     -- Garam
(1, 61, 0.002, 160),    -- Merica

-- =====================================================
-- 2. Fettuccine Carbonara
-- =====================================================
(2, 7, 0.20, 3600),     -- Fettuccine
(2, 21, 0.08, 1440),    -- Susu
(2, 22, 0.05, 2250),    -- Whipping Cream
(2, 19, 0.03, 2550),    -- Keju Cheddar
(2, 15, 0.20, 5000),    -- Sosis
(2, 29, 0.03, 900),     -- Bawang Bombay
(2, 27, 0.01, 350),     -- Bawang Putih
(2, 18, 1, 2500),       -- Telur
(2, 61, 0.002, 160),    -- Merica
(2, 67, 0.005, 125),    -- Parsley/Oregano
(2, 68, 0.01, 180),     -- Minyak

-- =====================================================
-- 3. Brulee
-- =====================================================
(3, 21, 0.15, 2700),    -- Susu Cair
(3, 24, 0.05, 6000),    -- Cream Cheese
(3, 17, 2, 5000),       -- Telur
(3, 55, 0.03, 510),     -- Gula Pasir
(3, 51, 0.005, 125),    -- Vanilla Essence
(3, 23, 0.01, 900),     -- Butter

-- =====================================================
-- 4. Siomay
-- =====================================================
(4, 10, 0.07, 4900),    -- Ikan Tenggiri
(4, 2, 0.03, 360),      -- Tapioka
(4, 17, 1, 2500),       -- Telur
(4, 27, 0.01, 350),     -- Bawang Putih
(4, 30, 0.02, 160),     -- Daun Bawang
(4, 60, 0.005, 50),     -- Garam
(4, 62, 0.005, 60),     -- Kaldu Bubuk

-- =====================================================
-- 5. Batagor
-- =====================================================
(5, 10, 0.05, 3500),    -- Tenggiri
(5, 2, 0.03, 360),      -- Tapioka
(5, 17, 1, 2500),       -- Telur
(5, 54, 0.20, 3000),    -- Kulit Lumpia
(5, 68, 0.03, 540),     -- Minyak
(5, 71, 0.02, 320),     -- Saus Sambal
(5, 60, 0.003, 30),     -- Garam

-- =====================================================
-- 6. Cimol Keju
-- =====================================================
(6, 2, 0.08, 960),      -- Tapioka
(6, 20, 0.01, 900),     -- Keju Bubuk
(6, 64, 0.003, 45),     -- Bawang Bubuk
(6, 68, 0.03, 540),     -- Minyak
(6, 60, 0.003, 30),     -- Garam

-- =====================================================
-- 7. Samyang Keju
-- =====================================================
(7, 6, 1, 15000),       -- Mie Samyang
(7, 19, 0.03, 2550),    -- Cheddar
(7, 20, 0.01, 900),     -- Keju Bubuk
(7, 65, 0.005, 90),     -- Cabai Bubuk
(7, 69, 0.01, 250),     -- Mayonnaise

-- =====================================================
-- 8. Dimsum Mentai Small
-- =====================================================
(8, 2, 0.03, 360),
(8, 9, 0.05, 2250),
(8, 17, 1, 2500),
(8, 27, 0.01, 350),
(8, 70, 0.03, 1050),    -- Saus Mentai
(8, 68, 0.01, 180),

-- =====================================================
-- 9. Dimsum Mentai Medium
-- =====================================================
(9, 2, 0.05, 600),
(9, 9, 0.08, 3600),
(9, 17, 1, 2500),
(9, 27, 0.01, 350),
(9, 70, 0.05, 1750),
(9, 68, 0.01, 180),

-- =====================================================
-- 10. Mini Cordon Bleu
-- =====================================================
(10, 8, 0.10, 5000),    -- Ayam Fillet
(10, 3, 0.03, 540),     -- Panir
(10, 19, 0.02, 1700),   -- Cheddar
(10, 16, 0.03, 1050),   -- Smoked Beef
(10, 17, 1, 2500),      -- Telur
(10, 68, 0.02, 360),    -- Minyak
(10, 60, 0.003, 30),    -- Garam
(10, 61, 0.002, 160);   -- Merica

-- =========================================
-- BOM : DESSERT (KANTIN)
-- =========================================

INSERT INTO bom
(id_produk, id_bahan_baku, qty, subtotal)
VALUES

-- =====================================================
-- 11. Cheese Cake
-- =====================================================
(11, 24, 0.050, 6000),   -- Cream Cheese
(11, 41, 0.100, 1200),   -- Biskuit Marie
(11, 23, 0.020, 1800),   -- Butter
(11, 55, 0.020, 340),    -- Gula Pasir
(11, 51, 0.003, 75),     -- Vanilla Essence

-- =====================================================
-- 12. Puding Dark Choco
-- =====================================================
(12, 42, 0.020, 1400),   -- Coklat Bubuk
(12, 43, 0.020, 1900),   -- Dark Chocolate
(12, 21, 0.200, 3600),   -- Susu Cair
(12, 45, 1.000, 6000),   -- Agar-Agar
(12, 55, 0.020, 340),    -- Gula Pasir
(12, 51, 0.002, 50),     -- Vanilla Essence

-- =====================================================
-- 13. Silky Puding
-- =====================================================
(13, 21, 0.200, 3600),   -- Susu Cair
(13, 45, 1.000, 6000),   -- Agar-Agar
(13, 32, 0.050, 650),    -- Susu Kental Manis
(13, 55, 0.015, 255),    -- Gula Pasir
(13, 51, 0.002, 50),     -- Vanilla Essence

-- =====================================================
-- 14. Slice Fruit Melon
-- =====================================================
(14, 38, 0.250, 6250),   -- Melon

-- =====================================================
-- 15. Slice Fruit Semangka
-- =====================================================
(15, 39, 0.250, 7500),   -- Semangka

-- =====================================================
-- 16. Sago Mango
-- =====================================================
(16, 40, 0.100, 3500),   -- Mangga
(16, 53, 0.030, 660),    -- Sagu Mutiara
(16, 52, 0.100, 1800),   -- Santan
(16, 21, 0.050, 900),    -- Susu Cair
(16, 55, 0.020, 340),    -- Gula Pasir
(16, 51, 0.002, 50);     -- Vanilla Essence

-- =========================================
-- BOM : SNACK (KANTIN)
-- =========================================

INSERT INTO bom
(id_produk, id_bahan_baku, qty, subtotal)
VALUES

-- =====================================================
-- 17. Soft Cookies
-- =====================================================
(17, 1, 0.050, 650),     -- Tepung Terigu
(17, 23, 0.020, 1800),   -- Butter
(17, 44, 0.020, 1500),   -- Chocolate Chips
(17, 18, 1.000, 2500),   -- Telur
(17, 55, 0.020, 340),    -- Gula Pasir
(17, 58, 0.003, 36),     -- Baking Powder
(17, 51, 0.002, 50),     -- Vanilla Essence
(17, 60, 0.001, 10),     -- Garam

-- =====================================================
-- 18. Croffle
-- =====================================================
(18, 54, 1.000, 35000),  -- Croissant Dough
(18, 23, 0.010, 900),    -- Butter
(18, 55, 0.010, 170),    -- Gula Pasir

-- =====================================================
-- 19. Aneka Risoles
-- =====================================================
(19, 54, 0.200, 3000),   -- Kulit Lumpia
(19, 8, 0.030, 1500),    -- Ayam Fillet
(19, 34, 0.020, 300),    -- Wortel
(19, 29, 0.010, 300),    -- Bawang Bombay
(19, 19, 0.020, 1700),   -- Keju Cheddar
(19, 21, 0.030, 540),    -- Susu Cair
(19, 1, 0.010, 130),     -- Tepung Terigu
(19, 3, 0.020, 360),     -- Tepung Panir
(19, 18, 1.000, 2500),   -- Telur
(19, 68, 0.020, 360),    -- Minyak Goreng
(19, 60, 0.002, 20),     -- Garam
(19, 62, 0.002, 24);     -- Kaldu Bubuk


-- =========================================
-- BOM : PAKET NASI BOX (FK SAFE)
-- =========================================

INSERT INTO bom (id_produk,id_bahan_baku,qty,subtotal)

-- =====================================================
-- 20. Paket Nasi Box 1
-- Nasi + Ayam Crispy + Sambal + Timun
-- =====================================================
SELECT 20,id_bahan_baku,0.150,2400 FROM bahan_baku WHERE nama_bahan='Beras'
UNION ALL
SELECT 20,id_bahan_baku,0.100,5000 FROM bahan_baku WHERE nama_bahan='Ayam Fillet'
UNION ALL
SELECT 20,id_bahan_baku,0.030,540 FROM bahan_baku WHERE nama_bahan='Tepung Panir'
UNION ALL
SELECT 20,id_bahan_baku,1.000,2500 FROM bahan_baku WHERE nama_bahan='Telur'
UNION ALL
SELECT 20,id_bahan_baku,0.020,360 FROM bahan_baku WHERE nama_bahan='Minyak Goreng'
UNION ALL
SELECT 20,id_bahan_baku,0.020,320 FROM bahan_baku WHERE nama_bahan='Saus Sambal'
UNION ALL
SELECT 20,id_bahan_baku,0.030,360 FROM bahan_baku WHERE nama_bahan='Timun'
UNION ALL
SELECT 20,id_bahan_baku,1.000,1500 FROM bahan_baku WHERE nama_bahan='Box Nasi'
UNION ALL
SELECT 20,id_bahan_baku,1.000,250 FROM bahan_baku WHERE nama_bahan='Sendok Plastik'
UNION ALL
SELECT 20,id_bahan_baku,0.050,600 FROM bahan_baku WHERE nama_bahan='Tisu'
UNION ALL
SELECT 20,id_bahan_baku,1.000,500 FROM bahan_baku WHERE nama_bahan='Plastik Kemasan'

-- =====================================================
-- 21. Paket Nasi Box 2
-- Nasi + Chicken Basil
-- =====================================================
UNION ALL
SELECT 21,id_bahan_baku,0.150,2400 FROM bahan_baku WHERE nama_bahan='Beras'
UNION ALL
SELECT 21,id_bahan_baku,0.100,5000 FROM bahan_baku WHERE nama_bahan='Ayam Fillet'
UNION ALL
SELECT 21,id_bahan_baku,0.020,200 FROM bahan_baku WHERE nama_bahan='Daun Basil'
UNION ALL
SELECT 21,id_bahan_baku,0.010,350 FROM bahan_baku WHERE nama_bahan='Bawang Putih'
UNION ALL
SELECT 21,id_bahan_baku,0.010,400 FROM bahan_baku WHERE nama_bahan='Bawang Merah'
UNION ALL
SELECT 21,id_bahan_baku,0.010,220 FROM bahan_baku WHERE nama_bahan='Saus Tiram'
UNION ALL
SELECT 21,id_bahan_baku,0.005,150 FROM bahan_baku WHERE nama_bahan='Fish Sauce'
UNION ALL
SELECT 21,id_bahan_baku,0.015,270 FROM bahan_baku WHERE nama_bahan='Minyak Goreng'
UNION ALL
SELECT 21,id_bahan_baku,1.000,1500 FROM bahan_baku WHERE nama_bahan='Box Nasi'
UNION ALL
SELECT 21,id_bahan_baku,1.000,250 FROM bahan_baku WHERE nama_bahan='Sendok Plastik'
UNION ALL
SELECT 21,id_bahan_baku,0.050,600 FROM bahan_baku WHERE nama_bahan='Tisu'
UNION ALL
SELECT 21,id_bahan_baku,1.000,500 FROM bahan_baku WHERE nama_bahan='Plastik Kemasan'

-- =====================================================
-- 22. Paket Nasi Box 3
-- Nasi + Fish and Chips Mini
-- =====================================================
UNION ALL
SELECT 22,id_bahan_baku,0.150,2400 FROM bahan_baku WHERE nama_bahan='Beras'
UNION ALL
SELECT 22,id_bahan_baku,0.100,6500 FROM bahan_baku WHERE nama_bahan='Ikan Dori Fillet'
UNION ALL
SELECT 22,id_bahan_baku,0.030,540 FROM bahan_baku WHERE nama_bahan='Tepung Panir'
UNION ALL
SELECT 22,id_bahan_baku,0.100,1800 FROM bahan_baku WHERE nama_bahan='Kentang'
UNION ALL
SELECT 22,id_bahan_baku,1.000,2500 FROM bahan_baku WHERE nama_bahan='Telur'
UNION ALL
SELECT 22,id_bahan_baku,0.030,540 FROM bahan_baku WHERE nama_bahan='Minyak Goreng'
UNION ALL
SELECT 22,id_bahan_baku,0.020,560 FROM bahan_baku WHERE nama_bahan='Tartar Sauce'
UNION ALL
SELECT 22,id_bahan_baku,1.000,1500 FROM bahan_baku WHERE nama_bahan='Box Nasi'
UNION ALL
SELECT 22,id_bahan_baku,1.000,250 FROM bahan_baku WHERE nama_bahan='Sendok Plastik'
UNION ALL
SELECT 22,id_bahan_baku,0.050,600 FROM bahan_baku WHERE nama_bahan='Tisu'
UNION ALL
SELECT 22,id_bahan_baku,1.000,500 FROM bahan_baku WHERE nama_bahan='Plastik Kemasan'

-- =====================================================
-- 23. Paket Nasi Box 4
-- Nasi + Ebi Furai
-- =====================================================
UNION ALL
SELECT 23,id_bahan_baku,0.150,2400 FROM bahan_baku WHERE nama_bahan='Beras'
UNION ALL
SELECT 23,id_bahan_baku,0.080,6800 FROM bahan_baku WHERE nama_bahan='Udang'
UNION ALL
SELECT 23,id_bahan_baku,0.030,540 FROM bahan_baku WHERE nama_bahan='Tepung Panir'
UNION ALL
SELECT 23,id_bahan_baku,1.000,2500 FROM bahan_baku WHERE nama_bahan='Telur'
UNION ALL
SELECT 23,id_bahan_baku,0.030,540 FROM bahan_baku WHERE nama_bahan='Minyak Goreng'
UNION ALL
SELECT 23,id_bahan_baku,0.020,320 FROM bahan_baku WHERE nama_bahan='Saus Sambal'
UNION ALL
SELECT 23,id_bahan_baku,1.000,1500 FROM bahan_baku WHERE nama_bahan='Box Nasi'
UNION ALL
SELECT 23,id_bahan_baku,1.000,250 FROM bahan_baku WHERE nama_bahan='Sendok Plastik'
UNION ALL
SELECT 23,id_bahan_baku,0.050,600 FROM bahan_baku WHERE nama_bahan='Tisu'
UNION ALL
SELECT 23,id_bahan_baku,1.000,500 FROM bahan_baku WHERE nama_bahan='Plastik Kemasan';

-- =========================================
-- BOM : CATERING HARIAN (FK SAFE)
-- =========================================

INSERT INTO bom (id_produk,id_bahan_baku,qty,subtotal)

-- =====================================================
-- 24. Chicken Basil Thailand
-- =====================================================
SELECT 24,id_bahan_baku,0.120,6000 FROM bahan_baku WHERE nama_bahan='Ayam Fillet'
UNION ALL
SELECT 24,id_bahan_baku,0.020,200 FROM bahan_baku WHERE nama_bahan='Daun Basil'
UNION ALL
SELECT 24,id_bahan_baku,0.010,350 FROM bahan_baku WHERE nama_bahan='Bawang Putih'
UNION ALL
SELECT 24,id_bahan_baku,0.010,400 FROM bahan_baku WHERE nama_bahan='Bawang Merah'
UNION ALL
SELECT 24,id_bahan_baku,0.010,220 FROM bahan_baku WHERE nama_bahan='Saus Tiram'
UNION ALL
SELECT 24,id_bahan_baku,0.005,150 FROM bahan_baku WHERE nama_bahan='Fish Sauce'
UNION ALL
SELECT 24,id_bahan_baku,0.020,360 FROM bahan_baku WHERE nama_bahan='Minyak Goreng'
UNION ALL
SELECT 24,id_bahan_baku,0.002,20 FROM bahan_baku WHERE nama_bahan='Garam'
UNION ALL
SELECT 24,id_bahan_baku,0.002,160 FROM bahan_baku WHERE nama_bahan='Merica'

-- =====================================================
-- 25. Ayam Fillet Crispy
-- =====================================================
UNION ALL
SELECT 25,id_bahan_baku,0.120,6000 FROM bahan_baku WHERE nama_bahan='Ayam Fillet'
UNION ALL
SELECT 25,id_bahan_baku,0.040,720 FROM bahan_baku WHERE nama_bahan='Tepung Panir'
UNION ALL
SELECT 25,id_bahan_baku,1.000,2500 FROM bahan_baku WHERE nama_bahan='Telur'
UNION ALL
SELECT 25,id_bahan_baku,0.030,540 FROM bahan_baku WHERE nama_bahan='Minyak Goreng'
UNION ALL
SELECT 25,id_bahan_baku,0.003,30 FROM bahan_baku WHERE nama_bahan='Garam'
UNION ALL
SELECT 25,id_bahan_baku,0.002,160 FROM bahan_baku WHERE nama_bahan='Merica'
UNION ALL
SELECT 25,id_bahan_baku,0.005,60 FROM bahan_baku WHERE nama_bahan='Kaldu Bubuk'

-- =====================================================
-- 26. Fish and Chips
-- =====================================================
UNION ALL
SELECT 26,id_bahan_baku,0.120,7800 FROM bahan_baku WHERE nama_bahan='Ikan Dori Fillet'
UNION ALL
SELECT 26,id_bahan_baku,0.150,2700 FROM bahan_baku WHERE nama_bahan='Kentang'
UNION ALL
SELECT 26,id_bahan_baku,0.040,720 FROM bahan_baku WHERE nama_bahan='Tepung Panir'
UNION ALL
SELECT 26,id_bahan_baku,1.000,2500 FROM bahan_baku WHERE nama_bahan='Telur'
UNION ALL
SELECT 26,id_bahan_baku,0.040,720 FROM bahan_baku WHERE nama_bahan='Minyak Goreng'
UNION ALL
SELECT 26,id_bahan_baku,0.020,560 FROM bahan_baku WHERE nama_bahan='Tartar Sauce'
UNION ALL
SELECT 26,id_bahan_baku,0.020,800 FROM bahan_baku WHERE nama_bahan='Lemon'
UNION ALL
SELECT 26,id_bahan_baku,0.002,20 FROM bahan_baku WHERE nama_bahan='Garam'

-- =====================================================
-- 27. Sate Lilit
-- Asumsi: Sate Lilit Ayam
-- =====================================================
UNION ALL
SELECT 27,id_bahan_baku,0.100,4500 FROM bahan_baku WHERE nama_bahan='Daging Ayam Giling'
UNION ALL
SELECT 27,id_bahan_baku,0.030,540 FROM bahan_baku WHERE nama_bahan='Santan'
UNION ALL
SELECT 27,id_bahan_baku,0.010,350 FROM bahan_baku WHERE nama_bahan='Bawang Putih'
UNION ALL
SELECT 27,id_bahan_baku,0.010,400 FROM bahan_baku WHERE nama_bahan='Bawang Merah'
UNION ALL
SELECT 27,id_bahan_baku,0.003,30 FROM bahan_baku WHERE nama_bahan='Garam'
UNION ALL
SELECT 27,id_bahan_baku,0.002,160 FROM bahan_baku WHERE nama_bahan='Merica'
UNION ALL
SELECT 27,id_bahan_baku,0.005,60 FROM bahan_baku WHERE nama_bahan='Kaldu Bubuk'

-- =====================================================
-- 28. Ebi Furai
-- =====================================================
UNION ALL
SELECT 28,id_bahan_baku,0.100,8500 FROM bahan_baku WHERE nama_bahan='Udang'
UNION ALL
SELECT 28,id_bahan_baku,0.040,720 FROM bahan_baku WHERE nama_bahan='Tepung Panir'
UNION ALL
SELECT 28,id_bahan_baku,1.000,2500 FROM bahan_baku WHERE nama_bahan='Telur'
UNION ALL
SELECT 28,id_bahan_baku,0.040,720 FROM bahan_baku WHERE nama_bahan='Minyak Goreng'
UNION ALL
SELECT 28,id_bahan_baku,0.020,320 FROM bahan_baku WHERE nama_bahan='Saus Sambal'
UNION ALL
SELECT 28,id_bahan_baku,0.002,20 FROM bahan_baku WHERE nama_bahan='Garam';

--  pesanan
--  detail pesanan