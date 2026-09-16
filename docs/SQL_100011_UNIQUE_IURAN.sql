-- Aplikasi Iuran Kantin MTsN 4 Jombang
-- Aturan: satu Penjual maksimal satu transaksi Iuran per tanggal.
--
-- LANGKAH 1: Jalankan SELECT ini terlebih dahulu.
-- Jika menghasilkan satu atau lebih baris, STOP. Koreksi data ganda terlebih dahulu.

SELECT
    `id_penjual`,
    `tanggal`,
    COUNT(*) AS `jumlah`
FROM `transaksi_iuran`
GROUP BY `id_penjual`, `tanggal`
HAVING COUNT(*) > 1
ORDER BY `tanggal`, `id_penjual`;

-- LANGKAH 2: Jalankan ALTER TABLE HANYA jika SELECT di atas menghasilkan 0 baris.

ALTER TABLE `transaksi_iuran`
ADD UNIQUE KEY `uniq_iuran_penjual_tanggal` (`id_penjual`, `tanggal`);

-- LANGKAH 3: Verifikasi index.

SHOW INDEX FROM `transaksi_iuran`
WHERE `Key_name` = 'uniq_iuran_penjual_tanggal';
