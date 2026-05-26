-- AccuLearn: Replace assessments 1-27 with CO1-CO7 FINALS PDF questions
-- Part 1: Assessments 1-14

DELETE aa FROM assessment_answers aa
JOIN assessment_questions aq ON aa.question_id = aq.id
WHERE aq.assessment_id BETWEEN 1 AND 27;
DELETE FROM assessment_questions WHERE assessment_id BETWEEN 1 AND 27;

-- =====================================================================
-- ASSESSMENT 1 — Module Quiz, CO1 Quiz1 Q1-15 (Regulatory Framework)
-- =====================================================================
INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (1,'In the Philippines, standardized accounting rules are rooted in the PFRS. What does PFRS stand for?','multiple_choice',1,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Philippine Financial Revenue Standards',0,1,NOW(),NOW()),(@q,'Philippine Financial Reporting Standards',1,2,NOW(),NOW()),(@q,'Public Financial Record Standards',0,3,NOW(),NOW()),(@q,'Philippine Fiscal Regulatory Standards',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (1,'To ensure consistency with international practices, the Philippine Financial Reporting Standards (PFRS) are aligned with which global framework?','multiple_choice',2,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'GAAP',0,1,NOW(),NOW()),(@q,'FASB',0,2,NOW(),NOW()),(@q,'IFRS',1,3,NOW(),NOW()),(@q,'IASB',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (1,'Which specific regulatory body is explicitly tasked with issuing the PFRS and setting accounting standards in the Philippines?','multiple_choice',3,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Securities and Exchange Commission (SEC)',0,1,NOW(),NOW()),(@q,'Financial Reporting Standards Council (FRSC)',1,2,NOW(),NOW()),(@q,'Board of Accountancy (BOA)',0,3,NOW(),NOW()),(@q,'Bureau of Internal Revenue (BIR)',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (1,'What is the full name of the FRSC, the body that oversees accounting standards in the country?','multiple_choice',4,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Financial Reporting Standards Council',1,1,NOW(),NOW()),(@q,'Fiscal Regulatory Standards Commission',0,2,NOW(),NOW()),(@q,'Financial Record System Council',0,3,NOW(),NOW()),(@q,'Federal Revenue Standards Committee',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (1,'Which government body is primarily responsible for regulating corporations and explicitly requires them to submit PFRS-compliant financial statements?','multiple_choice',5,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Bangko Sentral ng Pilipinas (BSP)',0,1,NOW(),NOW()),(@q,'Bureau of Internal Revenue (BIR)',0,2,NOW(),NOW()),(@q,'Board of Accountancy (BOA)',0,3,NOW(),NOW()),(@q,'Securities and Exchange Commission (SEC)',1,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (1,'If a business needs to be assessed for taxes, which regulatory body primarily uses its financial statements for this compliance?','multiple_choice',6,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Securities and Exchange Commission (SEC)',0,1,NOW(),NOW()),(@q,'Bureau of Internal Revenue (BIR)',1,2,NOW(),NOW()),(@q,'Financial Reporting Standards Council (FRSC)',0,3,NOW(),NOW()),(@q,'Bangko Sentral ng Pilipinas (BSP)',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (1,'Which institution is strictly responsible for regulating banks and other financial institutions in the Philippines?','multiple_choice',7,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Bureau of Internal Revenue (BIR)',0,1,NOW(),NOW()),(@q,'Board of Accountancy (BOA)',0,2,NOW(),NOW()),(@q,'Bangko Sentral ng Pilipinas (BSP)',1,3,NOW(),NOW()),(@q,'Securities and Exchange Commission (SEC)',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (1,'The Board of Accountancy (BOA) plays a crucial role in the accounting profession. Which of the following best describes its primary role?','multiple_choice',8,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'It regulates commercial banks and lending institutions.',0,1,NOW(),NOW()),(@q,'It assesses and collects corporate income taxes.',0,2,NOW(),NOW()),(@q,'It oversees CPA practice and enforces ethical and professional standards.',1,3,NOW(),NOW()),(@q,'It issues the PFRS and aligns them with global standards.',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (1,'What is the recognized professional organization for Certified Public Accountants (CPAs) in the Philippines that guides their ethical conduct?','multiple_choice',9,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'PICPA',1,1,NOW(),NOW()),(@q,'BOA',0,2,NOW(),NOW()),(@q,'FRSC',0,3,NOW(),NOW()),(@q,'SEC',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (1,'According to the regulatory framework ecosystem, which of the following bodies is NOT explicitly mentioned as an enforcing body of accounting rules?','multiple_choice',10,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Department of Trade and Industry (DTI)',1,1,NOW(),NOW()),(@q,'Securities and Exchange Commission (SEC)',0,2,NOW(),NOW()),(@q,'Bureau of Internal Revenue (BIR)',0,3,NOW(),NOW()),(@q,'Bangko Sentral ng Pilipinas (BSP)',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (1,'A newly established commercial bank needs to ensure its financial operations comply with its specific industry regulator. Which body''s regulations must it prioritize?','multiple_choice',11,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'SEC',0,1,NOW(),NOW()),(@q,'BSP',1,2,NOW(),NOW()),(@q,'BOA',0,3,NOW(),NOW()),(@q,'PICPA',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (1,'If a practicing CPA violates professional and ethical standards, which government regulatory body has the direct authority to oversee and discipline them?','multiple_choice',12,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Board of Accountancy (BOA)',1,1,NOW(),NOW()),(@q,'Bureau of Internal Revenue (BIR)',0,2,NOW(),NOW()),(@q,'Financial Reporting Standards Council (FRSC)',0,3,NOW(),NOW()),(@q,'Philippine Institute of Certified Public Accountants (PICPA)',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (1,'The SEC requires financial statements submitted by corporations to be compliant with which specific set of standards?','multiple_choice',13,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Internal Revenue Code (IRC)',0,1,NOW(),NOW()),(@q,'Philippine Financial Reporting Standards (PFRS)',1,2,NOW(),NOW()),(@q,'Generally Accepted Auditing Standards (GAAS)',0,3,NOW(),NOW()),(@q,'Bank Secrecy Laws',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (1,'Which two bodies mentioned in the framework are primarily associated with the ethical conduct, professional standards, and practice of CPAs in the Philippines?','multiple_choice',14,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'SEC and BIR',0,1,NOW(),NOW()),(@q,'BSP and FRSC',0,2,NOW(),NOW()),(@q,'BOA and PICPA',1,3,NOW(),NOW()),(@q,'BIR and BOA',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (1,'The global accounting standards that the PFRS is aligned with are known by the acronym:','multiple_choice',15,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'IFRS',1,1,NOW(),NOW()),(@q,'GAAP',0,2,NOW(),NOW()),(@q,'SEC',0,3,NOW(),NOW()),(@q,'FRSC',0,4,NOW(),NOW());

-- =====================================================================
-- ASSESSMENT 2 — Lesson Quiz, CO1 Quiz1 Q16-20
-- =====================================================================
INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (2,'Which regulatory body uses financial statements primarily to ensure that the correct amount of government revenues is collected from businesses?','multiple_choice',1,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'BSP',0,1,NOW(),NOW()),(@q,'FRSC',0,2,NOW(),NOW()),(@q,'SEC',0,3,NOW(),NOW()),(@q,'BIR',1,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (2,'Before diving into individual accounting principles, it is important to understand the authorities behind them. Which council is strictly responsible for overseeing these rules in the Philippines?','multiple_choice',2,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'FRSC',1,1,NOW(),NOW()),(@q,'BOA',0,2,NOW(),NOW()),(@q,'SEC',0,3,NOW(),NOW()),(@q,'BIR',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (2,'What does the acronym SEC stand for in the context of Philippine business regulation?','multiple_choice',3,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Standard Exchange Commission',0,1,NOW(),NOW()),(@q,'Securities and Exchange Commission',1,2,NOW(),NOW()),(@q,'System of Enterprise Control',0,3,NOW(),NOW()),(@q,'Secretariat of Economic Commerce',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (2,'Which body issues the accounting standards that the SEC eventually requires corporations to follow?','multiple_choice',4,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'PICPA',0,1,NOW(),NOW()),(@q,'BSP',0,2,NOW(),NOW()),(@q,'FRSC',1,3,NOW(),NOW()),(@q,'BOA',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (2,'Which of the following statements correctly matches the regulatory body with its specific role in the Philippine accounting framework?','multiple_choice',5,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'BSP regulates corporate taxes.',0,1,NOW(),NOW()),(@q,'BIR regulates banks and financial institutions.',0,2,NOW(),NOW()),(@q,'FRSC issues PFRS and sets accounting standards aligned with IFRS.',1,3,NOW(),NOW()),(@q,'BOA regulates all registered corporations.',0,4,NOW(),NOW());

-- =====================================================================
-- ASSESSMENT 3 — Lesson Quiz, CO1 Quiz2 Q1-5 (Accounting Concepts)
-- =====================================================================
INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (3,'A business is treated as a separate entity from its owner, meaning all business transactions are recorded separately from the owner''s personal transactions. What accounting assumption is this?','multiple_choice',1,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Going Concern Assumption',0,1,NOW(),NOW()),(@q,'Time Period Assumption',0,2,NOW(),NOW()),(@q,'Economic Entity Assumption',1,3,NOW(),NOW()),(@q,'Duality Concept',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (3,'Dr. Nana runs a pediatric clinic. According to accounting rules, why must her personal grocery expenses never be mixed with the clinic''s expenses?','multiple_choice',2,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Because of the Monetary Concept',0,1,NOW(),NOW()),(@q,'Because of the Economic Entity Assumption',1,2,NOW(),NOW()),(@q,'Because of the Matching Concept',0,3,NOW(),NOW()),(@q,'Because of the Accrual Basis',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (3,'Financial statements are prepared assuming that a business will continue to operate into the foreseeable future and will not be closed down or liquidated. This refers to the:','multiple_choice',3,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Terminating Concern',0,1,NOW(),NOW()),(@q,'Time Period Assumption',0,2,NOW(),NOW()),(@q,'Realization Concept',0,3,NOW(),NOW()),(@q,'Going Concern Assumption',1,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (3,'Because of the Going Concern Assumption, how does an accountant justify recording long-term assets like clinic equipment?','multiple_choice',4,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'By valuing them at their immediate liquidation price',0,1,NOW(),NOW()),(@q,'By excluding them from the financial statements entirely',0,2,NOW(),NOW()),(@q,'By charging them entirely to expense in the first month',0,3,NOW(),NOW()),(@q,'By recording them as assets rather than valuing everything at liquidation price',1,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (3,'A company chooses to report its financial performance covering a 12-month period that begins on July 1 and ends on June 30 of the following year. This specific time frame is known as a:','multiple_choice',5,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Calendar Year',0,1,NOW(),NOW()),(@q,'Natural Business Year',0,2,NOW(),NOW()),(@q,'Fiscal Year',1,3,NOW(),NOW()),(@q,'Semi-annual Period',0,4,NOW(),NOW());

-- =====================================================================
-- ASSESSMENT 4 — Lesson Quiz, CO1 Quiz2 Q6-10
-- =====================================================================
INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (4,'Which type of accounting period ends when a company''s business activity is at its lowest point?','multiple_choice',1,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Fiscal Year',0,1,NOW(),NOW()),(@q,'Natural Business Year',1,2,NOW(),NOW()),(@q,'Calendar Year',0,3,NOW(),NOW()),(@q,'Quarterly Period',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (4,'Under the preferred method of PFRS/GAAP, income is recorded in the period it is earned regardless of when collected, and expenses are recorded when incurred regardless of when paid. This defines the:','multiple_choice',2,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Accrual Basis',1,1,NOW(),NOW()),(@q,'Monetary Concept',0,2,NOW(),NOW()),(@q,'Duality Concept',0,3,NOW(),NOW()),(@q,'Cash Basis of Accounting',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (4,'Under the accrual basis, what is the correct term for an expense that has already been incurred but not yet paid?','multiple_choice',3,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Prepaid Expense',0,1,NOW(),NOW()),(@q,'Deferred Income',0,2,NOW(),NOW()),(@q,'Accrued Expense',1,3,NOW(),NOW()),(@q,'Accrued Income',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (4,'A beach resort receives an advance payment from a guest who will check in next month. Why can the resort NOT record this as income yet?','multiple_choice',4,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Because the amount is immaterial',0,1,NOW(),NOW()),(@q,'Because it must be recorded as a Prepaid Expense',0,2,NOW(),NOW()),(@q,'Because the Cash Basis requires waiting for the guest to leave',0,3,NOW(),NOW()),(@q,'Because the service has not yet been rendered, making it Unearned Revenue',1,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (4,'What do you call income that has been collected by a business but has not yet been earned?','multiple_choice',5,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Accrued Income',0,1,NOW(),NOW()),(@q,'Deferred Income (Unearned Revenue)',1,2,NOW(),NOW()),(@q,'Accrued Expense',0,3,NOW(),NOW()),(@q,'Prepaid Expense',0,4,NOW(),NOW());

-- =====================================================================
-- ASSESSMENT 5 — Lesson Quiz, CO1 Quiz2 Q11-15
-- =====================================================================
INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (5,'The hard work and dedication of nurses in a clinic are valuable, but they cannot be recorded as an asset in the clinic''s books. Which concept explains why?','multiple_choice',1,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Realization Concept',0,1,NOW(),NOW()),(@q,'Monetary / Measurement Concept',1,2,NOW(),NOW()),(@q,'Matching Concept',0,3,NOW(),NOW()),(@q,'Economic Entity Assumption',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (5,'If a business has non-financial information that is highly relevant to users but cannot be measured in Philippine Pesos, how should it be handled according to the Monetary Concept?','multiple_choice',2,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'It should be converted into an estimated peso value and recorded as an asset.',0,1,NOW(),NOW()),(@q,'It must be completely ignored and never communicated.',0,2,NOW(),NOW()),(@q,'It should be noted via a memo entry in the books.',1,3,NOW(),NOW()),(@q,'It should be recorded as a liability.',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (5,'According to the Realization Concept, when do ownership and income transfer to the buyer if goods are shipped under FOB Destination terms?','multiple_choice',3,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'When the goods leave the seller''s premises',0,1,NOW(),NOW()),(@q,'When the goods arrive at the buyer''s warehouse',1,2,NOW(),NOW()),(@q,'Upon signing the shipping contract',0,3,NOW(),NOW()),(@q,'Exactly midway through the shipping route',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (5,'A trucking company ships goods under FOB Shipping Point terms on December 30, and the buyer receives them on January 3. When should the seller recognize the income?','multiple_choice',4,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'December 30',1,1,NOW(),NOW()),(@q,'December 31',0,2,NOW(),NOW()),(@q,'January 3',0,3,NOW(),NOW()),(@q,'When cash is fully collected',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (5,'Aling Rosa buys a commercial oven for P35,000. Instead of recording the full cost as an immediate expense, its cost is allocated (depreciated) over its useful life. This perfectly illustrates the:','multiple_choice',5,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Matching Concept',1,1,NOW(),NOW()),(@q,'Realization Concept',0,2,NOW(),NOW()),(@q,'Cash Basis of Accounting',0,3,NOW(),NOW()),(@q,'Duality Concept',0,4,NOW(),NOW());

-- =====================================================================
-- ASSESSMENT 6 — Lesson Quiz, CO1 Quiz3 Q1-5 (GAAP Principles)
-- =====================================================================
INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (6,'All accounting records and financial statements must be based on reliable, verifiable evidence rather than personal opinions or guesses. Which principle does this represent?','multiple_choice',1,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Materiality Principle',0,1,NOW(),NOW()),(@q,'Conservatism Principle',0,2,NOW(),NOW()),(@q,'Objectivity Principle',1,3,NOW(),NOW()),(@q,'Consistency Principle',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (6,'An employee asks the accountant to reimburse P1,500 for office supplies but admits they lost the Official Receipt. The accountant refuses to record the expense. Which principle justifies the accountant''s refusal?','multiple_choice',2,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Historical Cost Principle',0,1,NOW(),NOW()),(@q,'Objectivity Principle',1,2,NOW(),NOW()),(@q,'Adequate Disclosure Principle',0,3,NOW(),NOW()),(@q,'Materiality Principle',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (6,'The principle that requires assets to be recorded at their original acquisition price, regardless of changes in market value over time, is called the:','multiple_choice',3,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Market Value Principle',0,1,NOW(),NOW()),(@q,'Objectivity Principle',0,2,NOW(),NOW()),(@q,'Conservatism Principle',0,3,NOW(),NOW()),(@q,'Historical Cost Principle',1,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (6,'A business purchased a piece of land in 2015 for P2,000,000. In 2024, a real estate appraiser values the land at P5,500,000. Under the Historical Cost Principle, at what amount should the land remain recorded on the balance sheet?','multiple_choice',4,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'P5,500,000',0,1,NOW(),NOW()),(@q,'P3,500,000',0,2,NOW(),NOW()),(@q,'P2,000,000',1,3,NOW(),NOW()),(@q,'The average of the two values',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (6,'Company X bought a delivery van with a list price of P1,000,000 but negotiated a cash discount and only paid P900,000. Under the Historical Cost Principle, at what amount should the van be recorded?','multiple_choice',5,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'P1,000,000',0,1,NOW(),NOW()),(@q,'P900,000',1,2,NOW(),NOW()),(@q,'P100,000',0,3,NOW(),NOW()),(@q,'The future resale value',0,4,NOW(),NOW());

-- =====================================================================
-- ASSESSMENT 7 — Lesson Quiz, CO1 Quiz3 Q6-10
-- =====================================================================
INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (7,'Financial statements should include all relevant information that would affect the understanding and decisions of users. Which principle dictates this?','multiple_choice',1,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Adequate Disclosure Principle',1,1,NOW(),NOW()),(@q,'Historical Cost Principle',0,2,NOW(),NOW()),(@q,'Conservatism Principle',0,3,NOW(),NOW()),(@q,'Materiality Principle',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (7,'The P5,500,000 current market value of land is highly relevant to investors, even though it cannot replace the historical cost on the balance sheet. How should the company communicate this market value?','multiple_choice',2,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'By replacing the historical cost in the asset section',0,1,NOW(),NOW()),(@q,'By mentioning it in the footnotes to the financial statements',1,2,NOW(),NOW()),(@q,'By recording the increase as a regular daily income',0,3,NOW(),NOW()),(@q,'By ignoring it completely until the land is sold',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (7,'If a company faces a pending lawsuit that could severely impact its financial stability, under which principle must this pending lawsuit be reported in the financial statement notes?','multiple_choice',3,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Conservatism Principle',0,1,NOW(),NOW()),(@q,'Consistency Principle',0,2,NOW(),NOW()),(@q,'Adequate Disclosure Principle',1,3,NOW(),NOW()),(@q,'Materiality Principle',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (7,'Strict adherence to accounting principles is not required for items of little significance that will not affect the decisions of financial statement users. This defines the:','multiple_choice',4,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Conservatism Principle',0,1,NOW(),NOW()),(@q,'Materiality Principle',1,2,NOW(),NOW()),(@q,'Objectivity Principle',0,3,NOW(),NOW()),(@q,'Consistency Principle',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (7,'A school purchases a P50 whiteboard marker that will last for two years. Instead of recording it as an asset and computing depreciation, the accountant immediately records it as an expense. Which principle allows this practical shortcut?','multiple_choice',5,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Objectivity Principle',0,1,NOW(),NOW()),(@q,'Conservatism Principle',0,2,NOW(),NOW()),(@q,'Materiality Principle',1,3,NOW(),NOW()),(@q,'Revenue Recognition Principle',0,4,NOW(),NOW());

-- =====================================================================
-- ASSESSMENT 8 — Module Quiz, CO2 Quiz1 Q1-15 (Accounting Equation)
-- =====================================================================
INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (8,'What term describes the economic resources owned or controlled by a business that are expected to provide future economic benefits?','multiple_choice',1,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Owner''s Equity',0,1,NOW(),NOW()),(@q,'Liabilities',0,2,NOW(),NOW()),(@q,'Assets',1,3,NOW(),NOW()),(@q,'Revenue',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (8,'Which of the following represents the present obligations of a business to outside parties (creditors)?','multiple_choice',2,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Capital',0,1,NOW(),NOW()),(@q,'Liabilities',1,2,NOW(),NOW()),(@q,'Expenses',0,3,NOW(),NOW()),(@q,'Assets',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (8,'What is defined as the residual interest in the assets of a business after deducting all its liabilities?','multiple_choice',3,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Owner''s Equity',1,1,NOW(),NOW()),(@q,'Current Assets',0,2,NOW(),NOW()),(@q,'Net Income',0,3,NOW(),NOW()),(@q,'Total Revenue',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (8,'According to the classification of accounts, which of the following is considered a Current Asset?','multiple_choice',4,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Office Equipment',0,1,NOW(),NOW()),(@q,'Accounts Receivable',1,2,NOW(),NOW()),(@q,'Mortgage Payable',0,3,NOW(),NOW()),(@q,'Land',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (8,'Which of the following is an example of a Non-Current (Fixed) Asset?','multiple_choice',5,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Delivery Equipment',1,1,NOW(),NOW()),(@q,'Cash',0,2,NOW(),NOW()),(@q,'Accounts Payable',0,3,NOW(),NOW()),(@q,'Office Supplies',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (8,'All of the following are classified as assets EXCEPT:','multiple_choice',6,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Merchandise Inventory',0,1,NOW(),NOW()),(@q,'Prepaid Expenses',0,2,NOW(),NOW()),(@q,'Unearned Revenue',1,3,NOW(),NOW()),(@q,'Notes Receivable',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (8,'Mang Tomas purchased P80,000 worth of cement bags from a supplier on credit. What type of account is created by this transaction?','multiple_choice',7,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Asset (Cash)',0,1,NOW(),NOW()),(@q,'Liability (Accounts Payable)',1,2,NOW(),NOW()),(@q,'Equity (Capital)',0,3,NOW(),NOW()),(@q,'Revenue (Sales)',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (8,'Which of the following liability accounts represents a long-term obligation rather than a current one?','multiple_choice',8,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Accrued Expenses',0,1,NOW(),NOW()),(@q,'Income Tax Payable',0,2,NOW(),NOW()),(@q,'Accounts Payable',0,3,NOW(),NOW()),(@q,'Mortgage Payable',1,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (8,'In the expanded accounting equation, which component represents the initial and additional investments made by the owner into the business?','multiple_choice',9,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Revenue',0,1,NOW(),NOW()),(@q,'Withdrawals',0,2,NOW(),NOW()),(@q,'Capital',1,3,NOW(),NOW()),(@q,'Assets',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (8,'What are amounts taken out of the business by the owner for their own personal use called?','multiple_choice',10,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Expenses',0,1,NOW(),NOW()),(@q,'Withdrawals (Drawings)',1,2,NOW(),NOW()),(@q,'Capital Reductions',0,3,NOW(),NOW()),(@q,'Accrued Liabilities',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (8,'How do Owner''s Withdrawals (Drawings) affect the overall Owner''s Equity of the business?','multiple_choice',11,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'They increase equity',0,1,NOW(),NOW()),(@q,'They have no effect on equity',0,2,NOW(),NOW()),(@q,'They decrease equity',1,3,NOW(),NOW()),(@q,'They convert equity into revenue',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (8,'Which element of the expanded accounting equation represents earned amounts, such as service fees or sales income, that INCREASE the owner''s equity?','multiple_choice',12,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Revenue / Income',1,1,NOW(),NOW()),(@q,'Capital',0,2,NOW(),NOW()),(@q,'Assets',0,3,NOW(),NOW()),(@q,'Long-Term Investments',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (8,'Rent, salaries, and utilities are costs incurred to operate the business and generate revenue. How are these classified?','multiple_choice',13,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Liabilities',0,1,NOW(),NOW()),(@q,'Expenses',1,2,NOW(),NOW()),(@q,'Withdrawals',0,3,NOW(),NOW()),(@q,'Current Assets',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (8,'How do Expenses affect the overall Owner''s Equity of the business?','multiple_choice',14,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'They increase equity',0,1,NOW(),NOW()),(@q,'They decrease equity',1,2,NOW(),NOW()),(@q,'They turn into liabilities',0,3,NOW(),NOW()),(@q,'They do not affect equity',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (8,'What is the correct mathematical formula for the expanded Owner''s Equity equation?','multiple_choice',15,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Equity = Capital + Withdrawals + Revenue + Expenses',0,1,NOW(),NOW()),(@q,'Equity = Capital - Withdrawals - Revenue + Expenses',0,2,NOW(),NOW()),(@q,'Equity = Capital - Withdrawals + Revenue - Expenses',1,3,NOW(),NOW()),(@q,'Equity = Capital + Withdrawals - Revenue - Expenses',0,4,NOW(),NOW());

-- =====================================================================
-- ASSESSMENT 9 — Lesson Quiz, CO2 Quiz2 Q1-5 (Transaction Effects)
-- =====================================================================
INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (9,'What is the fundamental rule that every business transaction must follow regarding the accounting equation?','multiple_choice',1,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'It must increase total assets.',0,1,NOW(),NOW()),(@q,'It must always generate revenue.',0,2,NOW(),NOW()),(@q,'It must affect at least two accounts and maintain the balance of the equation.',1,3,NOW(),NOW()),(@q,'It must involve the exchange of cash.',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (9,'When an owner invests cash into the business to start operations, how does this affect the accounting equation?','multiple_choice',2,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Increase in Assets and Increase in Liabilities',0,1,NOW(),NOW()),(@q,'Increase in Assets and Increase in Owner''s Equity',1,2,NOW(),NOW()),(@q,'Decrease in Assets and Decrease in Owner''s Equity',0,3,NOW(),NOW()),(@q,'Increase in Liabilities and Decrease in Owner''s Equity',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (9,'If the owner invests a non-cash asset, such as personal delivery equipment, into the business, what is the effect on the accounting equation?','multiple_choice',3,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Assets increase and Owner''s Equity increases.',1,1,NOW(),NOW()),(@q,'Assets increase and Liabilities increase.',0,2,NOW(),NOW()),(@q,'No change in total assets; it is just an asset swap.',0,3,NOW(),NOW()),(@q,'Assets increase and Owner''s Equity decreases.',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (9,'A business purchases new office equipment and pays immediately with cash. What is the overall effect on the accounting equation?','multiple_choice',4,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Total assets increase.',0,1,NOW(),NOW()),(@q,'Total assets decrease.',0,2,NOW(),NOW()),(@q,'Assets increase and Liabilities increase.',0,3,NOW(),NOW()),(@q,'There is no net change in total assets (it is an asset swap).',1,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (9,'Don JPacs purchases office equipment worth P24,000 from Andsco Steel on account (on credit). How does this transaction affect the accounting equation?','multiple_choice',5,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Increase in Assets (Equipment) and Decrease in Assets (Cash)',0,1,NOW(),NOW()),(@q,'Increase in Assets (Equipment) and Increase in Owner''s Equity',0,2,NOW(),NOW()),(@q,'Increase in Assets (Equipment) and Increase in Liabilities (Accounts Payable)',1,3,NOW(),NOW()),(@q,'Decrease in Assets and Decrease in Liabilities',0,4,NOW(),NOW());

-- =====================================================================
-- ASSESSMENT 10 — Lesson Quiz, CO2 Quiz1 Q16-20
-- =====================================================================
INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (10,'Mang Tomas''s hardware store has P180,000 worth of unsold goods on display. Under which specific asset category do these goods fall?','multiple_choice',1,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Cash',0,1,NOW(),NOW()),(@q,'Office Supplies',0,2,NOW(),NOW()),(@q,'Prepaid Expenses',0,3,NOW(),NOW()),(@q,'Merchandise Inventory',1,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (10,'If a business collects money from a customer before rendering the actual service, it creates an obligation to deliver that service in the future. What is this liability account called?','multiple_choice',2,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Unearned Revenue / Deferred Income',1,1,NOW(),NOW()),(@q,'Accrued Expenses',0,2,NOW(),NOW()),(@q,'Accounts Receivable',0,3,NOW(),NOW()),(@q,'Notes Payable',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (10,'Why is Owner''s Equity often referred to as a ''residual interest''?','multiple_choice',3,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Because it represents the owner''s net investment after all debts (liabilities) are deducted from the assets.',1,1,NOW(),NOW()),(@q,'Because it is the money left over after the owner makes a personal withdrawal.',0,2,NOW(),NOW()),(@q,'Because it represents the total value of all physical properties the business owns.',0,3,NOW(),NOW()),(@q,'Because it is the amount owed to the government in taxes.',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (10,'Which of the following scenarios best describes a liability?','multiple_choice',4,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'A business buys land to build a new warehouse.',0,1,NOW(),NOW()),(@q,'A business takes out a P200,000 bank loan to renovate its store.',1,2,NOW(),NOW()),(@q,'A business owner invests their personal savings into the company bank account.',0,3,NOW(),NOW()),(@q,'A business earns P10,000 from rendering advertising services.',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (10,'A business pays its employees their monthly wages. This transaction is considered a/an:','multiple_choice',5,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Withdrawal',0,1,NOW(),NOW()),(@q,'Liability settlement',0,2,NOW(),NOW()),(@q,'Expense',1,3,NOW(),NOW()),(@q,'Asset swap',0,4,NOW(),NOW());

-- =====================================================================
-- ASSESSMENT 11 — Lesson Quiz, CO2 Quiz3 Q1-5 (Balance Scale)
-- =====================================================================
INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (11,'In the balance scale metaphor used to visualize the accounting equation, what is placed on the LEFT side of the scale?','multiple_choice',1,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Liabilities and Owner''s Equity',0,1,NOW(),NOW()),(@q,'Revenue and Expenses',0,2,NOW(),NOW()),(@q,'Assets',1,3,NOW(),NOW()),(@q,'Net Income',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (11,'In the same balance scale metaphor, what makes up the RIGHT side of the scale?','multiple_choice',2,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Liabilities and Owner''s Equity',1,1,NOW(),NOW()),(@q,'Assets and Expenses',0,2,NOW(),NOW()),(@q,'Cash and Receivables',0,3,NOW(),NOW()),(@q,'Assets only',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (11,'According to the lesson''s visualization of the balance scale, the LEFT side (Assets) represents which accounting side?','multiple_choice',3,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'The Credit side',0,1,NOW(),NOW()),(@q,'The Debit side',1,2,NOW(),NOW()),(@q,'The Revenue side',0,3,NOW(),NOW()),(@q,'The Expense side',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (11,'According to the lesson''s visualization, the RIGHT side (Liabilities + Equity) represents which accounting side?','multiple_choice',4,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'The Debit side',0,1,NOW(),NOW()),(@q,'The Credit side',1,2,NOW(),NOW()),(@q,'The Asset side',0,3,NOW(),NOW()),(@q,'The Expense side',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (11,'What is the most critical rule regarding the accounting ''balance scale'' after any transaction is recorded?','multiple_choice',5,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'The left side must always be heavier than the right side.',0,1,NOW(),NOW()),(@q,'The right side must always be heavier to show profit.',0,2,NOW(),NOW()),(@q,'The scale must always remain perfectly balanced.',1,3,NOW(),NOW()),(@q,'The scale will temporarily unbalance until the end of the month.',0,4,NOW(),NOW());

-- =====================================================================
-- ASSESSMENT 12 — Module Quiz, CO3 Quiz1 Q1-15 (Assets/Liabilities/Capital)
-- =====================================================================
INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (12,'Which major account type represents present economic resources controlled by the entity as a result of past events?','multiple_choice',1,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Liabilities',0,1,NOW(),NOW()),(@q,'Assets',1,2,NOW(),NOW()),(@q,'Capital',0,3,NOW(),NOW()),(@q,'Income',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (12,'In everyday terms, how are liabilities best described?','multiple_choice',2,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Amounts the business OWES to external parties',1,1,NOW(),NOW()),(@q,'Everything of value that the business OWNS',0,2,NOW(),NOW()),(@q,'The initial investment made by the owner',0,3,NOW(),NOW()),(@q,'The money the business earns from its main operations',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (12,'What is defined as the residual interest in the assets of the entity after deducting all liabilities?','multiple_choice',3,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Current Assets',0,1,NOW(),NOW()),(@q,'Operating Income',0,2,NOW(),NOW()),(@q,'Non-Current Liabilities',0,3,NOW(),NOW()),(@q,'Capital (Owner''s Equity)',1,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (12,'Assets that are expected to be converted to cash or used up within one year are classified as:','multiple_choice',4,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Other Assets',0,1,NOW(),NOW()),(@q,'Non-Current Assets',0,2,NOW(),NOW()),(@q,'Current Assets',1,3,NOW(),NOW()),(@q,'Fixed Assets',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (12,'Which of the following is an example of a Non-Current (Fixed) Asset used in long-term operations?','multiple_choice',5,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Accounts Receivable',0,1,NOW(),NOW()),(@q,'Prepaid Rent',0,2,NOW(),NOW()),(@q,'Delivery Equipment',1,3,NOW(),NOW()),(@q,'Office Supplies',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (12,'Obligations of the business that are expected to be settled within one year are known as:','multiple_choice',6,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Long-Term Debt',0,1,NOW(),NOW()),(@q,'Non-Current Liabilities',0,2,NOW(),NOW()),(@q,'Current Assets',0,3,NOW(),NOW()),(@q,'Current Liabilities',1,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (12,'Which of the following accounts represents an obligation due beyond one year (a Non-Current Liability)?','multiple_choice',7,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Accounts Payable',0,1,NOW(),NOW()),(@q,'Mortgage Payable',1,2,NOW(),NOW()),(@q,'Accrued Salaries Payable',0,3,NOW(),NOW()),(@q,'Unearned Revenue',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (12,'If a business collects money from a customer but has not yet rendered the service, it records Unearned Revenue. How is Unearned Revenue classified?','multiple_choice',8,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Current Liability',1,1,NOW(),NOW()),(@q,'Current Asset',0,2,NOW(),NOW()),(@q,'Capital',0,3,NOW(),NOW()),(@q,'Non-Current Liability',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (12,'Which of the following is an example of a Current Asset?','multiple_choice',9,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Building',0,1,NOW(),NOW()),(@q,'Prepaid Insurance',1,2,NOW(),NOW()),(@q,'Land',0,3,NOW(),NOW()),(@q,'Store Equipment',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (12,'Accumulated Depreciation is a special type of account that reduces the value of a related fixed asset. Under which classification does it fall?','multiple_choice',10,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Owner''s Equity',0,1,NOW(),NOW()),(@q,'Current Liability',0,2,NOW(),NOW()),(@q,'Contra-Asset',1,3,NOW(),NOW()),(@q,'Operating Expense',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (12,'Within the owner''s equity section, what specifically represents the initial and additional investments made by the owner into the business?','multiple_choice',11,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Withdrawals',0,1,NOW(),NOW()),(@q,'Capital',1,2,NOW(),NOW()),(@q,'Revenue',0,3,NOW(),NOW()),(@q,'Retained Earnings',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (12,'When a sole proprietor takes cash out of the business for personal use, which account is used and how does it affect equity?','multiple_choice',12,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Drawings/Withdrawals, which reduces equity',1,1,NOW(),NOW()),(@q,'Expenses, which reduces equity',0,2,NOW(),NOW()),(@q,'Income, which increases equity',0,3,NOW(),NOW()),(@q,'Capital, which increases equity',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (12,'How does a corporation''s equity structure differ from a sole proprietorship?','multiple_choice',13,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'It uses a single capital account for the entire business.',0,1,NOW(),NOW()),(@q,'It uses a capital account and a drawings account for each partner.',0,2,NOW(),NOW()),(@q,'It uses Stockholders'' Equity, comprising Share Capital and Retained Earnings.',1,3,NOW(),NOW()),(@q,'It does not have an equity section; everything is classified as a liability.',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (12,'Which of the following accounts represents an amount the business OWES to its employees for work they have already performed?','multiple_choice',14,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Accounts Receivable',0,1,NOW(),NOW()),(@q,'Accrued Salaries Payable',1,2,NOW(),NOW()),(@q,'Prepaid Rent',0,3,NOW(),NOW()),(@q,'SSS Contributions Payable',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (12,'Merchandise Inventory is the stock of goods a trading business has available for sale. How is this account classified?','multiple_choice',15,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Capital',0,1,NOW(),NOW()),(@q,'Non-Current Asset',0,2,NOW(),NOW()),(@q,'Current Liability',0,3,NOW(),NOW()),(@q,'Current Asset',1,4,NOW(),NOW());

-- =====================================================================
-- ASSESSMENT 13 — Lesson Quiz, CO3 Quiz2 Q1-5 (Income and Expenses)
-- =====================================================================
INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (13,'What major account type refers to increases in assets, or decreases in liabilities, that result in an increase in equity (other than owner contributions)?','multiple_choice',1,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Capital',0,1,NOW(),NOW()),(@q,'Income',1,2,NOW(),NOW()),(@q,'Liabilities',0,3,NOW(),NOW()),(@q,'Withdrawals',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (13,'In everyday business terms, how is Income (Revenue) best described?','multiple_choice',2,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'What the business OWES to external parties',0,1,NOW(),NOW()),(@q,'The initial investment made by the business owner',0,2,NOW(),NOW()),(@q,'What the business EARNS from its main operations',1,3,NOW(),NOW()),(@q,'The costs incurred to run the business',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (13,'What major account type refers to decreases in assets, or increases in liabilities, that result in a decrease in equity (other than owner distributions)?','multiple_choice',3,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Expenses',1,1,NOW(),NOW()),(@q,'Current Liabilities',0,2,NOW(),NOW()),(@q,'Contra-Assets',0,3,NOW(),NOW()),(@q,'Withdrawals',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (13,'How do expenses generally affect the owner''s equity?','multiple_choice',4,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'They increase equity.',0,1,NOW(),NOW()),(@q,'They have no effect on equity.',0,2,NOW(),NOW()),(@q,'They decrease equity.',1,3,NOW(),NOW()),(@q,'They convert equity into liabilities.',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (13,'Income derived from the primary business activity, such as service fees earned or sales revenue, is specifically classified as:','multiple_choice',5,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Other Income',0,1,NOW(),NOW()),(@q,'Non-Operating Income',0,2,NOW(),NOW()),(@q,'Capital',0,3,NOW(),NOW()),(@q,'Operating Revenue',1,4,NOW(),NOW());

-- =====================================================================
-- ASSESSMENT 14 — Lesson Quiz, CO3 Quiz2 Q6-10
-- =====================================================================
INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (14,'A trading business earns money from a bank savings account. How is this Interest Income classified?','multiple_choice',1,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Operating Revenue',0,1,NOW(),NOW()),(@q,'Other Income / Non-Operating Income',1,2,NOW(),NOW()),(@q,'Owner''s Equity',0,3,NOW(),NOW()),(@q,'Current Asset',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (14,'For a trading or merchandising business, what specific expense account represents the direct cost of the merchandise that was sold to customers?','multiple_choice',2,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Operating Expenses',0,1,NOW(),NOW()),(@q,'Miscellaneous Expense',0,2,NOW(),NOW()),(@q,'Cost of Goods Sold (COGS) / Cost of Sales',1,3,NOW(),NOW()),(@q,'Bad Debts Expense',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (14,'Regular business costs such as salaries, rent, utilities, and supplies are classified under:','multiple_choice',3,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Operating Expenses',1,1,NOW(),NOW()),(@q,'Cost of Goods Sold',0,2,NOW(),NOW()),(@q,'Non-Operating Expenses',0,3,NOW(),NOW()),(@q,'Capital',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (14,'Which of the following is an example of an Operating Revenue account for a service-based business?','multiple_choice',4,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Sales Revenue',0,1,NOW(),NOW()),(@q,'Interest Income',0,2,NOW(),NOW()),(@q,'Consultation Revenue',1,3,NOW(),NOW()),(@q,'Gain on Sale of Assets',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (14,'Which of the following is the primary Operating Revenue account for a trading or merchandising business?','multiple_choice',5,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Service Fees Earned',0,1,NOW(),NOW()),(@q,'Professional Fees',0,2,NOW(),NOW()),(@q,'Tuition Fees',0,3,NOW(),NOW()),(@q,'Sales Revenue (Sales)',1,4,NOW(),NOW());
