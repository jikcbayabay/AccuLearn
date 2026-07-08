-- ============================================================
-- AccuLearn FABM 1 — Quiz Data Loader
-- Generated : 2026-05-24
-- Subject   : Fundamentals of Accountancy, Business and Management 1
-- Quizzes   : 7 (one per CO / module)
-- Questions : 105  |  Answers : 373
--
-- Usage:
--   mysql -u root acculearn < load-quizzes.sql
--   (or run from Laravel: php artisan db:seed --class=QuizSeeder)
--
-- Prerequisites:
--   • `assessments` table exists (seeder already created 27 rows)
--   • `competencies` and `modules` tables exist with correct order values
--
-- To re-run cleanly, uncomment the CLEANUP section below.
-- ============================================================

SET NAMES utf8mb4;
SET foreign_key_checks = 0;

-- ─────────────────────────────────────────────────────────────────────────────
-- OPTIONAL CLEANUP (uncomment to reset)
-- ─────────────────────────────────────────────────────────────────────────────
-- DELETE FROM assessment_answers;
-- DELETE FROM assessment_questions;
-- ALTER TABLE assessment_answers  AUTO_INCREMENT = 1;
-- ALTER TABLE assessment_questions AUTO_INCREMENT = 1;

-- ─────────────────────────────────────────────────────────────────────────────
-- 1. CREATE TABLES (idempotent)
-- ─────────────────────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS `assessment_questions` (
  `id`             BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `assessment_id`  BIGINT UNSIGNED  NOT NULL,
  `question_text`  TEXT             NOT NULL,
  `question_type`  VARCHAR(50)      NOT NULL DEFAULT 'multiple_choice',
  `sequence_order` TINYINT UNSIGNED NOT NULL DEFAULT 1,
  `is_active`      TINYINT(1)       NOT NULL DEFAULT 1,
  `created_at`     TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `aq_assessment_idx` (`assessment_id`),
  CONSTRAINT `fk_aq_assessment`
    FOREIGN KEY (`assessment_id`) REFERENCES `assessments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `assessment_answers` (
  `id`             BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `question_id`    BIGINT UNSIGNED  NOT NULL,
  `answer_text`    TEXT             NOT NULL,
  `is_correct`     TINYINT(1)       NOT NULL DEFAULT 0,
  `sequence_order` TINYINT UNSIGNED NOT NULL DEFAULT 1,
  `explanation`    TEXT                 NULL,
  `created_at`     TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `aa_question_idx` (`question_id`),
  CONSTRAINT `fk_aa_question`
    FOREIGN KEY (`question_id`) REFERENCES `assessment_questions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────────────────────────
-- 2. UPDATE ASSESSMENT TITLES (first competency of each module)
-- ─────────────────────────────────────────────────────────────────────────────

UPDATE `assessments` a
  JOIN `competencies` c ON a.competency_id = c.id
  JOIN `modules` m ON c.module_id = m.id
SET a.title = 'Module Quiz: Accounting Concepts and Principles', a.passing_score = 75
WHERE m.`order` = 1 AND c.`order` = 1;

UPDATE `assessments` a
  JOIN `competencies` c ON a.competency_id = c.id
  JOIN `modules` m ON c.module_id = m.id
SET a.title = 'Module Quiz: The Accounting Equation', a.passing_score = 75
WHERE m.`order` = 2 AND c.`order` = 1;

UPDATE `assessments` a
  JOIN `competencies` c ON a.competency_id = c.id
  JOIN `modules` m ON c.module_id = m.id
SET a.title = 'Module Quiz: The Five Major Accounts and Chart of Accounts', a.passing_score = 75
WHERE m.`order` = 3 AND c.`order` = 1;

UPDATE `assessments` a
  JOIN `competencies` c ON a.competency_id = c.id
  JOIN `modules` m ON c.module_id = m.id
SET a.title = 'Module Quiz: Books of Accounts', a.passing_score = 75
WHERE m.`order` = 4 AND c.`order` = 1;

UPDATE `assessments` a
  JOIN `competencies` c ON a.competency_id = c.id
  JOIN `modules` m ON c.module_id = m.id
SET a.title = 'Module Quiz: Business Transactions and Their Analysis (Steps 1-3)', a.passing_score = 75
WHERE m.`order` = 5 AND c.`order` = 1;

UPDATE `assessments` a
  JOIN `competencies` c ON a.competency_id = c.id
  JOIN `modules` m ON c.module_id = m.id
SET a.title = 'Module Quiz: Trial Balance and Adjusting Entries (Steps 4-5)', a.passing_score = 75
WHERE m.`order` = 6 AND c.`order` = 1;

UPDATE `assessments` a
  JOIN `competencies` c ON a.competency_id = c.id
  JOIN `modules` m ON c.module_id = m.id
SET a.title = 'Module Quiz: Completion of the Accounting Cycle (Steps 6-10)', a.passing_score = 75
WHERE m.`order` = 7 AND c.`order` = 1;

-- ─────────────────────────────────────────────────────────────────────────────
-- 3. RESOLVE ASSESSMENT IDs
-- ─────────────────────────────────────────────────────────────────────────────

SET @a1_id = (SELECT a.id FROM assessments a JOIN competencies c ON a.competency_id = c.id JOIN modules m ON c.module_id = m.id WHERE m.`order` = 1 AND c.`order` = 1 LIMIT 1);
SET @a2_id = (SELECT a.id FROM assessments a JOIN competencies c ON a.competency_id = c.id JOIN modules m ON c.module_id = m.id WHERE m.`order` = 2 AND c.`order` = 1 LIMIT 1);
SET @a3_id = (SELECT a.id FROM assessments a JOIN competencies c ON a.competency_id = c.id JOIN modules m ON c.module_id = m.id WHERE m.`order` = 3 AND c.`order` = 1 LIMIT 1);
SET @a4_id = (SELECT a.id FROM assessments a JOIN competencies c ON a.competency_id = c.id JOIN modules m ON c.module_id = m.id WHERE m.`order` = 4 AND c.`order` = 1 LIMIT 1);
SET @a5_id = (SELECT a.id FROM assessments a JOIN competencies c ON a.competency_id = c.id JOIN modules m ON c.module_id = m.id WHERE m.`order` = 5 AND c.`order` = 1 LIMIT 1);
SET @a6_id = (SELECT a.id FROM assessments a JOIN competencies c ON a.competency_id = c.id JOIN modules m ON c.module_id = m.id WHERE m.`order` = 6 AND c.`order` = 1 LIMIT 1);
SET @a7_id = (SELECT a.id FROM assessments a JOIN competencies c ON a.competency_id = c.id JOIN modules m ON c.module_id = m.id WHERE m.`order` = 7 AND c.`order` = 1 LIMIT 1);

-- ─────────────────────────────────────────────────────────────────────────────
-- CO1 — Accounting Concepts and Principles (15 questions, 44 answers)
-- ─────────────────────────────────────────────────────────────────────────────

INSERT INTO `assessment_questions` (assessment_id, question_text, question_type, sequence_order, is_active, created_at, updated_at) VALUES
(@a1_id, 'What is often referred to as the "Language of Business"?', 'multiple_choice', 1, 1, NOW(), NOW()),
(@a1_id, 'Who is the "Father of Modern Accounting"?', 'multiple_choice', 2, 1, NOW(), NOW()),
(@a1_id, 'Which body regulates the CPA Licensure Exam in the Philippines?', 'multiple_choice', 3, 1, NOW(), NOW()),
(@a1_id, 'Which concept treats the business and the owner as two separate "personalities"?', 'multiple_choice', 4, 1, NOW(), NOW()),
(@a1_id, 'Which assumption states that a business will continue to operate indefinitely?', 'multiple_choice', 5, 1, NOW(), NOW()),
(@a1_id, 'Accounting involves recording transactions in a __________ manner.', 'multiple_choice', 6, 1, NOW(), NOW()),
(@a1_id, 'A "Calendar Year" ends on which date?', 'multiple_choice', 7, 1, NOW(), NOW()),
(@a1_id, 'Which principle requires assets to be recorded at their original purchase price?', 'multiple_choice', 8, 1, NOW(), NOW()),
(@a1_id, 'Which of the following is an internal user of accounting information?', 'multiple_choice', 9, 1, NOW(), NOW()),
(@a1_id, 'The process of transferring information from a journal to a ledger is called:', 'multiple_choice', 10, 1, NOW(), NOW()),
(@a1_id, 'Which branch of accounting prepares reports primarily for external users?', 'multiple_choice', 11, 1, NOW(), NOW()),
(@a1_id, 'What does GAAP stand for?', 'multiple_choice', 12, 1, NOW(), NOW()),
(@a1_id, 'Which concept requires transactions to be measured in Philippine Pesos?', 'multiple_choice', 13, 1, NOW(), NOW()),
(@a1_id, 'Bookkeeping is a part of the broader field of accounting.', 'multiple_choice', 14, 1, NOW(), NOW()),
(@a1_id, 'Which principle requires receipts or invoices to support all recorded data?', 'multiple_choice', 15, 1, NOW(), NOW());

SET @q1_start = LAST_INSERT_ID();

INSERT INTO `assessment_answers` (question_id, answer_text, is_correct, sequence_order, explanation, created_at, updated_at) VALUES
(@q1_start+0,'Mathematics',0,1,'Mathematics is a tool used in accounting, not the language of business itself.',NOW(),NOW()),
(@q1_start+0,'Accounting',1,2,'Accounting is called the language of business because it communicates financial information that allows businesses to make decisions.',NOW(),NOW()),
(@q1_start+0,'Management',0,3,'Management is a business function, not the language of business.',NOW(),NOW()),
(@q1_start+1,'Luca Pacioli',1,1,'Luca Pacioli introduced the double-entry bookkeeping system in 1494, earning him the title Father of Modern Accounting.',NOW(),NOW()),
(@q1_start+1,'Leonardo da Vinci',0,2,'Leonardo da Vinci was a Renaissance artist and inventor, not an accountant.',NOW(),NOW()),
(@q1_start+1,'Queen Victoria',0,3,'Queen Victoria was a British monarch with no connection to accounting.',NOW(),NOW()),
(@q1_start+2,'SEC',0,1,'The SEC (Securities and Exchange Commission) regulates the securities market, not CPA licensure.',NOW(),NOW()),
(@q1_start+2,'PRC-BOA',1,2,'The Professional Regulation Commission - Board of Accountancy (PRC-BOA) administers and regulates the CPA Licensure Exam in the Philippines.',NOW(),NOW()),
(@q1_start+2,'BIR',0,3,'The Bureau of Internal Revenue collects taxes; it does not regulate CPA licensure.',NOW(),NOW()),
(@q1_start+3,'Going Concern',0,1,'Going Concern assumes the business will operate indefinitely, not that owner and business are separate.',NOW(),NOW()),
(@q1_start+3,'Economic Entity',1,2,'The Economic Entity concept requires that the business be treated as a separate entity from its owner. Personal transactions must not be mixed with business transactions.',NOW(),NOW()),
(@q1_start+3,'Time Period',0,3,'The Time Period concept divides the life of a business into equal intervals for reporting.',NOW(),NOW()),
(@q1_start+4,'Accrual',0,1,'The Accrual assumption requires revenues and expenses to be recognized when earned/incurred, not when cash is received/paid.',NOW(),NOW()),
(@q1_start+4,'Going Concern',1,2,'The Going Concern assumption holds that a business will continue to operate for the foreseeable future, allowing assets to be valued at historical cost rather than liquidation value.',NOW(),NOW()),
(@q1_start+4,'Monetary Unit',0,3,'The Monetary Unit assumption requires that all transactions be measured in a stable monetary unit (Philippine Peso).',NOW(),NOW()),
(@q1_start+5,'Random',0,1,'Random recording would make it impossible to trace or verify transactions.',NOW(),NOW()),
(@q1_start+5,'Chronological',1,2,'Transactions are recorded in chronological (date) order in the journal, providing a complete and traceable history of business events.',NOW(),NOW()),
(@q1_start+5,'Alphabetical',0,3,'Alphabetical order is not used for recording transactions; that would obscure the timing of events.',NOW(),NOW()),
(@q1_start+6,'June 30',0,1,'June 30 is the end of the second quarter, not the calendar year.',NOW(),NOW()),
(@q1_start+6,'December 31',1,2,'A Calendar Year runs from January 1 to December 31. Any accounting period ending on a date other than December 31 is called a Fiscal Year.',NOW(),NOW()),
(@q1_start+6,'March 31',0,3,'March 31 marks the end of the first quarter of the calendar year.',NOW(),NOW()),
(@q1_start+7,'Cost Principle',1,1,'The Cost (Historical Cost) Principle requires assets to be recorded at the actual price paid to acquire them, not at current market value or appraised value.',NOW(),NOW()),
(@q1_start+7,'Matching Principle',0,2,'The Matching Principle requires expenses to be recorded in the same period as the revenues they helped generate.',NOW(),NOW()),
(@q1_start+7,'Objectivity',0,3,'The Objectivity Principle requires that accounting data be supported by verifiable evidence, not that assets be at original cost.',NOW(),NOW()),
(@q1_start+8,'Bank Manager',0,1,'A bank manager is an external user who needs accounting information to decide on loans.',NOW(),NOW()),
(@q1_start+8,'Business Manager',1,2,'A business manager is an internal user who uses accounting information for planning, controlling, and making internal business decisions.',NOW(),NOW()),
(@q1_start+8,'Tax Auditor',0,3,'A tax auditor works for the government (BIR) and is an external user of accounting information.',NOW(),NOW()),
(@q1_start+9,'Recording',0,1,'Recording refers to writing down transactions in the journal, not moving them to the ledger.',NOW(),NOW()),
(@q1_start+9,'Posting',1,2,'Posting is the process of transferring each debit and credit from the general journal to the appropriate account in the general ledger. It is Step 3 of the accounting cycle.',NOW(),NOW()),
(@q1_start+9,'Summarizing',0,3,'Summarizing occurs when account balances are compiled, such as when preparing the trial balance.',NOW(),NOW()),
(@q1_start+10,'Financial Accounting',1,1,'Financial Accounting prepares general-purpose financial statements for external users such as investors, creditors, government agencies, and the public.',NOW(),NOW()),
(@q1_start+10,'Management Accounting',0,2,'Management Accounting provides information for internal users (managers) to plan and make decisions.',NOW(),NOW()),
(@q1_start+10,'Tax Accounting',0,3,'Tax Accounting focuses on compliance with tax laws and regulations, primarily for the BIR.',NOW(),NOW()),
(@q1_start+11,'General Accounting and Audit Principles',0,1,'This is not the correct expansion; GAAP does not refer to auditing specifically.',NOW(),NOW()),
(@q1_start+11,'Generally Accepted Accounting Principles',1,2,'GAAP stands for Generally Accepted Accounting Principles — the standard framework of guidelines, conventions, and rules that accountants follow when preparing financial statements.',NOW(),NOW()),
(@q1_start+11,'Global Accounting and Audit Practices',0,3,'GAAP is not a global standard; in the Philippines it refers specifically to locally adopted principles.',NOW(),NOW()),
(@q1_start+12,'Economic Entity',0,1,'The Economic Entity concept separates the business from its owner, not about currency.',NOW(),NOW()),
(@q1_start+12,'Monetary Unit',1,2,'The Monetary Unit (Stable Monetary Unit) Assumption requires all business transactions to be recorded in a single, stable monetary unit — the Philippine Peso for Philippine businesses.',NOW(),NOW()),
(@q1_start+12,'Adequate Disclosure',0,3,'Adequate Disclosure requires all material financial information to be included in financial reports, not related to currency.',NOW(),NOW()),
(@q1_start+13,'True',1,1,'True. Bookkeeping is the recording phase of accounting. Accounting is broader and includes recording, classifying, summarizing, and interpreting financial data.',NOW(),NOW()),
(@q1_start+13,'False',0,2,'False is incorrect. Bookkeeping is indeed a subset of the accounting process, specifically the data entry and recording phase.',NOW(),NOW()),
(@q1_start+14,'Matching Principle',0,1,'The Matching Principle deals with timing of revenues and expenses, not documentary support.',NOW(),NOW()),
(@q1_start+14,'Objectivity Principle',1,2,'The Objectivity (Verifiability) Principle requires that all accounting data be supported by objective, verifiable evidence such as official receipts, invoices, checks, and other source documents.',NOW(),NOW()),
(@q1_start+14,'Consistency Principle',0,3,'The Consistency Principle requires using the same accounting methods from period to period, not documentary support.',NOW(),NOW());

-- ─────────────────────────────────────────────────────────────────────────────
-- CO2 — The Accounting Equation (15 questions, 44 answers)
-- ─────────────────────────────────────────────────────────────────────────────

INSERT INTO `assessment_questions` (assessment_id, question_text, question_type, sequence_order, is_active, created_at, updated_at) VALUES
(@a2_id, 'What is the fundamental accounting equation?', 'multiple_choice', 1, 1, NOW(), NOW()),
(@a2_id, 'These are the economic resources owned or controlled by a business.', 'multiple_choice', 2, 1, NOW(), NOW()),
(@a2_id, 'Which element represents the "claims of creditors" against the assets?', 'multiple_choice', 3, 1, NOW(), NOW()),
(@a2_id, 'What is the alternative name for Owner''s Equity?', 'multiple_choice', 4, 1, NOW(), NOW()),
(@a2_id, 'If Assets = ₱50,000 and Liabilities = ₱20,000, what is the Owner''s Equity?', 'multiple_choice', 5, 1, NOW(), NOW()),
(@a2_id, 'When an owner invests cash into the business, which side(s) of the equation increase?', 'multiple_choice', 6, 1, NOW(), NOW()),
(@a2_id, 'Buying office supplies for cash is an example of an:', 'multiple_choice', 7, 1, NOW(), NOW()),
(@a2_id, 'Which of these is a common example of a Liability?', 'multiple_choice', 8, 1, NOW(), NOW()),
(@a2_id, 'If the Balance Scale shows ₱100,000 on the Asset side and ₱60,000 on the Equity side, how much are the Liabilities?', 'multiple_choice', 9, 1, NOW(), NOW()),
(@a2_id, 'Which account title represents money owed TO the business by customers?', 'multiple_choice', 10, 1, NOW(), NOW()),
(@a2_id, 'The accounting equation must remain balanced after every single transaction.', 'multiple_choice', 11, 1, NOW(), NOW()),
(@a2_id, 'Paying a ₱5,000 debt using business cash results in:', 'multiple_choice', 12, 1, NOW(), NOW()),
(@a2_id, 'Which element is described as "Economic Obligations"?', 'multiple_choice', 13, 1, NOW(), NOW()),
(@a2_id, 'If a business has ₱30,000 in Assets and ₱30,000 in Equity, what is the value of Liabilities?', 'multiple_choice', 14, 1, NOW(), NOW()),
(@a2_id, 'Owner''s Drawings (withdrawals) have what effect on the equation?', 'multiple_choice', 15, 1, NOW(), NOW());

SET @q2_start = LAST_INSERT_ID();

INSERT INTO `assessment_answers` (question_id, answer_text, is_correct, sequence_order, explanation, created_at, updated_at) VALUES
(@q2_start+0,'Assets + Liabilities = Owner''s Equity',0,1,'This is incorrect. Adding assets and liabilities together does not produce owner''s equity.',NOW(),NOW()),
(@q2_start+0,'Assets = Liabilities + Owner''s Equity',1,2,'The fundamental accounting equation states that a business''s resources (Assets) equal the claims on those resources by creditors (Liabilities) plus the owner (Owner''s Equity). This equation must always remain balanced.',NOW(),NOW()),
(@q2_start+0,'Liabilities = Assets + Owner''s Equity',0,3,'This rearrangement is mathematically wrong.',NOW(),NOW()),
(@q2_start+1,'Assets',1,1,'Assets are the economic resources — things of value owned or controlled by the business that are expected to provide future economic benefits.',NOW(),NOW()),
(@q2_start+1,'Liabilities',0,2,'Liabilities are economic obligations (debts the business owes), not resources owned.',NOW(),NOW()),
(@q2_start+1,'Revenues',0,3,'Revenues are income earned from business operations, not physical or financial resources.',NOW(),NOW()),
(@q2_start+2,'Owner''s Equity',0,1,'Owner''s Equity represents the owner''s claim on the assets, not creditors'' claims.',NOW(),NOW()),
(@q2_start+2,'Liabilities',1,2,'Liabilities (Economic Obligations) are the claims of creditors — amounts the business owes to outside parties such as banks, suppliers, and lenders.',NOW(),NOW()),
(@q2_start+2,'Economic Resources',0,3,'Economic Resources is another term for assets, not creditors'' claims.',NOW(),NOW()),
(@q2_start+3,'Residual Interest',1,1,'Owner''s Equity is also called Residual Interest because it represents what remains of the assets after all liabilities have been paid. Equation: Equity = Assets - Liabilities.',NOW(),NOW()),
(@q2_start+3,'Economic Obligations',0,2,'Economic Obligations is the alternative name for Liabilities, not Owner''s Equity.',NOW(),NOW()),
(@q2_start+3,'Total Debt',0,3,'Total Debt refers to Liabilities, not Owner''s Equity.',NOW(),NOW()),
(@q2_start+4,'₱70,000',0,1,'₱70,000 would be incorrect — that is the sum of assets and liabilities, not the difference.',NOW(),NOW()),
(@q2_start+4,'₱30,000',1,2,'Owner''s Equity = Assets - Liabilities = ₱50,000 - ₱20,000 = ₱30,000.',NOW(),NOW()),
(@q2_start+4,'₱50,000',0,3,'₱50,000 is the total assets, not the owner''s equity.',NOW(),NOW()),
(@q2_start+5,'Only the left side (Assets)',0,1,'If only assets increase without a corresponding increase on the right side, the equation becomes unbalanced.',NOW(),NOW()),
(@q2_start+5,'Only the right side (Equity)',0,2,'If only equity increases without assets increasing, the equation becomes unbalanced.',NOW(),NOW()),
(@q2_start+5,'Both sides',1,3,'When an owner invests cash, Assets (Cash) increase on the left side AND Owner''s Equity (Capital) increases on the right side, keeping the equation balanced.',NOW(),NOW()),
(@q2_start+6,'Asset Swap',1,1,'Buying supplies for cash is an asset swap — one asset (Cash) decreases while another asset (Supplies) increases by the same amount. Total assets remain unchanged.',NOW(),NOW()),
(@q2_start+6,'Increase in Liabilities',0,2,'No liability is created when paying cash — the transaction is settled immediately.',NOW(),NOW()),
(@q2_start+6,'Increase in Equity',0,3,'Equity does not change in a cash purchase of supplies; it is purely an asset exchange.',NOW(),NOW()),
(@q2_start+7,'Accounts Receivable',0,1,'Accounts Receivable is an asset — it represents money that customers owe TO the business.',NOW(),NOW()),
(@q2_start+7,'Notes Payable',1,2,'Notes Payable is a liability — it represents a formal written promise by the business to pay a creditor a specific amount on a future date.',NOW(),NOW()),
(@q2_start+7,'Office Equipment',0,3,'Office Equipment is an asset — it is a physical resource owned by the business.',NOW(),NOW()),
(@q2_start+8,'₱160,000',0,1,'₱160,000 is the sum of assets and equity, not the liabilities.',NOW(),NOW()),
(@q2_start+8,'₱40,000',1,2,'Liabilities = Assets - Owner''s Equity = ₱100,000 - ₱60,000 = ₱40,000.',NOW(),NOW()),
(@q2_start+8,'₱100,000',0,3,'₱100,000 is the total assets, not the liabilities.',NOW(),NOW()),
(@q2_start+9,'Accounts Payable',0,1,'Accounts Payable represents money the business owes TO its suppliers — it is a liability.',NOW(),NOW()),
(@q2_start+9,'Accounts Receivable',1,2,'Accounts Receivable is an asset representing amounts owed TO the business by customers who purchased goods or services on credit.',NOW(),NOW()),
(@q2_start+9,'Salaries Payable',0,3,'Salaries Payable is a liability representing unpaid wages owed TO employees.',NOW(),NOW()),
(@q2_start+10,'True',1,1,'True. The accounting equation (Assets = Liabilities + Owner''s Equity) must always be in balance after every transaction. This is the foundation of double-entry bookkeeping — every transaction has a dual effect.',NOW(),NOW()),
(@q2_start+10,'False',0,2,'False. The equation must remain balanced at all times. An unbalanced equation indicates a recording error.',NOW(),NOW()),
(@q2_start+11,'Decrease Asset, Decrease Liability',1,1,'Cash (an asset) decreases by ₱5,000, and the corresponding debt (a liability) also decreases by ₱5,000. Both sides of the equation decrease equally, maintaining balance.',NOW(),NOW()),
(@q2_start+11,'Increase Asset, Decrease Liability',0,2,'Cash would decrease (go out), not increase, when paying a debt.',NOW(),NOW()),
(@q2_start+11,'Decrease Asset, Decrease Equity',0,3,'Owner''s Equity does not decrease when paying off a liability — the business is simply reducing a debt.',NOW(),NOW()),
(@q2_start+12,'Assets',0,1,'Assets are Economic Resources — things of value owned by the business.',NOW(),NOW()),
(@q2_start+12,'Liabilities',1,2,'Liabilities are also called Economic Obligations — present duties arising from past events that are expected to result in an outflow of resources from the business.',NOW(),NOW()),
(@q2_start+12,'Equity',0,3,'Equity (Residual Interest) is what remains after obligations are subtracted from resources.',NOW(),NOW()),
(@q2_start+13,'₱60,000',0,1,'₱60,000 would be incorrect; that is the sum of assets and equity.',NOW(),NOW()),
(@q2_start+13,'₱0',1,2,'Liabilities = Assets - Equity = ₱30,000 - ₱30,000 = ₱0. This means the business has no debt; all assets are owned entirely by the owner.',NOW(),NOW()),
(@q2_start+13,'₱30,000',0,3,'If liabilities were ₱30,000, then Assets = Liabilities + Equity = ₱30,000 + ₱30,000 = ₱60,000, but assets are only ₱30,000.',NOW(),NOW()),
(@q2_start+14,'Decrease Assets and Decrease Equity',1,1,'When an owner withdraws cash, Assets (Cash) decrease on the left side, and Owner''s Equity (via the Drawings account) decreases on the right side. The equation remains balanced.',NOW(),NOW()),
(@q2_start+14,'Increase Assets and Increase Equity',0,2,'Withdrawals remove cash from the business — they decrease, not increase, assets.',NOW(),NOW()),
(@q2_start+14,'Decrease Assets and Increase Liabilities',0,3,'Drawings are a reduction in owner''s equity, not the creation of a new liability.',NOW(),NOW());

-- ─────────────────────────────────────────────────────────────────────────────
-- CO3 — Types of Major Accounts (15 questions, 60 answers)
-- ─────────────────────────────────────────────────────────────────────────────

INSERT INTO `assessment_questions` (assessment_id, question_text, question_type, sequence_order, is_active, created_at, updated_at) VALUES
(@a3_id, 'The five major account classifications used in accounting are:', 'multiple_choice', 1, 1, NOW(), NOW()),
(@a3_id, 'Which of the following is an example of an ASSET account?', 'multiple_choice', 2, 1, NOW(), NOW()),
(@a3_id, 'Which account records money owed TO the business by customers?', 'multiple_choice', 3, 1, NOW(), NOW()),
(@a3_id, 'Which of the following is classified as a LIABILITY?', 'multiple_choice', 4, 1, NOW(), NOW()),
(@a3_id, 'Owner''s Capital belongs to which account classification?', 'multiple_choice', 5, 1, NOW(), NOW()),
(@a3_id, 'Service Revenue is an example of which type of account?', 'multiple_choice', 6, 1, NOW(), NOW()),
(@a3_id, 'Which account is classified as an EXPENSE?', 'multiple_choice', 7, 1, NOW(), NOW()),
(@a3_id, 'The Chart of Accounts is best described as:', 'multiple_choice', 8, 1, NOW(), NOW()),
(@a3_id, 'Accounts with balances that carry over from one period to the next are called:', 'multiple_choice', 9, 1, NOW(), NOW()),
(@a3_id, 'Which of the following are classified as TEMPORARY accounts?', 'multiple_choice', 10, 1, NOW(), NOW()),
(@a3_id, 'Owner''s Drawings is best classified as:', 'multiple_choice', 11, 1, NOW(), NOW()),
(@a3_id, 'Which account would appear in the ASSET section of the chart of accounts?', 'multiple_choice', 12, 1, NOW(), NOW()),
(@a3_id, 'In a typical Chart of Accounts for a service business, which number range is usually assigned to ASSET accounts?', 'multiple_choice', 13, 1, NOW(), NOW()),
(@a3_id, 'Which of the following is NOT classified as a current asset?', 'multiple_choice', 14, 1, NOW(), NOW()),
(@a3_id, 'Accumulated Depreciation is best described as:', 'multiple_choice', 15, 1, NOW(), NOW());

SET @q3_start = LAST_INSERT_ID();

INSERT INTO `assessment_answers` (question_id, answer_text, is_correct, sequence_order, explanation, created_at, updated_at) VALUES
(@q3_start+0,'Cash, Assets, Debits, Credits, Equity',0,1,'Cash is only one account within Assets; Debits and Credits are recording tools, not account classifications.',NOW(),NOW()),
(@q3_start+0,'Assets, Liabilities, Owner''s Equity, Revenue, Expenses',1,2,'The five major account classifications are: Assets (resources), Liabilities (obligations), Owner''s Equity (residual interest), Revenue (income earned), and Expenses (costs incurred). These five categories encompass every account in a business''s chart of accounts.',NOW(),NOW()),
(@q3_start+0,'Income, Costs, Capital, Debts, Properties',0,3,'These informal terms do not represent the standard five major account classifications used in accounting.',NOW(),NOW()),
(@q3_start+0,'Current Assets, Fixed Assets, Payables, Revenue, Net Income',0,4,'These are subcategories, not the five major classification groups.',NOW(),NOW()),
(@q3_start+1,'Accounts Payable',0,1,'Accounts Payable is a Liability — it represents money the business owes to suppliers.',NOW(),NOW()),
(@q3_start+1,'Cash',1,2,'Cash is the most liquid asset. It is classified as a current asset because it is available immediately for business use.',NOW(),NOW()),
(@q3_start+1,'Service Revenue',0,3,'Service Revenue is a Revenue account, not an asset.',NOW(),NOW()),
(@q3_start+1,'Rent Expense',0,4,'Rent Expense is an Expense account, representing the cost of using rented space.',NOW(),NOW()),
(@q3_start+2,'Accounts Payable',0,1,'Accounts Payable records money the business owes TO creditors — it is a liability.',NOW(),NOW()),
(@q3_start+2,'Notes Payable',0,2,'Notes Payable is a liability representing a formal written debt of the business.',NOW(),NOW()),
(@q3_start+2,'Accounts Receivable',1,3,'Accounts Receivable is an asset account that records the amounts owed TO the business by its credit customers. It represents a future cash inflow.',NOW(),NOW()),
(@q3_start+2,'Unearned Revenue',0,4,'Unearned Revenue is a liability — cash received in advance for services not yet performed.',NOW(),NOW()),
(@q3_start+3,'Equipment',0,1,'Equipment is a non-current asset — a physical resource owned by the business.',NOW(),NOW()),
(@q3_start+3,'Owner''s Capital',0,2,'Owner''s Capital is an Owner''s Equity account representing the owner''s investment in the business.',NOW(),NOW()),
(@q3_start+3,'Notes Payable',1,3,'Notes Payable is a liability — a formal written obligation (promissory note) to repay borrowed money. It represents the business''s debt to a lender.',NOW(),NOW()),
(@q3_start+3,'Supplies',0,4,'Supplies is a current asset — items purchased for use in business operations.',NOW(),NOW()),
(@q3_start+4,'Asset',0,1,'Assets are resources owned by the business, not the owner''s investment claim.',NOW(),NOW()),
(@q3_start+4,'Liability',0,2,'Liabilities are obligations to outside parties (creditors), not to the owner.',NOW(),NOW()),
(@q3_start+4,'Owner''s Equity',1,3,'Owner''s Capital is the primary Owner''s Equity account. It represents the owner''s total investment in the business and increases with additional investments and net income, decreasing with withdrawals and net losses.',NOW(),NOW()),
(@q3_start+4,'Expense',0,4,'Owner''s Capital is not an expense; it is the owner''s stake in the business.',NOW(),NOW()),
(@q3_start+5,'Asset',0,1,'Service Revenue increases equity through income earned, not a resource owned.',NOW(),NOW()),
(@q3_start+5,'Liability',0,2,'Revenue is not an obligation owed to creditors.',NOW(),NOW()),
(@q3_start+5,'Owner''s Equity',0,3,'Revenue is a separate major classification that increases equity indirectly through net income.',NOW(),NOW()),
(@q3_start+5,'Revenue',1,4,'Service Revenue is a Revenue account — it records income earned by the business from performing services. It has a normal credit balance and increases Owner''s Equity when closed at year-end.',NOW(),NOW()),
(@q3_start+6,'Prepaid Insurance',0,1,'Prepaid Insurance is an asset — it represents an insurance premium paid in advance that provides future benefit.',NOW(),NOW()),
(@q3_start+6,'Utilities Expense',1,2,'Utilities Expense is an Expense account — it records the cost of electricity, water, and other utilities consumed during the period.',NOW(),NOW()),
(@q3_start+6,'Land',0,3,'Land is a non-current asset — a physical resource owned by the business.',NOW(),NOW()),
(@q3_start+6,'Notes Receivable',0,4,'Notes Receivable is an asset — a formal written promise from a customer or debtor to pay the business.',NOW(),NOW()),
(@q3_start+7,'A list of all customers and their balances',0,1,'A list of customers and balances would be the Accounts Receivable subsidiary ledger, not the chart of accounts.',NOW(),NOW()),
(@q3_start+7,'A financial statement showing profits',0,2,'Financial statements showing profits are income statements, not the chart of accounts.',NOW(),NOW()),
(@q3_start+7,'A numbered list of all accounts used by a business',1,3,'The Chart of Accounts is an indexed, numbered directory of all account titles used by a business. Each account is assigned a unique number for easy reference, organized by the five major account classifications.',NOW(),NOW()),
(@q3_start+7,'A record of all cash transactions',0,4,'A record of cash transactions would be the Cash Receipts/Disbursements Journal, not the chart of accounts.',NOW(),NOW()),
(@q3_start+8,'Temporary accounts',0,1,'Temporary (nominal) accounts are closed to zero at the end of each period; their balances do NOT carry over.',NOW(),NOW()),
(@q3_start+8,'Nominal accounts',0,2,'Nominal accounts is another name for temporary accounts — Revenue, Expense, and Drawings. They are reset to zero each period.',NOW(),NOW()),
(@q3_start+8,'Permanent accounts',1,3,'Permanent (real) accounts — Assets, Liabilities, and Owner''s Capital — carry their balances from one accounting period to the next. They are never closed.',NOW(),NOW()),
(@q3_start+8,'Closing accounts',0,4,'Closing accounts is not a classification; closing is a process applied to temporary accounts.',NOW(),NOW()),
(@q3_start+9,'Assets, Liabilities, Equity',0,1,'Assets, Liabilities, and Equity are permanent accounts — their balances carry forward to the next period.',NOW(),NOW()),
(@q3_start+9,'Revenues, Expenses, Drawings',1,2,'Revenues, Expenses, and Drawings are temporary (nominal) accounts. They are closed to zero at the end of every accounting period so they reflect only the current period''s activity.',NOW(),NOW()),
(@q3_start+9,'Cash, Equipment, Land',0,3,'Cash, Equipment, and Land are all asset accounts — they are permanent accounts.',NOW(),NOW()),
(@q3_start+9,'Payables, Receivables, Inventory',0,4,'Payables (liability), Receivables (asset), and Inventory (asset) are all permanent accounts.',NOW(),NOW()),
(@q3_start+10,'A Liability',0,1,'Drawings is not a debt owed to an outside party; it is the owner taking back their own investment.',NOW(),NOW()),
(@q3_start+10,'An Asset',0,2,'Drawings reduces assets (usually cash) but is not itself an asset.',NOW(),NOW()),
(@q3_start+10,'A Revenue account',0,3,'Drawings decreases equity; revenue increases equity. They are opposites.',NOW(),NOW()),
(@q3_start+10,'A contra-equity account',1,4,'Owner''s Drawings is a contra-equity account — it has a normal debit balance and reduces Owner''s Capital. It is a temporary account closed to Capital at year-end.',NOW(),NOW()),
(@q3_start+11,'Loans Payable',0,1,'Loans Payable is a liability — a debt owed to a financial institution.',NOW(),NOW()),
(@q3_start+11,'Prepaid Rent',1,2,'Prepaid Rent is a current asset — it represents rent paid in advance that provides a future economic benefit (the right to occupy space in future periods).',NOW(),NOW()),
(@q3_start+11,'Unearned Revenue',0,3,'Unearned Revenue is a liability — it represents cash received for services not yet performed.',NOW(),NOW()),
(@q3_start+11,'Service Fees Earned',0,4,'Service Fees Earned is a revenue account, not an asset.',NOW(),NOW()),
(@q3_start+12,'100-199',1,1,'By convention, asset accounts are numbered 100-199, liabilities 200-299, owner''s equity 300-399, revenues 400-499, and expenses 500-599. This numbering system makes it easy to identify an account''s classification at a glance.',NOW(),NOW()),
(@q3_start+12,'200-299',0,2,'The 200-299 range is typically reserved for Liability accounts.',NOW(),NOW()),
(@q3_start+12,'300-399',0,3,'The 300-399 range is typically reserved for Owner''s Equity accounts.',NOW(),NOW()),
(@q3_start+12,'400-499',0,4,'The 400-499 range is typically reserved for Revenue accounts.',NOW(),NOW()),
(@q3_start+13,'Cash',0,1,'Cash is a current asset — it is the most liquid asset available for immediate use.',NOW(),NOW()),
(@q3_start+13,'Accounts Receivable',0,2,'Accounts Receivable is a current asset — it is expected to be collected within one year.',NOW(),NOW()),
(@q3_start+13,'Prepaid Insurance',0,3,'Prepaid Insurance is typically a current asset if the coverage period is within one year.',NOW(),NOW()),
(@q3_start+13,'Land',1,4,'Land is a non-current (long-term) asset. It has an indefinite useful life and is NOT subject to depreciation. It is not expected to be converted to cash within one year.',NOW(),NOW()),
(@q3_start+14,'An expense account',0,1,'Depreciation Expense is an expense account. Accumulated Depreciation is different — it is the running total of depreciation already recorded.',NOW(),NOW()),
(@q3_start+14,'A liability account',0,2,'Accumulated Depreciation is not a liability. It does not represent money owed to an outside party.',NOW(),NOW()),
(@q3_start+14,'A contra-asset account',1,3,'Accumulated Depreciation is a contra-asset account — it offsets the related asset (e.g., Equipment). It has a normal credit balance and reduces the book value of the asset it is paired with.',NOW(),NOW()),
(@q3_start+14,'An equity account',0,4,'Accumulated Depreciation is not an equity account; it is directly associated with specific long-term assets.',NOW(),NOW());

-- ─────────────────────────────────────────────────────────────────────────────
-- CO4 — Books of Accounts (15 questions, 45 answers)
-- ─────────────────────────────────────────────────────────────────────────────

INSERT INTO `assessment_questions` (assessment_id, question_text, question_type, sequence_order, is_active, created_at, updated_at) VALUES
(@a4_id, 'Which book is known as the "Book of Original Entry"?', 'multiple_choice', 1, 1, NOW(), NOW()),
(@a4_id, 'What is the primary purpose of the General Journal?', 'multiple_choice', 2, 1, NOW(), NOW()),
(@a4_id, 'Which book is used to find the "Running Balance" of an account?', 'multiple_choice', 3, 1, NOW(), NOW()),
(@a4_id, 'Which of the following correctly describes the order of use for accounting books?', 'multiple_choice', 4, 1, NOW(), NOW()),
(@a4_id, 'What is the General Ledger''s nickname?', 'multiple_choice', 5, 1, NOW(), NOW()),
(@a4_id, 'Where are DEBITED accounts written in a journal entry?', 'multiple_choice', 6, 1, NOW(), NOW()),
(@a4_id, 'What is the "Narration" in a journal entry?', 'multiple_choice', 7, 1, NOW(), NOW()),
(@a4_id, 'A "Simple Entry" involves how many accounts?', 'multiple_choice', 8, 1, NOW(), NOW()),
(@a4_id, 'When is the PR (Posting Reference) column in the Journal filled in?', 'multiple_choice', 9, 1, NOW(), NOW()),
(@a4_id, 'What is written on the line immediately AFTER the account titles in a journal entry?', 'multiple_choice', 10, 1, NOW(), NOW()),
(@a4_id, 'Which journal records ONLY credit sales (sales on account)?', 'multiple_choice', 11, 1, NOW(), NOW()),
(@a4_id, 'What does CDJ stand for?', 'multiple_choice', 12, 1, NOW(), NOW()),
(@a4_id, 'Cash sales are recorded in which special journal?', 'multiple_choice', 13, 1, NOW(), NOW()),
(@a4_id, 'Where do you record a CREDIT purchase of supplies (buy now, pay later)?', 'multiple_choice', 14, 1, NOW(), NOW()),
(@a4_id, 'What is the source document for a Sales Journal entry?', 'multiple_choice', 15, 1, NOW(), NOW());

SET @q4_start = LAST_INSERT_ID();

INSERT INTO `assessment_answers` (question_id, answer_text, is_correct, sequence_order, explanation, created_at, updated_at) VALUES
(@q4_start+0,'Ledger',0,1,'The Ledger is the Book of Final Entry — it organizes transactions by account title.',NOW(),NOW()),
(@q4_start+0,'Journal',1,2,'The Journal is the Book of Original Entry — it is the first formal record of every business transaction, written in chronological order with debits and credits.',NOW(),NOW()),
(@q4_start+0,'Worksheet',0,3,'A Worksheet is a working paper used to organize data before preparing financial statements; it is not a book of original entry.',NOW(),NOW()),
(@q4_start+1,'Find running account balances',0,1,'Running balances are found in the Ledger, not the Journal.',NOW(),NOW()),
(@q4_start+1,'Record transactions in chronological order',1,2,'The Journal''s primary purpose is to record every business transaction in chronological (date) order with its corresponding debit and credit entries, providing a complete audit trail.',NOW(),NOW()),
(@q4_start+1,'Prepare tax returns',0,3,'Tax returns are prepared using financial statements, not directly from the Journal.',NOW(),NOW()),
(@q4_start+2,'Journal',0,1,'The Journal records transactions chronologically but does not show running balances per account.',NOW(),NOW()),
(@q4_start+2,'Ledger',1,2,'The Ledger (specifically the Balance-Column form) shows the running balance of each account after every posting, making it easy to determine the current balance at any time.',NOW(),NOW()),
(@q4_start+2,'Invoice',0,3,'An invoice is a source document, not an accounting book.',NOW(),NOW()),
(@q4_start+3,'The Ledger is used BEFORE the Journal',0,1,'Incorrect. You must first record in the Journal before posting to the Ledger.',NOW(),NOW()),
(@q4_start+3,'The Journal is used BEFORE the Ledger',1,2,'The correct order is: Journal first (Step 2), then Ledger (Step 3). You cannot post to the Ledger without a journal entry to reference.',NOW(),NOW()),
(@q4_start+3,'Both are used at the same time',0,3,'They are used sequentially, not simultaneously. Journalizing precedes posting.',NOW(),NOW()),
(@q4_start+4,'Book of First Entry',0,1,'Book of First Entry (or Original Entry) refers to the Journal, not the Ledger.',NOW(),NOW()),
(@q4_start+4,'Book of Final Entry',1,2,'The General Ledger is called the Book of Final Entry because it contains the final, organized balances of all accounts — the balances used to prepare the Trial Balance and Financial Statements.',NOW(),NOW()),
(@q4_start+4,'Diary of Business',0,3,'The Journal is often called the diary of the business because it records events in chronological order.',NOW(),NOW()),
(@q4_start+5,'Indented (set in from the margin)',0,1,'Credited accounts are indented, not debited accounts.',NOW(),NOW()),
(@q4_start+5,'Flush left (at the left margin)',1,2,'In a standard journal entry, debited accounts are written flush left (at the margin). Credited accounts are indented below them to distinguish debits from credits visually.',NOW(),NOW()),
(@q4_start+5,'On the last line of the entry',0,3,'Debited accounts always appear first (flush left), not on the last line.',NOW(),NOW()),
(@q4_start+6,'The account title',0,1,'Account titles are part of the journal entry itself, not the narration.',NOW(),NOW()),
(@q4_start+6,'A brief explanation of the transaction',1,2,'The Narration (also called Explanation) is a brief description written below the debit and credit accounts. It identifies the nature and source of the transaction (e.g., "To record initial cash investment by owner").',NOW(),NOW()),
(@q4_start+6,'The page number of the journal',0,3,'Page numbers appear in the journal header, not as the narration.',NOW(),NOW()),
(@q4_start+7,'Two',1,1,'A Simple Entry involves exactly two accounts — one debit and one credit. When more than two accounts are needed, it is called a Compound Entry.',NOW(),NOW()),
(@q4_start+7,'Three',0,2,'Three accounts indicate a Compound Entry, not a Simple Entry.',NOW(),NOW()),
(@q4_start+7,'Many',0,3,'Many accounts describes a complex Compound Entry, not a Simple Entry.',NOW(),NOW()),
(@q4_start+8,'When journalizing (while writing the entry)',0,1,'The PR column is intentionally left blank during journalizing. A blank PR means "not yet posted" — an important signal.',NOW(),NOW()),
(@q4_start+8,'After posting (once the entry is transferred to the Ledger)',1,2,'The PR column in the Journal is filled in AFTER the entry has been posted to the Ledger. The account number is written as confirmation that posting was completed. A blank PR warns the accountant that posting is still pending.',NOW(),NOW()),
(@q4_start+8,'Never',0,3,'The PR column must be filled in after posting to maintain the audit trail between the Journal and Ledger.',NOW(),NOW()),
(@q4_start+9,'The debit amount',0,1,'Debit amounts appear in the debit column next to the account title, not on a separate line after.',NOW(),NOW()),
(@q4_start+9,'The explanation (narration)',1,2,'After writing the debited and credited account titles and their amounts, the accountant writes a brief explanation (narration) on the next line. This explains what the transaction represents.',NOW(),NOW()),
(@q4_start+9,'The credit amount',0,3,'Credit amounts appear in the credit column next to the credited account title, not as a separate line after the entry.',NOW(),NOW()),
(@q4_start+10,'CRJ (Cash Receipts Journal)',0,1,'The CRJ records all cash received, including cash sales — not credit sales.',NOW(),NOW()),
(@q4_start+10,'SJ (Sales Journal)',1,2,'The Sales Journal (SJ) records only credit sales — transactions where goods or services are sold on account (customer owes money later). Cash sales are recorded in the CRJ.',NOW(),NOW()),
(@q4_start+10,'GJ (General Journal)',0,3,'The GJ records transactions that do not fit the special journals, such as adjusting and closing entries.',NOW(),NOW()),
(@q4_start+11,'Cash Deposit Journal',0,1,'There is no accounting journal called the Cash Deposit Journal.',NOW(),NOW()),
(@q4_start+11,'Cash Disbursements Journal',1,2,'CDJ stands for Cash Disbursements Journal — it records all cash payments made by the business, such as paying rent, salaries, or settling accounts payable.',NOW(),NOW()),
(@q4_start+11,'Credit Daily Journal',0,3,'There is no accounting journal called the Credit Daily Journal.',NOW(),NOW()),
(@q4_start+12,'SJ (Sales Journal)',0,1,'The Sales Journal records only CREDIT sales (sales on account), not cash sales.',NOW(),NOW()),
(@q4_start+12,'CRJ (Cash Receipts Journal)',1,2,'The Cash Receipts Journal (CRJ) records ALL cash received, including cash sales, collection of accounts receivable, and owner investments.',NOW(),NOW()),
(@q4_start+12,'PJ (Purchases Journal)',0,3,'The Purchases Journal records credit purchases of merchandise/supplies, not cash sales.',NOW(),NOW()),
(@q4_start+13,'CDJ (Cash Disbursements Journal)',0,1,'The CDJ records cash going OUT — since no cash is paid yet in a credit purchase, the CDJ is not used.',NOW(),NOW()),
(@q4_start+13,'PJ (Purchases Journal)',1,2,'The Purchases Journal (PJ) records all purchases made on account (credit) — both merchandise and supplies bought from suppliers with payment due later.',NOW(),NOW()),
(@q4_start+13,'SJ (Sales Journal)',0,3,'The Sales Journal records credit SALES, not credit purchases.',NOW(),NOW()),
(@q4_start+14,'Official Receipt',0,1,'An Official Receipt is issued when cash is received — it is the source document for the Cash Receipts Journal, not the Sales Journal.',NOW(),NOW()),
(@q4_start+14,'Sales Invoice',1,2,'A Sales Invoice is the source document for credit sales. It is given to the customer as evidence of the goods/services sold on account and is used to record the entry in the Sales Journal.',NOW(),NOW()),
(@q4_start+14,'Check',0,3,'A Check is a written order to pay cash — it is the source document for the Cash Disbursements Journal.',NOW(),NOW());

-- ─────────────────────────────────────────────────────────────────────────────
-- CO5 — Business Transactions and Their Analysis (15 questions, 60 answers)
-- ─────────────────────────────────────────────────────────────────────────────

INSERT INTO `assessment_questions` (assessment_id, question_text, question_type, sequence_order, is_active, created_at, updated_at) VALUES
(@a5_id, 'Which statement BEST describes the relationship between Steps 1, 2, and 3 of the accounting cycle?', 'multiple_choice', 1, 1, NOW(), NOW()),
(@a5_id, 'What is the correct description of Step 1, Step 2, and Step 3 of the accounting cycle?', 'multiple_choice', 2, 1, NOW(), NOW()),
(@a5_id, 'Which of the following is a misconception about source documents?', 'multiple_choice', 3, 1, NOW(), NOW()),
(@a5_id, 'What happens if an accountant skips Step 2 (journalizing) and posts transactions directly to the ledger?', 'multiple_choice', 4, 1, NOW(), NOW()),
(@a5_id, 'Which of the following accounts uses a DEBIT to record an INCREASE?', 'multiple_choice', 5, 1, NOW(), NOW()),
(@a5_id, 'A business receives cash from a client for services to be performed in the future. What liability account is created?', 'multiple_choice', 6, 1, NOW(), NOW()),
(@a5_id, 'After posting all journal entries, the total of all debit balances in the ledger should:', 'multiple_choice', 7, 1, NOW(), NOW()),
(@a5_id, 'The P.R. column in both the journal and the ledger serves to:', 'multiple_choice', 8, 1, NOW(), NOW()),
(@a5_id, 'Which of the following is NOT a step covered in CO5 (Steps 1-3)?', 'multiple_choice', 9, 1, NOW(), NOW()),
(@a5_id, 'A business transaction involving a cash payment for an expense affects the accounting equation as follows:', 'multiple_choice', 10, 1, NOW(), NOW()),
(@a5_id, 'A business owner invests ₱40,000 cash to start a new service business. Which effect on the accounting equation is correct?', 'multiple_choice', 11, 1, NOW(), NOW()),
(@a5_id, 'A service business pays ₱5,000 for one month''s office rent in cash. What is the correct journal entry?', 'multiple_choice', 12, 1, NOW(), NOW()),
(@a5_id, 'A business purchases cleaning supplies worth ₱2,000 on account. What is the effect on the accounting equation?', 'multiple_choice', 13, 1, NOW(), NOW()),
(@a5_id, 'A Cash ledger account shows: Beginning Balance ₱0; Debit posting of ₱25,000; Credit posting of ₱8,000. What is the ending balance?', 'multiple_choice', 14, 1, NOW(), NOW()),
(@a5_id, 'A business pays off ₱4,000 of its Accounts Payable in cash. What is the correct journal entry?', 'multiple_choice', 15, 1, NOW(), NOW());

SET @q5_start = LAST_INSERT_ID();

INSERT INTO `assessment_answers` (question_id, answer_text, is_correct, sequence_order, explanation, created_at, updated_at) VALUES
(@q5_start+0,'They are independent and can be done in any order',0,1,'Each step depends on the previous one; you cannot post without first journalizing.',NOW(),NOW()),
(@q5_start+0,'Each step depends on the previous: you must analyze before journalizing, and journalize before posting',1,2,'Steps 1-3 are sequential: analyze the transaction, record it in the journal, then transfer (post) to the ledger. Skipping any step would break the accounting cycle.',NOW(),NOW()),
(@q5_start+0,'Step 3 can replace Steps 1 and 2 if the accounts are known',0,3,'Step 3 (posting) relies entirely on journal entries from Step 2; it cannot replace prior steps.',NOW(),NOW()),
(@q5_start+0,'Only Step 1 and Step 3 are required; Step 2 is optional',0,4,'Step 2 (journalizing) is mandatory; it creates the chronological record used for posting and auditing.',NOW(),NOW()),
(@q5_start+1,'Post → Analyze → Journalize',0,1,'Posting comes after journalizing, not before.',NOW(),NOW()),
(@q5_start+1,'Journalize → Post → Analyze',0,2,'Analysis must come first — you must identify the accounts affected before journalizing.',NOW(),NOW()),
(@q5_start+1,'Analyze → Journalize → Post',1,3,'The correct sequence is: Step 1 Analyze (identify affected accounts and effects), Step 2 Journalize (record the debit and credit entry), Step 3 Post (transfer to the general ledger).',NOW(),NOW()),
(@q5_start+1,'Analyze → Post → Journalize',0,4,'Posting to the ledger requires a journal entry; you cannot post before journalizing.',NOW(),NOW()),
(@q5_start+2,'An official receipt is a valid source document',0,1,'An official receipt is indeed a valid source document — it evidences a cash transaction.',NOW(),NOW()),
(@q5_start+2,'Any informal note can serve as a source document',1,2,'This is a misconception. Source documents must be formal, verifiable evidence (official receipts, sales invoices, checks, bank deposit slips). Informal notes lack the objectivity required by the Objectivity Principle.',NOW(),NOW()),
(@q5_start+2,'A check serves as evidence of a cash payment',0,3,'A check is a valid source document — it is a written order to pay a specified amount and evidences a cash disbursement.',NOW(),NOW()),
(@q5_start+2,'A purchase invoice evidences a credit purchase',0,4,'A purchase invoice is indeed the source document for credit purchases from suppliers.',NOW(),NOW()),
(@q5_start+3,'The financial statements will still be accurate',0,1,'Without a journal, there is no way to verify the accuracy or completeness of transactions.',NOW(),NOW()),
(@q5_start+3,'The trial balance will automatically correct the errors',0,2,'The trial balance only checks mathematical equality; it cannot detect missing or misapplied entries.',NOW(),NOW()),
(@q5_start+3,'There will be no chronological record linking transactions to their debits and credits',1,3,'The journal provides the audit trail — a dated, chronological record of every transaction. Without it, there is no way to trace a ledger balance back to its source, making audits and error correction impossible.',NOW(),NOW()),
(@q5_start+3,'Only the Cash account will be affected',0,4,'All accounts would be affected; the entire system''s traceability would be compromised.',NOW(),NOW()),
(@q5_start+4,'Loans Payable',0,1,'Loans Payable is a liability with a normal CREDIT balance. To increase a liability, you credit it.',NOW(),NOW()),
(@q5_start+4,'Unearned Revenue',0,2,'Unearned Revenue is a liability with a normal CREDIT balance. Increases are recorded as credits.',NOW(),NOW()),
(@q5_start+4,'Owner''s Capital',0,3,'Owner''s Capital has a normal CREDIT balance. Investments by the owner are recorded as credits to Capital.',NOW(),NOW()),
(@q5_start+4,'Accounts Receivable',1,4,'Accounts Receivable is an asset. Asset accounts have normal DEBIT balances, so increases are recorded as debits. When a business earns revenue on account, Accounts Receivable is debited.',NOW(),NOW()),
(@q5_start+5,'Accounts Payable',0,1,'Accounts Payable represents money owed to suppliers for goods/services already received.',NOW(),NOW()),
(@q5_start+5,'Notes Payable',0,2,'Notes Payable is a formal written promissory debt to a lender, not for services to be performed.',NOW(),NOW()),
(@q5_start+5,'Unearned Revenue',1,3,'Unearned Revenue (Deferred Revenue) is a liability created when cash is received BEFORE the service is performed. The business owes the client a service. Once the service is rendered, the liability decreases and revenue is recognized.',NOW(),NOW()),
(@q5_start+5,'Rent Payable',0,4,'Rent Payable is a liability for accrued rent owed, not for advance payments from clients.',NOW(),NOW()),
(@q5_start+6,'Exceed the total of all credit balances',0,1,'Debit totals exceeding credits would mean the accounting equation is not balanced.',NOW(),NOW()),
(@q5_start+6,'Be less than the total of all credit balances',0,2,'Credit totals exceeding debits would also mean the equation is unbalanced.',NOW(),NOW()),
(@q5_start+6,'Equal the total of all credit balances',1,3,'After posting, the sum of all debit balances must equal the sum of all credit balances in the ledger. This equality confirms no posting errors occurred and is verified by the Trial Balance (Step 4).',NOW(),NOW()),
(@q5_start+6,'Equal zero',0,4,'Debit balances equal credit balances, not zero. Both totals are typically large positive numbers that happen to be equal.',NOW(),NOW()),
(@q5_start+7,'Show the total balance of every account',0,1,'Account balances are shown in the Balance column of the ledger, not the P.R. column.',NOW(),NOW()),
(@q5_start+7,'Link the journal and ledger to each other for audit and verification purposes',1,2,'The Posting Reference (P.R.) column creates a cross-reference between the Journal and Ledger. The Journal''s P.R. shows the Ledger account number it was posted to; the Ledger''s Ref. shows the Journal page it came from.',NOW(),NOW()),
(@q5_start+7,'Record the name of the bookkeeper',0,3,'The bookkeeper''s name is not recorded in the P.R. column.',NOW(),NOW()),
(@q5_start+7,'Show whether the transaction involved cash',0,4,'The P.R. column shows account numbers or journal references, not whether cash was involved.',NOW(),NOW()),
(@q5_start+8,'Analyzing business transactions',0,1,'Analyzing transactions is Step 1 and IS covered in CO5.',NOW(),NOW()),
(@q5_start+8,'Journalizing business transactions',0,2,'Journalizing is Step 2 and IS covered in CO5.',NOW(),NOW()),
(@q5_start+8,'Preparing the trial balance',1,3,'Preparing the trial balance is Step 4 — it is covered in CO6, not CO5. CO5 covers only Steps 1 (Analyze), 2 (Journalize), and 3 (Post).',NOW(),NOW()),
(@q5_start+8,'Posting to the general ledger',0,4,'Posting is Step 3 and IS covered in CO5.',NOW(),NOW()),
(@q5_start+9,'Assets increase; Owner''s Equity increases',0,1,'Paying cash for an expense DECREASES both assets and equity, not increases them.',NOW(),NOW()),
(@q5_start+9,'Assets decrease; Liabilities increase',0,2,'Paying cash for an expense decreases assets, but no new liability is created when cash is paid.',NOW(),NOW()),
(@q5_start+9,'Assets decrease; Owner''s Equity decreases',1,3,'When cash is paid for an expense: Assets (Cash) decrease and Owner''s Equity decreases (expenses reduce net income, which reduces equity). The equation remains balanced.',NOW(),NOW()),
(@q5_start+9,'Liabilities decrease; Owner''s Equity decreases',0,4,'This describes paying off a liability (Accrued Expense), not a direct cash payment for an expense.',NOW(),NOW()),
(@q5_start+10,'Cash +₱40,000; Loans Payable +₱40,000',0,1,'An owner''s investment is not a loan; it does not create a liability.',NOW(),NOW()),
(@q5_start+10,'Cash +₱40,000; Owner''s Capital +₱40,000',1,2,'An owner''s cash investment increases the asset Cash and simultaneously increases Owner''s Capital (equity). The journal entry is: Debit Cash / Credit Owner''s Capital.',NOW(),NOW()),
(@q5_start+10,'Owner''s Capital +₱40,000; Accounts Payable +₱40,000',0,3,'No liability is created from an owner''s investment.',NOW(),NOW()),
(@q5_start+10,'Cash -₱40,000; Owner''s Equity -₱40,000',0,4,'Cash comes INTO the business (increases), not out.',NOW(),NOW()),
(@q5_start+11,'Debit Cash ₱5,000; Credit Rent Expense ₱5,000',0,1,'Cash is paid OUT (decreases), so it must be CREDITED. Rent Expense increases, so it is DEBITED.',NOW(),NOW()),
(@q5_start+11,'Debit Rent Expense ₱5,000; Credit Accounts Payable ₱5,000',0,2,'Since cash was paid immediately, Accounts Payable should not be used.',NOW(),NOW()),
(@q5_start+11,'Debit Rent Expense ₱5,000; Credit Cash ₱5,000',1,3,'Rent Expense is debited (expense increases = debit). Cash is credited (asset decreases = credit). Total: Assets -₱5,000, Owner''s Equity -₱5,000.',NOW(),NOW()),
(@q5_start+11,'Debit Cash ₱5,000; Credit Owner''s Capital ₱5,000',0,4,'Paying rent is not an owner''s investment; it is an expense.',NOW(),NOW()),
(@q5_start+12,'Assets (Supplies) +₱2,000; Owner''s Equity -₱2,000',0,1,'No expense is recognized yet when supplies are purchased; they become Supplies Expense only when used.',NOW(),NOW()),
(@q5_start+12,'Assets (Supplies) +₱2,000; Liabilities (Accounts Payable) +₱2,000',1,2,'Buying on account: Supplies (asset) increases by ₱2,000 on the left, and Accounts Payable (liability) increases by ₱2,000 on the right. Both sides increase equally.',NOW(),NOW()),
(@q5_start+12,'Assets (Cash) -₱2,000; Liabilities (Accounts Payable) +₱2,000',0,3,'No cash was paid — the purchase was on account, so Cash does not decrease.',NOW(),NOW()),
(@q5_start+12,'No effect because no cash was paid',0,4,'A transaction occurs when goods are received on credit. The obligation to pay exists immediately, creating both an asset and a liability.',NOW(),NOW()),
(@q5_start+13,'₱8,000 Debit',0,1,'₱8,000 is only the credit side; it does not account for the debit posting.',NOW(),NOW()),
(@q5_start+13,'₱25,000 Credit',0,2,'Cash has a normal debit balance; ₱25,000 debit is the inflow, not the balance.',NOW(),NOW()),
(@q5_start+13,'₱17,000 Debit',1,3,'Ending Balance = ₱0 + ₱25,000 (debit) - ₱8,000 (credit) = ₱17,000 Debit. Cash has a normal debit balance.',NOW(),NOW()),
(@q5_start+13,'₱33,000 Debit',0,4,'₱33,000 would be the sum of both sides, not the net balance.',NOW(),NOW()),
(@q5_start+14,'Debit Cash ₱4,000; Credit Accounts Payable ₱4,000',0,1,'Cash is paid OUT (decreases), so it is CREDITED, not debited.',NOW(),NOW()),
(@q5_start+14,'Debit Accounts Payable ₱4,000; Credit Cash ₱4,000',1,2,'Paying off a liability: Accounts Payable (liability) decreases — debit it. Cash (asset) decreases — credit it. Effect: Assets -₱4,000, Liabilities -₱4,000.',NOW(),NOW()),
(@q5_start+14,'Debit Expense ₱4,000; Credit Cash ₱4,000',0,3,'Paying a previously recorded liability is not an expense — the expense was already recorded when the liability was created.',NOW(),NOW()),
(@q5_start+14,'Debit Accounts Payable ₱4,000; Credit Revenue ₱4,000',0,4,'Revenue is earned by performing services, not by paying debts.',NOW(),NOW());

-- ─────────────────────────────────────────────────────────────────────────────
-- CO6 — Preparation of Trial Balance (15 questions, 60 answers)
-- ─────────────────────────────────────────────────────────────────────────────

INSERT INTO `assessment_questions` (assessment_id, question_text, question_type, sequence_order, is_active, created_at, updated_at) VALUES
(@a6_id, 'Which step of the accounting cycle directly follows Step 4 (Trial Balance)?', 'multiple_choice', 1, 1, NOW(), NOW()),
(@a6_id, 'Which statement about the trial balance is TRUE?', 'multiple_choice', 2, 1, NOW(), NOW()),
(@a6_id, 'Where should Accumulated Depreciation — Equipment be placed in the trial balance?', 'multiple_choice', 3, 1, NOW(), NOW()),
(@a6_id, 'Which of the following accounts belongs in the DEBIT column of the trial balance?', 'multiple_choice', 4, 1, NOW(), NOW()),
(@a6_id, 'Which type of adjusting entry is needed for employees who earned wages in the last week of the month but will not be paid until next month?', 'multiple_choice', 5, 1, NOW(), NOW()),
(@a6_id, 'A business completes a service for a client on the last day of the month but will not collect payment until next month. Which adjusting entry is needed?', 'multiple_choice', 6, 1, NOW(), NOW()),
(@a6_id, 'Which correctly describes the relationship between the trial balance and adjusting entries?', 'multiple_choice', 7, 1, NOW(), NOW()),
(@a6_id, 'An account with a credit balance of ₱7,500 is mistakenly placed in the DEBIT column of the trial balance. What is the effect?', 'multiple_choice', 8, 1, NOW(), NOW()),
(@a6_id, 'After posting all adjusting entries to the ledger, what is the NEXT action taken?', 'multiple_choice', 9, 1, NOW(), NOW()),
(@a6_id, 'Which memory aid correctly groups accounts by their normal balance?', 'multiple_choice', 10, 1, NOW(), NOW()),
(@a6_id, 'A business paid ₱24,000 for a one-year lease on July 1. By July 31, one month of the lease has been used. What is the correct adjusting entry?', 'multiple_choice', 11, 1, NOW(), NOW()),
(@a6_id, 'Office equipment costs ₱36,000 with a useful life of 3 years and no salvage value. What is the correct MONTHLY depreciation adjusting entry?', 'multiple_choice', 12, 1, NOW(), NOW()),
(@a6_id, 'A business collected ₱9,000 advance payment for 3 months of service on October 1. By October 31, one month''s service has been performed. What adjusting entry is recorded?', 'multiple_choice', 13, 1, NOW(), NOW()),
(@a6_id, 'Employees earned ₱6,000 in salaries during the last 3 days of the month. Payday falls in the next accounting period. What is the correct adjusting entry?', 'multiple_choice', 14, 1, NOW(), NOW()),
(@a6_id, 'A business performed a consulting service worth ₱4,500 on the last day of the month. No cash has been received and no invoice has been issued yet. Which adjusting entry is correct?', 'multiple_choice', 15, 1, NOW(), NOW());

SET @q6_start = LAST_INSERT_ID();

INSERT INTO `assessment_answers` (question_id, answer_text, is_correct, sequence_order, explanation, created_at, updated_at) VALUES
(@q6_start+0,'Preparing the financial statements',0,1,'Financial statements come in Step 7, not immediately after the trial balance.',NOW(),NOW()),
(@q6_start+0,'Journalizing and posting all transactions',0,2,'That is Step 2 and 3, which come before the trial balance.',NOW(),NOW()),
(@q6_start+0,'Journalizing and posting adjusting entries',1,3,'Step 5 immediately follows the trial balance (Step 4). Adjusting entries are made to update account balances before financial statements are prepared.',NOW(),NOW()),
(@q6_start+0,'Closing temporary accounts',0,4,'Closing entries (Step 8) come after the financial statements, not right after the trial balance.',NOW(),NOW()),
(@q6_start+1,'A balanced trial balance confirms all transactions were recorded to the correct accounts',0,1,'A balanced trial balance only proves mathematical equality. Errors such as posting to the wrong account or omitting a transaction entirely would not be detected.',NOW(),NOW()),
(@q6_start+1,'A balanced trial balance only confirms mathematical equality of total debits and credits',1,2,'The trial balance is a mathematical check only — it verifies that total debits equal total credits. It does NOT guarantee that all transactions were recorded correctly or completely.',NOW(),NOW()),
(@q6_start+1,'The trial balance lists only asset and liability accounts',0,3,'The trial balance lists ALL accounts with balances — assets, liabilities, equity, revenues, and expenses.',NOW(),NOW()),
(@q6_start+1,'The trial balance is prepared after the financial statements',0,4,'The trial balance is prepared BEFORE financial statements as a prerequisite check.',NOW(),NOW()),
(@q6_start+2,'Debit column, because it relates to equipment (an asset)',0,1,'Though it is associated with an asset, Accumulated Depreciation is a contra-asset with a CREDIT normal balance.',NOW(),NOW()),
(@q6_start+2,'Credit column, because it is a contra-asset account',1,2,'Accumulated Depreciation is a contra-asset account with a normal CREDIT balance. It offsets the asset''s cost, and its balance is placed in the credit column of the trial balance.',NOW(),NOW()),
(@q6_start+2,'Debit column, because all depreciation is an expense',0,3,'Depreciation EXPENSE has a debit balance. Accumulated DEPRECIATION (the contra-asset) has a credit balance — they are different accounts.',NOW(),NOW()),
(@q6_start+2,'It is not listed in the trial balance',0,4,'Accumulated Depreciation is a permanent account and must appear in every trial balance once it has a balance.',NOW(),NOW()),
(@q6_start+3,'Salaries Payable',0,1,'Salaries Payable is a liability with a normal CREDIT balance.',NOW(),NOW()),
(@q6_start+3,'Consulting Fees Earned',0,2,'Consulting Fees Earned is a revenue account with a normal CREDIT balance.',NOW(),NOW()),
(@q6_start+3,'Owner''s Capital',0,3,'Owner''s Capital is an equity account with a normal CREDIT balance.',NOW(),NOW()),
(@q6_start+3,'Insurance Expense',1,4,'Insurance Expense is an expense account with a normal DEBIT balance. All expense accounts appear in the debit column of the trial balance. Memory aid: ADE = Assets, Drawings, Expenses → Debit normal balance.',NOW(),NOW()),
(@q6_start+4,'Prepaid Expense',0,1,'Prepaid expense adjustments are for costs paid in advance that have been partially used.',NOW(),NOW()),
(@q6_start+4,'Unearned Revenue',0,2,'Unearned revenue adjustments are for cash collected in advance for services not yet rendered.',NOW(),NOW()),
(@q6_start+4,'Accrued Expense',1,3,'Accrued Expense adjustments are for costs incurred (services received) but not yet paid. Wages earned but unpaid are an accrued expense. The adjusting entry: Debit Salaries Expense / Credit Salaries Payable.',NOW(),NOW()),
(@q6_start+4,'Depreciation',0,4,'Depreciation adjustments allocate the cost of long-term assets over their useful life.',NOW(),NOW()),
(@q6_start+5,'Prepaid Expense',0,1,'Prepaid expenses are costs paid in advance by the business, not services rendered without collection.',NOW(),NOW()),
(@q6_start+5,'Accrued Revenue',1,2,'Accrued Revenue (Accrued Income) is revenue earned during the current period but not yet collected. The adjusting entry: Debit Accounts Receivable / Credit Service Revenue.',NOW(),NOW()),
(@q6_start+5,'Unearned Revenue',0,3,'Unearned Revenue is for cash received BEFORE services are performed — the opposite situation.',NOW(),NOW()),
(@q6_start+5,'Depreciation',0,4,'Depreciation allocates asset cost over time; it is unrelated to collecting service revenue.',NOW(),NOW()),
(@q6_start+6,'The trial balance is prepared after adjusting entries have been posted',0,1,'The UNADJUSTED trial balance is prepared BEFORE adjusting entries. The adjusted trial balance comes after adjusting entries.',NOW(),NOW()),
(@q6_start+6,'Adjusting entries are made because the trial balance detected mathematical errors',0,2,'Adjusting entries are made to apply accrual accounting, not to correct mathematical errors.',NOW(),NOW()),
(@q6_start+6,'The trial balance confirms equality first; adjusting entries then update balances before financial statements are prepared',1,3,'The unadjusted trial balance (Step 4) confirms ledger balance equality. Adjusting entries (Step 5) then update account balances for accruals, deferrals, and depreciation, producing the adjusted trial balance used for financial statements.',NOW(),NOW()),
(@q6_start+6,'Adjusting entries are used to prepare the trial balance from scratch',0,4,'Adjusting entries modify existing balances; they do not create the trial balance from scratch.',NOW(),NOW()),
(@q6_start+7,'No effect — the totals will still balance',0,1,'Placing a credit-balance account in the debit column will distort both totals.',NOW(),NOW()),
(@q6_start+7,'The debit column will be overstated by ₱7,500 and the trial balance will not balance',1,2,'The debit column is overstated by ₱7,500 (wrong column) PLUS the credit column is understated by ₱7,500 (missing from there), creating a total difference of ₱15,000 between the two columns.',NOW(),NOW()),
(@q6_start+7,'The credit column will increase by ₱7,500 automatically',0,3,'Moving the amount to the debit column means it is removed from the credit column — the credit column decreases.',NOW(),NOW()),
(@q6_start+7,'The account will automatically switch to the correct column',0,4,'The trial balance does not self-correct. The accountant must find and fix the error manually.',NOW(),NOW()),
(@q6_start+8,'Re-prepare the unadjusted trial balance',0,1,'There is no need to re-prepare the unadjusted trial balance after adjusting entries.',NOW(),NOW()),
(@q6_start+8,'Compile updated balances into the adjusted trial balance',1,2,'After all adjusting entries are posted, the updated ledger balances are compiled into the Adjusted Trial Balance (Step 6). This confirms debits still equal credits after adjustments.',NOW(),NOW()),
(@q6_start+8,'Prepare the general journal',0,3,'The general journal has already been used for adjusting entries; preparing it again is not the next step.',NOW(),NOW()),
(@q6_start+8,'Close all expense and revenue accounts immediately',0,4,'Closing entries (Step 8) come after financial statements (Step 7), not immediately after adjusting entries.',NOW(),NOW()),
(@q6_start+9,'ACE = Debit; LRD = Credit',0,1,'This grouping is incorrect; Revenue has a credit balance and Drawings has a debit balance.',NOW(),NOW()),
(@q6_start+9,'ALL = Credit; DER = Debit',0,2,'This grouping is incorrect.',NOW(),NOW()),
(@q6_start+9,'ADE = Debit (Assets, Drawings, Expenses); LCR = Credit (Liabilities, Capital, Revenues)',1,3,'ADE = Assets, Drawings, Expenses have DEBIT normal balances. LCR = Liabilities, Capital (Equity), Revenues have CREDIT normal balances. This is the standard mnemonic for remembering normal account balances.',NOW(),NOW()),
(@q6_start+9,'ALCE = Debit; DRE = Credit',0,4,'This grouping is incorrect; Liabilities and Capital/Equity have credit, not debit, normal balances.',NOW(),NOW()),
(@q6_start+10,'Debit Prepaid Rent ₱2,000; Credit Cash ₱2,000',0,1,'No cash changes hands in an adjusting entry. Cash was already paid on July 1.',NOW(),NOW()),
(@q6_start+10,'Debit Rent Expense ₱2,000; Credit Prepaid Rent ₱2,000',1,2,'Monthly amount = ₱24,000 ÷ 12 = ₱2,000. One month of the prepaid lease has been used, so the asset (Prepaid Rent) decreases and the expense (Rent Expense) increases.',NOW(),NOW()),
(@q6_start+10,'Debit Cash ₱2,000; Credit Rent Expense ₱2,000',0,3,'The adjusting entry for a prepaid expense does not involve Cash.',NOW(),NOW()),
(@q6_start+10,'Debit Rent Expense ₱24,000; Credit Prepaid Rent ₱24,000',0,4,'The full ₱24,000 is for 12 months; only 1 month (₱2,000) has been used in July.',NOW(),NOW()),
(@q6_start+11,'Debit Depreciation Expense ₱1,000; Credit Accumulated Depreciation ₱1,000',1,1,'Annual depreciation = ₱36,000 ÷ 3 years = ₱12,000/year. Monthly = ₱12,000 ÷ 12 = ₱1,000. The entry debits Depreciation Expense (increases expense) and credits Accumulated Depreciation (reduces book value of asset).',NOW(),NOW()),
(@q6_start+11,'Debit Equipment ₱1,000; Credit Depreciation Expense ₱1,000',0,2,'Equipment is not debited for depreciation. The asset''s cost remains unchanged; only Accumulated Depreciation (contra-asset) is credited.',NOW(),NOW()),
(@q6_start+11,'Debit Accumulated Depreciation ₱1,000; Credit Depreciation Expense ₱1,000',0,3,'This reverses the correct entry — Depreciation Expense has a debit normal balance and Accumulated Depreciation has a credit normal balance.',NOW(),NOW()),
(@q6_start+11,'Debit Depreciation Expense ₱36,000; Credit Equipment ₱36,000',0,4,'Recording the full cost as an expense in one month would violate the Matching Principle. Also, Equipment should not be directly credited.',NOW(),NOW()),
(@q6_start+12,'Debit Service Revenue ₱3,000; Credit Unearned Revenue ₱3,000',0,1,'Revenue is recognized when earned, not reversed. Unearned Revenue should decrease (debit), not increase (credit).',NOW(),NOW()),
(@q6_start+12,'Debit Cash ₱3,000; Credit Service Revenue ₱3,000',0,2,'No cash changes hands in this adjusting entry — cash was already received on October 1.',NOW(),NOW()),
(@q6_start+12,'Debit Unearned Revenue ₱3,000; Credit Service Revenue ₱3,000',1,3,'1 month earned = ₱9,000 ÷ 3 = ₱3,000. The liability (Unearned Revenue) decreases by ₱3,000 (debit) because the obligation is fulfilled, and Revenue is recognized (credit).',NOW(),NOW()),
(@q6_start+12,'Debit Service Revenue ₱9,000; Credit Unearned Revenue ₱9,000',0,4,'Only 1/3 of the service has been performed. The full ₱9,000 would be recognized only after all 3 months of service are complete.',NOW(),NOW()),
(@q6_start+13,'Debit Cash ₱6,000; Credit Salaries Expense ₱6,000',0,1,'Cash is not paid yet — payday is next period. This entry is incorrect in both accounts and direction.',NOW(),NOW()),
(@q6_start+13,'Debit Salaries Expense ₱6,000; Credit Salaries Payable ₱6,000',1,2,'Salaries Expense is debited to recognize the cost in the current period (when earned by employees). Salaries Payable (liability) is credited because the cash will be paid next period.',NOW(),NOW()),
(@q6_start+13,'Debit Salaries Payable ₱6,000; Credit Cash ₱6,000',0,3,'This would be the entry when cash is actually PAID next period, not the adjusting entry.',NOW(),NOW()),
(@q6_start+13,'Debit Salaries Expense ₱6,000; Credit Cash ₱6,000',0,4,'Cash has not been paid yet — crediting Cash reduces the asset, but no cash left the business.',NOW(),NOW()),
(@q6_start+14,'Debit Cash ₱4,500; Credit Consulting Revenue ₱4,500',0,1,'No cash was received yet. Debiting Cash would overstate assets.',NOW(),NOW()),
(@q6_start+14,'Debit Consulting Revenue ₱4,500; Credit Accounts Receivable ₱4,500',0,2,'This entry is backward. Revenue should be credited (recognized), and Accounts Receivable should be debited.',NOW(),NOW()),
(@q6_start+14,'Debit Accounts Receivable ₱4,500; Credit Consulting Revenue ₱4,500',1,3,'This is an accrued revenue entry. The service was earned but not yet collected. Accounts Receivable (asset) is debited to recognize the right to collect, and Consulting Revenue is credited to recognize the income in the current period per the accrual basis.',NOW(),NOW()),
(@q6_start+14,'No entry needed until cash is received',0,4,'Under the accrual basis, revenue is recognized when EARNED, not when cash is received. An adjusting entry is required.',NOW(),NOW());

-- ─────────────────────────────────────────────────────────────────────────────
-- CO7 — Completion of the Accounting Cycle (15 questions, 60 answers)
-- ─────────────────────────────────────────────────────────────────────────────

INSERT INTO `assessment_questions` (assessment_id, question_text, question_type, sequence_order, is_active, created_at, updated_at) VALUES
(@a7_id, 'Which is the correct sequence of the LAST five steps of the accounting cycle?', 'multiple_choice', 1, 1, NOW(), NOW()),
(@a7_id, 'Financial statements under PFRS are prepared directly from the:', 'multiple_choice', 2, 1, NOW(), NOW()),
(@a7_id, 'Which of the following is a temporary (nominal) account?', 'multiple_choice', 3, 1, NOW(), NOW()),
(@a7_id, 'In Step 3 of the closing entry process, if the business has a net LOSS, the closing entry is:', 'multiple_choice', 4, 1, NOW(), NOW()),
(@a7_id, 'The Owner''s Drawing account is closed:', 'multiple_choice', 5, 1, NOW(), NOW()),
(@a7_id, 'Which account would appear in the POST-CLOSING trial balance?', 'multiple_choice', 6, 1, NOW(), NOW()),
(@a7_id, 'The balances in the post-closing trial balance become the _______ for the new accounting period.', 'multiple_choice', 7, 1, NOW(), NOW()),
(@a7_id, 'Which type of adjusting entry requires a reversing entry?', 'multiple_choice', 8, 1, NOW(), NOW()),
(@a7_id, 'Reversing entries are the exact _______ of certain adjusting entries.', 'multiple_choice', 9, 1, NOW(), NOW()),
(@a7_id, 'The Statement of Financial Position proves which accounting equation?', 'multiple_choice', 10, 1, NOW(), NOW()),
(@a7_id, 'Which financial statement is a reconciliation of beginning and ending capital balances?', 'multiple_choice', 11, 1, NOW(), NOW()),
(@a7_id, 'After all four closing steps, the Income Summary account balance should be:', 'multiple_choice', 12, 1, NOW(), NOW()),
(@a7_id, 'A business has: Beginning Capital ₱200,000; Net Income ₱45,000; Additional Investment ₱10,000; Owner''s Drawings ₱15,000. What is the Ending Capital?', 'multiple_choice', 13, 1, NOW(), NOW()),
(@a7_id, 'Total revenues ₱150,000; Total operating expenses ₱85,000. What is the net income for the period?', 'multiple_choice', 14, 1, NOW(), NOW()),
(@a7_id, 'A year-end adjusting entry was made to record accrued interest payable of ₱1,200. What is the correct REVERSING ENTRY on January 1 of the new period?', 'multiple_choice', 15, 1, NOW(), NOW());

SET @q7_start = LAST_INSERT_ID();

INSERT INTO `assessment_answers` (question_id, answer_text, is_correct, sequence_order, explanation, created_at, updated_at) VALUES
(@q7_start+0,'Closing entries → Adjusted TB → Financial Statements → Post-closing TB → Reversing entries',0,1,'The Adjusted Trial Balance comes before financial statements and closing entries, not after.',NOW(),NOW()),
(@q7_start+0,'Adjusted TB → Financial Statements → Closing entries → Post-closing TB → Reversing entries',1,2,'The correct sequence of Steps 6-10: Step 6 (Adjusted Trial Balance) → Step 7 (Financial Statements) → Step 8 (Closing Entries) → Step 9 (Post-Closing Trial Balance) → Step 10 (Reversing Entries).',NOW(),NOW()),
(@q7_start+0,'Financial Statements → Adjusted TB → Closing entries → Reversing entries → Post-closing TB',0,3,'Financial statements cannot be prepared before the adjusted trial balance.',NOW(),NOW()),
(@q7_start+0,'Adjusted TB → Closing entries → Financial Statements → Post-closing TB → Reversing entries',0,4,'Financial statements are prepared before closing entries, not after.',NOW(),NOW()),
(@q7_start+1,'Original trial balance',0,1,'The original (unadjusted) trial balance does not include adjustments for accruals, deferrals, and depreciation.',NOW(),NOW()),
(@q7_start+1,'Post-closing trial balance',0,2,'The post-closing trial balance is prepared AFTER closing entries — financial statements cannot be prepared from it because temporary accounts have already been zeroed out.',NOW(),NOW()),
(@q7_start+1,'Adjusted trial balance',1,3,'Financial statements are prepared from the ADJUSTED trial balance (Step 6), which contains updated balances after all adjusting entries. The Income Statement uses revenue and expense accounts; the Balance Sheet uses asset, liability, and equity accounts.',NOW(),NOW()),
(@q7_start+1,'General journal',0,4,'The general journal records individual entries in chronological order; it is not a source for preparing financial statements directly.',NOW(),NOW()),
(@q7_start+2,'Accounts Payable',0,1,'Accounts Payable is a Liability — a permanent account that carries its balance forward to the next period.',NOW(),NOW()),
(@q7_start+2,'Equipment',0,2,'Equipment is an Asset — a permanent account.',NOW(),NOW()),
(@q7_start+2,'Owner''s Capital',0,3,'Owner''s Capital is an equity account — a permanent account that carries forward.',NOW(),NOW()),
(@q7_start+2,'Salaries Expense',1,4,'Salaries Expense is a temporary (nominal) account. It accumulates expenses only for the current period and is closed to zero via closing entries at the end of each accounting period.',NOW(),NOW()),
(@q7_start+3,'Debit Income Summary / Credit Capital',0,1,'This entry is used when there is a net INCOME (credit to capital increases equity).',NOW(),NOW()),
(@q7_start+3,'Debit Capital / Credit Income Summary',1,2,'A net loss means total expenses exceed revenues. After Steps 1 and 2, Income Summary has a DEBIT balance (the net loss). To close it: Debit Capital (reducing equity for the loss) / Credit Income Summary (zeroing it out).',NOW(),NOW()),
(@q7_start+3,'Debit Revenue / Credit Capital',0,3,'Revenue accounts are closed in Step 1, not Step 3.',NOW(),NOW()),
(@q7_start+3,'Debit Income Summary / Credit Revenue',0,4,'Revenue accounts were already closed to Income Summary in Step 1.',NOW(),NOW()),
(@q7_start+4,'To Income Summary in Step 2',0,1,'Step 2 closes expense accounts to Income Summary. Drawings is a contra-equity account, not an expense.',NOW(),NOW()),
(@q7_start+4,'Directly to Capital in Step 4',1,2,'Drawings is closed DIRECTLY to Owner''s Capital in Step 4 (not through Income Summary). The entry: Debit Capital / Credit Drawing. This reduces the capital account by the total amount the owner withdrew during the period.',NOW(),NOW()),
(@q7_start+4,'To Revenue accounts in Step 1',0,3,'Step 1 closes revenue accounts TO Income Summary, not the other way around.',NOW(),NOW()),
(@q7_start+4,'To Expenses in Step 3',0,4,'Step 3 closes the Income Summary balance to Capital. Drawings is handled in Step 4.',NOW(),NOW()),
(@q7_start+5,'Utilities Expense',0,1,'Utilities Expense is a temporary account closed to zero; it does NOT appear in the post-closing trial balance.',NOW(),NOW()),
(@q7_start+5,'Owner''s Drawings',0,2,'Owner''s Drawings is a temporary account closed directly to Capital; it does NOT appear in the post-closing trial balance.',NOW(),NOW()),
(@q7_start+5,'Interest Income',0,3,'Interest Income is a revenue (temporary) account closed to zero; it does NOT appear in the post-closing trial balance.',NOW(),NOW()),
(@q7_start+5,'Accounts Receivable',1,4,'Accounts Receivable is a permanent (real) asset account. Only permanent accounts — Assets, Liabilities, and the updated Owner''s Capital — appear in the post-closing trial balance.',NOW(),NOW()),
(@q7_start+6,'Closing balances',0,1,'These balances are closing balances for the CURRENT period, but they become opening balances for the NEXT period.',NOW(),NOW()),
(@q7_start+6,'Adjusted balances',0,2,'Adjusted balances are the balances from the adjusted trial balance (Step 6), not the post-closing trial balance.',NOW(),NOW()),
(@q7_start+6,'Opening balances',1,3,'The balances in the post-closing trial balance are the OPENING balances for the next accounting period. This ensures continuity in the accounting records from one period to the next.',NOW(),NOW()),
(@q7_start+6,'Estimated balances',0,4,'These are actual, final balances — not estimates.',NOW(),NOW()),
(@q7_start+7,'Depreciation expense',0,1,'Depreciation does not require a reversing entry because it does not create a future cash transaction that needs to be split.',NOW(),NOW()),
(@q7_start+7,'Bad Debts Expense',0,2,'Bad Debts Expense adjustments do not require reversing entries.',NOW(),NOW()),
(@q7_start+7,'Accrued Expenses',1,3,'Accrued Expenses (e.g., accrued salaries) require reversing entries because the cash payment will occur in the NEXT period. The reversing entry prevents double-recording of the expense when cash is eventually paid.',NOW(),NOW()),
(@q7_start+7,'Prepayments using the Asset Method',0,4,'Prepayments using the Asset Method (initial debit to the asset account) do NOT require reversing entries. Only the Expense Method requires a reversal.',NOW(),NOW()),
(@q7_start+8,'Copy',0,1,'A copy would record the same debit and credit direction — reversing entries swap the sides.',NOW(),NOW()),
(@q7_start+8,'Summary',0,2,'Reversing entries are not summaries; they are mirror-image transactions.',NOW(),NOW()),
(@q7_start+8,'Opposite',1,3,'Reversing entries are the EXACT OPPOSITE (mirror image) of certain adjusting entries — every debit becomes a credit and every credit becomes a debit, with the same amounts. They are dated the first day of the new accounting period.',NOW(),NOW()),
(@q7_start+8,'Extension',0,4,'Reversing entries do not extend adjusting entries; they cancel their effect on certain accounts.',NOW(),NOW()),
(@q7_start+9,'Revenue - Expenses = Net Income',0,1,'This is the equation proved by the Income Statement, not the Balance Sheet.',NOW(),NOW()),
(@q7_start+9,'Assets = Liabilities + Owner''s Equity',1,2,'The Statement of Financial Position (Balance Sheet) directly proves the accounting equation Assets = Liabilities + Owner''s Equity. The left side shows Assets; the right side shows Liabilities + Owner''s Equity (ending capital from the Statement of Changes in Equity).',NOW(),NOW()),
(@q7_start+9,'Ending Capital = Beginning Capital + Net Income - Drawings',0,3,'This formula is proved by the Statement of Changes in Equity, not the Balance Sheet.',NOW(),NOW()),
(@q7_start+9,'Total Debits = Total Credits',0,4,'This equality is proved by the Trial Balance, not the Balance Sheet.',NOW(),NOW()),
(@q7_start+10,'Income Statement',0,1,'The Income Statement shows revenues and expenses for the period; it does not reconcile capital balances.',NOW(),NOW()),
(@q7_start+10,'Statement of Financial Position',0,2,'The Balance Sheet shows balances at a point in time; it does not show the changes in capital.',NOW(),NOW()),
(@q7_start+10,'Statement of Cash Flows',0,3,'The Statement of Cash Flows reconciles beginning and ending cash balances, not capital.',NOW(),NOW()),
(@q7_start+10,'Statement of Changes in Equity',1,4,'The Statement of Changes in Equity shows how Owner''s Capital changed during the period: Beginning Capital + Net Income + Additional Investment - Drawings = Ending Capital.',NOW(),NOW()),
(@q7_start+11,'Equal to total revenues',0,1,'After Step 1, Income Summary has a credit balance equal to total revenues, but Steps 2 and 3 then zero it out.',NOW(),NOW()),
(@q7_start+11,'Equal to total expenses',0,2,'After Step 2, the debit side equals total expenses, but after Step 3 the account is zeroed.',NOW(),NOW()),
(@q7_start+11,'Zero',1,3,'After all four closing steps, the Income Summary account has a ZERO balance. It is a temporary holding account used only during the closing process. Step 3 closes it to Capital.',NOW(),NOW()),
(@q7_start+11,'Equal to net income',0,4,'After Step 2 (before Step 3), Income Summary shows the net income/loss. But after Step 3 closes it to Capital, its balance is zero.',NOW(),NOW()),
(@q7_start+12,'₱230,000',0,1,'₱230,000 omits the additional investment. Formula: 200k + 45k + 10k - 15k = 240k.',NOW(),NOW()),
(@q7_start+12,'₱240,000',1,2,'Ending Capital = Beginning Capital + Net Income + Additional Investment - Drawings = ₱200,000 + ₱45,000 + ₱10,000 - ₱15,000 = ₱240,000.',NOW(),NOW()),
(@q7_start+12,'₱255,000',0,3,'₱255,000 forgets to deduct drawings. Without deducting: 200k + 45k + 10k = 255k.',NOW(),NOW()),
(@q7_start+12,'₱260,000',0,4,'₱260,000 does not apply the formula correctly.',NOW(),NOW()),
(@q7_start+13,'₱235,000',0,1,'₱235,000 is the sum of revenues and expenses, not the difference.',NOW(),NOW()),
(@q7_start+13,'₱85,000',0,2,'₱85,000 is the total expenses, not the net income.',NOW(),NOW()),
(@q7_start+13,'₱65,000',1,3,'Net Income = Total Revenues - Total Expenses = ₱150,000 - ₱85,000 = ₱65,000.',NOW(),NOW()),
(@q7_start+13,'₱150,000',0,4,'₱150,000 is the total revenues before deducting expenses.',NOW(),NOW()),
(@q7_start+14,'Debit Accrued Interest Payable ₱1,200 / Credit Interest Expense ₱1,200',1,1,'The original adjusting entry was: Debit Interest Expense / Credit Accrued Interest Payable. The reversing entry on January 1 swaps both sides: Debit Accrued Interest Payable / Credit Interest Expense. This cancels the liability and reverses the expense so that when cash is paid, only a simple Debit Interest Expense / Credit Cash is needed.',NOW(),NOW()),
(@q7_start+14,'Debit Interest Expense ₱1,200 / Credit Accrued Interest Payable ₱1,200',0,2,'This is the original adjusting entry, not the reversing entry. The reversing entry is the opposite.',NOW(),NOW()),
(@q7_start+14,'Debit Cash ₱1,200 / Credit Interest Expense ₱1,200',0,3,'This would be the entry when cash is actually paid, not the reversing entry.',NOW(),NOW()),
(@q7_start+14,'No reversing entry is needed for accrued expenses',0,4,'Accrued expenses DO require reversing entries. They simplify bookkeeping when the cash is paid in the new period.',NOW(),NOW());

-- ─────────────────────────────────────────────────────────────────────────────
-- DONE
-- ─────────────────────────────────────────────────────────────────────────────

SET foreign_key_checks = 1;

SELECT
  CONCAT('assessment_questions inserted: ', COUNT(*)) AS status
FROM assessment_questions
UNION ALL
SELECT
  CONCAT('assessment_answers inserted: ', COUNT(*))
FROM assessment_answers;
