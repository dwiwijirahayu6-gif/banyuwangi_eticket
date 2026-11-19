-- ============================================================================
-- DATABASE DDL & SEEDER SCRIPT
-- Project: Sistem Pemesanan Tiket Wisata di Kabupaten Banyuwangi Berbasis Web
-- Database Engine: MySQL 8.0+
-- Standard: ISO/IEC/IEEE 29148:2018 SRS & Interoperabilitas REST API
-- ============================================================================

CREATE DATABASE IF NOT EXISTS `banyuwangi_eticket`
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_unicode_ci;

USE `banyuwangi_eticket`;

-- Disable Foreign Key Checks during Drop/Create
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `audit_logs`;
DROP TABLE IF EXISTS `ledger_entries`;
DROP TABLE IF EXISTS `withdrawals`;
DROP TABLE IF EXISTS `payment_events`;
DROP TABLE IF EXISTS `payments`;
DROP TABLE IF EXISTS `tickets`;
DROP TABLE IF EXISTS `booking_items`;
DROP TABLE IF EXISTS `bookings`;
DROP TABLE IF EXISTS `visit_slots`;
DROP TABLE IF EXISTS `ticket_types`;
DROP TABLE IF EXISTS `destinations`;
DROP TABLE IF EXISTS `commission_schemes`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `tenants`;

SET FOREIGN_KEY_CHECKS = 1;

-- ----------------------------------------------------------------------------
-- 1. TABEL: tenants
-- ----------------------------------------------------------------------------
CREATE TABLE `tenants` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `code` VARCHAR(50) NOT NULL UNIQUE,
  `status` ENUM('active', 'inactive', 'suspended') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------------------
-- 2. TABEL: users
-- ----------------------------------------------------------------------------
CREATE TABLE `users` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `tenant_id` BIGINT UNSIGNED NULL,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('super_admin', 'tenant_admin', 'field_officer') NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_users_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------------------
-- 3. TABEL: commission_schemes
-- ----------------------------------------------------------------------------
CREATE TABLE `commission_schemes` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `tenant_id` BIGINT UNSIGNED NULL,
  `percentage` DECIMAL(5,2) NOT NULL DEFAULT 5.00,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_commissions_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------------------
-- 4. TABEL: destinations
-- ----------------------------------------------------------------------------
CREATE TABLE `destinations` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `tenant_id` BIGINT UNSIGNED NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL UNIQUE,
  `description` TEXT NULL,
  `location` VARCHAR(255) NULL,
  `operating_hours` VARCHAR(255) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_destinations_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------------------
-- 5. TABEL: ticket_types
-- ----------------------------------------------------------------------------
CREATE TABLE `ticket_types` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `tenant_id` BIGINT UNSIGNED NOT NULL,
  `destination_id` BIGINT UNSIGNED NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `price` DECIMAL(19,4) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_ticket_types_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ticket_types_destination` FOREIGN KEY (`destination_id`) REFERENCES `destinations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------------------
-- 6. TABEL: visit_slots
-- ----------------------------------------------------------------------------
CREATE TABLE `visit_slots` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `tenant_id` BIGINT UNSIGNED NOT NULL,
  `destination_id` BIGINT UNSIGNED NOT NULL,
  `visit_date` DATE NOT NULL,
  `quota_capacity` INT NOT NULL DEFAULT 500,
  `quota_remaining` INT NOT NULL DEFAULT 500,
  CONSTRAINT `fk_visit_slots_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_visit_slots_destination` FOREIGN KEY (`destination_id`) REFERENCES `destinations` (`id`) ON DELETE CASCADE,
  UNIQUE KEY `uq_destination_visit_date` (`destination_id`, `visit_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------------------
-- 7. TABEL: bookings
-- ----------------------------------------------------------------------------
CREATE TABLE `bookings` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `booking_code` VARCHAR(50) NOT NULL UNIQUE,
  `customer_name` VARCHAR(255) NOT NULL,
  `customer_email` VARCHAR(255) NOT NULL,
  `customer_phone` VARCHAR(50) NOT NULL,
  `total_amount` DECIMAL(19,4) NOT NULL,
  `status` ENUM('draft', 'pending_payment', 'paid', 'completed', 'cancelled', 'expired') NOT NULL DEFAULT 'draft',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------------------
-- 8. TABEL: booking_items
-- ----------------------------------------------------------------------------
CREATE TABLE `booking_items` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `booking_id` BIGINT UNSIGNED NOT NULL,
  `tenant_id` BIGINT UNSIGNED NOT NULL,
  `ticket_type_id` BIGINT UNSIGNED NOT NULL,
  `visit_slot_id` BIGINT UNSIGNED NOT NULL,
  `quantity` INT NOT NULL DEFAULT 1,
  `unit_price_snapshot` DECIMAL(19,4) NOT NULL,
  `subtotal` DECIMAL(19,4) NOT NULL,
  CONSTRAINT `fk_booking_items_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_booking_items_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_booking_items_ticket_type` FOREIGN KEY (`ticket_type_id`) REFERENCES `ticket_types` (`id`),
  CONSTRAINT `fk_booking_items_visit_slot` FOREIGN KEY (`visit_slot_id`) REFERENCES `visit_slots` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------------------
-- 9. TABEL: tickets
-- ----------------------------------------------------------------------------
CREATE TABLE `tickets` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `booking_item_id` BIGINT UNSIGNED NOT NULL,
  `booking_id` BIGINT UNSIGNED NOT NULL,
  `tenant_id` BIGINT UNSIGNED NOT NULL,
  `verified_by_user_id` BIGINT UNSIGNED NULL,
  `ticket_code` VARCHAR(128) NOT NULL UNIQUE,
  `status` ENUM('issued', 'checked_in', 'expired', 'cancelled') NOT NULL DEFAULT 'issued',
  `visit_date` DATE NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_tickets_booking_item` FOREIGN KEY (`booking_item_id`) REFERENCES `booking_items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_tickets_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_tickets_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_tickets_verifier` FOREIGN KEY (`verified_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------------------
-- 10. TABEL: payments
-- ----------------------------------------------------------------------------
CREATE TABLE `payments` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `booking_id` BIGINT UNSIGNED NOT NULL,
  `payment_reference` VARCHAR(100) NOT NULL UNIQUE,
  `qris_payload` TEXT NULL,
  `gross_amount` DECIMAL(19,4) NOT NULL,
  `status` ENUM('pending', 'settlement', 'expire', 'failed', 'cancel') NOT NULL DEFAULT 'pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_payments_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------------------
-- 11. TABEL: payment_events
-- ----------------------------------------------------------------------------
CREATE TABLE `payment_events` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `payment_id` BIGINT UNSIGNED NOT NULL,
  `event_type` VARCHAR(100) NOT NULL,
  `payload_json` TEXT NOT NULL,
  `signature` VARCHAR(255) NULL,
  `processed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_payment_events_payment` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------------------
-- 12. TABEL: withdrawals
-- ----------------------------------------------------------------------------
CREATE TABLE `withdrawals` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `tenant_id` BIGINT UNSIGNED NOT NULL,
  `approved_by_user_id` BIGINT UNSIGNED NULL,
  `amount` DECIMAL(19,4) NOT NULL,
  `status` ENUM('pending', 'approved', 'disbursed', 'rejected') NOT NULL DEFAULT 'pending',
  `bank_account_info` TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_withdrawals_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_withdrawals_approver` FOREIGN KEY (`approved_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------------------
-- 13. TABEL: ledger_entries
-- ----------------------------------------------------------------------------
CREATE TABLE `ledger_entries` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `tenant_id` BIGINT UNSIGNED NOT NULL,
  `booking_item_id` BIGINT UNSIGNED NULL,
  `withdrawal_id` BIGINT UNSIGNED NULL,
  `transaction_type` ENUM('credit_sale', 'debit_withdrawal', 'fee_deduction', 'refund') NOT NULL,
  `amount` DECIMAL(19,4) NOT NULL,
  `balance_after` DECIMAL(19,4) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_ledger_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ledger_booking_item` FOREIGN KEY (`booking_item_id`) REFERENCES `booking_items` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_ledger_withdrawal` FOREIGN KEY (`withdrawal_id`) REFERENCES `withdrawals` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------------------
-- 14. TABEL: audit_logs
-- ----------------------------------------------------------------------------
CREATE TABLE `audit_logs` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` BIGINT UNSIGNED NULL,
  `action` VARCHAR(255) NOT NULL,
  `entity_type` VARCHAR(100) NOT NULL,
  `ip_address` VARCHAR(45) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_audit_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================================
-- SAMPLE DATA SEEDER (DUMMY DATA AWAL)
-- ============================================================================

-- 1. Tenants
INSERT INTO `tenants` (`id`, `name`, `code`, `status`) VALUES
(1, 'Pengelola Air Terjun Lider', 'TEN-LIDER', 'active'),
(2, 'Pengelola Kawah Ijen', 'TEN-IJEN', 'active'),
(3, 'Pengelola Pantai Pulau Merah', 'TEN-PULAU-MERAH', 'active'),
(4, 'Pengelola Hutan De Djatan', 'TEN-DE-DJATAN', 'active');

-- 2. Users
INSERT INTO `users` (`id`, `tenant_id`, `name`, `email`, `password`, `role`) VALUES
(1, NULL, 'Super Admin Dinas Pariwisata', 'admin@banyuwangitourism.go.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'super_admin'),
(2, 1, 'Pengelola Air Terjun Lider', 'admin.lider@banyuwangitourism.go.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'tenant_admin'),
(3, 1, 'Petugas Lapangan Lider', 'petugas.lider@banyuwangitourism.go.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'field_officer'),
(4, 3, 'Petugas Pintu Pulau Merah', 'petugas.pulaumerah@banyuwangitourism.go.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'field_officer');

-- 3. Commission Schemes
INSERT INTO `commission_schemes` (`id`, `tenant_id`, `percentage`, `is_active`) VALUES
(1, NULL, 5.00, 1),
(2, 1, 5.00, 1),
(3, 2, 5.00, 1),
(4, 3, 5.00, 1);

-- 4. Destinations
INSERT INTO `destinations` (`id`, `tenant_id`, `name`, `slug`, `description`, `location`, `operating_hours`) VALUES
(1, 1, 'Air Terjun Lider', 'air-terjun-lider', 'Air terjun tertinggi di Banyuwangi dengan pemandangan alam asri di kaki Gunung Raung.', 'Kecamatan Sragi, Banyuwangi', '07:00 - 16:00 WIB'),
(2, 2, 'Taman Wisata Kawah Ijen', 'kawah-ijen', 'Fenomena Blue Fire legendaris dan danau kawah asam terbesar di dunia.', 'Kecamatan Licin, Banyuwangi', '01:00 - 12:00 WIB'),
(3, 3, 'Pantai Pulau Merah', 'pantai-pulau-merah', 'Destinasi pantai populer dengan bukit merah ikonik dan ombak selancar.', 'Kecamatan Pesanggaran, Banyuwangi', '06:00 - 18:00 WIB'),
(4, 4, 'Hutan De Djatan', 'hutan-de-djatan', 'Hutan trembesi raksasa nan eksotis mirip fenomena Fangorn Forest.', 'Kecamatan Cluring, Banyuwangi', '07:00 - 17:00 WIB');

-- 5. Ticket Types
INSERT INTO `ticket_types` (`id`, `tenant_id`, `destination_id`, `name`, `price`) VALUES
(1, 1, 1, 'Tiket Masuk Wisatawan Nusantara', 15000.0000),
(2, 1, 1, 'Tiket Masuk Wisatawan Mancanegara', 50000.0000),
(3, 2, 2, 'Tiket Masuk Kawah Ijen (Lokal)', 20000.0000),
(4, 3, 3, 'Tiket Masuk Pantai Pulau Merah', 10000.0000),
(5, 3, 3, 'Retribusi Parkir Roda 2', 5000.0000),
(6, 4, 4, 'Tiket Masuk De Djatan', 10000.0000);

-- 6. Visit Slots
INSERT INTO `visit_slots` (`id`, `tenant_id`, `destination_id`, `visit_date`, `quota_capacity`, `quota_remaining`) VALUES
(1, 1, 1, '2026-09-20', 300, 298),
(2, 2, 2, '2026-09-20', 1000, 995),
(3, 3, 3, '2026-09-20', 1500, 1490);

-- 7. Bookings
INSERT INTO `bookings` (`id`, `booking_code`, `customer_name`, `customer_email`, `customer_phone`, `total_amount`, `status`) VALUES
(1, 'BWI-20260918-001', 'Ahmad Rizky', 'ahmad.rizky@gmail.com', '081234567890', 30000.0000, 'paid'),
(2, 'BWI-20260918-002', 'Budi Santoso', 'budi.santoso@yahoo.com', '081987654321', 20000.0000, 'pending_payment');

-- 8. Booking Items
INSERT INTO `booking_items` (`id`, `booking_id`, `tenant_id`, `ticket_type_id`, `visit_slot_id`, `quantity`, `unit_price_snapshot`, `subtotal`) VALUES
(1, 1, 1, 1, 1, 2, 15000.0000, 30000.0000),
(2, 2, 2, 3, 2, 1, 20000.0000, 20000.0000);

-- 9. Tickets (Sesuai dengan kode tiket pada Laporan REST API Express.js)
INSERT INTO `tickets` (`id`, `booking_item_id`, `booking_id`, `tenant_id`, `verified_by_user_id`, `ticket_code`, `status`, `visit_date`) VALUES
(1, 1, 1, 1, NULL, 'TIX-BWI-20260918-001', 'issued', '2026-09-20'),
(2, 1, 1, 1, NULL, 'TIX-BWI-20260918-002', 'issued', '2026-09-20');

-- 10. Payments
INSERT INTO `payments` (`id`, `booking_id`, `payment_reference`, `qris_payload`, `gross_amount`, `status`) VALUES
(1, 1, 'QRIS-BWI-20260918-001', '00020101021226680016ID.CO.QRIS.WWW01189360091100000000005204581253033605405300005802ID5919BANYUWANGI ETICKET6010BANYUWANGI61056841162070703A0163041A2B', 30000.0000, 'settlement');

-- 11. Payment Events
INSERT INTO `payment_events` (`id`, `payment_id`, `event_type`, `payload_json`, `signature`) VALUES
(1, 1, 'payment.settlement', '{"transaction_status": "settlement", "order_id": "BWI-20260918-001", "gross_amount": "30000.00"}', 'sig_9a8b7c6d5e4f3a2b1c');

-- 12. Ledger Entries
INSERT INTO `ledger_entries` (`id`, `tenant_id`, `booking_item_id`, `withdrawal_id`, `transaction_type`, `amount`, `balance_after`) VALUES
(1, 1, 1, NULL, 'credit_sale', 28500.0000, 28500.0000);

-- 13. Audit Logs
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `ip_address`) VALUES
(1, 1, 'CREATE_TENANT', 'tenants', '127.0.0.1'),
(2, 2, 'UPDATE_DESTINATION', 'destinations', '127.0.0.1');
