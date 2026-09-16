-- Snapshot Golongan pada transaksi_iuran
-- Jalankan di phpMyAdmin sebelum source aplikasi yang memakai fitur ini diaktifkan.
-- Aman untuk MariaDB production yang mendukung ADD COLUMN/INDEX IF NOT EXISTS.

ALTER TABLE `transaksi_iuran`
    ADD COLUMN IF NOT EXISTS `id_golongan_snapshot` INT(11) UNSIGNED NULL AFTER `id_penjual`,
    ADD COLUMN IF NOT EXISTS `nama_golongan_snapshot` VARCHAR(100) NULL AFTER `id_golongan_snapshot`,
    ADD COLUMN IF NOT EXISTS `nominal_golongan_snapshot` DECIMAL(12,2) NULL AFTER `nama_golongan_snapshot`;

-- Backfill data historis memakai kondisi Golongan Penjual saat SQL ini dijalankan.
-- Setelah fitur aktif, transaksi baru menyimpan snapshot ketika Input Iuran disimpan.
UPDATE `transaksi_iuran` AS `ti`
INNER JOIN `penjual` AS `p`
    ON `p`.`id_penjual` = `ti`.`id_penjual`
LEFT JOIN `golongan_penjual` AS `g`
    ON `g`.`id_golongan` = `p`.`id_golongan`
SET
    `ti`.`id_golongan_snapshot` = COALESCE(`ti`.`id_golongan_snapshot`, `p`.`id_golongan`),
    `ti`.`nama_golongan_snapshot` = COALESCE(`ti`.`nama_golongan_snapshot`, `g`.`nama_golongan`),
    `ti`.`nominal_golongan_snapshot` = COALESCE(`ti`.`nominal_golongan_snapshot`, `g`.`nominal_iuran`)
WHERE `ti`.`id_golongan_snapshot` IS NULL
   OR `ti`.`nama_golongan_snapshot` IS NULL
   OR `ti`.`nominal_golongan_snapshot` IS NULL;

ALTER TABLE `transaksi_iuran`
    ADD INDEX IF NOT EXISTS `idx_iuran_golongan_snapshot` (`id_golongan_snapshot`);

-- Verifikasi: seharusnya jumlah_belum_snapshot = 0.
SELECT COUNT(*) AS `jumlah_belum_snapshot`
FROM `transaksi_iuran`
WHERE `id_golongan_snapshot` IS NULL
   OR `nama_golongan_snapshot` IS NULL
   OR `nominal_golongan_snapshot` IS NULL;

-- Sampel verifikasi hasil backfill.
SELECT
    `id_transaksi`,
    `id_penjual`,
    `tanggal`,
    `id_golongan_snapshot`,
    `nama_golongan_snapshot`,
    `nominal_golongan_snapshot`,
    `nominal`
FROM `transaksi_iuran`
ORDER BY `id_transaksi` DESC
LIMIT 20;
