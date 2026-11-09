-- ============================================================
-- Sistem Antrian Gudang - Data awal
-- Akun admin default:
--   username : admin
--   password : admin123
-- SEGERA ganti password setelah login pertama.
-- ============================================================

INSERT INTO `admin` (`username`, `password`, `role`, `aktif`)
SELECT 'admin', '$2y$10$d07GpHTIfDoo.8OVbObz5uRFLMceLO7PBlPH0oxVJ4lTr4hM/Tgx2', 'admin', 1
WHERE NOT EXISTS (SELECT 1 FROM `admin` WHERE `username` = 'admin');
