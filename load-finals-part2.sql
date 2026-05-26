-- AccuLearn: Replace assessments 1-27 with CO1-CO7 FINALS PDF questions
-- Part 2: Assessments 15-27

-- =====================================================================
-- ASSESSMENT 15 — Lesson Quiz, CO3 Quiz3 Q1-5 (COA)
-- =====================================================================
INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (15,'What is a Chart of Accounts (COA)?','multiple_choice',1,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'A graphical representation of the business''s profits and losses over the year',0,1,NOW(),NOW()),(@q,'A systematic list of all accounts used by a business, organized and numbered for easy identification',1,2,NOW(),NOW()),(@q,'A legal document submitted to the government for tax purposes',0,3,NOW(),NOW()),(@q,'A daily record of the owner''s personal expenses',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (15,'Which of the following is a primary reason why a Chart of Accounts is important to a business?','multiple_choice',2,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'It calculates the exact amount of taxes owed to the BIR.',0,1,NOW(),NOW()),(@q,'It provides a standardized reference system that ensures consistency in recording transactions.',1,2,NOW(),NOW()),(@q,'It acts as a substitute for official receipts and source documents.',0,3,NOW(),NOW()),(@q,'It legally separates the owner''s assets from the business''s assets.',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (15,'According to the standard numbering convention for small businesses in the Philippines, which account series is assigned to Assets?','multiple_choice',3,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'100-199',1,1,NOW(),NOW()),(@q,'200-299',0,2,NOW(),NOW()),(@q,'300-399',0,3,NOW(),NOW()),(@q,'400-499',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (15,'Under the standard numbering system, accounts numbered in the 200-299 series represent:','multiple_choice',4,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Owner''s Equity',0,1,NOW(),NOW()),(@q,'Expenses',0,2,NOW(),NOW()),(@q,'Income',0,3,NOW(),NOW()),(@q,'Liabilities',1,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (15,'If an accountant is looking for the Owner''s Capital and Owner''s Drawings accounts, which numbering series should they check?','multiple_choice',5,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'100-199',0,1,NOW(),NOW()),(@q,'300-399',1,2,NOW(),NOW()),(@q,'400-499',0,3,NOW(),NOW()),(@q,'500-599',0,4,NOW(),NOW());

-- =====================================================================
-- ASSESSMENT 16 — Module Quiz, CO4 Quiz1 Q1-15 (Books of Accounts)
-- =====================================================================
INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (16,'In the accounting process, the journal is commonly referred to as the:','multiple_choice',1,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Book of secondary entry',0,1,NOW(),NOW()),(@q,'Book of original entry',1,2,NOW(),NOW()),(@q,'Book of final entry',0,3,NOW(),NOW()),(@q,'Master ledger',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (16,'The ledger takes information from the journal and groups it by account. What is it commonly called?','multiple_choice',2,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Book of chronological entry',0,1,NOW(),NOW()),(@q,'Book of subsidiary entry',0,2,NOW(),NOW()),(@q,'Book of final entry',1,3,NOW(),NOW()),(@q,'Book of original entry',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (16,'How are business transactions organized when they are first recorded in the general journal?','multiple_choice',3,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Alphabetical order by account name',0,1,NOW(),NOW()),(@q,'By account type (Assets, Liabilities, etc.)',0,2,NOW(),NOW()),(@q,'By the amount of money involved, from highest to lowest',0,3,NOW(),NOW()),(@q,'Chronological (date) order',1,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (16,'How are transactions grouped and organized within the ledger?','multiple_choice',4,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'By account title',1,1,NOW(),NOW()),(@q,'Chronologically by day',0,2,NOW(),NOW()),(@q,'By the employee who recorded them',0,3,NOW(),NOW()),(@q,'By the source document number',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (16,'In the standard accounting cycle, what step corresponds to Journalizing?','multiple_choice',5,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Step 1',0,1,NOW(),NOW()),(@q,'Step 2',1,2,NOW(),NOW()),(@q,'Step 3',0,3,NOW(),NOW()),(@q,'Step 4',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (16,'Which specific step in the accounting cycle involves the ledger?','multiple_choice',6,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Step 1: Analyzing',0,1,NOW(),NOW()),(@q,'Step 2: Journalizing',0,2,NOW(),NOW()),(@q,'Step 3: Posting',1,3,NOW(),NOW()),(@q,'Step 4: Trial Balance',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (16,'What vital information does the ledger provide that the journal does NOT directly show?','multiple_choice',7,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'A brief explanation of the transaction',0,1,NOW(),NOW()),(@q,'The running balance of each account at any given time',1,2,NOW(),NOW()),(@q,'The chronological order of daily business events',0,3,NOW(),NOW()),(@q,'The names of the accounts to be debited and credited',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (16,'When writing an entry in the General Journal, how should the credited account be formatted?','multiple_choice',8,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Flush with the left margin, above the debited account',0,1,NOW(),NOW()),(@q,'Written in all capital letters on the same line as the debit',0,2,NOW(),NOW()),(@q,'Indented below the debited account',1,3,NOW(),NOW()),(@q,'Placed in the PR column',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (16,'When an entry is first being recorded in the General Journal, what should be done with the PR (Posting Reference) column?','multiple_choice',9,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'It should be filled with the account number.',0,1,NOW(),NOW()),(@q,'It should be filled with the journal page number.',0,2,NOW(),NOW()),(@q,'It should be left blank.',1,3,NOW(),NOW()),(@q,'It should contain the check or invoice number.',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (16,'When does the accountant finally fill in the PR column in the General Journal?','multiple_choice',10,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'At the exact moment the transaction occurs',0,1,NOW(),NOW()),(@q,'At the end of the year when preparing financial statements',0,2,NOW(),NOW()),(@q,'Only after the entry has been completely posted to the ledger',1,3,NOW(),NOW()),(@q,'When the business owner approves the transaction',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (16,'A journal entry that involves only two accounts — one debit and one credit — is called a:','multiple_choice',11,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Simple Entry',1,1,NOW(),NOW()),(@q,'Compound Entry',0,2,NOW(),NOW()),(@q,'Adjusting Entry',0,3,NOW(),NOW()),(@q,'Closing Entry',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (16,'A journal entry that involves three or more accounts in a single transaction is called a:','multiple_choice',12,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'General Entry',0,1,NOW(),NOW()),(@q,'Compound Entry',1,2,NOW(),NOW()),(@q,'Complex Entry',0,3,NOW(),NOW()),(@q,'Subsidiary Entry',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (16,'A business purchased a motorcycle worth P110,000, paying P80,000 in cash and leaving the remaining P30,000 on account. What type of journal entry is required for this transaction?','multiple_choice',13,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Simple Entry',0,1,NOW(),NOW()),(@q,'Reversing Entry',0,2,NOW(),NOW()),(@q,'Compound Entry',1,3,NOW(),NOW()),(@q,'Closing Entry',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (16,'Why is the journal considered absolutely necessary in the accounting process?','multiple_choice',14,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'It calculates the final net income for the year.',0,1,NOW(),NOW()),(@q,'It replaces the need to keep physical source documents like receipts.',0,2,NOW(),NOW()),(@q,'It shows the running balance of cash available to spend.',0,3,NOW(),NOW()),(@q,'It provides a complete, dated record of every transaction, essential for traceability and auditing.',1,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (16,'Why is the ledger essential for preparing reports like the Trial Balance, Income Statement, and Balance Sheet?','multiple_choice',15,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Because it lists all transactions by date.',0,1,NOW(),NOW()),(@q,'Because it contains the necessary explanations for every transaction.',0,2,NOW(),NOW()),(@q,'Because it provides the updated account balances needed for these reports.',1,3,NOW(),NOW()),(@q,'Because it is the only book the BIR reviews.',0,4,NOW(),NOW());

-- =====================================================================
-- ASSESSMENT 17 — Lesson Quiz, CO4 Quiz2 Q1-5 (Special Journals)
-- =====================================================================
INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (17,'What is the primary reason a business uses special journals in addition to the general journal?','multiple_choice',1,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'To completely replace the general ledger',0,1,NOW(),NOW()),(@q,'To record transactions that do not involve money',0,2,NOW(),NOW()),(@q,'To handle high-volume recurring transactions more efficiently and reduce repetition',1,3,NOW(),NOW()),(@q,'To prepare the trial balance directly without posting',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (17,'Which special journal is used exclusively to record sales of goods or services made on credit?','multiple_choice',2,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Cash Receipts Journal',0,1,NOW(),NOW()),(@q,'Purchases Journal',0,2,NOW(),NOW()),(@q,'General Journal',0,3,NOW(),NOW()),(@q,'Sales Journal',1,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (17,'A business sells merchandise to a customer who pays in cash immediately. In which journal should this transaction be recorded?','multiple_choice',3,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Cash Receipts Journal',1,1,NOW(),NOW()),(@q,'Sales Journal',0,2,NOW(),NOW()),(@q,'Cash Disbursements Journal',0,3,NOW(),NOW()),(@q,'General Journal',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (17,'What is the typical source document that serves as the basis for recording a transaction in the Sales Journal?','multiple_choice',4,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Check Voucher',0,1,NOW(),NOW()),(@q,'Official Receipt',0,2,NOW(),NOW()),(@q,'Sales Invoice / Charge Invoice',1,3,NOW(),NOW()),(@q,'Purchase Invoice',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (17,'Which journal is used to record all purchases of merchandise or supplies made specifically on account?','multiple_choice',5,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Purchases Journal',1,1,NOW(),NOW()),(@q,'Sales Journal',0,2,NOW(),NOW()),(@q,'Cash Disbursements Journal',0,3,NOW(),NOW()),(@q,'General Journal',0,4,NOW(),NOW());

-- =====================================================================
-- ASSESSMENT 18 — Lesson Quiz, CO4 Quiz3 Q1-5 (Ledger and Posting)
-- =====================================================================
INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (18,'In accounting terminology, the ledger is most commonly referred to as the:','multiple_choice',1,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Book of original entry',0,1,NOW(),NOW()),(@q,'Book of secondary entry / final entry',1,2,NOW(),NOW()),(@q,'Source document',0,3,NOW(),NOW()),(@q,'General journal',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (18,'While the journal records transactions in chronological order, how does the general ledger organize transactions?','multiple_choice',2,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'By date',0,1,NOW(),NOW()),(@q,'By source document number',0,2,NOW(),NOW()),(@q,'By account title',1,3,NOW(),NOW()),(@q,'By the amount of money',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (18,'What is the primary financial report that is directly prepared using the balances found in the general ledger?','multiple_choice',3,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Trial Balance',1,1,NOW(),NOW()),(@q,'Bank Reconciliation',0,2,NOW(),NOW()),(@q,'Cash Disbursements Report',0,3,NOW(),NOW()),(@q,'Chart of Accounts',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (18,'What is the definition of Posting in the accounting cycle?','multiple_choice',4,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Recording a transaction from a receipt into the general journal',0,1,NOW(),NOW()),(@q,'The process of transferring data from the journal to the ledger',1,2,NOW(),NOW()),(@q,'Preparing the final financial statements for the owner',0,3,NOW(),NOW()),(@q,'Approving a check voucher for payment',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (18,'Posting represents which specific step in the standard accounting cycle?','multiple_choice',5,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Step 1',0,1,NOW(),NOW()),(@q,'Step 2',0,2,NOW(),NOW()),(@q,'Step 3',1,3,NOW(),NOW()),(@q,'Step 5',0,4,NOW(),NOW());

-- =====================================================================
-- ASSESSMENT 19 — Module Quiz, CO5 Quiz1 Q1-15 (Transaction Analysis)
-- =====================================================================
INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (19,'What is a business transaction?','multiple_choice',1,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Any activity planned or discussed by the owner',0,1,NOW(),NOW()),(@q,'Any economic event that affects assets, liabilities, or owner''s equity and can be measured in money',1,2,NOW(),NOW()),(@q,'Any purchase made by the business, whether or not it involves money',0,3,NOW(),NOW()),(@q,'Any communication between the business and its customers',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (19,'Which of the following is considered a business transaction?','multiple_choice',2,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'The owner thinks about buying a new delivery vehicle',0,1,NOW(),NOW()),(@q,'The business owner attends a trade seminar',0,2,NOW(),NOW()),(@q,'The business pays a monthly utility bill',1,3,NOW(),NOW()),(@q,'The business hires a new employee but work has not yet started',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (19,'Why is hiring a new employee NOT immediately considered a business transaction?','multiple_choice',3,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Because employees are not part of the accounting equation',0,1,NOW(),NOW()),(@q,'Because no financial exchange has yet occurred',1,2,NOW(),NOW()),(@q,'Because labor costs are not measurable',0,3,NOW(),NOW()),(@q,'Because the hiring document is not a source document',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (19,'What is the purpose of a source document in the accounting process?','multiple_choice',4,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'To record the final balance of all accounts',0,1,NOW(),NOW()),(@q,'To organize transactions by account type',0,2,NOW(),NOW()),(@q,'To serve as written evidence that a transaction has occurred',1,3,NOW(),NOW()),(@q,'To summarize all transactions for the month',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (19,'A business renders services and gives the customer proof of payment. What source document is issued?','multiple_choice',5,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Purchase invoice',0,1,NOW(),NOW()),(@q,'Bank deposit slip',0,2,NOW(),NOW()),(@q,'Official receipt',1,3,NOW(),NOW()),(@q,'Voucher',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (19,'Which source document would a business receive when it buys supplies on credit from a supplier?','multiple_choice',6,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Official receipt',0,1,NOW(),NOW()),(@q,'Check',0,2,NOW(),NOW()),(@q,'Bank deposit slip',0,3,NOW(),NOW()),(@q,'Purchase invoice',1,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (19,'A business deposits cash into its bank account. What source document is used?','multiple_choice',7,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Sales invoice',0,1,NOW(),NOW()),(@q,'Bank deposit slip',1,2,NOW(),NOW()),(@q,'Check',0,3,NOW(),NOW()),(@q,'Promissory note',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (19,'A check is best described as:','multiple_choice',8,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'A written order to the bank to pay a specified amount',1,1,NOW(),NOW()),(@q,'A record of goods received from a supplier',0,2,NOW(),NOW()),(@q,'A document issued when revenue is earned on credit',0,3,NOW(),NOW()),(@q,'An internal form for approving purchases',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (19,'During Step 1 of the accounting cycle, what is the accountant''s first question for any transaction?','multiple_choice',9,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'What is the total revenue for the period?',0,1,NOW(),NOW()),(@q,'Which journal page should this entry go on?',0,2,NOW(),NOW()),(@q,'What accounts are affected by this transaction?',1,3,NOW(),NOW()),(@q,'What is the ending balance of the cash account?',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (19,'The accounting equation is best stated as:','multiple_choice',10,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Assets = Revenue + Expenses',0,1,NOW(),NOW()),(@q,'Assets = Liabilities + Owner''s Equity',1,2,NOW(),NOW()),(@q,'Assets + Liabilities = Owner''s Equity',0,3,NOW(),NOW()),(@q,'Revenue - Expenses = Assets',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (19,'When a business owner invests cash into the business, which accounts are affected?','multiple_choice',11,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Cash increases; Liabilities increase',0,1,NOW(),NOW()),(@q,'Cash increases; Owner''s Equity (Capital) increases',1,2,NOW(),NOW()),(@q,'Cash decreases; Owner''s Equity decreases',0,3,NOW(),NOW()),(@q,'Equipment increases; Cash decreases',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (19,'A business buys office supplies with cash. How does this affect the accounting equation?','multiple_choice',12,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Assets increase and liabilities increase',0,1,NOW(),NOW()),(@q,'Assets decrease and equity decreases',0,2,NOW(),NOW()),(@q,'One asset (Supplies) increases; another asset (Cash) decreases - net effect is zero',1,3,NOW(),NOW()),(@q,'Assets increase and equity increases',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (19,'A business purchases equipment on account (credit). What is the effect on the accounting equation?','multiple_choice',13,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Assets increase; Owner''s Equity increases',0,1,NOW(),NOW()),(@q,'Assets increase; Liabilities increase',1,2,NOW(),NOW()),(@q,'Assets decrease; Liabilities decrease',0,3,NOW(),NOW()),(@q,'No effect - no cash was paid',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (19,'When a business earns revenue from services and collects cash immediately, which effect on the equation is correct?','multiple_choice',14,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Assets decrease; Owner''s Equity decreases',0,1,NOW(),NOW()),(@q,'Liabilities increase; Owner''s Equity decreases',0,2,NOW(),NOW()),(@q,'Assets (Cash) increase; Owner''s Equity (Revenue) increases',1,3,NOW(),NOW()),(@q,'Only the Cash account changes',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (19,'Paying an expense in cash has what effect on the accounting equation?','multiple_choice',15,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Assets increase; Liabilities decrease',0,1,NOW(),NOW()),(@q,'Assets (Cash) decrease; Owner''s Equity decreases',1,2,NOW(),NOW()),(@q,'Liabilities increase; Owner''s Equity increases',0,3,NOW(),NOW()),(@q,'No effect since it is an internal transaction',0,4,NOW(),NOW());

-- =====================================================================
-- ASSESSMENT 20 — Lesson Quiz, CO5 Quiz2 Q1-5 (Journalizing)
-- =====================================================================
INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (20,'The general journal is best described as the:','multiple_choice',1,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Book of final entry for all account balances',0,1,NOW(),NOW()),(@q,'Book of original entry that records transactions in chronological order',1,2,NOW(),NOW()),(@q,'Summary of all revenue and expenses for the period',0,3,NOW(),NOW()),(@q,'Record of each individual account''s running balance',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (20,'In a journal entry, which account is written first?','multiple_choice',2,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'The account with the larger amount',0,1,NOW(),NOW()),(@q,'The credited account',0,2,NOW(),NOW()),(@q,'The debited account',1,3,NOW(),NOW()),(@q,'The asset account, regardless of debit or credit',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (20,'How is the credited account formatted in a journal entry?','multiple_choice',3,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Flush with the left margin, above the debited account',0,1,NOW(),NOW()),(@q,'Indented below the debited account',1,2,NOW(),NOW()),(@q,'Written on the same line as the debited account',0,3,NOW(),NOW()),(@q,'Written in all capital letters',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (20,'Which element is NOT part of a standard journal entry?','multiple_choice',4,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'The date of the transaction',0,1,NOW(),NOW()),(@q,'A brief narration or explanation',0,2,NOW(),NOW()),(@q,'The account numbers in the P.R. column',0,3,NOW(),NOW()),(@q,'The ending balance of every account affected',1,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (20,'According to the debit and credit rules, which accounts have a normal DEBIT balance?','multiple_choice',5,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Assets, Liabilities, and Capital',0,1,NOW(),NOW()),(@q,'Assets, Drawings, and Expenses',1,2,NOW(),NOW()),(@q,'Revenues, Capital, and Liabilities',0,3,NOW(),NOW()),(@q,'Expenses, Revenues, and Capital',0,4,NOW(),NOW());

-- =====================================================================
-- ASSESSMENT 21 — Lesson Quiz, CO5 Quiz3 Q1-5 (Posting)
-- =====================================================================
INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (21,'The general ledger is best described as the:','multiple_choice',1,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Book of original entry that records transactions in time order',0,1,NOW(),NOW()),(@q,'Book of final entry that organizes transactions by account',1,2,NOW(),NOW()),(@q,'A summary of all revenue collected during the period',0,3,NOW(),NOW()),(@q,'A list of all source documents for the period',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (21,'What is the main difference between the general journal and the general ledger?','multiple_choice',2,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'The journal is used for assets; the ledger is used for liabilities',0,1,NOW(),NOW()),(@q,'The journal records transactions chronologically; the ledger organizes them by account',1,2,NOW(),NOW()),(@q,'The journal is more accurate than the ledger',0,3,NOW(),NOW()),(@q,'The ledger is prepared before the journal',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (21,'What does ''posting'' mean in accounting?','multiple_choice',3,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Writing a narration below each journal entry',0,1,NOW(),NOW()),(@q,'Transferring each debit and credit from the journal to the appropriate ledger account',1,2,NOW(),NOW()),(@q,'Copying the entire journal entry into the ledger',0,3,NOW(),NOW()),(@q,'Summarizing all accounts into the trial balance',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (21,'What is the first action taken when posting a journal entry to the ledger?','multiple_choice',4,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Compute the new running balance',0,1,NOW(),NOW()),(@q,'Write the journal page reference in the ledger''s P.R. column',0,2,NOW(),NOW()),(@q,'Enter the date of the journal entry in the ledger account',1,3,NOW(),NOW()),(@q,'Fill in the account number in the journal''s P.R. column',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (21,'What is written in the ledger''s P.R. (Posting Reference) column?','multiple_choice',5,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'The account number',0,1,NOW(),NOW()),(@q,'The date of posting',0,2,NOW(),NOW()),(@q,'The journal page reference (e.g., J1)',1,3,NOW(),NOW()),(@q,'The name of the account',0,4,NOW(),NOW());

-- =====================================================================
-- ASSESSMENT 22 — Module Quiz, CO6 Quiz1 Q1-15 (Trial Balance)
-- =====================================================================
INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (22,'What is the primary purpose of the trial balance in the accounting cycle?','multiple_choice',1,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'To record all business transactions for the period',0,1,NOW(),NOW()),(@q,'To verify that total debits equal total credits across all ledger accounts',1,2,NOW(),NOW()),(@q,'To prepare the income statement and balance sheet',0,3,NOW(),NOW()),(@q,'To identify which accounts need to be adjusted',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (22,'The trial balance is prepared at which point in the accounting cycle?','multiple_choice',2,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Before any transactions are journalized',0,1,NOW(),NOW()),(@q,'After journalizing but before posting to the ledger',0,2,NOW(),NOW()),(@q,'After all transactions have been journalized and posted to the ledger',1,3,NOW(),NOW()),(@q,'After the financial statements are completed',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (22,'In what order are accounts listed in the trial balance?','multiple_choice',3,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Alphabetical order, from A to Z',0,1,NOW(),NOW()),(@q,'By account number in descending order',0,2,NOW(),NOW()),(@q,'In the same order they appear in the general ledger; assets first, then liabilities, equity, revenues, and expenses',1,3,NOW(),NOW()),(@q,'Revenue and expense accounts first, followed by assets and liabilities',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (22,'How does the peso sign appear in the trial balance?','multiple_choice',4,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Beside every figure in both the debit and credit columns',0,1,NOW(),NOW()),(@q,'Only beside the first figure in each column and beside the totals',1,2,NOW(),NOW()),(@q,'Only beside the total at the bottom of each column',0,3,NOW(),NOW()),(@q,'It is not used in the trial balance',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (22,'What does it mean to double-rule the totals at the bottom of the trial balance?','multiple_choice',5,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Add a footnote below each total',0,1,NOW(),NOW()),(@q,'Place two underlines below both column totals to indicate the trial balance is finalized and balanced',1,2,NOW(),NOW()),(@q,'Recalculate each total twice to make sure they are correct',0,3,NOW(),NOW()),(@q,'Write the totals in bold and italic format',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (22,'How many times does each account appear in the trial balance?','multiple_choice',6,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Twice - once in the debit and once in the credit column',0,1,NOW(),NOW()),(@q,'As many times as the account was used in journal entries',0,2,NOW(),NOW()),(@q,'Only once - with either a debit or credit balance, never both',1,3,NOW(),NOW()),(@q,'It depends on the number of transactions in the account',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (22,'An account has a zero balance at the end of the period. How should it be treated in the trial balance?','multiple_choice',7,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Listed with a zero in the debit column',0,1,NOW(),NOW()),(@q,'Listed with a zero in the credit column',0,2,NOW(),NOW()),(@q,'Listed with a dash to show it was considered',0,3,NOW(),NOW()),(@q,'Skipped entirely - accounts with zero balances are not listed',1,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (22,'A balanced trial balance means that:','multiple_choice',8,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'All transactions were recorded to the correct accounts',0,1,NOW(),NOW()),(@q,'No transactions were omitted from the journal',0,2,NOW(),NOW()),(@q,'The total of the debit column equals the total of the credit column',1,3,NOW(),NOW()),(@q,'The financial statements are ready to be distributed',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (22,'Which of the following types of errors would NOT be detected by the trial balance?','multiple_choice',9,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'A debit entry posted without its corresponding credit',0,1,NOW(),NOW()),(@q,'A transaction recorded twice - both as debit and credit',0,2,NOW(),NOW()),(@q,'A transaction posted to the wrong account but with the correct amount',1,3,NOW(),NOW()),(@q,'A credit entry posted to the debit column of a ledger account',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (22,'The heading of the trial balance must include which three elements?','multiple_choice',10,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Name of the accountant, date, and total debits',0,1,NOW(),NOW()),(@q,'Name of the business, Trial Balance, and the specific date',1,2,NOW(),NOW()),(@q,'Name of the business, period covered, and type of industry',0,3,NOW(),NOW()),(@q,'Account titles, normal balances, and adjusted amounts',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (22,'Which account has a normal DEBIT balance?','multiple_choice',11,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Accounts Payable',0,1,NOW(),NOW()),(@q,'Owner''s Capital',0,2,NOW(),NOW()),(@q,'Service Revenue',0,3,NOW(),NOW()),(@q,'Prepaid Rent',1,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (22,'Which of the following accounts belongs in the CREDIT column of the trial balance?','multiple_choice',12,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Salaries Expense',0,1,NOW(),NOW()),(@q,'Office Equipment',0,2,NOW(),NOW()),(@q,'Owner''s Drawings',0,3,NOW(),NOW()),(@q,'Unearned Revenue',1,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (22,'Why is Accumulated Depreciation placed in the CREDIT column of the trial balance?','multiple_choice',13,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Because it represents income earned by the business',0,1,NOW(),NOW()),(@q,'Because it is a liability owed to suppliers',0,2,NOW(),NOW()),(@q,'Because it is a contra-asset account with a normal credit balance',1,3,NOW(),NOW()),(@q,'Because all long-term accounts have a credit balance',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (22,'Owner''s Drawings is placed in the DEBIT column of the trial balance because:','multiple_choice',14,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'It is classified as an expense of the business',0,1,NOW(),NOW()),(@q,'It has a normal debit balance as a contra-equity account',1,2,NOW(),NOW()),(@q,'It always involves a cash payment',0,3,NOW(),NOW()),(@q,'It increases the owner''s capital account',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (22,'Why is Unearned Revenue placed in the CREDIT column despite having the word revenue in its name?','multiple_choice',15,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Because it will be earned in a future period and is classified as revenue',0,1,NOW(),NOW()),(@q,'Because revenue accounts always have a credit balance regardless of type',0,2,NOW(),NOW()),(@q,'Because it is a liability - the service or product has not yet been delivered to the customer',1,3,NOW(),NOW()),(@q,'Because it is an asset that has been collected in advance',0,4,NOW(),NOW());

-- =====================================================================
-- ASSESSMENT 23 — Lesson Quiz, CO6 Quiz2 Q1-5 (Adjusting Entries)
-- =====================================================================
INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (23,'What is the main purpose of adjusting entries?','multiple_choice',1,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'To correct mathematical errors in the journal',0,1,NOW(),NOW()),(@q,'To update account balances at the end of the period so revenues and expenses are properly matched before financial statements are prepared',1,2,NOW(),NOW()),(@q,'To close all temporary accounts at year-end',0,3,NOW(),NOW()),(@q,'To transfer balances from the ledger to the trial balance',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (23,'Adjusting entries are made at:','multiple_choice',2,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'The beginning of each accounting period',0,1,NOW(),NOW()),(@q,'Any time a transaction occurs during the period',0,2,NOW(),NOW()),(@q,'The end of the accounting period, before financial statements are prepared',1,3,NOW(),NOW()),(@q,'The end of the accounting period, after financial statements are completed',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (23,'Which accounting principle requires that revenues be recorded in the period they are earned and expenses in the period they are incurred?','multiple_choice',3,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Cost principle',0,1,NOW(),NOW()),(@q,'Going concern principle',0,2,NOW(),NOW()),(@q,'Accrual basis and matching principle',1,3,NOW(),NOW()),(@q,'Objectivity principle',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (23,'Every adjusting entry must affect at least:','multiple_choice',4,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Two asset accounts',0,1,NOW(),NOW()),(@q,'One income statement account and one balance sheet account',1,2,NOW(),NOW()),(@q,'One cash account and one revenue account',0,3,NOW(),NOW()),(@q,'Two liability accounts',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (23,'Which account is NEVER part of an adjusting entry?','multiple_choice',5,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Prepaid Insurance',0,1,NOW(),NOW()),(@q,'Salaries Payable',0,2,NOW(),NOW()),(@q,'Cash',1,3,NOW(),NOW()),(@q,'Unearned Revenue',0,4,NOW(),NOW());

-- =====================================================================
-- ASSESSMENT 24 — Lesson Quiz, CO6 Summative Q1-5
-- =====================================================================
INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (24,'Which step of the accounting cycle directly follows Step 4 (Trial Balance)?','multiple_choice',1,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Preparing the financial statements',0,1,NOW(),NOW()),(@q,'Journalizing and posting all transactions',0,2,NOW(),NOW()),(@q,'Journalizing and posting adjusting entries',1,3,NOW(),NOW()),(@q,'Closing temporary accounts',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (24,'Which of the following statements about the trial balance is TRUE?','multiple_choice',2,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'A balanced trial balance confirms all transactions were recorded to the correct accounts',0,1,NOW(),NOW()),(@q,'A balanced trial balance only confirms mathematical equality of total debits and credits',1,2,NOW(),NOW()),(@q,'The trial balance lists only asset and liability accounts',0,3,NOW(),NOW()),(@q,'The trial balance is prepared after the financial statements',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (24,'Where should Accumulated Depreciation - Equipment be placed in the trial balance?','multiple_choice',3,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Debit column, because it relates to equipment (an asset)',0,1,NOW(),NOW()),(@q,'Credit column, because it is a contra-asset account',1,2,NOW(),NOW()),(@q,'Debit column, because all depreciation is an expense',0,3,NOW(),NOW()),(@q,'It is not listed in the trial balance',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (24,'Which of the following accounts belongs in the DEBIT column of the trial balance?','multiple_choice',4,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Salaries Payable',0,1,NOW(),NOW()),(@q,'Consulting Fees Earned',0,2,NOW(),NOW()),(@q,'Owner''s Capital',0,3,NOW(),NOW()),(@q,'Insurance Expense',1,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (24,'Which type of adjusting entry would a business record if its employees earned wages during the last week of the month but will not be paid until next month?','multiple_choice',5,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Prepaid Expense',0,1,NOW(),NOW()),(@q,'Unearned Revenue',0,2,NOW(),NOW()),(@q,'Accrued Expense',1,3,NOW(),NOW()),(@q,'Depreciation',0,4,NOW(),NOW());

-- =====================================================================
-- ASSESSMENT 25 — Module Quiz, CO7 Quiz1 Q1-15 (Adjusted Trial Balance)
-- =====================================================================
INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (25,'The accounting cycle is best described as:','multiple_choice',1,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'A one-time process done when a business closes down',0,1,NOW(),NOW()),(@q,'A systematic set of steps a business follows every accounting period',1,2,NOW(),NOW()),(@q,'A method used only by large corporations',0,3,NOW(),NOW()),(@q,'A summary prepared only at the end of the year',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (25,'How many steps make up the complete accounting cycle?','multiple_choice',2,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'5',0,1,NOW(),NOW()),(@q,'7',0,2,NOW(),NOW()),(@q,'8',0,3,NOW(),NOW()),(@q,'10',1,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (25,'Which phase of the accounting cycle covers Steps 1 through 5?','multiple_choice',3,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Communicating phase',0,1,NOW(),NOW()),(@q,'Summarizing phase',0,2,NOW(),NOW()),(@q,'Recording phase',1,3,NOW(),NOW()),(@q,'Reporting phase',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (25,'Preparing the adjusted trial balance belongs to which phase of the accounting cycle?','multiple_choice',4,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Recording phase',0,1,NOW(),NOW()),(@q,'Communicating phase',0,2,NOW(),NOW()),(@q,'Summarizing phase',1,3,NOW(),NOW()),(@q,'Analyzing phase',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (25,'What is the correct order of the first three steps in the accounting cycle?','multiple_choice',5,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Journalizing -> Posting to ledger -> Analyzing transactions',0,1,NOW(),NOW()),(@q,'Analyzing transactions -> Journalizing -> Posting to ledger',1,2,NOW(),NOW()),(@q,'Posting to ledger -> Analyzing transactions -> Journalizing',0,3,NOW(),NOW()),(@q,'Journalizing -> Analyzing transactions -> Preparing trial balance',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (25,'Which step immediately follows the preparation of the trial balance in the accounting cycle?','multiple_choice',6,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Preparing the adjusted trial balance',0,1,NOW(),NOW()),(@q,'Preparing financial statements',0,2,NOW(),NOW()),(@q,'Journalizing and posting adjusting entries',1,3,NOW(),NOW()),(@q,'Preparing closing entries',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (25,'Preparing the financial statements is categorized under which phase?','multiple_choice',7,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Recording phase',0,1,NOW(),NOW()),(@q,'Communicating phase',1,2,NOW(),NOW()),(@q,'Summarizing phase',0,3,NOW(),NOW()),(@q,'Adjusting phase',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (25,'What is the LAST step of the complete accounting cycle?','multiple_choice',8,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Preparing the post-closing trial balance',0,1,NOW(),NOW()),(@q,'Preparing financial statements',0,2,NOW(),NOW()),(@q,'Journalizing closing entries',0,3,NOW(),NOW()),(@q,'Preparing reversing journal entries',1,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (25,'The final five steps of the accounting cycle form the:','multiple_choice',9,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Recording and analyzing phase',0,1,NOW(),NOW()),(@q,'Summarizing and communicating phase',1,2,NOW(),NOW()),(@q,'Journalizing and posting phase',0,3,NOW(),NOW()),(@q,'Adjusting and balancing phase',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (25,'An adjusted trial balance is defined as:','multiple_choice',10,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'A list of accounts before adjusting entries are recorded',0,1,NOW(),NOW()),(@q,'A list of all accounts after recording and posting the adjusting entries',1,2,NOW(),NOW()),(@q,'A statement that shows revenues and expenses only',0,3,NOW(),NOW()),(@q,'A balance sheet prepared at mid-year',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (25,'The adjusted trial balance is prepared to reflect:','multiple_choice',11,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Original account balances without any changes',0,1,NOW(),NOW()),(@q,'Updated and accurate balances of all accounts before financial statements are prepared',1,2,NOW(),NOW()),(@q,'Only the asset and liability accounts',0,3,NOW(),NOW()),(@q,'The closing entries for the period',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (25,'When preparing the adjusted trial balance using a worksheet, where are account balances from the trial balance first entered?','multiple_choice',12,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'The Adjusted Trial Balance columns',0,1,NOW(),NOW()),(@q,'The Income Statement columns',0,2,NOW(),NOW()),(@q,'The first two columns (Debit and Credit)',1,3,NOW(),NOW()),(@q,'The Adjustments column',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (25,'In a worksheet, adjusting entries are entered in which columns?','multiple_choice',13,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'First and second columns',0,1,NOW(),NOW()),(@q,'Third and fourth columns',1,2,NOW(),NOW()),(@q,'Fifth and sixth columns',0,3,NOW(),NOW()),(@q,'Seventh and eighth columns',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (25,'When a trial balance amount and an adjustment are BOTH on the debit side, what should be done to compute the adjusted balance?','multiple_choice',14,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Subtract the smaller from the larger',0,1,NOW(),NOW()),(@q,'Carry over only the trial balance amount',0,2,NOW(),NOW()),(@q,'Add the two amounts together',1,3,NOW(),NOW()),(@q,'Disregard the adjustment',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (25,'If a trial balance amount is on the debit side and an adjustment is on the credit side, what is the correct procedure?','multiple_choice',15,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Add both amounts and place on the debit side',0,1,NOW(),NOW()),(@q,'Subtract the smaller from the larger and enter on the side of the greater amount',1,2,NOW(),NOW()),(@q,'Enter only the adjustment amount in the adjusted trial balance',0,3,NOW(),NOW()),(@q,'Move both amounts to the credit side',0,4,NOW(),NOW());

-- =====================================================================
-- ASSESSMENT 26 — Lesson Quiz, CO7 Quiz3 Q1-5 (Closing Entries)
-- =====================================================================
INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (26,'Closing entries are journal entries made at the end of an accounting period to transfer the balances of all nominal accounts to:','multiple_choice',1,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'The income summary, then ultimately to the capital account',1,1,NOW(),NOW()),(@q,'The accounts receivable account',0,2,NOW(),NOW()),(@q,'The adjusted trial balance directly',0,3,NOW(),NOW()),(@q,'A separate closing ledger',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (26,'What is the main purpose of preparing closing entries?','multiple_choice',2,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'To create the adjusted trial balance',0,1,NOW(),NOW()),(@q,'To reset temporary accounts to zero for the next accounting period',1,2,NOW(),NOW()),(@q,'To update asset values to current market prices',0,3,NOW(),NOW()),(@q,'To record transactions that were missed during the period',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (26,'Which of the following are classified as nominal (temporary) accounts?','multiple_choice',3,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Cash, Equipment, Accounts Payable',0,1,NOW(),NOW()),(@q,'Revenues, Expenses, and Drawing/Withdrawal',1,2,NOW(),NOW()),(@q,'Capital, Buildings, and Notes Payable',0,3,NOW(),NOW()),(@q,'Accumulated Depreciation and Prepaid Expenses',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (26,'Which of the following are classified as real (permanent) accounts?','multiple_choice',4,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Revenues, Expenses, and Drawing',0,1,NOW(),NOW()),(@q,'Income Summary, Salaries Expense, and Service Revenue',0,2,NOW(),NOW()),(@q,'Assets, Liabilities, and Capital',1,3,NOW(),NOW()),(@q,'All accounts in the adjusted trial balance',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (26,'Real (permanent) accounts are:','multiple_choice',5,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Closed to zero at the end of every accounting period',0,1,NOW(),NOW()),(@q,'Transferred to the income summary account',0,2,NOW(),NOW()),(@q,'Accounts that carry over to the next period and are NOT closed',1,3,NOW(),NOW()),(@q,'Only the revenue accounts',0,4,NOW(),NOW());

-- =====================================================================
-- ASSESSMENT 27 — Lesson Quiz, CO7 Quiz5 Formative Q1-5 (Reversing Entries)
-- =====================================================================
INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (27,'Reversing entries are journal entries that are:','multiple_choice',1,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Made at the end of the accounting period to close nominal accounts',0,1,NOW(),NOW()),(@q,'Made at the beginning of the next accounting period and are the exact opposite of certain adjusting entries',1,2,NOW(),NOW()),(@q,'Made during the period to record regular business transactions',0,3,NOW(),NOW()),(@q,'Made to correct errors in the general journal',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (27,'Reversing entries are described in the lesson as:','multiple_choice',2,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Mandatory for all businesses',0,1,NOW(),NOW()),(@q,'Optional but helpful in simplifying bookkeeping',1,2,NOW(),NOW()),(@q,'Required by law for all corporations',0,3,NOW(),NOW()),(@q,'Always prepared for depreciation entries',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (27,'What problem do reversing entries solve, according to the lesson?','multiple_choice',3,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'They correct arithmetic errors in the ledger',0,1,NOW(),NOW()),(@q,'They eliminate the need to prepare closing entries',0,2,NOW(),NOW()),(@q,'They eliminate the need to split payments or receipts in the new period',1,3,NOW(),NOW()),(@q,'They replace the need for adjusting entries',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (27,'Prepayments recorded using the EXPENSE METHOD require a reversing entry because:','multiple_choice',4,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'The asset was never recorded',0,1,NOW(),NOW()),(@q,'The original payment was debited to an expense account',1,2,NOW(),NOW()),(@q,'Expenses must always be reversed',0,3,NOW(),NOW()),(@q,'It is required under all accounting methods',0,4,NOW(),NOW());

INSERT INTO assessment_questions (assessment_id,question_text,question_type,sequence_order,is_active,created_at,updated_at) VALUES (27,'Precollections recorded using the REVENUE METHOD require a reversing entry because:','multiple_choice',5,1,NOW(),NOW());
SET @q=LAST_INSERT_ID();
INSERT INTO assessment_answers (question_id,answer_text,is_correct,sequence_order,created_at,updated_at) VALUES (@q,'Cash was not yet received',0,1,NOW(),NOW()),(@q,'The original receipt was credited directly to a revenue account',1,2,NOW(),NOW()),(@q,'Revenue must always be reversed at year end',0,3,NOW(),NOW()),(@q,'The liability method was used',0,4,NOW(),NOW());
