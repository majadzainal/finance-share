USE finance_share;

INSERT INTO users (name, username, password, role, status)
SELECT 'Administrator', 'admin', '$2y$10$ifyPtxOV5qsvUElX1afUGerKoXddr6OFK1YLwfD5yB3yDagVoJmxK', 'admin', 1
WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'admin');

INSERT INTO mst_roles (code, name, description)
VALUES
    ('admin', 'Administrator', 'Akses penuh termasuk user management dan audit log'),
    ('finance', 'Finance', 'Akses operasional ke transaksi dan laporan'),
    ('viewer', 'Viewer', 'Akses baca untuk monitoring')
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    description = VALUES(description),
    is_active = 1;

INSERT INTO mst_expense_categories (code, name, description)
VALUES
    ('RENT', 'Sewa', 'Biaya sewa tempat atau fasilitas usaha'),
    ('UTILITIES', 'Utilitas', 'Listrik, air, internet, dan kebutuhan operasional rutin'),
    ('SUPPLIES', 'Perlengkapan', 'Perlengkapan usaha dan bahan pendukung'),
    ('SALARY', 'Gaji', 'Gaji, honor, atau komisi tim'),
    ('MARKETING', 'Marketing', 'Promosi, iklan, dan biaya pemasaran'),
    ('MAINTENANCE', 'Maintenance', 'Perawatan alat, tempat, atau aset usaha'),
    ('PROFIT_DISTRIBUTION', 'Profit Distribution', 'Pembayaran profit distribution ke member'),
    ('TRANSFER_FEE', 'Biaya Transfer', 'Biaya admin transfer, BI Fast, RTGS, top up, atau biaya bank lain'),
    ('OTHER', 'Lain-lain', 'Kategori cadangan untuk biaya lain')
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    description = VALUES(description),
    is_active = 1;

INSERT INTO mst_transfer_methods (code, name, default_fee)
VALUES
    ('NONE', 'Tanpa Biaya', 0.00),
    ('BI_FAST', 'BI Fast', 2500.00),
    ('RTGS', 'RTGS', 30000.00),
    ('TOP_UP', 'Top Up', 1000.00),
    ('OTHER', 'Lain-lain', 0.00)
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    default_fee = VALUES(default_fee),
    is_active = 1;

INSERT INTO mst_balance_accounts (code, name, current_balance, notes)
VALUES
    ('REK-001', 'Rekening Penampungan 1', 0.00, 'Saldo diisi manual'),
    ('REK-002', 'Rekening Penampungan 2', 0.00, 'Saldo diisi manual'),
    ('REK-003', 'Rekening Penampungan 3', 0.00, 'Saldo diisi manual')
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    notes = VALUES(notes),
    is_active = 1;

INSERT INTO mst_groups (code, name, type, description)
VALUES
    ('STORE-001', 'Toko Contoh Utama', 'store', 'Contoh store awal untuk development'),
    ('GROUP-001', 'Finance Share Group', 'group', 'Contoh group kepemilikan awal')
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    type = VALUES(type),
    description = VALUES(description),
    is_active = 1;

INSERT INTO mst_members (code, name, nickname, email, phone)
VALUES
    ('MBR-001', 'Andi Pratama', 'Andi', 'andi@example.test', '081200000001'),
    ('MBR-002', 'Budi Santoso', 'Budi', 'budi@example.test', '081200000002'),
    ('MBR-003', 'Citra Lestari', 'Citra', 'citra@example.test', '081200000003')
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    nickname = VALUES(nickname),
    phone = VALUES(phone),
    is_active = 1;

INSERT INTO mst_group_members (group_id, member_id, share_percent, joined_at)
SELECT g.id, m.id, x.share_percent, '2026-01-01'
FROM (
    SELECT 'STORE-001' AS group_code, 'MBR-001' AS member_code, 40.000 AS share_percent
    UNION ALL SELECT 'STORE-001', 'MBR-002', 35.000
    UNION ALL SELECT 'STORE-001', 'MBR-003', 25.000
    UNION ALL SELECT 'GROUP-001', 'MBR-001', 50.000
    UNION ALL SELECT 'GROUP-001', 'MBR-002', 50.000
) AS x
JOIN mst_groups g ON g.code = x.group_code
JOIN mst_members m ON m.code = x.member_code
ON DUPLICATE KEY UPDATE
    share_percent = VALUES(share_percent),
    joined_at = VALUES(joined_at),
    is_active = 1;
