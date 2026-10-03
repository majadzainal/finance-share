-- Migration for Group Savings (Tabungan Toko) and Closing updates

ALTER TABLE trx_closings 
ADD COLUMN IF NOT EXISTS savings_amount DECIMAL(18,2) NOT NULL DEFAULT 0.00 AFTER net_profit,
ADD COLUMN IF NOT EXISTS distributable_profit DECIMAL(18,2) NOT NULL DEFAULT 0.00 AFTER savings_amount;

CREATE TABLE IF NOT EXISTS trx_group_savings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    group_id BIGINT UNSIGNED NOT NULL,
    closing_id BIGINT UNSIGNED NULL,
    transaction_date DATE NOT NULL,
    type ENUM('deposit', 'withdrawal') NOT NULL,
    amount DECIMAL(18,2) NOT NULL,
    source VARCHAR(50) NOT NULL DEFAULT 'closing',
    reference_no VARCHAR(100) NULL,
    description VARCHAR(255) NULL,
    receipt_file VARCHAR(255) NULL,
    created_by VARCHAR(100) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_trx_group_savings_group_date (group_id, transaction_date),
    KEY idx_trx_group_savings_closing_id (closing_id),
    KEY idx_trx_group_savings_type (type),
    CONSTRAINT fk_trx_group_savings_group_id FOREIGN KEY (group_id) REFERENCES mst_groups (id),
    CONSTRAINT fk_trx_group_savings_closing_id FOREIGN KEY (closing_id) REFERENCES trx_closings (id),
    CONSTRAINT chk_trx_group_savings_amount CHECK (amount > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
