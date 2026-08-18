-- =============================================
-- MORECO COOPERATIVE MANAGEMENT SYSTEM
-- Seed Data v1.2
-- Run AFTER schema.sql
-- IMPORTANT: Replace all PASTE_HASH_HERE values
-- with real hashes from db/generate_hashes.php
-- =============================================

USE moreco_db;

-- =============================================
-- USERS
-- =============================================

-- Admin
INSERT IGNORE INTO users (first_name, last_name, email, username, password, role, phone, address)
VALUES ('Admin', 'MORECO', 'admin@moreco.coop', 'admin', '$2y$10$1bmXkp3l93WAjaLGK8pNBOCYRtHLsfP.aPn28iLKezeY0tlGC98a2', 'admin', '09001234567', 'Morong, Rizal');

-- Clerk
INSERT IGNORE INTO users (first_name, last_name, email, username, password, role, phone, address)
VALUES ('Clerk', 'One', 'clerk@moreco.coop', 'clerk1', '$2y$10$4xSG/nU6t1FuN9AGpngi4eh0gfv6j0tsRq8tRFmm7Io3u/.zZMmwm', 'clerk', '09001234568', 'Morong, Rizal');

-- Loan Officer
INSERT IGNORE INTO users (first_name, last_name, email, username, password, role, phone, address)
VALUES ('Loan', 'Officer', 'loanofficer@moreco.coop', 'loanofficer1', '$2y$10$4QPyqFDqydX/.bTxbewDEeg.aRYL8C5AyAeQHa0YMVgd2ZGoPeMyy', 'loan_officer', '09001234569', 'Morong, Rizal');

-- Demo Member
INSERT IGNORE INTO users (first_name, last_name, email, username, password, role, phone, address)
VALUES ('Juan', 'Dela Cruz', 'juan@email.com', 'juan', '$2y$10$bS6i0Sk5NL.trycRZ.AxXOzKhGPXnDhwa92u82OwksFMXGG7z2qaa', 'member', '09009999999', 'Morong, Rizal');

-- Demo Applicant
INSERT IGNORE INTO users (first_name, last_name, email, username, password, role, phone, address)
VALUES ('Maria', 'Santos', 'maria@email.com', 'maria', '$2y$10$oziXqIZPMS6swy.U8vi7JeneyCJ5L/f/vQ6.xO7TslJkEkJnLko92', 'applicant', '09008888888', 'Morong, Rizal');

-- =============================================
-- EVENTS
-- =============================================
INSERT IGNORE INTO events (title, category, description, event_date, location, organizer, slots, emoji, status)
VALUES
('General Assembly 2026', 'General',
 'Annual General Assembly of all MORECO members. Discussion of annual reports, election of officers, and cooperative updates.',
 '2026-05-15', 'MORECO Main Hall, Morong Rizal', 'MORECO Board of Directors', 200, '🏛️', 'upcoming'),

('Financial Literacy Seminar', 'Education',
 'Learn how to manage your savings, investments, and loans effectively. Free for all members.',
 '2026-05-22', 'Morong Municipal Hall', 'MORECO Education Committee', 80, '📚', 'upcoming'),

('Livelihood Training Program', 'Livelihood',
 'Hands-on training for small business management and livelihood programs for cooperative members.',
 '2026-06-05', 'URS Morong Campus', 'MORECO Livelihood Committee', 50, '🛠️', 'upcoming'),

('Member Orientation — Batch 1', 'Orientation',
 'Mandatory orientation for newly approved members. Learn about cooperative policies, benefits, and member responsibilities.',
 '2026-05-10', 'MORECO Office, Morong Rizal', 'MORECO Membership Committee', 30, '🎓', 'upcoming'),

('Cooperative Anniversary Celebration', 'Special',
 'Celebrate the founding anniversary of MORECO with cultural presentations, raffle, and dinner.',
 '2026-06-20', 'Morong Plaza', 'MORECO Events Committee', 300, '🎉', 'upcoming'),

('Credit and Loans Workshop', 'Finance',
 'Workshop on how to apply for loans, understand interest rates, and manage loan repayments.',
 '2026-04-18', 'MORECO Training Room', 'Loans and Credit Committee', 40, '💰', 'completed');

-- =============================================
-- ANNOUNCEMENTS (with branch support)
-- =============================================
INSERT IGNORE INTO announcements (title, body, icon, priority, branch, posted_at)
VALUES
('Membership Application Now Open',
 'MORECO is now accepting new membership applications for 2026. Visit our office or apply online. Requirements: valid ID, proof of residence, and 2x2 photo.',
 '📋', 'high', 'all', '2026-04-20'),

('Loan Interest Rate Update',
 'Effective May 1, 2026, the interest rate for regular loans will be adjusted to 2.5% per month. Existing loans are not affected.',
 '💰', 'high', 'all', '2026-04-15'),

('General Assembly Schedule',
 'The Annual General Assembly is scheduled for May 15, 2026 at the MORECO Main Hall. All members are encouraged to attend.',
 '🏛️', 'normal', 'morong', '2026-04-10'),

('New Livelihood Program Available',
 'MORECO is launching a new livelihood support program for members engaged in small retail businesses. Apply at the main office.',
 '🛠️', 'normal', 'all', '2026-04-05'),

('Holiday Office Hours',
 'The MORECO office will be closed on May 1 (Labor Day). Regular operations resume on May 2.',
 '📅', 'normal', 'all', '2026-04-28'),

('Teresa Branch Extended Hours',
 'Starting June 2026, the Teresa Branch will extend operating hours to 5:00 PM on weekdays to better serve our growing membership.',
 '🕔', 'normal', 'teresa', '2026-05-01'),

('Antipolo Branch Grand Opening Promo',
 'To celebrate the Antipolo Branch opening, all new members who sign up at Antipolo Branch this May will receive a free share capital top-up of PHP 200.',
 '🎉', 'high', 'antipolo', '2026-05-05'),

('Tanay Branch Closure Notice',
 'The Tanay Branch will be temporarily closed from May 20–22, 2026 for office renovation. We apologize for the inconvenience.',
 '🔧', 'high', 'tanay', '2026-05-12'),

('Siniloan Branch Loan Caravan',
 'Join our Loan Awareness Caravan at the Siniloan Branch on June 3, 2026. Learn about our loan products and get pre-qualified on the spot.',
 '🚌', 'normal', 'siniloan', '2026-05-10'),

('Taytay Branch New Saturday Hours',
 'The Taytay Branch is now open on Saturdays from 8:00 AM to 12:00 PM. Members can transact, deposit, and inquire on selected Saturdays.',
 '📅', 'normal', 'taytay', '2026-04-22'),

('Masinag Branch Orientation Schedule',
 'New member orientation at Masinag Branch is set for June 14, 2026, 9:00 AM. Newly approved members from Antipolo and surrounding areas are encouraged to attend.',
 '🎓', 'normal', 'masinag', '2026-05-08');

-- =============================================
-- BENEFITS — Official MORECO Services
-- =============================================
INSERT IGNORE INTO benefits (title, category, description, eligibility, requirements, how_to_apply, emoji, is_active)
VALUES
-- LOANS
('Regular Loan', 'loan',
 'Borrow up to 3x your share capital at low monthly interest. Flexible repayment terms from 6 to 36 months to help meet everyday financial needs.',
 'Active member for at least 3 months with no outstanding unpaid loans.',
 'Filled loan application form, valid government-issued ID, latest payslip or proof of income, co-maker if required.',
 'Fill out the loan application form online or at the office. Loan officer will review your application within 3–5 business days. Once approved, funds are released via QR transaction.',
 '💰', 1),

('Multipurpose Loan', 'loan',
 'Flexible loan for various personal and household purposes — home improvement, education, medical expenses, and more. Higher loan ceiling than regular loans.',
 'Active member for at least 6 months with good standing.',
 'Filled multipurpose loan form, valid ID, proof of purpose (if applicable), latest payslip or income documents.',
 'Apply at the MORECO office or online. Specify your loan purpose on the form. Processing takes 3–7 business days.',
 '🔄', 1),

('Business Loan', 'loan',
 'Financial assistance for members who want to start, expand, or sustain a small business or livelihood project. Competitive rates with longer repayment terms.',
 'Active member for at least 1 year with a viable business plan.',
 'Business loan application form, valid ID, business plan or proposal, barangay business permit (if existing), financial statements or income proof.',
 'Submit a complete application with business plan at any MORECO branch. A loan officer will schedule a business evaluation interview.',
 '🏪', 1),

('Secured Loan', 'loan',
 'Borrow using your assets as collateral for higher loan amounts and lower interest rates. Ideal for members needing larger capital.',
 'Active member in good standing with acceptable collateral.',
 'Secured loan application form, valid ID, collateral documents (land title, vehicle OR, TCT, etc.), proof of income.',
 'Visit the MORECO main office with your collateral documents. A loan officer will assess collateral value and process your application.',
 '🔒', 1),

('Jewelry Loan', 'loan',
 'Use your gold jewelry as collateral for quick cash. Loan amount is based on appraised value. Fast approval, same-day release.',
 'Any active MORECO member.',
 'Valid ID, jewelry item for appraisal.',
 'Bring your jewelry and valid ID to any MORECO branch. Our appraiser will assess the value and release the loan on the same day.',
 '💍', 1),

('Emergency Loan', 'loan',
 'Quick financial assistance for urgent and unexpected needs such as hospitalization, calamity, or family emergencies. Released within 24 hours of approval.',
 'Active member for at least 1 month.',
 'Emergency loan form, valid ID, brief description or document supporting the emergency.',
 'Apply online or visit any MORECO branch. Emergency loans are prioritized and processed within the same business day.',
 '🚨', 1),

('Botika Loan', 'loan',
 'Special loan designed to help members purchase medicines and medical supplies. Low interest rate with flexible terms.',
 'Active member for at least 3 months.',
 'Botika loan form, valid ID, medical prescription (if applicable).',
 'Apply at any MORECO branch. Present your prescription or medical need. Approval within 1–2 business days.',
 '💊', 1),

('Loan for SSS/GSIS Pensioner', 'loan',
 'Exclusive loan program for members who are SSS or GSIS pensioners. Repayment is automatically deducted from pension.',
 'Active MORECO member receiving SSS or GSIS pension.',
 'Pension loan form, valid ID, pension card or ATM, latest pension credit slip.',
 'Visit the MORECO main office with your pension documents. Loan is processed within 3–5 business days.',
 '👴', 1),

-- SAVINGS
('Savings Deposit', 'savings',
 'Earn quarterly interest on your savings. Keep your money safe while it grows. Minimum maintaining balance of ₱500. Withdrawable anytime.',
 'All active MORECO members.',
 'Member passbook, valid ID.',
 'Open or update your savings account at any MORECO branch. Deposits and withdrawals can be made over the counter.',
 '🏦', 1),

('Time Deposit', 'savings',
 'Earn higher interest by committing your funds for a fixed period. Choose terms from 30 to 360 days. Ideal for members with idle funds.',
 'All active MORECO members.',
 'Time deposit form, valid ID, initial deposit amount (minimum ₱5,000).',
 'Visit any MORECO branch. Fill out the time deposit form and choose your preferred term. Interest is credited upon maturity.',
 '⏳', 1),

('MORECO Savings Plan (MSP)', 'savings',
 'A structured savings program with a fixed monthly contribution. Build your savings systematically with competitive interest rates and year-end incentives.',
 'All active MORECO members.',
 'MSP enrollment form, valid ID, agreed monthly contribution amount.',
 'Enroll at any MORECO branch. Choose your monthly contribution amount and term.',
 '📊', 1),

('One-time Payment Plan (OTP)', 'savings',
 'Make a one-time lump-sum savings deposit and earn higher interest. Great for members who receive lump-sum income such as bonuses, inheritances, or severance pay.',
 'All active MORECO members.',
 'OTP form, valid ID, lump-sum deposit amount.',
 'Visit any MORECO branch with your deposit. Fill out the OTP form. Interest is credited at the end of the agreed term.',
 '💵', 1),

-- INSURANCE / MEMBER BENEFITS
('Pension', 'insurance',
 'Retirement assistance benefit for long-standing MORECO members. Provides periodic financial support to qualifying members.',
 'Member in good standing for at least 10 years or as specified in cooperative by-laws.',
 'Pension application form, valid ID, proof of membership tenure.',
 'Apply at the MORECO main office. The board reviews pension applications periodically.',
 '🏅', 1),

('Medical and Hospitalization', 'insurance',
 'Financial assistance for hospitalization and major medical expenses. Covers partial hospital bills to ease the burden on members and their families.',
 'Active member for at least 6 months.',
 'Medical benefit claim form, hospital billing statement, discharge summary, valid ID.',
 'Submit complete documents at the MORECO main office within 30 days of discharge. Claims are processed within 5–7 business days.',
 '🏥', 1),

('Insurance', 'insurance',
 'Members are covered by cooperative insurance providing protection against accidents, disability, and life events. Coverage is automatic upon active membership.',
 'All active MORECO members are automatically enrolled.',
 'No separate application required. For claims, submit the insurance claim form with supporting documents.',
 'Coverage is automatic upon active membership. For claims, visit any MORECO branch with the claim form and supporting documents.',
 '🛡️', 1),

('Damayan', 'insurance',
 'Death and burial assistance for immediate family members of active MORECO members. Provides financial relief to the bereaved family during a difficult time.',
 'Active member for at least 6 months.',
 'Damayan claim form, death certificate, valid ID of claimant, proof of relationship to deceased.',
 'Submit requirements at any MORECO branch within 30 days of death. Claims are processed within 3–5 business days.',
 '🕊️', 1),

-- PROGRAMS
('Livelihood Support Program', 'program',
 'Financial assistance and free business coaching for members who want to start or grow a small business. Grant or low-interest loan of up to ₱20,000.',
 'Active member for at least 1 year with a viable business plan.',
 'Business plan, valid ID, barangay clearance, filled application form.',
 'Submit a business plan at the MORECO office. A committee will review and schedule an interview within 2 weeks.',
 '🛠️', 1);

-- =============================================
-- ORIENTATION SCHEDULES
-- =============================================
INSERT IGNORE INTO orientation_schedules (title, scheduled_date, scheduled_time, location, max_slots, is_active)
VALUES
('New Member Orientation — Batch May A', '2026-05-10', '09:00:00', 'MORECO Main Hall, Morong Rizal', 20, 1),
('New Member Orientation — Batch May B', '2026-05-17', '14:00:00', 'MORECO Training Room, Morong Rizal', 20, 1),
('New Member Orientation — Batch June A', '2026-06-07', '09:00:00', 'MORECO Main Hall, Morong Rizal', 25, 1);
