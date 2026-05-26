-- ============================================================
-- load-quizzes-complete.sql
-- Adds 5 questions (4 options each) to 20 empty assessments.
-- Run AFTER the seeder and load-quizzes.sql have been applied.
-- ============================================================

-- Update assessment titles for the 20 per-lesson quizzes
UPDATE assessments SET title = 'Lesson Quiz: Accounting vs. Bookkeeping'             WHERE id = 2;
UPDATE assessments SET title = 'Lesson Quiz: Functions of Accounting'                 WHERE id = 3;
UPDATE assessments SET title = 'Lesson Quiz: History of Accounting'                   WHERE id = 4;
UPDATE assessments SET title = 'Lesson Quiz: Fundamental Accounting Concepts'         WHERE id = 5;
UPDATE assessments SET title = 'Lesson Quiz: Basic Accounting Principles'             WHERE id = 6;
UPDATE assessments SET title = 'Lesson Quiz: Concepts & Principles in Practice'       WHERE id = 7;
UPDATE assessments SET title = 'Lesson Quiz: Transaction Analysis (Accounting Eq.)'   WHERE id = 9;
UPDATE assessments SET title = 'Lesson Quiz: Expanded Accounting Equation'            WHERE id = 10;
UPDATE assessments SET title = 'Lesson Quiz: Verifying Equation Balance'              WHERE id = 11;
UPDATE assessments SET title = 'Lesson Quiz: Account Classification'                  WHERE id = 13;
UPDATE assessments SET title = 'Lesson Quiz: Service vs. Trading Business Accounts'   WHERE id = 14;
UPDATE assessments SET title = 'Lesson Quiz: Chart of Accounts'                       WHERE id = 15;
UPDATE assessments SET title = 'Lesson Quiz: Special Journals'                        WHERE id = 17;
UPDATE assessments SET title = 'Lesson Quiz: Ledger and Subsidiary Ledger'            WHERE id = 18;
UPDATE assessments SET title = 'Lesson Quiz: Journalizing Transactions'               WHERE id = 20;
UPDATE assessments SET title = 'Lesson Quiz: Posting to the General Ledger'           WHERE id = 21;
UPDATE assessments SET title = 'Lesson Quiz: Adjusting Entries'                       WHERE id = 23;
UPDATE assessments SET title = 'Lesson Quiz: Adjusted Trial Balance'                  WHERE id = 24;
UPDATE assessments SET title = 'Lesson Quiz: Closing Entries & Post-Closing TB'       WHERE id = 26;
UPDATE assessments SET title = 'Lesson Quiz: Reversing Entries & Complete Cycle'      WHERE id = 27;

-- ─── Assessment 2: Accounting vs. Bookkeeping ────────────────────────────────
INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (2, 'What is the PRIMARY role of a bookkeeper?', 'multiple_choice', 1, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, 'To record business transactions systematically', TRUE, 1, 'Bookkeeping is the clerical/recording phase of accounting — it focuses on systematic, accurate data entry.'),
(@q, 'To prepare and interpret financial statements', FALSE, 2, 'Preparing and interpreting financial statements goes beyond bookkeeping and requires accounting judgment.'),
(@q, 'To audit financial records for errors', FALSE, 3, 'Auditing is a separate professional function performed by an external or internal auditor.'),
(@q, 'To advise management on business strategy', FALSE, 4, 'Strategic advice falls under management accounting or consulting, not bookkeeping.');

INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (2, 'Which task is performed by an ACCOUNTANT but NOT typically by a bookkeeper?', 'multiple_choice', 2, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, 'Analyzing financial ratios and preparing management reports', TRUE, 1, 'Ratio analysis, interpretation, and management reporting require accounting judgment beyond clerical bookkeeping.'),
(@q, 'Posting journal entries to the ledger', FALSE, 2, 'Posting entries to the ledger is a routine recording task within the scope of bookkeeping.'),
(@q, 'Recording daily cash transactions', FALSE, 3, 'Recording cash transactions daily is a core bookkeeping task.'),
(@q, 'Maintaining subsidiary ledger cards', FALSE, 4, 'Subsidiary ledger maintenance is a bookkeeping function involving systematic recording.');

INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (2, 'In the Philippines, which law regulates the practice of public accountancy?', 'multiple_choice', 3, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, 'Republic Act 9298 — Philippine Accountancy Act of 2004', TRUE, 1, 'RA 9298 governs the practice of accountancy in the Philippines, including CPA licensure and regulation by the BOA.'),
(@q, 'Republic Act 7160 — Local Government Code', FALSE, 2, 'RA 7160 governs local government units, not the accounting profession.'),
(@q, 'Republic Act 3591 — PDIC Charter', FALSE, 3, 'RA 3591 created the Philippine Deposit Insurance Corporation, not the accounting profession.'),
(@q, 'Republic Act 6938 — Cooperative Code', FALSE, 4, 'RA 6938 governs cooperatives, not the regulation of accountants.');

INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (2, 'Which statement BEST describes the relationship between bookkeeping and accounting?', 'multiple_choice', 4, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, 'Accounting encompasses bookkeeping and adds analysis, interpretation, and reporting', TRUE, 1, 'Bookkeeping is the recording phase; accounting includes bookkeeping plus the full cycle: classify, summarize, interpret, communicate.'),
(@q, 'Bookkeeping encompasses all of accounting', FALSE, 2, 'This is reversed — bookkeeping is a subset of accounting, not the other way around.'),
(@q, 'They are completely separate, unrelated fields', FALSE, 3, 'Bookkeeping is an integral part of accounting; they are closely related.'),
(@q, 'Both require the same level of professional judgment and education', FALSE, 4, 'Accounting requires substantially more judgment, education, and professional licensure than bookkeeping.');

INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (2, 'The output of accounting that goes BEYOND what bookkeeping provides is:', 'multiple_choice', 5, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, 'Financial statements with analysis and interpretation for decision-makers', TRUE, 1, 'Accounting produces financial statements, ratio analysis, and reports that enable informed economic decisions — far beyond recordkeeping.'),
(@q, 'Individual transaction records in chronological order', FALSE, 2, 'Chronological transaction records are the output of bookkeeping (the journal).'),
(@q, 'Daily sales totals and inventory counts', FALSE, 3, 'Daily operational summaries are routine bookkeeping outputs, not the broader output of accounting.'),
(@q, 'Payroll computations and SSS contribution schedules', FALSE, 4, 'Payroll is an administrative function that applies to both bookkeeping and basic accounting tasks.');

-- ─── Assessment 3: Functions of Accounting ───────────────────────────────────
INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (3, 'Which function of accounting ensures managers are accountable to owners for the resources entrusted to them?', 'multiple_choice', 1, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, 'Stewardship / Accountability Function', TRUE, 1, 'The stewardship function holds managers responsible for how they use owner-entrusted resources; financial reports evaluate this stewardship.'),
(@q, 'Control Function', FALSE, 2, 'The control function uses budgets and internal audits to prevent fraud and enforce policies — it is different from stewardship.'),
(@q, 'Planning Function', FALSE, 3, 'The planning function involves preparing budgets and forecasts for future decisions.'),
(@q, 'Reporting Function', FALSE, 4, 'The reporting function presents financial data in formal statements; it is an output of accounting, not the oversight mechanism.');

INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (3, 'The BIR uses accounting information primarily for:', 'multiple_choice', 2, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, 'Assessing taxable income and legal tax compliance', TRUE, 1, 'The BIR relies on financial statements and accounting records to compute and collect the correct amount of taxes.'),
(@q, 'Evaluating employee productivity and performance', FALSE, 2, 'Employee evaluation is an internal management function, not the BIR''s role.'),
(@q, 'Determining return on investment for shareholders', FALSE, 3, 'ROI analysis is used by investors, not by the BIR for tax purposes.'),
(@q, 'Approving or denying business loans', FALSE, 4, 'Loan decisions are made by creditors and banks, not by the BIR.');

INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (3, 'Which function of accounting involves preparing budgets and financial forecasts before major decisions?', 'multiple_choice', 3, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, 'Planning Function', TRUE, 1, 'The planning function uses accounting data to prepare budgets, financial projections, and cost analyses for future business decisions.'),
(@q, 'Communication Function', FALSE, 2, 'The communication function translates economic activity into standardized financial statements for stakeholders.'),
(@q, 'Reporting Function', FALSE, 3, 'The reporting function produces formal financial statements, not forward-looking budgets.'),
(@q, 'Control Function', FALSE, 4, 'Control uses existing accounting data to detect deviations; it is retrospective, not forward-looking.');

INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (3, 'A bank reviews a company''s balance sheet before approving a loan. This bank is acting as a:', 'multiple_choice', 4, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, 'Creditor / Lender — evaluating solvency and liquidity', TRUE, 1, 'Banks are creditors; they examine financial statements to assess whether the borrower can repay the loan.'),
(@q, 'Investor — evaluating return on equity', FALSE, 2, 'Investors evaluate profitability and earnings growth, not just solvency for loan repayment.'),
(@q, 'Government regulator — assessing tax compliance', FALSE, 3, 'Government regulators (BIR, SEC) assess compliance; banks assess creditworthiness.'),
(@q, 'Employee — assessing job security', FALSE, 4, 'Employees are internal stakeholders; banks are external creditors.');

INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (3, 'Which function translates complex economic activity into a standardized language that stakeholders worldwide can understand?', 'multiple_choice', 5, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, 'Communication Function', TRUE, 1, 'Financial statements serve as the universal language of business — the communication function makes accounting information accessible to all stakeholders.'),
(@q, 'Stewardship Function', FALSE, 2, 'Stewardship is about accountability of managers, not the format of information for external parties.'),
(@q, 'Planning Function', FALSE, 3, 'Planning is forward-looking; communication is about presenting existing financial data clearly.'),
(@q, 'Legal Compliance Function', FALSE, 4, 'Legal compliance ensures adherence to regulations; it does not focus on making data universally understandable.');

-- ─── Assessment 4: History of Accounting ─────────────────────────────────────
INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (4, 'Who is known as the "Father of Modern Accounting" and described double-entry bookkeeping in 1494?', 'multiple_choice', 1, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, 'Luca Pacioli', TRUE, 1, 'Italian friar and mathematician Luca Pacioli published Summa de Arithmetica (1494), documenting the double-entry system used by Venetian merchants.'),
(@q, 'Adam Smith', FALSE, 2, 'Adam Smith was an economist known for The Wealth of Nations, not for accounting methodology.'),
(@q, 'John Maynard Keynes', FALSE, 3, 'Keynes was a 20th-century economist, not associated with the origins of accounting.'),
(@q, 'Benjamin Franklin', FALSE, 4, 'Franklin was an inventor and statesman, not a figure in accounting history.');

INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (4, 'The core rule of double-entry bookkeeping is:', 'multiple_choice', 2, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, 'Every transaction has two equal and opposite effects — a debit and a credit', TRUE, 1, 'This is the foundation of double-entry: total debits always equal total credits, keeping the accounting equation balanced.'),
(@q, 'All transactions must be approved by a licensed CPA before recording', FALSE, 2, 'CPA approval is a modern regulatory requirement; the double-entry rule is about recording technique, not authorization.'),
(@q, 'Income must always exceed expenses to keep records valid', FALSE, 3, 'Double-entry recording is valid regardless of whether the business is profitable.'),
(@q, 'Assets must always be greater than liabilities in the books', FALSE, 4, 'Assets = Liabilities + Equity; assets can equal but need not exceed liabilities. Double-entry is a recording rule, not a financial health rule.');

INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (4, 'Which Philippine body issues PFRS and sets local accounting standards aligned with IFRS?', 'multiple_choice', 3, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, 'Financial Reporting Standards Council (FRSC)', TRUE, 1, 'The FRSC adopts and issues PFRS in the Philippines, aligning local standards with the IASB''s IFRS.'),
(@q, 'Board of Accountancy (BOA)', FALSE, 2, 'The BOA regulates the practice of accountancy and administers the CPA board exam; it does not issue accounting standards.'),
(@q, 'Bureau of Internal Revenue (BIR)', FALSE, 3, 'The BIR administers tax laws; it uses but does not issue financial reporting standards.'),
(@q, 'Securities and Exchange Commission (SEC)', FALSE, 4, 'The SEC requires PFRS-compliant financial statements from corporations but does not itself issue the standards.');

INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (4, 'The U.S. stock market crash of 1929 accelerated the development of GAAP because it:', 'multiple_choice', 4, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, 'Exposed weak and inconsistent financial reporting practices', TRUE, 1, 'The crash revealed that misleading financial reports contributed to investor losses, creating urgency for standardized accounting rules.'),
(@q, 'Abolished the use of double-entry bookkeeping', FALSE, 2, 'Double-entry was not abolished; the crash triggered calls for standardization, not a change in recording method.'),
(@q, 'Led to the founding of the IASB in 1929', FALSE, 3, 'The IASB was not founded until 1973, decades after the 1929 crash.'),
(@q, 'Required all countries to adopt a single global accounting standard', FALSE, 4, 'A single global standard (IFRS) came much later in 2001; the crash led to U.S.-specific GAAP development.');

INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (4, 'In what year was the IASB formed to harmonize global accounting standards?', 'multiple_choice', 5, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, '1973', TRUE, 1, 'The IASB (originally IASC) was formed in 1973 to develop international accounting standards, later becoming IFRS.'),
(@q, '1494', FALSE, 2, '1494 is when Pacioli published Summa de Arithmetica, describing double-entry bookkeeping.'),
(@q, '1929', FALSE, 3, '1929 was the year of the U.S. stock market crash, which spurred GAAP development but not the IASB.'),
(@q, '2001', FALSE, 4, '2001 is when the IASB issued IFRS (International Financial Reporting Standards), after being reorganized from the IASC.');

-- ─── Assessment 5: Fundamental Accounting Concepts ───────────────────────────
INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (5, 'Dr. Nana uses her clinic''s cash to pay her personal grocery bills. Which accounting concept is VIOLATED?', 'multiple_choice', 1, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, 'Economic Entity Assumption', TRUE, 1, 'The business and the owner are legally and financially separate. Personal transactions must never be mixed with business transactions.'),
(@q, 'Going Concern Assumption', FALSE, 2, 'Going concern relates to whether the business will continue to operate; it does not address the owner-business separation.'),
(@q, 'Time Period Assumption', FALSE, 3, 'Time period relates to reporting at regular intervals; it has nothing to do with separating personal from business funds.'),
(@q, 'Monetary Concept', FALSE, 4, 'The monetary concept requires that only peso-measurable transactions be recorded; it does not govern personal vs. business separation.');

INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (5, 'A resort records a ₱30,000 advance guest payment as "Unearned Revenue" instead of income. Which concept is applied?', 'multiple_choice', 2, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, 'Accrual Basis', TRUE, 1, 'Under accrual basis, income is recognized only when earned (service rendered). Cash received in advance for a future service has not yet been earned.'),
(@q, 'Going Concern Assumption', FALSE, 2, 'Going concern addresses business continuity, not the timing of revenue recognition.'),
(@q, 'Economic Entity Assumption', FALSE, 3, 'Economic entity separates owner and business; it is not about when revenue is recognized.'),
(@q, 'Monetary Concept', FALSE, 4, 'The monetary concept requires a peso measure to record transactions; it does not govern timing of recognition.');

INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (5, 'An accountant prepares financial statements covering January to December. This applies the:', 'multiple_choice', 3, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, 'Time Period (Periodicity) Assumption', TRUE, 1, 'Financial performance must be measured and reported at regular, defined intervals (monthly, quarterly, annually).'),
(@q, 'Economic Entity Assumption', FALSE, 2, 'Economic entity relates to separating the business from its owner, not to reporting intervals.'),
(@q, 'Going Concern Assumption', FALSE, 3, 'Going concern assumes the business will continue operating; it does not define reporting periods.'),
(@q, 'Matching Principle', FALSE, 4, 'Matching ensures expenses align with related revenues in the same period, but it does not define what that period is.');

INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (5, 'Why does an accountant record equipment at historical cost rather than current market value under normal conditions?', 'multiple_choice', 4, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, 'Going Concern Assumption — the business is expected to keep using the asset, not sell it', TRUE, 1, 'If the business will continue operating, the asset''s liquidation value is irrelevant; recording at cost is justified.'),
(@q, 'Monetary Concept — market value cannot be expressed in pesos', FALSE, 2, 'Market value can be expressed in pesos; the monetary concept is not the reason for using historical cost.'),
(@q, 'Time Period Assumption — market values change every period', FALSE, 3, 'Time period defines reporting intervals, not asset valuation method.'),
(@q, 'Accrual Basis — cost must match the period it was incurred', FALSE, 4, 'Accrual governs revenue/expense timing, not the valuation of long-term assets on the balance sheet.');

INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (5, 'Which of the following CANNOT be recorded under the Monetary Concept?', 'multiple_choice', 5, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, 'An owner''s excellent management skills and community reputation', TRUE, 1, 'Non-financial items — skills, morale, reputation — cannot be objectively measured in pesos and are therefore excluded from the books.'),
(@q, 'Equipment purchased for ₱150,000', FALSE, 2, 'Equipment has an objective peso cost of ₱150,000 and is recorded as an asset.'),
(@q, 'Prepaid insurance of ₱24,000', FALSE, 3, 'Prepaid insurance has a definite peso value and is recorded as a current asset.'),
(@q, 'Accounts receivable of ₱45,000', FALSE, 4, 'Accounts receivable has a measurable peso amount and is recorded as a current asset.');

-- ─── Assessment 6: Basic Accounting Principles ───────────────────────────────
INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (6, 'Land purchased for ₱2M stays on the books at ₱2M even though its market value is ₱5M. Which principle governs this?', 'multiple_choice', 1, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, 'Historical Cost Principle', TRUE, 1, 'Assets are recorded at their original acquisition cost and are not normally adjusted for market value changes.'),
(@q, 'Objectivity Principle', FALSE, 2, 'Objectivity requires evidence-backed entries but does not specifically mandate that assets stay at original cost.'),
(@q, 'Consistency Principle', FALSE, 3, 'Consistency requires using the same method period-to-period; it does not determine how assets are valued.'),
(@q, 'Materiality Principle', FALSE, 4, 'Materiality determines what requires full accounting treatment; it does not govern asset valuation methods.');

INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (6, 'An accountant records a probable lawsuit loss of ₱500,000 but does NOT record a probable insurance recovery of ₱200,000. Which principle applies?', 'multiple_choice', 2, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, 'Conservatism (Prudence) Principle', TRUE, 1, 'Conservatism requires anticipating probable losses but not recording probable gains until actually realized.'),
(@q, 'Consistency Principle', FALSE, 2, 'Consistency requires using the same accounting methods across periods; it is not about gain/loss asymmetry.'),
(@q, 'Adequate Disclosure Principle', FALSE, 3, 'Disclosure requires revealing all material facts; conservatism governs the asymmetric treatment of losses vs. gains.'),
(@q, 'Matching Principle', FALSE, 4, 'Matching aligns expenses with related revenues; it does not govern the treatment of contingent gains and losses.');

INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (6, 'A ₱50 box of staples is expensed immediately rather than being capitalized. Which principle applies?', 'multiple_choice', 3, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, 'Materiality Principle', TRUE, 1, 'The amount is too trivial to affect any user''s decision; the most practical treatment (immediate expense) is acceptable.'),
(@q, 'Historical Cost Principle', FALSE, 2, 'Historical cost determines how to value assets; it does not justify expensing trivial items immediately.'),
(@q, 'Matching Principle', FALSE, 3, 'Matching would suggest capitalizing items that benefit future periods; materiality overrides matching for trivial amounts.'),
(@q, 'Objectivity Principle', FALSE, 4, 'Objectivity requires evidence for entries; it does not address whether small items are expensed or capitalized.');

INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (6, 'A company switches depreciation methods from straight-line to declining-balance every year without explanation. This violates:', 'multiple_choice', 4, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, 'Consistency Principle', TRUE, 1, 'The same accounting methods must be applied from one period to the next; any change must be disclosed and justified.'),
(@q, 'Objectivity Principle', FALSE, 2, 'Objectivity requires verifiable evidence; it does not restrict changing methods.'),
(@q, 'Conservatism Principle', FALSE, 3, 'Conservatism favors lower values when uncertain; it is not violated by switching depreciation methods.'),
(@q, 'Historical Cost Principle', FALSE, 4, 'Historical cost governs asset valuation at acquisition; it does not dictate which depreciation method to use.');

INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (6, 'Which principle requires that all material facts affecting a user''s decision be disclosed in financial statements or notes?', 'multiple_choice', 5, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, 'Adequate Disclosure Principle', TRUE, 1, 'The full-disclosure principle requires that all information material to financial statement users be included — whether in the statements or in the accompanying notes.'),
(@q, 'Materiality Principle', FALSE, 2, 'Materiality determines the threshold of significance; adequate disclosure is the obligation to reveal what IS material.'),
(@q, 'Matching Principle', FALSE, 3, 'Matching aligns revenues with their related expenses; it does not address what must be disclosed to readers.'),
(@q, 'Historical Cost Principle', FALSE, 4, 'Historical cost governs asset valuation; it is not a disclosure requirement.');

-- ─── Assessment 7: Concepts & Principles in Practice ─────────────────────────
INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (7, 'Aling Rosa uses her personal ₱50,000 savings to start a bakery. The ₱50,000 is recorded as Owner''s Capital in the bakery''s books only. Which concept applies?', 'multiple_choice', 1, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, 'Economic Entity Assumption', TRUE, 1, 'The business (bakery) and the owner (Rosa) are treated as separate entities. Rosa''s personal finances stay out of the bakery''s books.'),
(@q, 'Going Concern Assumption', FALSE, 2, 'Going concern means the bakery is expected to continue; it does not govern which transactions belong in the bakery''s books.'),
(@q, 'Monetary Concept', FALSE, 3, 'The monetary concept requires a peso measure; it does not determine whether a transaction belongs to the business or the owner.'),
(@q, 'Time Period Assumption', FALSE, 4, 'Time period defines reporting intervals; it does not address owner-business separation.');

INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (7, 'Rosa''s commercial oven (purchased for ₱35,000) stays on the books at ₱35,000 even though its current market value is ₱20,000. What principle applies?', 'multiple_choice', 2, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, 'Historical Cost Principle', TRUE, 1, 'Assets are recorded and maintained at their original acquisition cost; market fluctuations do not alter book value (except under specific PFRS revaluation rules).'),
(@q, 'Consistency Principle', FALSE, 2, 'Consistency requires using the same method period-to-period; it does not mandate the use of historical cost over market value.'),
(@q, 'Matching Principle', FALSE, 3, 'Matching allocates costs to the periods they benefit; it is separate from the initial valuation rule for assets.'),
(@q, 'Materiality Principle', FALSE, 4, 'Materiality determines significance thresholds; it is not related to asset valuation at historical cost vs. market value.');

INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (7, 'A client pays ₱10,000 in advance for a birthday cake to be delivered next month. Rosa records this as "Unearned Revenue." What concept/principle applies?', 'multiple_choice', 3, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, 'Accrual Basis — Deferred Income', TRUE, 1, 'Income is recognized only when earned (cake delivered). Cash received before the service is performed is a liability (unearned revenue) until the obligation is met.'),
(@q, 'Economic Entity Assumption', FALSE, 2, 'Economic entity separates owner and business transactions; it does not govern when revenue is recognized.'),
(@q, 'Historical Cost Principle', FALSE, 3, 'Historical cost relates to asset valuation; it has no bearing on the timing of revenue recognition.'),
(@q, 'Going Concern Assumption', FALSE, 4, 'Going concern justifies long-term asset valuation at cost; it does not determine revenue recognition timing.');

INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (7, 'Which memory aid CORRECTLY summarizes the accounting treatment of "prepaid" and "unearned" items?', 'multiple_choice', 4, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, 'Prepaid = Asset; Unearned = Liability', TRUE, 1, 'Prepaid items represent future benefits the business has paid for (asset). Unearned items represent future obligations the business still owes (liability).'),
(@q, 'Prepaid = Liability; Unearned = Asset', FALSE, 2, 'This is reversed. Prepaid expenses are assets (benefit not yet used), and unearned revenue is a liability (service not yet rendered).'),
(@q, 'Prepaid = Expense; Unearned = Revenue', FALSE, 3, 'These become expenses/revenue only when USED or EARNED. At the time of payment/receipt, they are asset/liability respectively.'),
(@q, 'Prepaid = Revenue; Unearned = Expense', FALSE, 4, 'This is completely reversed. Prepaid is what you paid in advance (asset), and unearned is what you received in advance (liability).');

INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (7, 'Rosa''s excellent reputation in the community CANNOT be recorded as an asset because of:', 'multiple_choice', 5, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, 'The Monetary (Measurement) Concept', TRUE, 1, 'Only items that can be measured objectively in monetary terms (pesos) are recorded. Reputation has no verifiable peso value.'),
(@q, 'The Consistency Principle', FALSE, 2, 'Consistency requires using the same methods period-to-period; it does not determine what qualifies as a recordable asset.'),
(@q, 'The Materiality Principle', FALSE, 3, 'Materiality sets thresholds for what requires full accounting treatment; it does not exclude non-monetary items from the books.'),
(@q, 'The Historical Cost Principle', FALSE, 4, 'Historical cost requires using the original acquisition cost; but reputation was never acquired at a specific price, so historical cost is not the governing rule here.');

-- ─── Assessment 9: Transaction Analysis (Accounting Equation) ────────────────
INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (9, 'An owner invests ₱120,000 cash into her business. What is the effect on the accounting equation?', 'multiple_choice', 1, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, 'Assets increase; Owner''s Equity increases', TRUE, 1, 'Cash (asset) increases and Owner''s Capital (equity) increases by equal amounts. A = L + OE remains balanced.'),
(@q, 'Assets increase; Liabilities increase', FALSE, 2, 'Liabilities increase when you BORROW money. An owner''s investment increases equity, not liabilities.'),
(@q, 'Assets increase; Expenses increase', FALSE, 3, 'Expenses decrease equity; an owner investment increases equity, not expenses.'),
(@q, 'No effect — owner transactions are personal', FALSE, 4, 'Owner investments directly affect the business books; they increase both Cash and Owner''s Capital.');

INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (9, 'A business buys office equipment worth ₱24,000 on account (credit). The effect on the equation is:', 'multiple_choice', 2, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, 'Assets increase; Liabilities increase', TRUE, 1, 'Equipment (asset) increases by ₱24,000; Accounts Payable (liability) also increases by ₱24,000. The equation stays balanced.'),
(@q, 'Assets increase; Owner''s Equity decreases', FALSE, 2, 'Equity decreases when expenses are incurred or drawings are made. A credit purchase increases liabilities, not decreases equity.'),
(@q, 'No change to total assets', FALSE, 3, 'Total assets do increase — the business gained equipment without giving up any existing asset.'),
(@q, 'Assets decrease; Liabilities decrease', FALSE, 4, 'This would describe paying off a liability with cash. Buying on credit increases both assets and liabilities.');

INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (9, 'JPacs buys supplies for ₱1,400 cash. The net effect on TOTAL ASSETS is:', 'multiple_choice', 3, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, 'Zero — this is an asset swap (Supplies ↑, Cash ↓)', TRUE, 1, 'Both sides of the asset category change by equal amounts in opposite directions. Total assets remain unchanged.'),
(@q, 'Total assets increase by ₱1,400', FALSE, 2, 'Total assets only increase when there is a new source of financing (liability or equity). An asset swap has no net effect.'),
(@q, 'Total assets decrease by ₱1,400', FALSE, 3, 'Total assets decrease only when paying down liabilities or recording expenses. An asset swap keeps the total constant.'),
(@q, 'Total assets increase by ₱2,800', FALSE, 4, 'This would be double-counting — both Supplies and Cash are already within total assets; no net increase occurs.');

INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (9, 'If Assets = ₱665,000 and Owner''s Equity = ₱347,000, the Liabilities must equal:', 'multiple_choice', 4, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, '₱318,000', TRUE, 1, 'Liabilities = Assets − Equity = ₱665,000 − ₱347,000 = ₱318,000.'),
(@q, '₱1,012,000', FALSE, 2, 'This incorrectly adds assets and equity. L = A − OE, not A + OE.'),
(@q, '₱347,000', FALSE, 3, 'This mistake sets Liabilities equal to Owner''s Equity. They are different elements of the equation.'),
(@q, '₱665,000', FALSE, 4, 'This equals total assets, not the missing liabilities value.');

INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (9, 'Owner''s Drawings of ₱3,000 affect the accounting equation by:', 'multiple_choice', 5, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, 'Decreasing assets and decreasing owner''s equity', TRUE, 1, 'When the owner withdraws cash, Cash (asset) decreases and Owner''s Equity (Capital) decreases by the same amount.'),
(@q, 'Decreasing assets and decreasing liabilities', FALSE, 2, 'Liabilities only decrease when debts are repaid. Owner withdrawals reduce equity, not liabilities.'),
(@q, 'Decreasing assets and increasing liabilities', FALSE, 3, 'Drawings do not create a liability. The business simply gives an asset to the owner.'),
(@q, 'Having no effect — drawings are personal', FALSE, 4, 'Drawings directly reduce the business''s Cash and the owner''s equity in the business.');

-- ─── Assessment 10: Expanded Accounting Equation ─────────────────────────────
INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (10, 'Which of the following INCREASES Owner''s Equity?', 'multiple_choice', 1, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, 'Revenue earned from operations', TRUE, 1, 'Revenue increases assets (Cash or Receivable) and simultaneously increases Owner''s Equity through net income.'),
(@q, 'Owner''s Drawings', FALSE, 2, 'Drawings reduce Owner''s Equity — the owner is taking resources out of the business.'),
(@q, 'Business expenses incurred', FALSE, 3, 'Expenses reduce Owner''s Equity by decreasing the net income component.'),
(@q, 'Payment of a liability with cash', FALSE, 4, 'Paying a liability reduces both Cash (asset) and the liability — it has no effect on Owner''s Equity.');

INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (10, 'The correct expanded form of Owner''s Equity is:', 'multiple_choice', 2, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, 'Capital − Drawings + Revenue − Expenses', TRUE, 1, 'Capital and Revenue increase equity; Drawings and Expenses decrease equity. This is the standard expanded equity formula.'),
(@q, 'Capital + Drawings + Revenue − Expenses', FALSE, 2, 'Drawings REDUCE equity; adding them is incorrect. The formula should subtract drawings.'),
(@q, 'Capital + Revenue − Expenses + Drawings', FALSE, 3, 'Drawings must be subtracted, not added. Adding drawings overstates Owner''s Equity.'),
(@q, 'Capital − Drawings − Revenue + Expenses', FALSE, 4, 'Revenue increases equity and expenses decrease it; this formula is completely inverted for revenue and expenses.');

INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (10, 'What is the KEY difference between Owner''s Drawings and Business Expenses?', 'multiple_choice', 3, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, 'Drawings represent personal withdrawals; expenses are costs of generating business revenue', TRUE, 1, 'Drawings bypass the income statement and reduce Capital directly. Expenses appear on the income statement and reduce net income, which then reduces Capital.'),
(@q, 'Drawings are paid to employees; expenses are paid to suppliers', FALSE, 2, 'Drawings are owner withdrawals, not employee payments. This description confuses drawings with payroll.'),
(@q, 'There is no difference — both appear on the income statement', FALSE, 3, 'Drawings do NOT appear on the income statement. Only expenses appear there; drawings go to the Statement of Changes in Equity.'),
(@q, 'Drawings reduce liabilities while expenses reduce assets', FALSE, 4, 'Both drawings and expenses ultimately reduce assets (usually Cash). Drawings reduce Capital; expenses reduce net income then Capital.');

INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (10, 'Beginning Capital = ₱200,000; Additional investment = ₱30,000; Revenue = ₱95,000; Expenses = ₱62,000; Drawings = ₱18,000. What is ending Owner''s Equity?', 'multiple_choice', 4, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, '₱245,000', TRUE, 1, 'Ending OE = 200,000 + 30,000 + 95,000 − 62,000 − 18,000 = ₱245,000.'),
(@q, '₱215,000', FALSE, 2, 'This calculation likely forgot to include the additional investment of ₱30,000.'),
(@q, '₱263,000', FALSE, 3, 'This calculation likely forgot to deduct the drawings of ₱18,000.'),
(@q, '₱235,000', FALSE, 4, 'This result comes from incorrectly computing net income (₱95,000 − ₱62,000 = ₱33,000) as ₱35,000 and missing one figure.');

INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (10, 'Revenue earned increases Owner''s Equity because:', 'multiple_choice', 5, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, 'The business has grown richer — assets increased while liabilities remained unchanged', TRUE, 1, 'Revenue brings in assets (Cash or Receivables) without creating a new obligation, so net assets and equity both increase.'),
(@q, 'Revenue creates a new liability that must be repaid', FALSE, 2, 'Revenue is earned — it does not create an obligation. Only unearned revenue (advance payment) creates a liability.'),
(@q, 'Revenue reduces expenses, which increases equity', FALSE, 3, 'Revenue and expenses are separate items on the income statement. Revenue does not cancel expenses; each has its own effect on equity.'),
(@q, 'It represents an additional owner investment in the business', FALSE, 4, 'Additional owner investment (Capital) and revenue are both increases to equity but through different channels. Revenue comes from operations, not from the owner.');

-- ─── Assessment 11: Verifying Equation Balance ───────────────────────────────
INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (11, 'Mang Tomas: Assets = ₱580,000; Liabilities = ₱80,000; Capital = ₱500,000. Is the equation balanced?', 'multiple_choice', 1, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, 'Yes — ₱580,000 = ₱80,000 + ₱500,000 ✓', TRUE, 1, 'L + OE = 80,000 + 500,000 = 580,000 = Assets. The equation balances.'),
(@q, 'No — Assets should equal ₱500,000 if there is no liability', FALSE, 2, 'Assets can and do exceed equity when liabilities exist. ₱80,000 in liabilities is correctly part of the right side.'),
(@q, 'No — there should be no liability since Mang Tomas bought inventory', FALSE, 3, 'Buying inventory on credit creates an Accounts Payable (liability). The ₱80,000 liability is correct.'),
(@q, 'Yes, but only because inventory is classified as a current asset', FALSE, 4, 'The equation balances regardless of asset classification. Current vs. non-current is irrelevant to the equation test.');

INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (11, 'Which situation would cause an IMBALANCE in the accounting equation?', 'multiple_choice', 2, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, 'Recording a transaction on only one side (single entry)', TRUE, 1, 'Double-entry requires that every transaction affect at least two sides. Recording only one side leaves the equation unbalanced.'),
(@q, 'Recording both the debit and credit sides of a transaction', FALSE, 2, 'Recording both sides is correct double-entry; it keeps the equation balanced.'),
(@q, 'Buying supplies for cash (asset swap)', FALSE, 3, 'An asset swap affects two asset accounts equally in opposite directions. Total assets and equity remain unchanged.'),
(@q, 'Collecting an outstanding account receivable', FALSE, 4, 'Collecting A/R swaps one asset (Receivable) for another (Cash). Total assets and equity are unaffected.');

INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (11, 'Luna Beauty Salon: Cash ₱85,000; Equipment ₱120,000; Accounts Receivable ₱25,000; Accounts Payable ₱40,000; Notes Payable ₱60,000. What is Owner''s Equity?', 'multiple_choice', 3, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, '₱130,000', TRUE, 1, 'Total Assets = 85,000 + 120,000 + 25,000 = ₱230,000. Total Liabilities = 40,000 + 60,000 = ₱100,000. OE = 230,000 − 100,000 = ₱130,000.'),
(@q, '₱100,000', FALSE, 2, 'This is the total liabilities figure, not Owner''s Equity.'),
(@q, '₱150,000', FALSE, 3, 'This calculation likely missed one asset or liability in the computation.'),
(@q, '₱230,000', FALSE, 4, 'This equals total assets. OE must deduct liabilities: 230,000 − 100,000 = 130,000.');

INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (11, 'The Statement of Financial Position (Balance Sheet) is essentially:', 'multiple_choice', 4, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, 'The accounting equation in formal, formatted presentation (Assets = Liabilities + Equity)', TRUE, 1, 'The balance sheet presents the accounting equation at a specific date, organizing assets on one side and claims (liabilities + equity) on the other.'),
(@q, 'A report of cash inflows and outflows for the year', FALSE, 2, 'Cash flows are shown on the Statement of Cash Flows, not the balance sheet.'),
(@q, 'A list of all revenues and expenses for the period', FALSE, 3, 'Revenues and expenses appear on the Income Statement (Statement of Comprehensive Income).'),
(@q, 'A chronological record of all business transactions', FALSE, 4, 'Chronological transaction records are found in the General Journal, not in a financial statement.');

INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (11, 'After recording all transactions, if Total Debits ≠ Total Credits in the trial balance, this indicates:', 'multiple_choice', 5, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, 'A recording or posting error exists in the accounts', TRUE, 1, 'An unbalanced trial balance means double-entry was violated somewhere. The error must be traced and corrected before financial statements can be prepared.'),
(@q, 'The business is operating at a net loss', FALSE, 2, 'A net loss simply means expenses exceed revenue; it still results in a balanced trial balance.'),
(@q, 'Closing entries have not yet been journalized', FALSE, 3, 'The trial balance is prepared BEFORE closing entries; its balance does not depend on closing.'),
(@q, 'Adjusting entries need to be reversed', FALSE, 4, 'Reversing entries are optional and made in the next period; they are not related to an unbalanced trial balance.');

-- ─── Assessment 13: Account Classification ───────────────────────────────────
INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (13, 'Unearned Revenue is classified as:', 'multiple_choice', 1, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, 'A Liability', TRUE, 1, 'Unearned Revenue is cash received before the service is rendered. The business still owes the service to the customer — it is a present obligation (liability).'),
(@q, 'Income / Revenue', FALSE, 2, 'Unearned revenue becomes income only AFTER the service is performed. Until then, it is a liability.'),
(@q, 'An Asset', FALSE, 3, 'Assets are resources owned by the business. Unearned revenue is something the business owes, not owns.'),
(@q, 'An Expense', FALSE, 4, 'Expenses reduce equity through costs incurred. Unearned revenue is not a cost — it is an obligation to deliver service.');

INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (13, 'Accumulated Depreciation is classified as:', 'multiple_choice', 2, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, 'A Contra-Asset (deducted from the related fixed asset on the balance sheet)', TRUE, 1, 'Accumulated Depreciation offsets the gross value of a fixed asset, showing net book value on the balance sheet.'),
(@q, 'An Expense account on the income statement', FALSE, 2, 'Depreciation EXPENSE is on the income statement, but Accumulated Depreciation is the cumulative total — a contra-asset on the balance sheet.'),
(@q, 'A Liability because it represents money owed to the asset', FALSE, 3, 'Accumulated Depreciation is not an obligation to an external party. It is a valuation adjustment against a fixed asset.'),
(@q, 'Owner''s Equity because it reduces the owner''s stake', FALSE, 4, 'While depreciation expense indirectly reduces equity via net income, Accumulated Depreciation itself is a contra-asset, not an equity account.');

INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (13, 'Accounts Receivable is classified as:', 'multiple_choice', 3, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, 'A Current Asset', TRUE, 1, 'Accounts Receivable represents money owed TO the business by customers — a resource expected to be collected within one year.'),
(@q, 'Income / Revenue', FALSE, 2, 'A/R is not income itself; it is the balance-sheet asset created when revenue is earned but not yet collected.'),
(@q, 'A Current Liability', FALSE, 3, 'Liabilities are amounts the business OWES to others. A/R is what others owe to the business.'),
(@q, 'An Expense', FALSE, 4, 'Expenses are costs incurred to operate. A/R is a receivable (asset), not a cost.');

INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (13, 'Owner''s Drawings reduce which of the following?', 'multiple_choice', 4, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, 'Owner''s Equity (Capital)', TRUE, 1, 'Drawings directly reduce the owner''s stake (Capital) in the business. They bypass the income statement entirely.'),
(@q, 'Liabilities', FALSE, 2, 'Liabilities are reduced only by repaying debts, not by owner withdrawals.'),
(@q, 'Expenses', FALSE, 3, 'Drawings are not business operating costs. They are personal withdrawals that reduce Capital, not the expense accounts.'),
(@q, 'Revenue', FALSE, 4, 'Revenue accounts are not affected by owner drawings. Revenue is increased by earning income from operations.');

INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (13, 'Which financial statement contains ONLY permanent accounts?', 'multiple_choice', 5, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, 'Statement of Financial Position (Balance Sheet)', TRUE, 1, 'The balance sheet shows Assets, Liabilities, and Owner''s Equity — all permanent accounts that carry forward to the next period.'),
(@q, 'Income Statement', FALSE, 2, 'The income statement contains temporary accounts (revenue and expenses) that are zeroed out at period-end via closing entries.'),
(@q, 'Statement of Changes in Equity', FALSE, 3, 'The SCE includes net income (from temporary revenue/expense accounts) and drawings — it bridges temporary and permanent accounts.'),
(@q, 'Trial Balance', FALSE, 4, 'The trial balance lists ALL accounts — both temporary and permanent — before closing entries.');

-- ─── Assessment 14: Service vs. Trading Business Accounts ────────────────────
INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (14, 'Which account is UNIQUE to a trading business and does NOT appear in a service business?', 'multiple_choice', 1, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, 'Merchandise Inventory', TRUE, 1, 'Merchandise Inventory represents physical goods purchased for resale — exclusive to trading businesses. Service businesses do not hold goods for sale.'),
(@q, 'Rent Expense', FALSE, 2, 'Rent Expense appears in both service and trading businesses that lease their premises.'),
(@q, 'Accounts Receivable', FALSE, 3, 'Both service and trading businesses extend credit to customers; A/R appears in both.'),
(@q, 'Cash', FALSE, 4, 'Cash is universal — every business, service or trading, has a Cash account.');

INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (14, 'The net income formula for a TRADING (merchandising) business is:', 'multiple_choice', 2, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, 'Net Sales − Cost of Goods Sold = Gross Profit − Operating Expenses = Net Income', TRUE, 1, 'Trading businesses first deduct COGS from sales to get Gross Profit, then deduct operating expenses to get Net Income.'),
(@q, 'Service Revenue − Operating Expenses = Net Income', FALSE, 2, 'This is the formula for a SERVICE business. Trading businesses have an additional COGS deduction step.'),
(@q, 'Assets − Liabilities = Net Income', FALSE, 3, 'Assets minus liabilities equals Owner''s Equity, not Net Income.'),
(@q, 'Total Revenue − Taxes Payable = Net Income', FALSE, 4, 'This oversimplification ignores Cost of Goods Sold and all operating expenses. Taxes come after, not in place of, expenses.');

INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (14, '"Unearned Revenue" appears in:', 'multiple_choice', 3, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, 'Both service and trading businesses', TRUE, 1, 'Any business that collects cash in advance — a clinic collecting a deposit, a store taking a layaway — can have Unearned Revenue.'),
(@q, 'Service businesses only', FALSE, 2, 'Trading businesses also receive advance payments (layaway, reservations). Unearned Revenue is not limited to service firms.'),
(@q, 'Trading businesses only', FALSE, 3, 'Service businesses frequently collect retainers and advance payments, creating Unearned Revenue.'),
(@q, 'Neither — it belongs only in notes to financial statements', FALSE, 4, 'Unearned Revenue is a recognized liability on the balance sheet, not just a footnote.');

INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (14, 'In a dental clinic (service business), the dentist''s chair is classified as:', 'multiple_choice', 4, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, 'Non-Current Asset (Equipment)', TRUE, 1, 'The dental chair is a long-lived tangible asset used in operations — properly classified as equipment (non-current asset).'),
(@q, 'Merchandise Inventory', FALSE, 2, 'Inventory consists of goods purchased for SALE. A dental chair is used in operations, not sold to patients.'),
(@q, 'Accounts Payable', FALSE, 3, 'Accounts Payable is a liability for amounts owed to suppliers. The chair is an asset the clinic owns.'),
(@q, 'Prepaid Expense', FALSE, 4, 'Prepaid expenses are advance payments for future benefits (like prepaid rent). The chair is a tangible asset, not a prepayment.');

INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (14, 'For a hardware store (trading business), which account represents the direct cost of merchandise sold to customers?', 'multiple_choice', 5, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, 'Cost of Goods Sold (COGS)', TRUE, 1, 'COGS is the purchase cost of the merchandise actually sold during the period — the primary expense of a trading business.'),
(@q, 'Purchases', FALSE, 2, 'Purchases records merchandise BOUGHT during the period; COGS tracks what was actually SOLD. Not all purchased goods are sold in the same period.'),
(@q, 'Salaries Expense', FALSE, 3, 'Salaries Expense is the cost of labor, not the cost of merchandise sold.'),
(@q, 'Sales Revenue', FALSE, 4, 'Sales Revenue is the INCOME from selling goods. COGS is the COST side, not the revenue side.');

-- ─── Assessment 15: Chart of Accounts ────────────────────────────────────────
INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (15, 'In the standard Philippine COA numbering system, which series represents LIABILITIES?', 'multiple_choice', 1, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, '200–299', TRUE, 1, 'The 200-series is assigned to all liability accounts in the Philippine sole proprietorship Chart of Accounts convention.'),
(@q, '100–199', FALSE, 2, 'The 100-series is reserved for Asset accounts (Cash, Receivables, Equipment, etc.).'),
(@q, '300–399', FALSE, 3, 'The 300-series is for Owner''s Equity / Capital accounts (Capital, Drawings).'),
(@q, '400–499', FALSE, 4, 'The 400-series is for Income / Revenue accounts (Service Fees, Sales Revenue).');

INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (15, 'Accumulated Depreciation — Equipment should appear in the Chart of Accounts in the:', 'multiple_choice', 2, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, '100–199 series (Assets), as a contra-asset account', TRUE, 1, 'Accumulated Depreciation is a contra-asset — it is presented in the asset section but with a credit balance, reducing the related fixed asset''s carrying value.'),
(@q, '500–599 series (Expenses)', FALSE, 2, 'Depreciation EXPENSE (the period cost) is in the 500s. Accumulated Depreciation (the cumulative total) stays in the asset section.'),
(@q, '200–299 series (Liabilities)', FALSE, 3, 'Accumulated Depreciation is not a liability; it is a valuation adjustment to a fixed asset.'),
(@q, '300–399 series (Owner''s Equity)', FALSE, 4, 'Owner''s Equity accounts include Capital and Drawings. Accumulated Depreciation is a balance sheet asset section item.');

INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (15, 'Why should account numbers be assigned WITH GAPS (e.g., 101, 102, 103) rather than filling consecutive numbers completely?', 'multiple_choice', 3, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, 'So new accounts can be inserted later without renumbering the entire chart', TRUE, 1, 'Leaving gaps allows the accountant to add new accounts (e.g., 102.5 or 104) between existing ones without disrupting the whole numbering system.'),
(@q, 'To limit the total number of accounts a business can have', FALSE, 2, 'The chart can have any number of accounts; gaps are for flexibility, not limitation.'),
(@q, 'Because the BIR requires unused account number ranges', FALSE, 3, 'The BIR does not mandate specific gaps in account numbering. This is an internal bookkeeping best practice.'),
(@q, 'To make accounts easier to memorize by reducing the count', FALSE, 4, 'Gaps do not reduce the number of accounts — they ensure future additions can be logically inserted without disruption.');

INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (15, 'In the JP Advertising Services sample COA, which account would appear FIRST?', 'multiple_choice', 4, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, 'Cash (101)', TRUE, 1, 'Accounts are listed in order from the lowest account number to the highest. Cash (101) is the first account in the 100-series assets.'),
(@q, 'Salary Expense (501)', FALSE, 2, 'Salary Expense is in the 500-series (expenses), which follows assets, liabilities, equity, and income in order.'),
(@q, 'JP, Capital (301)', FALSE, 3, 'Capital accounts (300-series) come after Assets (100) and Liabilities (200) in the standard ordering.'),
(@q, 'Advertising Income (401)', FALSE, 4, 'Income accounts (400-series) appear after assets, liabilities, and equity in the COA ordering.');

INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (15, 'A trading business adds a new account "Freight-In." Based on the Philippine COA convention, this should be numbered in the:', 'multiple_choice', 5, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, '500–599 range (Expenses)', TRUE, 1, 'Freight-In is the cost of transporting purchased merchandise to the business — an operating expense in the 500-series.'),
(@q, '100–199 range (Assets)', FALSE, 2, 'Asset accounts represent resources owned by the business. Freight-In is a cost incurred, not a resource.'),
(@q, '200–299 range (Liabilities)', FALSE, 3, 'Liabilities are amounts owed to external parties. Freight-In is not an obligation; it is an expense.'),
(@q, '400–499 range (Income)', FALSE, 4, 'Income accounts record revenues earned. Freight-In is a cost of acquiring inventory, not a source of income.');

-- ─── Assessment 17: Special Journals ─────────────────────────────────────────
INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (17, 'Which special journal is used for goods sold to customers who will pay within 30 days?', 'multiple_choice', 1, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, 'Sales Journal (SJ)', TRUE, 1, 'The Sales Journal records ALL credit sales. The entry is always: Debit A/R, Credit Sales Revenue.'),
(@q, 'Cash Receipts Journal (CRJ)', FALSE, 2, 'The CRJ records cash RECEIVED. A credit sale has not yet been collected — cash will come later.'),
(@q, 'General Journal (GJ)', FALSE, 3, 'The GJ handles non-routine and adjusting entries; high-volume credit sales belong in the Sales Journal.'),
(@q, 'Cash Disbursements Journal (CDJ)', FALSE, 4, 'The CDJ records cash paid OUT. A credit sale involves no immediate cash outflow.');

INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (17, 'A business pays its electricity bill by check. In which journal is this recorded?', 'multiple_choice', 2, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, 'Cash Disbursements Journal (CDJ)', TRUE, 1, 'The CDJ records ALL cash paid out, including utility payments by check. Entry: Debit Utilities Expense, Credit Cash.'),
(@q, 'Purchases Journal (PJ)', FALSE, 2, 'The Purchases Journal records credit PURCHASES of merchandise. A utility payment is a cash disbursement, not a credit purchase.'),
(@q, 'Sales Journal (SJ)', FALSE, 3, 'The Sales Journal records credit SALES. A utility payment is neither a sale nor a credit transaction.'),
(@q, 'Cash Receipts Journal (CRJ)', FALSE, 4, 'The CRJ records cash RECEIVED. Paying a bill is cash going out, which belongs in the CDJ.');

INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (17, 'The "Sundry" column in the Cash Receipts Journal is used for:', 'multiple_choice', 3, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, 'Irregular items without their own dedicated column, such as capital investments or loan proceeds', TRUE, 1, 'The Sundry column captures non-routine receipts. Each sundry amount is posted individually to the General Ledger rather than in a column total.'),
(@q, 'All sales transactions regardless of payment method', FALSE, 2, 'Sales transactions have their own columns (Cr: Sales). The Sundry column is not for routine sales.'),
(@q, 'Payroll and salary payments', FALSE, 3, 'Payroll payments are disbursements — they go in the Cash Disbursements Journal, not the Cash Receipts Journal.'),
(@q, 'Adjusting and closing entries', FALSE, 4, 'Adjusting and closing entries are always recorded in the General Journal, never in special journals.');

INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (17, 'When posting from Special Journals at month-end, what is posted to the General Ledger?', 'multiple_choice', 4, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, 'Only the column totals for each account', TRUE, 1, 'The efficiency of special journals comes from posting only column totals at month-end, drastically reducing the number of postings to the General Ledger.'),
(@q, 'Each individual line item in the journal', FALSE, 2, 'Individual line posting defeats the purpose of special journals. Only Sundry column items are posted individually.'),
(@q, 'Nothing — special journals automatically update the General Ledger', FALSE, 3, 'Special journals require manual posting at month-end; they are not automatically linked to the General Ledger.'),
(@q, 'Only entries exceeding ₱10,000', FALSE, 4, 'There is no peso threshold for posting. All column totals are posted regardless of amount.');

INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (17, 'The owner invests ₱100,000 additional cash into the business. This is recorded in:', 'multiple_choice', 5, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, 'Cash Receipts Journal (CRJ)', TRUE, 1, 'The CRJ records ALL cash received by the business, including owner investments. The amount goes in the Sundry (Cr) column against Capital.'),
(@q, 'Sales Journal (SJ)', FALSE, 2, 'The Sales Journal is exclusively for credit sales of goods/services. An owner investment is neither a sale nor on credit.'),
(@q, 'Purchases Journal (PJ)', FALSE, 3, 'The Purchases Journal records credit purchases of merchandise. An owner investment is not a purchase.'),
(@q, 'General Journal (GJ)', FALSE, 4, 'Cash transactions belong in the cash journals. Only non-cash, non-routine transactions (like adjustments) go in the GJ.');

-- ─── Assessment 18: Ledger and Subsidiary Ledger ─────────────────────────────
INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (18, 'What is the purpose of the PR (Posting Reference) column in the General Journal?', 'multiple_choice', 1, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, 'To create a cross-reference between the journal entry and its corresponding ledger account', TRUE, 1, 'When posting is complete, the ledger account number is written in the journal''s PR column — linking each journal entry to the ledger and confirming posting is done.'),
(@q, 'To record the peso amount of the transaction', FALSE, 2, 'Peso amounts are in the Debit and Credit columns, not the PR column.'),
(@q, 'To record the payroll reference number for salary transactions', FALSE, 3, 'The PR column is a posting reference, not a payroll reference. It is used for ALL types of journal entries.'),
(@q, 'To indicate that the transaction has been approved by management', FALSE, 4, 'Management approval is documented on source documents (vouchers), not in the accounting records'' PR column.');

INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (18, 'The Accounts Receivable Subsidiary Ledger must always reconcile to:', 'multiple_choice', 2, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, 'The Accounts Receivable Control Account in the General Ledger', TRUE, 1, 'The A/R control account''s balance must always equal the sum of all individual customer balances in the subsidiary ledger. Any difference signals a posting error.'),
(@q, 'The Accounts Payable Control Account in the General Ledger', FALSE, 2, 'A/P Subsidiary Ledger reconciles to the A/P control account. A/R subsidiary reconciles to A/R control — different accounts.'),
(@q, 'The Cash account in the General Ledger', FALSE, 3, 'Cash and A/R are separate accounts. A/R collections affect both, but the A/R subsidiary reconciles only to the A/R control account.'),
(@q, 'The Revenue account in the General Ledger', FALSE, 4, 'Revenue is recognized when services are rendered (creating A/R). The subsidiary ledger tracks individual customer balances, not total revenue.');

INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (18, 'The A/R Subsidiary Ledger totals ₱16,200 but the A/R Control Account shows ₱16,000. This means:', 'multiple_choice', 3, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, 'A ₱200 posting error exists that must be found and corrected before the trial balance', TRUE, 1, 'Control account and subsidiary must always reconcile. A ₱200 difference means one posting went to the wrong account or was entered at the wrong amount.'),
(@q, 'The business has an extra ₱200 profit from a rounding adjustment', FALSE, 2, 'Rounding is not a valid accounting adjustment. The discrepancy is a posting error requiring correction.'),
(@q, 'The difference is due to normal bank charges', FALSE, 3, 'Bank charges affect Cash, not A/R. A discrepancy between A/R subsidiary and control account is always a recording error.'),
(@q, 'One customer paid in advance, creating a ₱200 credit balance', FALSE, 4, 'Advance payments create Unearned Revenue (liability), not a negative A/R balance. This would not explain the subsidiary-control discrepancy.');

INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (18, 'In the balance-column ledger format, the Balance column is updated:', 'multiple_choice', 4, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, 'After each individual entry is posted, providing a running balance', TRUE, 1, 'The balance-column format provides a current account balance after each posting — eliminating the need to scan all entries to find the total.'),
(@q, 'Only at month-end when all entries for the period are posted', FALSE, 2, 'Updating only at month-end defeats the purpose of the balance-column format. The running balance is its key advantage.'),
(@q, 'Only when preparing the Trial Balance', FALSE, 3, 'The Trial Balance uses the current ledger balances; those balances are maintained continuously, not computed at trial balance time.'),
(@q, 'Quarterly, when management reviews the accounts', FALSE, 4, 'Quarterly updates would leave the balance column out-of-date. The running balance is maintained after each posting.');

INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (18, 'Which step confirms that posting from the journal to the ledger is complete?', 'multiple_choice', 5, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, 'Writing the ledger account number in the journal''s PR column', TRUE, 1, 'The PR column in the journal is left blank during journalizing and filled in only after posting is confirmed — it serves as the audit trail cross-reference.'),
(@q, 'Computing the new running balance in the ledger', FALSE, 2, 'Computing the balance is part of posting, but confirmation of completion is marked by filling the PR column in the journal.'),
(@q, 'Signing the journal entry with management approval', FALSE, 3, 'Management signatures are on source documents, not inside accounting journals. The PR cross-reference confirms posting.'),
(@q, 'Filing the original source document in the voucher folder', FALSE, 4, 'Source document filing is a good internal control practice but does not confirm that ledger posting has occurred.');

-- ─── Assessment 20: Journalizing Transactions ────────────────────────────────
INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (20, 'Which memory aid identifies accounts with a DEBIT normal balance?', 'multiple_choice', 1, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, 'ADE — Assets, Drawings, Expenses', TRUE, 1, 'Assets, Drawings, and Expenses all have normal DEBIT balances. To increase them: debit; to decrease them: credit.'),
(@q, 'LCR — Liabilities, Capital, Revenues', FALSE, 2, 'LCR identifies accounts with normal CREDIT balances, not debit balances.'),
(@q, 'ACE — Assets, Capital, Equity', FALSE, 3, 'Capital/Equity has a CREDIT normal balance. Only Assets have a debit normal balance among these three.'),
(@q, 'LDE — Liabilities, Drawings, Expenses', FALSE, 4, 'Liabilities have a CREDIT normal balance. Only Drawings and Expenses from this set have debit normal balances.');

INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (20, 'In a journal entry, credited accounts are written:', 'multiple_choice', 2, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, 'Indented below the debited accounts', TRUE, 1, 'Journal entry format: debited accounts flush left first, credited accounts indented below. This visual separation distinguishes debits from credits.'),
(@q, 'Flush with the left margin, listed before the debited accounts', FALSE, 2, 'Debited accounts are always written first, at the left margin. Credited accounts follow, indented.'),
(@q, 'In a separate column to the right of all debit entries', FALSE, 3, 'There is no separate column for credited accounts by name. They are listed below the debit accounts in the same Account Title column, just indented.'),
(@q, 'At the top of the journal page before any debit entries', FALSE, 4, 'Debits always appear first. Listing credits at the top would violate standard journal format.');

INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (20, 'A business buys a motorcycle for ₱110,000, paying ₱80,000 cash and the balance on account. What type of entry is this?', 'multiple_choice', 3, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, 'Compound entry — three or more accounts involved', TRUE, 1, 'This entry has three accounts: Motorcycle (Dr ₱110,000), Cash (Cr ₱80,000), Accounts Payable (Cr ₱30,000). Three accounts = compound entry.'),
(@q, 'Simple entry — one debit and one credit', FALSE, 2, 'A simple entry has exactly two accounts. This transaction involves three, making it a compound entry.'),
(@q, 'Adjusting entry — made at the end of the period', FALSE, 3, 'Adjusting entries update balances for accruals and deferrals. This is a regular business transaction (acquisition), not an adjustment.'),
(@q, 'Closing entry — transfers balances to Capital', FALSE, 4, 'Closing entries zero out temporary accounts. This is a regular purchase transaction that creates a non-current asset and related obligations.');

INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (20, 'The narration (explanation) in a journal entry serves to:', 'multiple_choice', 4, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, 'Briefly describe the nature and purpose of the transaction', TRUE, 1, 'The narration provides context for the journal entry — who, what, and why — making the records interpretable during audit or review.'),
(@q, 'Record the peso amount of the transaction', FALSE, 2, 'Peso amounts are in the Debit and Credit columns. The narration is a text description, not a numerical record.'),
(@q, 'Cross-reference the ledger posting', FALSE, 3, 'Cross-referencing is done through the PR column, not the narration. The narration explains the transaction in plain language.'),
(@q, 'Identify the employee who processed the transaction', FALSE, 4, 'Employee identification for authorization is done on source documents (vouchers), not in journal narrations.');

INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (20, 'Services were rendered for ₱10,000 in cash. Which journal entry is CORRECT?', 'multiple_choice', 5, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, 'Dr. Cash 10,000 / Cr. Service Revenue 10,000', TRUE, 1, 'Cash (asset) increases: debit. Service Revenue (income) increases: credit. ADE vs. LCR rules applied correctly.'),
(@q, 'Dr. Service Revenue 10,000 / Cr. Cash 10,000', FALSE, 2, 'This is reversed. Revenue increases with a credit, not a debit. Cash increases with a debit, not a credit.'),
(@q, 'Dr. Accounts Receivable 10,000 / Cr. Service Revenue 10,000', FALSE, 3, 'This is the entry for services rendered ON CREDIT (not yet collected). The problem states cash was received.'),
(@q, 'Dr. Cash 10,000 / Cr. Capital 10,000', FALSE, 4, 'Credit to Capital is for owner INVESTMENTS. Revenue earned from services is credited to Service Revenue, not Capital.');

-- ─── Assessment 21: Posting to the General Ledger ────────────────────────────
INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (21, 'What does "posting" mean in the accounting cycle?', 'multiple_choice', 1, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, 'Transferring journal entry data to the appropriate General Ledger account', TRUE, 1, 'Posting moves each debit and credit from the journal to the correct ledger account, updating the running balance for that account.'),
(@q, 'Recording transactions in the journal for the first time', FALSE, 2, 'Initial recording is journalizing (Step 2). Posting (Step 3) comes after journalizing.'),
(@q, 'Preparing the trial balance from ledger balances', FALSE, 3, 'The trial balance (Step 4) is prepared after posting. Posting is the transfer step, not the trial balance step.'),
(@q, 'Adjusting account balances at period-end', FALSE, 4, 'Adjusting entries are made in Step 5. Posting (Step 3) handles the regular transactions recorded in Step 2.');

INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (21, 'After posting, what is written in the journal''s PR column to confirm completion?', 'multiple_choice', 2, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, 'The ledger account number', TRUE, 1, 'Writing the account number in the journal''s PR column confirms that the entry has been posted to that specific ledger account — creating an audit trail.'),
(@q, 'The date of the transaction', FALSE, 2, 'The date is already in the Date column of the journal. The PR column is for account number cross-reference, not date repetition.'),
(@q, 'The running balance of the account', FALSE, 3, 'The running balance is computed in the ledger''s Balance column. The journal''s PR column gets the account number only.'),
(@q, 'The journal page number', FALSE, 4, 'The journal page number (e.g., GJ 1) goes in the LEDGER''s PR column to reference where the entry came from. The journal''s PR column gets the ledger account number.');

INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (21, 'Why is the General Journal called the "Book of Original Entry"?', 'multiple_choice', 3, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, 'Transactions are first recorded here before being transferred to the ledger', TRUE, 1, 'The journal is where every transaction is entered first — in chronological order — before it is posted to the corresponding ledger accounts.'),
(@q, 'The journal is permanent; the ledger entries are temporary', FALSE, 2, 'Both the journal and ledger are permanent records. The terms "original" and "final" refer to the sequence in the accounting process, not permanence.'),
(@q, 'The journal records credits only; the ledger records debits', FALSE, 3, 'Both journal and ledger record both debits AND credits for each transaction.'),
(@q, 'The journal is prepared at year-end; the ledger at year-start', FALSE, 4, 'Both books are maintained continuously throughout the year, not at specific times.');

INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (21, 'After posting all July transactions for Pamilya Services (₱60K invested, ₱50K loan, ₱30K equipment bought, ₱10K services earned, ₱10K rent, ₱4K salaries, ₱2K taxes, ₱2.5K utilities, ₱0.5K drawings), the Cash balance is:', 'multiple_choice', 4, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, '₱71,000', TRUE, 1, 'Cash: +60,000 +50,000 −30,000 +10,000 −10,000 −4,000 −2,000 −2,500 −500 = ₱71,000.'),
(@q, '₱90,000', FALSE, 2, 'This figure omits some of the cash payments (rent, salaries, taxes, utilities, drawings).'),
(@q, '₱80,000', FALSE, 3, 'This figure likely includes only the first few transactions and misses later payments.'),
(@q, '₱76,000', FALSE, 4, 'This is close but does not account for all payments correctly — one or more disbursements was omitted or incorrectly computed.');

INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (21, 'After posting all entries for the period, the NEXT step in the accounting cycle is:', 'multiple_choice', 5, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, 'Preparing the Trial Balance (Step 4)', TRUE, 1, 'After all journal entries are posted (Step 3), the ledger balances are compiled into a Trial Balance (Step 4) to verify mathematical equality.'),
(@q, 'Journalizing adjusting entries (Step 5)', FALSE, 2, 'Adjusting entries come after the Trial Balance is prepared, not immediately after posting.'),
(@q, 'Preparing the financial statements (Step 7)', FALSE, 3, 'Financial statements are prepared after adjusting entries and the Adjusted Trial Balance — Step 7, not right after posting.'),
(@q, 'Journalizing closing entries (Step 8)', FALSE, 4, 'Closing entries are the second-to-last step. The next step after posting is the Trial Balance (Step 4).');

-- ─── Assessment 23: Adjusting Entries ────────────────────────────────────────
INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (23, 'The GOLDEN RULE of adjusting entries is:', 'multiple_choice', 1, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, 'Every adjusting entry affects at least one income statement account AND one balance sheet account; Cash is never involved', TRUE, 1, 'Adjusting entries always pair a revenue/expense account (income statement) with an asset/liability account (balance sheet). Cash is never part of an adjusting entry.'),
(@q, 'Every adjusting entry must involve Cash on one side', FALSE, 2, 'This is the opposite of the golden rule. Adjusting entries specifically NEVER involve Cash.'),
(@q, 'Adjusting entries are optional; only required if errors are found', FALSE, 3, 'Adjusting entries are mandatory under accrual accounting to correctly state revenues and expenses for the period.'),
(@q, 'Adjusting entries only affect balance sheet accounts', FALSE, 4, 'Adjusting entries ALWAYS affect both a balance sheet account and an income statement account — not just one type.');

INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (23, 'Supplies on hand = ₱3,200; balance in Supplies account = ₱8,000. The adjusting entry is:', 'multiple_choice', 2, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, 'Dr. Supplies Expense ₱4,800 / Cr. Supplies ₱4,800', TRUE, 1, 'Supplies used = 8,000 − 3,200 = ₱4,800. The expense is debited; the asset (Supplies) is reduced by crediting.'),
(@q, 'Dr. Supplies ₱4,800 / Cr. Supplies Expense ₱4,800', FALSE, 2, 'This entry increases the Supplies asset while reducing expense — the opposite of what should happen when supplies are used up.'),
(@q, 'Dr. Supplies Expense ₱3,200 / Cr. Supplies ₱3,200', FALSE, 3, '₱3,200 is what REMAINS on hand, not what was used. The adjustment should be for the used portion (₱4,800).'),
(@q, 'Dr. Cash ₱4,800 / Cr. Supplies ₱4,800', FALSE, 4, 'Adjusting entries never involve Cash. The used supplies become an expense, not a cash receipt.');

INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (23, 'Employees earned ₱5,000 in unpaid salaries at month-end; payday is next month. What type of adjusting entry is needed?', 'multiple_choice', 3, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, 'Accrued Expense — Dr. Salaries Expense / Cr. Salaries Payable', TRUE, 1, 'An accrued expense is incurred but not yet paid. The expense is recognized this period; the liability is recorded until cash is paid.'),
(@q, 'Prepaid Expense adjustment', FALSE, 2, 'Prepaid expense adjustments are for costs paid in ADVANCE that are now being used. Unpaid salaries are a cost incurred without advance payment.'),
(@q, 'Unearned Revenue adjustment', FALSE, 3, 'Unearned revenue involves cash received before service delivery. Salaries are an expense, not revenue.'),
(@q, 'Depreciation adjustment', FALSE, 4, 'Depreciation allocates the cost of long-term assets. This is a salary accrual for services received from employees, not asset depreciation.');

INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (23, 'Equipment costs ₱60,000, useful life 5 years, no salvage value. Monthly straight-line depreciation equals:', 'multiple_choice', 4, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, '₱1,000 per month', TRUE, 1, 'Annual depreciation = ₱60,000 ÷ 5 = ₱12,000. Monthly = ₱12,000 ÷ 12 = ₱1,000.'),
(@q, '₱500 per month', FALSE, 2, 'This would imply a 10-year useful life (₱60,000 ÷ 10 = ₱6,000/year ÷ 12 = ₱500/month), not 5 years.'),
(@q, '₱12,000 per month', FALSE, 3, '₱12,000 is the ANNUAL depreciation, not monthly. Divide by 12 to get the monthly amount.'),
(@q, '₱5,000 per month', FALSE, 4, 'This overstates monthly depreciation. ₱5,000/month × 12 = ₱60,000/year, which would depreciate the asset fully in one year, not five.');

INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (23, 'A client paid ₱6,000 advance for 3 months of consulting. After 1 month, the adjusting entry recognizes:', 'multiple_choice', 5, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, 'Dr. Unearned Revenue ₱2,000 / Cr. Service Revenue ₱2,000', TRUE, 1, 'One month of the three-month advance has now been earned: ₱6,000 ÷ 3 = ₱2,000. The liability (Unearned Revenue) decreases; revenue is recognized.'),
(@q, 'Dr. Unearned Revenue ₱6,000 / Cr. Service Revenue ₱6,000', FALSE, 2, 'Only 1 of the 3 months has been earned. Recognizing the full ₱6,000 overstates revenue.'),
(@q, 'Dr. Service Revenue ₱2,000 / Cr. Unearned Revenue ₱2,000', FALSE, 3, 'This entry is reversed — debiting revenue reduces it, while the adjustment should be to INCREASE revenue and REDUCE the liability.'),
(@q, 'Dr. Cash ₱2,000 / Cr. Service Revenue ₱2,000', FALSE, 4, 'Cash was received in the original entry; adjusting entries never involve Cash. The adjustment reclassifies the liability to revenue.');

-- ─── Assessment 24: Adjusted Trial Balance ───────────────────────────────────
INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (24, 'The primary purpose of the Adjusted Trial Balance is:', 'multiple_choice', 1, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, 'To provide the final, accurate account balances used to prepare all three financial statements', TRUE, 1, 'The Adjusted Trial Balance is prepared after adjusting entries are posted. It is the definitive source for the Income Statement, SCE, and Balance Sheet.'),
(@q, 'To detect if any regular transactions were missed during journalizing', FALSE, 2, 'Transaction completeness is checked earlier; the Adjusted TB is about post-adjustment accuracy for financial statement preparation.'),
(@q, 'To close temporary accounts and zero them out for the next period', FALSE, 3, 'Closing entries come AFTER financial statements are prepared (Step 8), not at the Adjusted TB stage.'),
(@q, 'To list only permanent accounts after all closings are done', FALSE, 4, 'The Post-CLOSING Trial Balance shows only permanent accounts. The Adjusted TB shows all accounts (before closing).');

INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (24, 'On the worksheet, if a debit balance and a debit adjustment are on the SAME SIDE for an account, you should:', 'multiple_choice', 2, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, 'Add the two amounts and place the sum on the debit side', TRUE, 1, 'Same-side amounts reinforce each other. Two debits are added: e.g., Supplies Expense ₱0 + Adjustment ₱5,500 = ₱5,500 Dr.'),
(@q, 'Subtract the smaller from the larger and keep the larger side', FALSE, 2, 'Subtraction applies only when the original balance and the adjustment are on OPPOSITE sides.'),
(@q, 'Ignore the smaller adjustment if it is less than 10% of the balance', FALSE, 3, 'There is no percentage threshold for ignoring adjustments. Every adjusting entry is applied fully.'),
(@q, 'Use only the larger of the two amounts', FALSE, 4, 'Both amounts must be combined. Using only the larger discards the other, which would misstate the account balance.');

INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (24, 'The Supplies account shows ₱8,000 before adjustment. After the ₱5,500 adjustment (supplies used), the Adjusted balance is:', 'multiple_choice', 3, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, '₱2,500 (debit)', TRUE, 1, 'Supplies is an asset (debit balance). ₱8,000 Dr − ₱5,500 Cr adjustment = ₱2,500 Dr. The remaining supplies on hand.'),
(@q, '₱13,500 (debit)', FALSE, 2, 'This incorrectly adds the adjustment to the original balance instead of subtracting. The adjustment REDUCES the supplies asset.'),
(@q, '₱5,500 (debit)', FALSE, 3, '₱5,500 is the amount of supplies USED (the expense), not the remaining balance.'),
(@q, '₱8,000 (no change)', FALSE, 4, 'The adjustment changes the Supplies balance. Ignoring it would leave the account overstated by ₱5,500.');

INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (24, 'The Adjusted TB shows totals of ₱486,750 Dr and ₱486,750 Cr. The unadjusted totals were ₱483,000 each. The ₱3,750 difference is due to:', 'multiple_choice', 4, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, 'Monthly depreciation of ₱3,750 recognized through adjustment (c)', TRUE, 1, 'Adjustment (c) debited Depreciation Expense ₱3,750 and credited Accumulated Depreciation ₱3,750 — adding new accounts that increased both sides by ₱3,750.'),
(@q, 'An error in the unadjusted trial balance', FALSE, 2, 'Both unadjusted and adjusted trial balances are balanced. The difference is caused by the adjustments themselves, not an error.'),
(@q, 'Unearned revenue that was recognized during the period', FALSE, 3, 'Recognizing unearned revenue shifts between liability and revenue — it does not add new totals to both sides. Only new accounts added by adjustments increase the totals.'),
(@q, 'New assets purchased after the trial balance date', FALSE, 4, 'Asset purchases are regular transactions journalized before the trial balance. They are already included in the unadjusted totals.');

INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (24, 'The Adjusted Trial Balance is prepared:', 'multiple_choice', 5, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, 'After adjusting entries have been journalized and posted (Step 6)', TRUE, 1, 'The Adjusted TB requires all adjusting entries to be posted first; it then provides the accurate balances for financial statement preparation.'),
(@q, 'Before adjusting entries to show the unadjusted account balances', FALSE, 2, 'The UNADJUSTED Trial Balance is prepared before adjustments. The ADJUSTED Trial Balance comes after.'),
(@q, 'After closing entries to show only permanent accounts', FALSE, 3, 'After closing entries, you prepare the POST-CLOSING Trial Balance. The Adjusted TB comes before closing entries.'),
(@q, 'At the beginning of the new accounting period', FALSE, 4, 'The Adjusted TB is prepared at the END of the current period, after adjustments, before financial statements.');

-- ─── Assessment 26: Closing Entries & Post-Closing Trial Balance ─────────────
INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (26, 'The purpose of closing entries is to:', 'multiple_choice', 1, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, 'Zero out temporary accounts (Revenue, Expenses, Drawings) and transfer net income to Capital', TRUE, 1, 'Closing entries ensure that revenue, expense, and drawing accounts start at zero in the next period, and that net income/loss is added to Capital.'),
(@q, 'Update account balances to reflect accruals and deferrals', FALSE, 2, 'Updating for accruals and deferrals is done by ADJUSTING entries (Step 5), not closing entries (Step 8).'),
(@q, 'Verify that total debits equal total credits after the period', FALSE, 3, 'Mathematical verification is the purpose of the Trial Balance (Step 4), not closing entries.'),
(@q, 'Prepare the financial statements from ledger balances', FALSE, 4, 'Financial statements are prepared in Step 7 from the Adjusted Trial Balance, before closing entries are made.');

INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (26, 'After Step 3 (closing Income Summary to Capital), the Income Summary balance should be:', 'multiple_choice', 2, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, 'Zero — the net income/loss has been fully transferred to Capital', TRUE, 1, 'Income Summary is a temporary clearing account. After transferring net income/loss to Capital, it has a zero balance.'),
(@q, 'Equal to net income for the period', FALSE, 2, 'Income Summary had a credit balance equal to net income BEFORE Step 3. After Step 3 closes it to Capital, the balance is zero.'),
(@q, 'Equal to total revenues for the period', FALSE, 3, 'After Step 1 (close revenues to Income Summary) and Step 2 (close expenses), Income Summary has the NET income balance. After Step 3, it is zero.'),
(@q, 'Equal to total expenses for the period', FALSE, 4, 'Expenses give Income Summary a debit in Step 2. The net balance (revenue − expenses = net income) is transferred to Capital in Step 3. After Step 3: zero.');

INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (26, 'Which account appears on the Post-Closing Trial Balance?', 'multiple_choice', 3, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, 'Accumulated Depreciation — Equipment', TRUE, 1, 'Accumulated Depreciation is a permanent contra-asset account that carries its balance forward to the next period. It appears on the Post-Closing TB.'),
(@q, 'Service Revenue', FALSE, 2, 'Service Revenue is a TEMPORARY account. After closing entries, its balance is zero and it does NOT appear on the Post-Closing TB.'),
(@q, 'Rent Expense', FALSE, 3, 'Rent Expense is a TEMPORARY account zeroed out by closing entries. It has no balance on the Post-Closing TB.'),
(@q, 'Owner''s Drawings', FALSE, 4, 'Drawings is a TEMPORARY account closed directly to Capital. After closing, its balance is zero and it does not appear on the Post-Closing TB.');

INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (26, 'For ABM Accounting Firm: Beginning Capital ₱300,000; Net Income ₱57,750; Drawings ₱10,000. After closing, Capital equals:', 'multiple_choice', 4, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, '₱347,750', TRUE, 1, 'Ending Capital = 300,000 + 57,750 − 10,000 = ₱347,750. Net income increases Capital; drawings reduce it.'),
(@q, '₱300,000', FALSE, 2, 'This is the BEGINNING balance, before adding net income and deducting drawings.'),
(@q, '₱357,750', FALSE, 3, 'This result forgets to deduct the ₱10,000 drawings: 300,000 + 57,750 = 357,750, but drawings must be subtracted.'),
(@q, '₱289,750', FALSE, 4, 'This result incorrectly deducts net income instead of adding it: 300,000 − 10,000 − 57,750. Net income INCREASES Capital.');

INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (26, 'When there is a NET LOSS, what happens to Income Summary in the closing process?', 'multiple_choice', 5, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, 'Income Summary has a DEBIT balance; it is closed by crediting it and debiting Capital', TRUE, 1, 'A net loss means expenses exceed revenues. Income Summary ends with a debit balance equal to the net loss. Closing it: Dr. Capital / Cr. Income Summary — reducing Capital.'),
(@q, 'Income Summary has a CREDIT balance; it is closed by debiting it and crediting Capital', FALSE, 2, 'A CREDIT balance in Income Summary indicates NET INCOME (revenues > expenses). A net loss produces a DEBIT balance.'),
(@q, 'Income Summary is not used when there is a net loss', FALSE, 3, 'Income Summary is used regardless of profit or loss. It always serves as the clearing account for revenues and expenses.'),
(@q, 'Income Summary retains the loss balance until next period', FALSE, 4, 'Income Summary is a TEMPORARY account that must be closed to zero at period-end. The loss is transferred to Capital, not carried forward.');

-- ─── Assessment 27: Reversing Entries & Complete Cycle ───────────────────────
INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (27, 'Reversing entries are made:', 'multiple_choice', 1, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, 'At the beginning of the NEXT accounting period', TRUE, 1, 'Reversing entries are optional entries made on the first day of the new period to simplify bookkeeping when recording the related cash transactions later.'),
(@q, 'At year-end, immediately after closing entries', FALSE, 2, 'Closing entries are made at year-end. Reversing entries are made at the START of the next period — typically January 1.'),
(@q, 'Before adjusting entries, at the end of each month', FALSE, 3, 'Reversing entries come AFTER the full closing cycle, at the beginning of the next period. They are not made before adjustments.'),
(@q, 'Monthly, for all five types of adjusting entries', FALSE, 4, 'Not all adjusting entries require reversal — only specific types (accruals, expense-method prepayments). And reversals are done annually at year-start, not monthly.');

INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (27, 'Which type of adjusting entry does NOT require a reversing entry?', 'multiple_choice', 2, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, 'Depreciation Expense', TRUE, 1, 'Depreciation never requires a reversing entry because no cash transaction follows it in the new period that would need simplification.'),
(@q, 'Accrued Expenses (e.g., unpaid salaries)', FALSE, 2, 'Accrued expenses DO require reversing entries so that when cash is paid in the new period, the bookkeeper can record it simply without splitting the payment.'),
(@q, 'Accrued Revenue (e.g., earned but uncollected fees)', FALSE, 3, 'Accrued revenue DOES require a reversing entry so that cash collection in the new period can be recorded without splitting.'),
(@q, 'Prepaid Expense recorded using the expense method', FALSE, 4, 'When the expense method is used (initial debit to Expense), a reversing entry IS required. The asset method does not require reversal.');

INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (27, 'The main reason for making reversing entries is to:', 'multiple_choice', 3, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, 'Simplify bookkeeping by eliminating the need to split cash transactions in the new period', TRUE, 1, 'Without reversing entries, bookkeepers must remember to allocate the new-period cash payment between the accrued portion and the new period — a complex split. Reversal removes this complexity.'),
(@q, 'Correct errors made in the prior period''s adjusting entries', FALSE, 2, 'Error corrections use correcting entries, not reversing entries. Reversal is a convenience technique, not a correction mechanism.'),
(@q, 'Transfer the net income/loss balance to Capital', FALSE, 3, 'Transferring net income to Capital is done by CLOSING entries (Step 8), not reversing entries (Step 10).'),
(@q, 'Zero out permanent accounts at the start of each period', FALSE, 4, 'Only TEMPORARY accounts are zeroed out by closing entries. Permanent accounts carry their balances forward. Reversing entries do not zero out accounts permanently.');

INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (27, 'Dec. 31 adjusting entry (expense method): Prepaid Rent ₱10,000 / Rent Expense ₱10,000. The Jan. 1 reversing entry is:', 'multiple_choice', 4, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, 'Dr. Rent Expense ₱10,000 / Cr. Prepaid Rent ₱10,000', TRUE, 1, 'The reversing entry is the exact opposite of the Dec. 31 adjusting entry — every account and direction is swapped.'),
(@q, 'Dr. Prepaid Rent ₱10,000 / Cr. Rent Expense ₱10,000', FALSE, 2, 'This repeats the adjusting entry rather than reversing it. The reversal must be the OPPOSITE of the adjusting entry.'),
(@q, 'Dr. Cash ₱10,000 / Cr. Prepaid Rent ₱10,000', FALSE, 3, 'Cash is not involved in the adjusting entry, so it should not appear in the reversal. Reversing entries mirror the original adjustment exactly.'),
(@q, 'No reversing entry is needed for this type of adjustment', FALSE, 4, 'The expense method for prepaid expenses DOES require a reversing entry. The asset method (initial debit to Prepaid) does not require reversal.');

INSERT INTO assessment_questions (assessment_id, question_text, question_type, sequence_order, is_active) VALUES (27, 'After completing all 10 steps of the accounting cycle, what happens next?', 'multiple_choice', 5, TRUE);
SET @q = LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id, answer_text, is_correct, sequence_order, explanation) VALUES
(@q, 'Step 1 of the NEXT period begins — the cycle repeats', TRUE, 1, 'The accounting cycle is continuous. After reversing entries (Step 10), the new period starts with Step 1 (analyzing new transactions). The cycle never ends as long as the business operates.'),
(@q, 'Step 5 of the same period is repeated for additional adjustments', FALSE, 2, 'Adjusting entries (Step 5) are for the current period only. The cycle for that period is complete; the next period starts fresh at Step 1.'),
(@q, 'Step 8 is repeated to close any newly discovered accounts', FALSE, 3, 'Closing entries are made once per period. Re-doing Step 8 would double-close accounts. Any omissions are corrected via correcting entries in the new period.'),
(@q, 'The adjusted trial balance is prepared again for verification', FALSE, 4, 'The adjusted trial balance (Step 6) belongs to the current period. After Step 10, the focus shifts entirely to the new accounting period starting at Step 1.');
