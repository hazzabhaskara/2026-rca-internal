-- ============================================
-- SQL Script untuk Mapping Klasifikasi Akun
-- Jalankan di phpMyAdmin atau MySQL client
-- ============================================

-- PENTING: Sesuaikan ID klasifikasi dengan database Anda terlebih dahulu!
-- Jalankan query ini untuk melihat ID klasifikasi:
-- SELECT id, nama_klasifikasi FROM klasifikasi_akuns;

-- ============================================
-- LANGKAH 1: Cek ID Klasifikasi Akun
-- ============================================
-- Jalankan dulu query ini untuk mendapatkan ID:
SELECT id, nama_klasifikasi FROM klasifikasi_akuns ORDER BY id;

-- ============================================
-- LANGKAH 2: Update Chart of Accounts
-- ============================================
-- Ganti @cash_id, @bank_id, dll dengan ID yang sesuai dari hasil query di atas

-- Set variabel (sesuaikan dengan ID di database Anda)
SET @cash_id = (SELECT id FROM klasifikasi_akuns WHERE nama_klasifikasi = 'Cash' LIMIT 1);
SET @bank_id = (SELECT id FROM klasifikasi_akuns WHERE nama_klasifikasi = 'Bank' LIMIT 1);
SET @piutang_usaha_id = (SELECT id FROM klasifikasi_akuns WHERE nama_klasifikasi = 'Piutang Usaha' LIMIT 1);
SET @piutang_lain_id = (SELECT id FROM klasifikasi_akuns WHERE nama_klasifikasi = 'Piutang Lain-Lain' LIMIT 1);
SET @persediaan_id = (SELECT id FROM klasifikasi_akuns WHERE nama_klasifikasi = 'Persediaan' LIMIT 1);
SET @biaya_dimuka_id = (SELECT id FROM klasifikasi_akuns WHERE nama_klasifikasi = 'Biaya Dibayar Dimuka' LIMIT 1);
SET @aset_tetap_id = (SELECT id FROM klasifikasi_akuns WHERE nama_klasifikasi = 'Aset Tetap' LIMIT 1);
SET @akum_penyusutan_id = (SELECT id FROM klasifikasi_akuns WHERE nama_klasifikasi = 'Akumulasi Penyusutan' LIMIT 1);
SET @hutang_usaha_id = (SELECT id FROM klasifikasi_akuns WHERE nama_klasifikasi = 'Hutang Usaha' LIMIT 1);
SET @hutang_lain_id = (SELECT id FROM klasifikasi_akuns WHERE nama_klasifikasi = 'Hutang Lain-Lain' LIMIT 1);
SET @modal_id = (SELECT id FROM klasifikasi_akuns WHERE nama_klasifikasi = 'Modal' LIMIT 1);
SET @pendapatan_id = (SELECT id FROM klasifikasi_akuns WHERE nama_klasifikasi = 'Pendapatan' LIMIT 1);
SET @hpp_id = (SELECT id FROM klasifikasi_akuns WHERE nama_klasifikasi = 'Harga Pokok Penjualan' LIMIT 1);
SET @beban_ops_id = (SELECT id FROM klasifikasi_akuns WHERE nama_klasifikasi = 'Beban Operasional' LIMIT 1);
SET @pendapatan_lain_id = (SELECT id FROM klasifikasi_akuns WHERE nama_klasifikasi = 'Pendapatan Lain' LIMIT 1);
SET @beban_lain_id = (SELECT id FROM klasifikasi_akuns WHERE nama_klasifikasi = 'Beban Lain' LIMIT 1);

-- ============================================
-- Update Kas (1101xxx)
-- ============================================
UPDATE chart_of_accounts 
SET klasifikasi_id = @cash_id 
WHERE kode_akun >= '1101001' AND kode_akun <= '1101999' 
AND level_akun IN ('SUB ACCOUNT', 'ACCOUNT')
AND (klasifikasi_id IS NULL OR klasifikasi_id = 0);

-- ============================================
-- Update Bank (1102xxx)
-- ============================================
UPDATE chart_of_accounts 
SET klasifikasi_id = @bank_id 
WHERE kode_akun >= '1102000' AND kode_akun <= '1102999' 
AND level_akun IN ('SUB ACCOUNT', 'ACCOUNT')
AND (klasifikasi_id IS NULL OR klasifikasi_id = 0);

-- ============================================
-- Update Piutang Usaha (1103xxx)
-- ============================================
UPDATE chart_of_accounts 
SET klasifikasi_id = @piutang_usaha_id 
WHERE kode_akun >= '1103000' AND kode_akun <= '1103999' 
AND level_akun IN ('SUB ACCOUNT', 'ACCOUNT')
AND (klasifikasi_id IS NULL OR klasifikasi_id = 0);

-- ============================================
-- Update Piutang Lain-Lain (1104xxx)
-- ============================================
UPDATE chart_of_accounts 
SET klasifikasi_id = @piutang_lain_id 
WHERE kode_akun >= '1104000' AND kode_akun <= '1104999' 
AND level_akun IN ('SUB ACCOUNT', 'ACCOUNT')
AND (klasifikasi_id IS NULL OR klasifikasi_id = 0);

-- ============================================
-- Update Persediaan (1105xxx)
-- ============================================
UPDATE chart_of_accounts 
SET klasifikasi_id = @persediaan_id 
WHERE kode_akun >= '1105000' AND kode_akun <= '1105999' 
AND level_akun IN ('SUB ACCOUNT', 'ACCOUNT')
AND (klasifikasi_id IS NULL OR klasifikasi_id = 0);

-- ============================================
-- Update Biaya Dibayar Dimuka (1106xxx)
-- ============================================
UPDATE chart_of_accounts 
SET klasifikasi_id = @biaya_dimuka_id 
WHERE kode_akun >= '1106000' AND kode_akun <= '1106999' 
AND level_akun IN ('SUB ACCOUNT', 'ACCOUNT')
AND (klasifikasi_id IS NULL OR klasifikasi_id = 0);

-- ============================================
-- Update Aset Tetap (120xxxx - 121xxxx)
-- ============================================
UPDATE chart_of_accounts 
SET klasifikasi_id = @aset_tetap_id 
WHERE kode_akun >= '1200000' AND kode_akun <= '1209999' 
AND level_akun IN ('SUB ACCOUNT', 'ACCOUNT')
AND (klasifikasi_id IS NULL OR klasifikasi_id = 0);

-- ============================================
-- Update Akumulasi Penyusutan (121xxxx - 129xxxx)
-- ============================================
UPDATE chart_of_accounts 
SET klasifikasi_id = @akum_penyusutan_id 
WHERE kode_akun >= '1210000' AND kode_akun <= '1299999' 
AND level_akun IN ('SUB ACCOUNT', 'ACCOUNT')
AND (klasifikasi_id IS NULL OR klasifikasi_id = 0);

-- ============================================
-- Update Hutang Usaha (2101xxx)
-- ============================================
UPDATE chart_of_accounts 
SET klasifikasi_id = @hutang_usaha_id 
WHERE kode_akun >= '2101000' AND kode_akun <= '2101999' 
AND level_akun IN ('SUB ACCOUNT', 'ACCOUNT')
AND (klasifikasi_id IS NULL OR klasifikasi_id = 0);

-- ============================================
-- Update Hutang Lain-Lain (2102xxx - 2199xxx)
-- ============================================
UPDATE chart_of_accounts 
SET klasifikasi_id = @hutang_lain_id 
WHERE kode_akun >= '2102000' AND kode_akun <= '2199999' 
AND level_akun IN ('SUB ACCOUNT', 'ACCOUNT')
AND (klasifikasi_id IS NULL OR klasifikasi_id = 0);

-- ============================================
-- Update Modal (3xxxxxx)
-- ============================================
UPDATE chart_of_accounts 
SET klasifikasi_id = @modal_id 
WHERE kode_akun >= '3000000' AND kode_akun <= '3999999' 
AND level_akun IN ('SUB ACCOUNT', 'ACCOUNT')
AND (klasifikasi_id IS NULL OR klasifikasi_id = 0);

-- ============================================
-- Update Pendapatan (41xxxxx)
-- ============================================
UPDATE chart_of_accounts 
SET klasifikasi_id = @pendapatan_id 
WHERE kode_akun >= '4100000' AND kode_akun <= '4199999' 
AND level_akun IN ('SUB ACCOUNT', 'ACCOUNT')
AND (klasifikasi_id IS NULL OR klasifikasi_id = 0);

-- ============================================
-- Update Pendapatan Lain (42xxxxx - 49xxxxx)
-- ============================================
UPDATE chart_of_accounts 
SET klasifikasi_id = @pendapatan_lain_id 
WHERE kode_akun >= '4200000' AND kode_akun <= '4999999' 
AND level_akun IN ('SUB ACCOUNT', 'ACCOUNT')
AND (klasifikasi_id IS NULL OR klasifikasi_id = 0);

-- ============================================
-- Update HPP (5xxxxxx)
-- ============================================
UPDATE chart_of_accounts 
SET klasifikasi_id = @hpp_id 
WHERE kode_akun >= '5000000' AND kode_akun <= '5999999' 
AND level_akun IN ('SUB ACCOUNT', 'ACCOUNT')
AND (klasifikasi_id IS NULL OR klasifikasi_id = 0);

-- ============================================
-- Update Beban Operasional (6xxxxxx)
-- ============================================
UPDATE chart_of_accounts 
SET klasifikasi_id = @beban_ops_id 
WHERE kode_akun >= '6000000' AND kode_akun <= '6999999' 
AND level_akun IN ('SUB ACCOUNT', 'ACCOUNT')
AND (klasifikasi_id IS NULL OR klasifikasi_id = 0);

-- ============================================
-- Update Beban Lain (7xxxxxx)
-- ============================================
UPDATE chart_of_accounts 
SET klasifikasi_id = @beban_lain_id 
WHERE kode_akun >= '7000000' AND kode_akun <= '7999999' 
AND level_akun IN ('SUB ACCOUNT', 'ACCOUNT')
AND (klasifikasi_id IS NULL OR klasifikasi_id = 0);

-- ============================================
-- Update Pendapatan Lain (8xxxxxx)
-- ============================================
UPDATE chart_of_accounts 
SET klasifikasi_id = @pendapatan_lain_id 
WHERE kode_akun >= '8000000' AND kode_akun <= '8999999' 
AND level_akun IN ('SUB ACCOUNT', 'ACCOUNT')
AND (klasifikasi_id IS NULL OR klasifikasi_id = 0);

-- ============================================
-- LANGKAH 3: Verifikasi Hasil
-- ============================================
SELECT 
    c.kode_akun, 
    c.nama_akun, 
    c.level_akun,
    k.nama_klasifikasi 
FROM chart_of_accounts c 
LEFT JOIN klasifikasi_akuns k ON c.klasifikasi_id = k.id 
WHERE c.level_akun IN ('SUB ACCOUNT', 'ACCOUNT')
ORDER BY c.kode_akun;

-- Cek akun yang masih belum terklasifikasi
SELECT kode_akun, nama_akun, level_akun 
FROM chart_of_accounts 
WHERE klasifikasi_id IS NULL 
AND level_akun IN ('SUB ACCOUNT', 'ACCOUNT')
ORDER BY kode_akun;
