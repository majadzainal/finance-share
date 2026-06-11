CREATE DATABASE IF NOT EXISTS finance_share
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE finance_share;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS trx_ledger;
DROP TABLE IF EXISTS trx_audit_logs;
DROP TABLE IF EXISTS trx_profit_distributions;
DROP TABLE IF EXISTS trx_closings;
DROP TABLE IF EXISTS trx_cash_advance_payments;
DROP TABLE IF EXISTS trx_cash_advances;
DROP TABLE IF EXISTS trx_expenses;
DROP TABLE IF EXISTS trx_incomes;
DROP TABLE IF EXISTS trx_imports;
DROP TABLE IF EXISTS mst_balance_accounts;
DROP TABLE IF EXISTS mst_transfer_methods;
DROP TABLE IF EXISTS mst_expense_categories;
DROP TABLE IF EXISTS mst_group_members;
DROP TABLE IF EXISTS mst_members;
DROP TABLE IF EXISTS mst_groups;
DROP TABLE IF EXISTS mst_roles;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE mst_groups (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL,
    name VARCHAR(150) NOT NULL,
    type ENUM('store', 'group') NOT NULL DEFAULT 'store',
    description TEXT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_mst_groups_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE mst_roles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL,
    name VARCHAR(150) NOT NULL,
    description TEXT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_mst_roles_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    username VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(50) NOT NULL DEFAULT 'admin',
    status TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_users_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE trx_audit_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    user_name VARCHAR(150) NULL,
    user_role VARCHAR(50) NULL,
    event VARCHAR(100) NOT NULL,
    module VARCHAR(100) NOT NULL,
    method VARCHAR(10) NOT NULL,
    path VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    request_data JSON NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_trx_audit_logs_user_id (user_id),
    KEY idx_trx_audit_logs_module_event (module, event),
    KEY idx_trx_audit_logs_created_at (created_at),
    CONSTRAINT fk_trx_audit_logs_user_id FOREIGN KEY (user_id) REFERENCES users (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE mst_members (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL,
    name VARCHAR(150) NOT NULL,
    nickname VARCHAR(100) NULL,
    email VARCHAR(150) NULL,
    phone VARCHAR(50) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_mst_members_code (code),
    UNIQUE KEY uq_mst_members_email (email),
    KEY idx_mst_members_name_nickname (name, nickname)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE mst_group_members (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    group_id BIGINT UNSIGNED NOT NULL,
    member_id BIGINT UNSIGNED NOT NULL,
    share_percent DECIMAL(6,3) NOT NULL DEFAULT 0.000,
    joined_at DATE NULL,
    left_at DATE NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_mst_group_members_group_member (group_id, member_id),
    KEY idx_mst_group_members_member_id (member_id),
    CONSTRAINT fk_mst_group_members_group_id FOREIGN KEY (group_id) REFERENCES mst_groups (id),
    CONSTRAINT fk_mst_group_members_member_id FOREIGN KEY (member_id) REFERENCES mst_members (id),
    CONSTRAINT chk_mst_group_members_share_percent CHECK (share_percent >= 0 AND share_percent <= 100)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE mst_expense_categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL,
    name VARCHAR(150) NOT NULL,
    description TEXT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_mst_expense_categories_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE mst_transfer_methods (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL,
    name VARCHAR(150) NOT NULL,
    default_fee DECIMAL(18,2) NOT NULL DEFAULT 0.00,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_mst_transfer_methods_code (code),
    CONSTRAINT chk_mst_transfer_methods_default_fee CHECK (default_fee >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE mst_balance_accounts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL,
    name VARCHAR(150) NOT NULL,
    current_balance DECIMAL(18,2) NOT NULL DEFAULT 0.00,
    notes TEXT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_mst_balance_accounts_code (code),
    CONSTRAINT chk_mst_balance_accounts_current_balance CHECK (current_balance >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE trx_imports (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    group_id BIGINT UNSIGNED NULL,
    file_name VARCHAR(255) NOT NULL,
    file_hash CHAR(64) NOT NULL,
    original_file_name VARCHAR(255) NULL,
    source VARCHAR(100) NULL,
    imported_by VARCHAR(100) NULL,
    imported_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    import_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    total_rows INT UNSIGNED NOT NULL DEFAULT 0,
    success_rows INT UNSIGNED NOT NULL DEFAULT 0,
    duplicate_rows INT UNSIGNED NOT NULL DEFAULT 0,
    failed_rows INT UNSIGNED NOT NULL DEFAULT 0,
    status ENUM('pending', 'processing', 'completed', 'failed') NOT NULL DEFAULT 'pending',
    notes TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_trx_imports_file_hash (file_hash),
    KEY idx_trx_imports_group_id (group_id),
    CONSTRAINT fk_trx_imports_group_id FOREIGN KEY (group_id) REFERENCES mst_groups (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE trx_closings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    group_id BIGINT UNSIGNED NOT NULL,
    period_start DATE NOT NULL,
    period_end DATE NOT NULL,
    total_income DECIMAL(18,2) NOT NULL DEFAULT 0.00,
    total_expense DECIMAL(18,2) NOT NULL DEFAULT 0.00,
    total_cash_advance DECIMAL(18,2) NOT NULL DEFAULT 0.00,
    total_cash_advance_payment DECIMAL(18,2) NOT NULL DEFAULT 0.00,
    net_profit DECIMAL(18,2) NOT NULL DEFAULT 0.00,
    status ENUM('draft', 'closed', 'paid', 'void') NOT NULL DEFAULT 'draft',
    closed_at DATETIME NULL,
    closed_by VARCHAR(100) NULL,
    notes TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_trx_closings_group_period (group_id, period_start, period_end),
    KEY idx_trx_closings_status (status),
    CONSTRAINT fk_trx_closings_group_id FOREIGN KEY (group_id) REFERENCES mst_groups (id),
    CONSTRAINT chk_trx_closings_period CHECK (period_start <= period_end)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE trx_incomes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    group_id BIGINT UNSIGNED NOT NULL,
    import_id BIGINT UNSIGNED NULL,
    closing_id BIGINT UNSIGNED NULL,
    transaction_date DATE NOT NULL,
    reference_no VARCHAR(100) NULL,
    external_id VARCHAR(150) NULL,
    bank_target VARCHAR(100) NULL,
    income_type VARCHAR(100) NULL,
    client_name VARCHAR(150) NULL,
    address VARCHAR(255) NULL,
    username VARCHAR(150) NULL,
    profile_package VARCHAR(150) NULL,
    description VARCHAR(255) NULL,
    amount DECIMAL(18,2) NOT NULL,
    payment_method VARCHAR(50) NULL,
    raw_payload JSON NULL,
    unique_key CHAR(64) GENERATED ALWAYS AS (
        SHA2(CONCAT_WS('|',
            group_id,
            DATE_FORMAT(transaction_date, '%Y-%m-%d'),
            COALESCE(reference_no, ''),
            COALESCE(external_id, ''),
            COALESCE(description, ''),
            CAST(amount AS CHAR),
            COALESCE(payment_method, '')
        ), 256)
    ) STORED,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_trx_incomes_unique_key (unique_key),
    KEY idx_trx_incomes_group_date (group_id, transaction_date),
    KEY idx_trx_incomes_import_id (import_id),
    KEY idx_trx_incomes_closing_id (closing_id),
    CONSTRAINT fk_trx_incomes_group_id FOREIGN KEY (group_id) REFERENCES mst_groups (id),
    CONSTRAINT fk_trx_incomes_import_id FOREIGN KEY (import_id) REFERENCES trx_imports (id),
    CONSTRAINT fk_trx_incomes_closing_id FOREIGN KEY (closing_id) REFERENCES trx_closings (id),
    CONSTRAINT chk_trx_incomes_amount CHECK (amount > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE trx_expenses (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    group_id BIGINT UNSIGNED NOT NULL,
    category_id BIGINT UNSIGNED NOT NULL,
    closing_id BIGINT UNSIGNED NULL,
    expense_date DATE NOT NULL,
    reference_no VARCHAR(100) NULL,
    description VARCHAR(255) NOT NULL,
    amount DECIMAL(18,2) NOT NULL,
    created_by VARCHAR(100) NULL,
    paid_by_member_id BIGINT UNSIGNED NULL,
    payment_method VARCHAR(50) NULL,
    receipt_file VARCHAR(255) NULL,
    transfer_method_id BIGINT UNSIGNED NULL,
    transfer_fee_amount DECIMAL(18,2) NOT NULL DEFAULT 0.00,
    transfer_fee_expense_id BIGINT UNSIGNED NULL,
    notes TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_trx_expenses_group_date (group_id, expense_date),
    KEY idx_trx_expenses_category_id (category_id),
    KEY idx_trx_expenses_closing_id (closing_id),
    KEY idx_trx_expenses_paid_by_member_id (paid_by_member_id),
    KEY idx_trx_expenses_transfer_method_id (transfer_method_id),
    KEY idx_trx_expenses_transfer_fee_expense_id (transfer_fee_expense_id),
    CONSTRAINT fk_trx_expenses_group_id FOREIGN KEY (group_id) REFERENCES mst_groups (id),
    CONSTRAINT fk_trx_expenses_category_id FOREIGN KEY (category_id) REFERENCES mst_expense_categories (id),
    CONSTRAINT fk_trx_expenses_closing_id FOREIGN KEY (closing_id) REFERENCES trx_closings (id),
    CONSTRAINT fk_trx_expenses_paid_by_member_id FOREIGN KEY (paid_by_member_id) REFERENCES mst_members (id),
    CONSTRAINT fk_trx_expenses_transfer_method_id FOREIGN KEY (transfer_method_id) REFERENCES mst_transfer_methods (id),
    CONSTRAINT fk_trx_expenses_transfer_fee_expense_id FOREIGN KEY (transfer_fee_expense_id) REFERENCES trx_expenses (id),
    CONSTRAINT chk_trx_expenses_amount CHECK (amount > 0),
    CONSTRAINT chk_trx_expenses_transfer_fee_amount CHECK (transfer_fee_amount >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE trx_cash_advances (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    group_id BIGINT UNSIGNED NULL,
    member_id BIGINT UNSIGNED NOT NULL,
    advance_date DATE NOT NULL,
    reference_no VARCHAR(100) NULL,
    description VARCHAR(255) NULL,
    amount DECIMAL(18,2) NOT NULL,
    transfer_method_id BIGINT UNSIGNED NULL,
    transfer_fee_amount DECIMAL(18,2) NOT NULL DEFAULT 0.00,
    transfer_fee_expense_id BIGINT UNSIGNED NULL,
    remaining_amount DECIMAL(18,2) NOT NULL,
    status TINYINT(1) NOT NULL DEFAULT 0,
    notes TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_trx_cash_advances_group_date (group_id, advance_date),
    KEY idx_trx_cash_advances_member_id (member_id),
    KEY idx_trx_cash_advances_status (status),
    KEY idx_trx_cash_advances_transfer_method_id (transfer_method_id),
    KEY idx_trx_cash_advances_transfer_fee_expense_id (transfer_fee_expense_id),
    CONSTRAINT fk_trx_cash_advances_group_id FOREIGN KEY (group_id) REFERENCES mst_groups (id),
    CONSTRAINT fk_trx_cash_advances_member_id FOREIGN KEY (member_id) REFERENCES mst_members (id),
    CONSTRAINT fk_trx_cash_advances_transfer_method_id FOREIGN KEY (transfer_method_id) REFERENCES mst_transfer_methods (id),
    CONSTRAINT fk_trx_cash_advances_transfer_fee_expense_id FOREIGN KEY (transfer_fee_expense_id) REFERENCES trx_expenses (id),
    CONSTRAINT chk_trx_cash_advances_amount CHECK (amount > 0),
    CONSTRAINT chk_trx_cash_advances_transfer_fee_amount CHECK (transfer_fee_amount >= 0),
    CONSTRAINT chk_trx_cash_advances_remaining_amount CHECK (remaining_amount >= 0 AND remaining_amount <= amount),
    CONSTRAINT chk_trx_cash_advances_status CHECK (status IN (0, 1))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE trx_cash_advance_payments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cash_advance_id BIGINT UNSIGNED NOT NULL,
    payment_date DATE NOT NULL,
    reference_no VARCHAR(100) NULL,
    amount DECIMAL(18,2) NOT NULL,
    payment_method VARCHAR(50) NULL,
    notes TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_trx_cash_advance_payments_cash_advance_id (cash_advance_id),
    KEY idx_trx_cash_advance_payments_payment_date (payment_date),
    CONSTRAINT fk_trx_cash_advance_payments_cash_advance_id FOREIGN KEY (cash_advance_id) REFERENCES trx_cash_advances (id),
    CONSTRAINT chk_trx_cash_advance_payments_amount CHECK (amount > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE trx_profit_distributions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    closing_id BIGINT UNSIGNED NOT NULL,
    group_id BIGINT UNSIGNED NOT NULL,
    member_id BIGINT UNSIGNED NOT NULL,
    share_percent DECIMAL(6,3) NOT NULL,
    profit_amount DECIMAL(18,2) NOT NULL DEFAULT 0.00,
    paid_amount DECIMAL(18,2) NOT NULL DEFAULT 0.00,
    status ENUM('pending', 'paid', 'void') NOT NULL DEFAULT 'pending',
    paid_at DATETIME NULL,
    notes TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_trx_profit_distributions_closing_member (closing_id, member_id),
    KEY idx_trx_profit_distributions_group_id (group_id),
    KEY idx_trx_profit_distributions_member_id (member_id),
    CONSTRAINT fk_trx_profit_distributions_closing_id FOREIGN KEY (closing_id) REFERENCES trx_closings (id),
    CONSTRAINT fk_trx_profit_distributions_group_id FOREIGN KEY (group_id) REFERENCES mst_groups (id),
    CONSTRAINT fk_trx_profit_distributions_member_id FOREIGN KEY (member_id) REFERENCES mst_members (id),
    CONSTRAINT chk_trx_profit_distributions_share_percent CHECK (share_percent >= 0 AND share_percent <= 100),
    CONSTRAINT chk_trx_profit_distributions_amounts CHECK (profit_amount >= 0 AND paid_amount >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE trx_ledger (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    group_id BIGINT UNSIGNED NOT NULL,
    member_id BIGINT UNSIGNED NULL,
    transaction_date DATE NOT NULL,
    source_table VARCHAR(80) NOT NULL,
    source_id BIGINT UNSIGNED NOT NULL,
    account_code VARCHAR(50) NOT NULL,
    account_name VARCHAR(150) NOT NULL,
    debit DECIMAL(18,2) NOT NULL DEFAULT 0.00,
    credit DECIMAL(18,2) NOT NULL DEFAULT 0.00,
    description VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_trx_ledger_group_date (group_id, transaction_date),
    KEY idx_trx_ledger_member_id (member_id),
    KEY idx_trx_ledger_source (source_table, source_id),
    KEY idx_trx_ledger_account_code (account_code),
    CONSTRAINT fk_trx_ledger_group_id FOREIGN KEY (group_id) REFERENCES mst_groups (id),
    CONSTRAINT fk_trx_ledger_member_id FOREIGN KEY (member_id) REFERENCES mst_members (id),
    CONSTRAINT chk_trx_ledger_amount CHECK (debit >= 0 AND credit >= 0 AND NOT (debit > 0 AND credit > 0))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
