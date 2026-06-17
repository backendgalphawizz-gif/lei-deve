-- ============================================================
-- LEI Registry Services - Super Admin MySQL Schema (Reference)
-- Laravel migrations source of truth; run: php artisan migrate
-- ============================================================

CREATE DATABASE IF NOT EXISTS lei_registry
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE lei_registry;

-- ------------------------------------------------------------
-- users (Laravel default + admin fields)
-- Login: system_id + password (Secure Token)
-- ------------------------------------------------------------
-- id, name, email, email_verified_at, password, remember_token
-- system_id VARCHAR(64) UNIQUE  -- "Admin Identifier"
-- role: super_admin | tier_1_admin | reviewer
-- avatar, tier, is_active, last_login_at, timestamps

-- ------------------------------------------------------------
-- admin_menu_items - Sidebar navigation
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS admin_menu_items (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  label VARCHAR(255) NOT NULL,
  route_name VARCHAR(255) NULL,
  icon VARCHAR(64) NULL,
  sort_order SMALLINT UNSIGNED DEFAULT 0,
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL
);

-- ------------------------------------------------------------
-- system_alerts - Dashboard SLA / Security banners
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS system_alerts (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  type ENUM('sla_breach','security','info','warning') DEFAULT 'info',
  title VARCHAR(255) NULL,
  message TEXT NOT NULL,
  region VARCHAR(64) NULL,
  severity ENUM('low','medium','high','critical') DEFAULT 'medium',
  is_active TINYINT(1) DEFAULT 1,
  resolved_at TIMESTAMP NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL
);

-- ------------------------------------------------------------
-- registry_applications - Application Management module
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS registry_applications (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  reference_no VARCHAR(64) NOT NULL UNIQUE,
  applicant_name VARCHAR(255) NOT NULL,
  entity_type VARCHAR(64) NULL,
  status ENUM('draft','submitted','pending','approved','rejected') DEFAULT 'submitted',
  source VARCHAR(32) DEFAULT 'main_registry',
  assigned_to BIGINT UNSIGNED NULL,
  submitted_at TIMESTAMP NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL
);

-- ------------------------------------------------------------
-- pending_approvals - Pending Approvals card / queue
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS pending_approvals (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  application_id BIGINT UNSIGNED NOT NULL,
  priority ENUM('normal','urgent') DEFAULT 'normal',
  validation_note TEXT NULL,
  reviewer_id BIGINT UNSIGNED NULL,
  status ENUM('open','in_review','completed') DEFAULT 'open',
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  FOREIGN KEY (application_id) REFERENCES registry_applications(id) ON DELETE CASCADE,
  FOREIGN KEY (reviewer_id) REFERENCES users(id) ON DELETE SET NULL
);

-- ------------------------------------------------------------
-- payment_transactions - Payments module
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS payment_transactions (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  transaction_ref VARCHAR(64) NOT NULL UNIQUE,
  application_id BIGINT UNSIGNED NULL,
  amount DECIMAL(14,2) NOT NULL,
  type ENUM('payment','refund') DEFAULT 'payment',
  currency CHAR(3) DEFAULT 'INR',
  status ENUM('pending','completed','failed') DEFAULT 'completed',
  paid_at TIMESTAMP NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  FOREIGN KEY (application_id) REFERENCES registry_applications(id) ON DELETE SET NULL
);

-- ------------------------------------------------------------
-- service_health_checks - System Health panel
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS service_health_checks (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  service_name VARCHAR(255) NOT NULL,
  service_key VARCHAR(64) NOT NULL UNIQUE,
  uptime_percent DECIMAL(6,2) DEFAULT 100.00,
  status ENUM('healthy','warning','critical') DEFAULT 'healthy',
  load_percent TINYINT UNSIGNED DEFAULT 0,
  sort_order INT UNSIGNED DEFAULT 0,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL
);

-- ------------------------------------------------------------
-- application_trend_metrics - Bar chart (Jan-Oct)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS application_trend_metrics (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  year SMALLINT UNSIGNED NOT NULL,
  month TINYINT UNSIGNED NOT NULL,
  main_registry_count INT UNSIGNED DEFAULT 0,
  partner_api_count INT UNSIGNED DEFAULT 0,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  UNIQUE KEY year_month (year, month)
);

-- ------------------------------------------------------------
-- dashboard_snapshots - KPI stat cards
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS dashboard_snapshots (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  metric_key VARCHAR(64) NOT NULL UNIQUE,
  label VARCHAR(255) NOT NULL,
  value_display VARCHAR(64) NOT NULL,
  value_numeric DECIMAL(16,2) NULL,
  trend_label VARCHAR(32) NULL,
  trend_percent DECIMAL(8,2) NULL,
  badge VARCHAR(32) NULL,
  meta JSON NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL
);

-- ------------------------------------------------------------
-- admin_notifications, audit_logs
-- ------------------------------------------------------------
-- admin_notifications: user_id, title, body, is_read
-- audit_logs: user_id, action, module, description, ip_address, payload
