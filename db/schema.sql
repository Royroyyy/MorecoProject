-- =============================================
-- MORECO COOPERATIVE MANAGEMENT SYSTEM
-- Database Schema — Phase 1
-- Run this FIRST in phpMyAdmin
-- =============================================

CREATE DATABASE IF NOT EXISTS moreco_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE moreco_db;

-- =============================================
-- 1. USERS
-- Roles: guest, applicant, member, admin, clerk, loan_officer
-- =============================================
CREATE TABLE IF NOT EXISTS users (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    first_name    VARCHAR(80)  NOT NULL,
    last_name     VARCHAR(80)  NOT NULL,
    email         VARCHAR(150) NOT NULL UNIQUE,
    username      VARCHAR(60)  NOT NULL UNIQUE,
    password      VARCHAR(255) NOT NULL,
    role          ENUM('applicant','member','admin','clerk','loan_officer') NOT NULL DEFAULT 'applicant',
    phone         VARCHAR(20),
    address       TEXT,
    date_of_birth DATE,
    gender        ENUM('male','female','other'),
    profile_photo VARCHAR(500),
    is_active     TINYINT(1)   NOT NULL DEFAULT 1,
    created_at    TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- =============================================
-- 2. MEMBERSHIP APPLICATIONS
-- Tracks the full application lifecycle
-- =============================================
CREATE TABLE IF NOT EXISTS membership_applications (
    id                INT AUTO_INCREMENT PRIMARY KEY,
    user_id           INT          NOT NULL,
    status            ENUM('pending','under_review','approved','rejected') NOT NULL DEFAULT 'pending',
    valid_id_path     VARCHAR(500) NOT NULL,
    proof_of_residence_path VARCHAR(500) NOT NULL,
    photo_path        VARCHAR(500),
    notes             TEXT,
    reviewed_by       INT,
    reviewed_at       TIMESTAMP    NULL,
    rejection_reason  TEXT,
    created_at        TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    updated_at        TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)     REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL
);

-- =============================================
-- 3. ORIENTATION SCHEDULES
-- Admin creates these, approved applicants pick one
-- =============================================
CREATE TABLE IF NOT EXISTS orientation_schedules (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    title        VARCHAR(200) NOT NULL,
    scheduled_date DATE        NOT NULL,
    scheduled_time TIME        NOT NULL,
    location     VARCHAR(200) NOT NULL,
    max_slots    INT          NOT NULL DEFAULT 20,
    is_active    TINYINT(1)   NOT NULL DEFAULT 1,
    created_by   INT,
    created_at   TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- =============================================
-- 4. ORIENTATION REGISTRATIONS
-- Tracks which applicant selected which schedule
-- =============================================
CREATE TABLE IF NOT EXISTS orientation_registrations (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    user_id         INT NOT NULL,
    schedule_id     INT NOT NULL,
    status          ENUM('scheduled','completed','missed') NOT NULL DEFAULT 'scheduled',
    completed_at    TIMESTAMP NULL,
    completed_by    INT,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY no_duplicate_orientation (user_id),
    FOREIGN KEY (user_id)      REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (schedule_id)  REFERENCES orientation_schedules(id) ON DELETE CASCADE,
    FOREIGN KEY (completed_by) REFERENCES users(id) ON DELETE SET NULL
);

-- =============================================
-- 5. EVENTS
-- =============================================
CREATE TABLE IF NOT EXISTS events (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    title        VARCHAR(200) NOT NULL,
    category     VARCHAR(80)  NOT NULL,
    description  TEXT,
    event_date   DATE         NOT NULL,
    location     VARCHAR(200),
    organizer    VARCHAR(150),
    slots        INT          NOT NULL DEFAULT 100,
    emoji        VARCHAR(10)  DEFAULT '📅',
    image_url    VARCHAR(500),
    status       ENUM('upcoming','completed') DEFAULT 'upcoming',
    created_by   INT,
    created_at   TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- =============================================
-- 6. EVENT REGISTRATIONS
-- Only members can register
-- =============================================
CREATE TABLE IF NOT EXISTS event_registrations (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    user_id       INT NOT NULL,
    event_id      INT NOT NULL,
    registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY no_duplicate_event_reg (user_id, event_id),
    FOREIGN KEY (user_id)  REFERENCES users(id)  ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
);

-- =============================================
-- 7. ANNOUNCEMENTS
-- =============================================
CREATE TABLE IF NOT EXISTS announcements (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    title      VARCHAR(250) NOT NULL,
    body       TEXT         NOT NULL,
    icon       VARCHAR(10)  DEFAULT '📢',
    priority   ENUM('normal','high') DEFAULT 'normal',
    branch     VARCHAR(60)  DEFAULT 'all',
    posted_at  DATE         NOT NULL,
    created_by INT,
    created_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- =============================================
-- 8. BENEFITS
-- Displayed like events — card UI
-- =============================================
CREATE TABLE IF NOT EXISTS benefits (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(200) NOT NULL,
    category    ENUM('loan','savings','insurance','program','other') NOT NULL DEFAULT 'other',
    description TEXT,
    eligibility TEXT,
    requirements TEXT,
    how_to_apply TEXT,
    emoji       VARCHAR(10)  DEFAULT '🎁',
    form_url    VARCHAR(500) NULL,
    image_url   VARCHAR(500),
    is_active   TINYINT(1)   NOT NULL DEFAULT 1,
    created_by  INT,
    created_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- =============================================
-- 9. WITHDRAWALS
-- Member submits → clerk/admin approves → QR generated
-- =============================================
CREATE TABLE IF NOT EXISTS withdrawals (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    user_id         INT            NOT NULL,
    account_number  VARCHAR(50)    NOT NULL,
    account_name    VARCHAR(100)   NOT NULL DEFAULT '',
    amount          DECIMAL(12,2)  NOT NULL,
    status          ENUM('pending','approved','rejected','released') NOT NULL DEFAULT 'pending',
    notes           TEXT,
    reviewed_by     INT,
    reviewed_at     TIMESTAMP NULL,
    rejection_reason TEXT,
    released_at     TIMESTAMP NULL,
    released_by     INT,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)     REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (released_by) REFERENCES users(id) ON DELETE SET NULL
);

-- =============================================
-- 10. LOANS
-- Member applies → loan officer approves → QR generated
-- =============================================
CREATE TABLE IF NOT EXISTS loans (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    user_id          INT           NOT NULL,
    amount           DECIMAL(12,2) NOT NULL,
    purpose          TEXT,
    term_months      INT           NOT NULL DEFAULT 12,
    interest_rate    DECIMAL(5,2)  NOT NULL DEFAULT 2.00,
    status           ENUM('pending','under_review','approved','rejected','released','paid') NOT NULL DEFAULT 'pending',
    notes            TEXT,
    reviewed_by      INT,
    reviewed_at      TIMESTAMP NULL,
    rejection_reason TEXT,
    released_at      TIMESTAMP NULL,
    released_by      INT,
    due_date         DATE,
    created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)     REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (released_by) REFERENCES users(id) ON DELETE SET NULL
);

-- =============================================
-- 11. QR TRANSACTIONS
-- One QR per approved withdrawal or loan
-- =============================================
CREATE TABLE IF NOT EXISTS qr_transactions (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    transaction_type ENUM('withdrawal','loan') NOT NULL,
    reference_id     INT          NOT NULL,
    qr_token         VARCHAR(255) NOT NULL UNIQUE,
    status           ENUM('active','scanned','expired') NOT NULL DEFAULT 'active',
    generated_at     TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    scanned_at       TIMESTAMP    NULL,
    scanned_by       INT,
    expires_at       TIMESTAMP    NULL,
    FOREIGN KEY (scanned_by) REFERENCES users(id) ON DELETE SET NULL
);

-- =============================================
-- 12. NOTIFICATIONS
-- Triggered by system actions
-- =============================================
CREATE TABLE IF NOT EXISTS notifications (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT          NOT NULL,
    title      VARCHAR(200) NOT NULL,
    message    TEXT         NOT NULL,
    type       ENUM('membership','orientation','loan','withdrawal','event','general') NOT NULL DEFAULT 'general',
    is_read    TINYINT(1)   NOT NULL DEFAULT 0,
    link       VARCHAR(500),
    created_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- =============================================
-- 13. AUDIT LOGS
-- Tracks all important system actions
-- =============================================
CREATE TABLE IF NOT EXISTS audit_logs (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT,
    action      VARCHAR(200) NOT NULL,
    target_type VARCHAR(80),
    target_id   INT,
    details     TEXT,
    ip_address  VARCHAR(45),
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- =============================================
-- INDEXES for performance
-- =============================================
CREATE INDEX idx_users_role           ON users(role);
CREATE INDEX idx_users_email          ON users(email);
CREATE INDEX idx_mem_app_status       ON membership_applications(status);
CREATE INDEX idx_mem_app_user         ON membership_applications(user_id);
CREATE INDEX idx_events_status        ON events(status);
CREATE INDEX idx_events_date          ON events(event_date);
CREATE INDEX idx_withdrawals_status   ON withdrawals(status);
CREATE INDEX idx_withdrawals_user     ON withdrawals(user_id);
CREATE INDEX idx_loans_status         ON loans(status);
CREATE INDEX idx_loans_user           ON loans(user_id);
CREATE INDEX idx_notifications_user   ON notifications(user_id);
CREATE INDEX idx_notifications_read   ON notifications(is_read);
CREATE INDEX idx_qr_token             ON qr_transactions(qr_token);
CREATE INDEX idx_audit_user           ON audit_logs(user_id);
