<?php
/**
 * One-off: split every AccuLearn quiz bank into Easy / Medium / Hard pools,
 * point the quizzes' random slots at them, and give the summative quizzes a
 * 50-minute time limit. Run 2026-10-05.
 *
 *   sudo -u www-data php local/acculearnfeedback/cli/difficulty_pools.php          dry run
 *   sudo -u www-data php local/acculearnfeedback/cli/difficulty_pools.php --apply
 *
 * The difficulty of each question comes from the three
 * "AccuLearn-Question-Categories" PDFs (CO1-CO2, CO3-CO4, CO5-CO7). Their
 * answer keys number questions by their position in the original bank, which
 * is the bank question's name ("Question 7"); every entry was checked against
 * the live bank's correct answer before this was written (716 of 720 letters
 * matched, the other 4 are letter typos in the PDF keys, not misplacements).
 * Q20 of CO7 Formative 5 is missing from the PDFs; it is a definition
 * question and is filed as Easy.
 *
 * The CO3 and CO4 "Advanced Conceptual Questions" are new questions, added to
 * the summative Hard pools (otherwise 3 questions each).
 *
 * Pools the PDFs leave short are left short, as decided on 2026-10-05:
 * question_pools::allocate() moves the missing draws to a neighbouring pool
 * until a teacher adds questions.
 *
 * Re-running is safe: categories, moves and new questions are skipped when
 * already present.
 */
define('CLI_SCRIPT', true);
require(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/clilib.php');
require_once($CFG->libdir . '/questionlib.php');
require_once($CFG->dirroot . '/mod/quiz/locallib.php');

use local_acculearnfeedback\question_pools;

[$options] = cli_get_params(['apply' => false, 'help' => false], ['h' => 'help']);
if ($options['help']) {
    echo "Dry run by default; pass --apply to make the changes.\n";
    exit(0);
}
$apply = $options['apply'];
\core\session\manager::set_user(get_admin());

// Bank category id => difficulty => question numbers ("Question N").
$difficulties = [
    14 => ['easy' => [1, 2, 4, 5, 6, 7, 9, 13, 15, 17, 18], 'medium' => [3, 8, 10, 11, 12, 14, 16, 19, 20], 'hard' => []], // CO1 Formative 1
    17 => ['easy' => [1, 3, 5, 6, 7, 8, 10, 16, 17, 18, 19], 'medium' => [2, 9, 11, 12, 13, 14, 15, 20], 'hard' => [4]], // CO1 Formative 2
    20 => ['easy' => [1, 3, 6, 9, 11, 12, 15, 18], 'medium' => [2, 5, 8, 10, 13, 14, 20], 'hard' => [4, 7, 16, 17, 19]], // CO1 Formative 3
    23 => ['easy' => [1, 2, 3, 5, 6, 10, 11, 15, 18], 'medium' => [4, 7, 8, 9, 12, 13, 14, 16, 17, 19, 20], 'hard' => [21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40]], // CO1 Summative
    26 => ['easy' => [1, 2, 3, 9, 10, 11, 12, 14], 'medium' => [4, 5, 6, 7, 8, 13, 15, 16, 17, 18, 19, 20], 'hard' => []], // CO2 Formative 1
    29 => ['easy' => [1, 10, 11, 12], 'medium' => [2, 3, 4, 5, 6, 7, 8, 9, 13, 16], 'hard' => [14, 15, 17, 18, 19, 20]], // CO2 Formative 2
    32 => ['easy' => [1, 2, 3, 4, 5, 6, 7, 20], 'medium' => [8, 9, 10], 'hard' => [11, 12, 13, 14, 15, 16, 17, 18, 19]], // CO2 Formative 3
    35 => ['easy' => [1, 2, 8, 9, 10, 11, 13, 14, 17, 20], 'medium' => [3, 4, 5, 6, 7, 12, 15, 16, 18, 19], 'hard' => [21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40]], // CO2 Summative
    38 => ['easy' => [1, 2, 3, 4, 5, 6, 11], 'medium' => [7, 8, 9, 10, 12, 14, 15, 16, 17, 18, 19], 'hard' => [13, 20]], // CO3 Formative 1
    41 => ['easy' => [1, 2, 3, 4, 5, 8], 'medium' => [6, 7, 9, 10, 11, 12, 13, 14, 16, 17, 18, 19], 'hard' => [15, 20]], // CO3 Formative 2
    44 => ['easy' => [1, 2, 3, 4, 5, 6], 'medium' => [7, 8, 9, 10, 11, 12, 14, 15, 16, 17, 18], 'hard' => [13, 19, 20]], // CO3 Formative 3
    47 => ['easy' => [1, 2, 3, 4, 5, 9, 12, 13, 14, 17, 20, 21, 22, 23, 24], 'medium' => [6, 7, 8, 10, 11, 15, 16, 18, 19, 25, 26, 27, 28, 29, 30, 31, 32, 34, 35, 36, 37, 38], 'hard' => [33, 39, 40]], // CO3 Summative
    50 => ['easy' => [1, 2, 5, 6, 8, 11, 12], 'medium' => [3, 4, 7, 9, 10, 14, 15, 16, 17, 18, 19, 20], 'hard' => [13]], // CO4 Formative 1
    53 => ['easy' => [2, 5, 8, 11, 14, 18], 'medium' => [1, 3, 4, 6, 7, 9, 10, 12, 13, 15, 16, 17, 19, 20], 'hard' => []], // CO4 Formative 2
    56 => ['easy' => [1, 2, 4, 5, 6, 7, 8, 11], 'medium' => [3, 9, 10, 12, 13, 14, 15, 16, 17, 19, 20], 'hard' => [18]], // CO4 Formative 3
    59 => ['easy' => [1, 2, 5, 6, 8, 11, 12, 13, 14, 15, 16, 18, 19], 'medium' => [3, 4, 7, 9, 10, 17, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 32, 33, 34, 36, 37, 38, 39, 40], 'hard' => [30, 31, 35]], // CO4 Summative
    62 => ['easy' => [1, 4, 8, 10, 16], 'medium' => [2, 3, 5, 6, 7, 9, 17, 18, 20], 'hard' => [11, 12, 13, 14, 15, 19]], // CO5 Formative 1
    65 => ['easy' => [1, 2, 3, 4, 5, 6, 16, 18, 19], 'medium' => [7, 8, 9, 15], 'hard' => [10, 11, 12, 13, 14, 17, 20]], // CO5 Formative 2
    68 => ['easy' => [1, 2, 3, 5, 6, 8, 11, 12, 13, 14, 20], 'medium' => [4, 7, 15, 16, 17, 18, 19], 'hard' => [9, 10]], // CO5 Formative 3
    71 => ['easy' => [1, 2, 12, 13, 14, 15, 16, 17, 18, 19, 20], 'medium' => [3, 4, 5, 6, 8, 9], 'hard' => [7, 10, 11, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40]], // CO5 Summative
    74 => ['easy' => [1, 2, 3, 4, 5, 6, 7, 8, 10, 11], 'medium' => [12, 13, 14, 15, 17, 18, 19, 20], 'hard' => [9, 16]], // CO6 Formative 1
    77 => ['easy' => [1, 2, 3, 4, 5, 8, 12, 14, 16], 'medium' => [6, 7, 9, 10, 13, 17, 18, 19, 20], 'hard' => [11, 15]], // CO6 Formative 2
    80 => ['easy' => [1, 2, 10, 11, 12, 13, 16, 18, 19, 20], 'medium' => [3, 4, 5, 6, 7, 14, 15, 17], 'hard' => [8, 9, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40]], // CO6 Summative
    83 => ['easy' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10], 'medium' => [11, 12, 13, 16, 17, 18, 19, 20], 'hard' => [14, 15]], // CO7 Formative 1
    86 => ['easy' => [1, 2, 3, 4, 5, 6, 7, 8, 10, 11, 13, 17, 18], 'medium' => [9, 12, 14, 16, 19, 20], 'hard' => [15]], // CO7 Formative 2
    89 => ['easy' => [1, 2, 3, 4, 5, 6, 7, 9, 13, 14, 18, 19], 'medium' => [8, 10, 11, 12, 15, 16, 17], 'hard' => [20]], // CO7 Formative 3
    92 => ['easy' => [1, 2, 3, 4, 5, 6, 12, 13, 18], 'medium' => [7, 10, 11, 14, 15, 16, 17, 20], 'hard' => [8, 9, 19]], // CO7 Formative 4
    95 => ['easy' => [1, 2, 12, 13, 20], 'medium' => [3, 4, 5, 6, 7, 8, 9, 10, 11, 14, 15, 16, 17, 18, 19], 'hard' => []], // CO7 Formative 5
    98 => ['easy' => [1, 2, 3, 5, 7, 8, 9, 11, 12, 16, 20], 'medium' => [4, 6, 10, 13, 15, 17, 18, 19], 'hard' => [14, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40]], // CO7 Summative
];

// New questions: bank category id => [question text, [A, B, C, D], correct index].
$advanced = [
    // CO3 Summative, Advanced Conceptual Questions 1-10.
    47 => [
        ["A business receives a ₱50,000 cash deposit from a client for a custom architectural design that will take three months to complete. Under the standard Chart of Accounts numbering convention, which series of accounts must be credited today, and what is its normal balance?",
            ['400–499 series; Normal Credit Balance', '200–299 series; Normal Credit Balance', '100–199 series; Normal Debit Balance', '300–399 series; Normal Credit Balance'], 1],
        ["An accountant is reviewing a Chart of Accounts and notices an account titled \"Allowance for Doubtful Accounts.\" This account reduces the total value of Accounts Receivable. How should this account be classified and what is its normal balance?",
            ['Operating Expense; Normal Debit Balance', 'Current Liability; Normal Credit Balance', 'Contra-Asset; Normal Credit Balance', 'Other Expense; Normal Debit Balance'], 2],
        ["A corporation has multiple equity accounts, unlike a sole proprietorship. If you are assigning account numbers to \"Retained Earnings\" and \"Share Capital,\" which specific block of the Chart of Accounts must you use, and how do these accounts typically increase?",
            ['200–299 series; Increased by credits', '300–399 series; Increased by credits', '400–499 series; Increased by debits', '300–399 series; Increased by debits'], 1],
        ["A business purchases a piece of heavy machinery for ₱1,000,000. It pays ₱200,000 in cash and signs a 3-year promissory note for the remaining ₱800,000. Which of the following correctly maps the account types affected in the Chart of Accounts?",
            ['Increase in a 100-series account, decrease in a 100-series account, and increase in a 200-series account.', 'Increase in a 500-series account, decrease in a 100-series account, and increase in a 200-series account.', 'Increase in a 100-series account and decrease in two different 200-series accounts.', 'Increase in a 100-series account, decrease in a 300-series account, and increase in a 200-series account.'], 0],
        ["At the end of the year, a company records its \"Depreciation Expense\" and updates its \"Accumulated Depreciation.\" In terms of financial statement placement based on the Chart of Accounts, where do these two accounts ultimately go?",
            ['Both go to the Statement of Financial Position (Balance Sheet).', 'Both go to the Income Statement.', 'Depreciation Expense goes to the Income Statement; Accumulated Depreciation goes to the Statement of Financial Position.', 'Depreciation Expense goes to the Statement of Financial Position; Accumulated Depreciation goes to the Income Statement.'], 2],
        ["The owner of a sole proprietorship withdraws ₱25,000 to pay for a personal family vacation. The bookkeeper mistakenly records this as a \"Miscellaneous Expense\" (500-series) instead of \"Owner's Drawings\" (300-series). What is the ultimate effect of this classification error on the financial statements?",
            ['Total Assets will be overstated.', "Net Income will be understated, but the ending Owner's Equity will remain correct.", 'Total Liabilities will be understated.', "Net Income will be overstated, and ending Owner's Equity will be overstated."], 1],
        ["Which of the following lists contains ONLY accounts that belong to the 200-299 numbering series in a standard Chart of Accounts?",
            ['Accounts Payable, Unearned Revenue, Mortgage Payable, Accrued Rent Payable', 'Accounts Receivable, Notes Payable, Prepaid Insurance, Unearned Revenue', 'Accounts Payable, Service Revenue, Interest Payable, Unearned Revenue', 'Notes Payable, Accumulated Depreciation, Accrued Salaries Payable, SSS Payable'], 0],
        ["A business prepays ₱120,000 for a one-year office lease. The accountant debits \"Prepaid Rent.\" Why is this classified as a 100-series account rather than a 500-series account at the time of the transaction?",
            ['Because the cash has already been paid and cannot be recovered.', 'Because it represents a future economic benefit (the right to use the space) that has not yet been consumed.', 'Because all rent payments are considered assets under PFRS.', 'Because it is a liability owed to the landlord until the year is over.'], 1],
        ["If a business discovers a patent it developed is highly valuable, and it registers the patent legally, where would \"Patents\" be classified in the standard Chart of Accounts?",
            ['100–199 series (Non-Current Asset)', "300–399 series (Owner's Equity)", '400–499 series (Other Income)', '200–299 series (Non-Current Liability)'], 0],
        ["A company's Chart of Accounts includes an account named \"Advances to Employees.\" This represents money loaned to staff that will be deducted from their future paychecks. How is this account classified and what is its normal balance?",
            ['Current Liability; Normal Credit Balance', 'Operating Expense; Normal Debit Balance', 'Current Asset; Normal Debit Balance', "Owner's Equity; Normal Debit Balance"], 2],
    ],
    // CO4 Summative, Advanced Conceptual Questions 1-15.
    59 => [
        ["A bookkeeper correctly records a ₱5,000 credit sale in the Sales Journal. However, when posting this transaction to the customer's subsidiary ledger, the bookkeeper accidentally writes ₱500. What will happen at the end of the month during reconciliation?",
            ['The general ledger Trial Balance will fail to balance.', 'The general ledger Trial Balance will balance, but the sum of the Accounts Receivable subsidiary ledger will be ₱4,500 lower than the control account.', 'The general ledger Trial Balance will balance, but the sum of the Accounts Receivable subsidiary ledger will be ₱4,500 higher than the control account.', 'The Accounts Receivable control account will automatically adjust to match the subsidiary ledger.'], 1],
        ["A business purchases a delivery van for ₱850,000. They issue a check for ₱150,000 as a down payment and sign a promissory note for the remaining ₱700,000. In which book(s) of accounts should this compound transaction be recorded?",
            ['The entire transaction must be recorded in the Cash Disbursements Journal.', 'The entire transaction must be recorded in the Purchases Journal.', 'It must be recorded in the General Journal because it does not fit neatly into a single special journal.', 'The down payment in the Cash Disbursements Journal, and the remaining note in the Sales Journal.'], 2],
        ["At the end of the month, the Cash Receipts Journal shows the following column totals: Cash (Debit) ₱120,000; Sales Discount (Debit) ₱5,000; Accounts Receivable (Credit) ₱40,000; Sales (Credit) ₱60,000; Sundry (Credit) ₱25,000. If the accountant attempts to post these totals to the general ledger, what issue will they encounter?",
            ['The journal is perfectly balanced and ready to post.', 'The journal cannot be posted because total debits (₱125,000) equal total credits (₱125,000), but Sundry cannot be posted as a column total.', 'The journal cannot be posted because total debits (₱120,000) do not equal total credits (₱125,000).', 'The Sales Discount column should be a credit, causing an imbalance.'], 1],
        ["A company issues Check Voucher No. 104 to purchase ₱8,000 worth of office supplies. The bookkeeper records this in the Cash Disbursements Journal but mistakenly debits the \"Office Equipment\" column instead of \"Supplies.\" Once posted to the General Ledger, how will this error affect the Trial Balance?",
            ['The Trial Balance will not balance because debits will exceed credits.', 'The Trial Balance will balance, but Total Assets will be overstated while Expenses are understated.', 'The Trial Balance will balance, and the financial statements will remain completely accurate because both are assets.', 'The Trial Balance will not balance because a Cash Disbursement requires a credit to an expense.'], 1],
        ["In a highly automated accounting system using special journals, a customer returns ₱3,000 worth of defective merchandise that they originally purchased on account. Since there is no \"Sales Returns Journal,\" where must the accountant record this specific transaction?",
            ['The Sales Journal as a negative number.', 'The Cash Disbursements Journal, because the business is losing value.', 'The Cash Receipts Journal, since it involves a customer.', 'The General Journal, because it does not involve cash, a credit sale, or a credit purchase.'], 3],
        ["The Accounts Payable Control Account in the general ledger has an ending credit balance of ₱145,000. The Accounts Payable Subsidiary Ledger shows the following supplier balances: Supplier A (₱40,000), Supplier B (₱65,000), and Supplier C. If the books are reconciled and error-free, what is the exact balance of Supplier C's account?",
            ['₱40,000 Debit Balance', '₱40,000 Credit Balance', '₱250,000 Credit Balance', '₱105,000 Credit Balance'], 1],
        ["A bookkeeper is posting a General Journal entry: Debit Rent Expense ₱10,000, Credit Cash ₱10,000. They write the account number \"501\" in the journal's PR column, but they forget to actually transfer the ₱10,000 debit into the Rent Expense ledger account. What is the immediate consequence of this specific error?",
            ['The ledger account for Rent Expense will be correct, but the journal will be invalid.', 'The PR column acts as a false confirmation; the Trial Balance will fail because total credits will exceed total debits by ₱10,000.', 'The Trial Balance will fail because total debits will exceed total credits by ₱10,000.', 'The error will not affect the Trial Balance.'], 1],
        ["A business purchases ₱50,000 of merchandise inventory on account from Supplier X. Two days later, the business pays ₱1,500 cash for the freight/shipping of that merchandise. How are these two transactions journalized?",
            ['Both are recorded in the Purchases Journal.', 'The ₱50,000 in the Purchases Journal; the ₱1,500 in the General Journal.', 'The ₱50,000 in the Purchases Journal; the ₱1,500 in the Cash Disbursements Journal.', 'Both must be combined into a single entry in the General Journal.'], 2],
        ["Why is the \"Sundry\" column in the Cash Receipts and Cash Disbursements journals treated differently during the month-end posting process compared to the \"Cash\" or \"Sales\" columns?",
            ['The Sundry column is posted as a single grand total to a specific Sundry ledger account.', 'The Sundry column is ignored during posting; it is only for internal notes.', 'The individual amounts in the Sundry column must be posted line-by-line to their specific general ledger accounts, rather than as a column total.', 'The Sundry column is only posted to subsidiary ledgers, never the general ledger.'], 2],
        ["A business processes its monthly payroll. The gross salaries are ₱100,000, but the business withholds ₱10,000 for SSS/PhilHealth and pays out ₱90,000 in cash to employees. If recorded in the General Journal, what does the compound entry look like?",
            ['Debit Salaries Expense ₱100,000; Credit SSS Payable ₱10,000; Credit Cash ₱90,000.', 'Debit Salaries Expense ₱90,000; Debit SSS Payable ₱10,000; Credit Cash ₱100,000.', 'Debit Cash ₱90,000; Debit SSS Payable ₱10,000; Credit Salaries Expense ₱100,000.', 'Debit Salaries Expense ₱100,000; Credit Cash ₱100,000.'], 0],
        ["An accountant is reconciling the Cash account. The beginning balance was ₱45,000 (Debit). During the month, the Cash Receipts Journal total posted was ₱180,000. The Cash Disbursements Journal total posted was ₱135,000. A General Journal entry also credited Cash for ₱12,000. What is the correct running balance of the Cash ledger account?",
            ['₱102,000 Debit Balance', '₱78,000 Debit Balance', '₱90,000 Debit Balance', '₱78,000 Credit Balance'], 1],
        ["The owner of a construction firm decides to take a ₱120,000 piece of equipment owned by the business and brings it home for permanent personal use. No cash changes hands. Which book of accounts is used, and what is the entry?",
            ["Cash Disbursements Journal: Debit Owner's Drawings ₱120,000, Credit Equipment ₱120,000.", "General Journal: Debit Owner's Drawings ₱120,000, Credit Equipment ₱120,000.", "Purchases Journal: Debit Equipment ₱120,000, Credit Owner's Drawings ₱120,000.", "General Journal: Debit Owner's Capital ₱120,000, Credit Cash ₱120,000."], 1],
        ["A business accidentally records a ₱7,600 cash payment for utilities as ₱6,700 in the Cash Disbursements Journal. It is posted to the ledger as ₱6,700. What is the resulting effect on the unadjusted Trial Balance?",
            ['The Trial Balance will fail to balance by ₱900.', 'The Trial Balance will balance, but both Cash (asset) and Utilities (expense) will be misstated by ₱900.', 'The Trial Balance will fail to balance by ₱14,300.', 'The Trial Balance will automatically correct the transposition error.'], 1],
        ["When a business uses a Sales Journal, what is the exact dual-posting procedure that happens at the end of the month for the grand total of ₱500,000?",
            ['Debit Accounts Receivable (General Ledger) ₱500,000; Credit Sales (General Ledger) ₱500,000.', 'Debit Cash (General Ledger) ₱500,000; Credit Sales (General Ledger) ₱500,000.', "Debit each individual customer's subsidiary ledger; Credit Sales (General Ledger) ₱500,000.", 'Debit Accounts Receivable (General Ledger) ₱500,000; Credit Accounts Payable (General Ledger) ₱500,000.'], 0],
        ["Why are adjusting entries (Step 5) and closing entries (Step 8) always recorded in the General Journal rather than the special journals?",
            ['Because special journals are destroyed at the end of the month.', 'Because adjusting and closing entries never involve the Cash account, nor do they involve routine credit sales or purchases.', 'Because adjusting entries are only estimates and special journals require exact source documents.', 'Because the BIR requires closing entries to be handwritten in the General Journal.'], 1],
    ],
];

// ---------------------------------------------------------------- plan --

$plan = [];   // bank cat id => [bank, level => [qbe ids], newcount]
$problems = 0;
foreach ($difficulties as $catid => $levels) {
    $bank = $DB->get_record('question_categories', ['id' => $catid], '*', MUST_EXIST);
    // Every entry currently anywhere in this bank (the category or its pools),
    // by its newest version's name.
    $entries = $DB->get_records_sql("
        SELECT qbe.id, q.name, qbe.questioncategoryid
          FROM {question_bank_entries} qbe
          JOIN {question_versions} qv ON qv.questionbankentryid = qbe.id
          JOIN {question} q ON q.id = qv.questionid
         WHERE (qbe.questioncategoryid = ? OR qbe.questioncategoryid IN
                   (SELECT id FROM {question_categories} WHERE parent = ?))
           AND qv.version = (SELECT MAX(version) FROM {question_versions} WHERE questionbankentryid = qbe.id)",
        [$catid, $catid]);
    $byname = [];
    foreach ($entries as $e) {
        $byname[$e->name] = $e;
    }
    $plan[$catid] = ['bank' => $bank, 'levels' => []];
    $seen = [];
    foreach ($levels as $level => $nums) {
        $plan[$catid]['levels'][$level] = [];
        foreach ($nums as $n) {
            $e = $byname["Question $n"] ?? null;
            if (!$e) {
                echo "!! cat $catid: no 'Question $n'\n";
                $problems++;
                continue;
            }
            $plan[$catid]['levels'][$level][] = $e->id;
            $seen[$e->id] = true;
        }
    }
    foreach ($entries as $e) {
        // Advanced questions added by an earlier run are expected leftovers.
        if (!isset($seen[$e->id]) && !preg_match('/^Question (4[1-9]|5\d)$/', $e->name)) {
            echo "!! cat $catid: '{$e->name}' (entry {$e->id}) has no difficulty in the PDFs\n";
            $problems++;
        }
    }
    $hard = count($plan[$catid]['levels']['hard']) + count($advanced[$catid] ?? []);
    printf("%-4s %-60s easy %2d  medium %2d  hard %2d%s\n", $catid, $bank->name,
        count($plan[$catid]['levels']['easy']), count($plan[$catid]['levels']['medium']), $hard,
        isset($advanced[$catid]) ? '  (incl. ' . count($advanced[$catid]) . ' new)' : '');
}
if ($problems) {
    cli_error("$problems problem(s) - nothing changed.");
}

// Quizzes on these banks.
$quizzes = $DB->get_records_sql("
    SELECT DISTINCT q.id, q.name, q.course, q.timelimit
      FROM {quiz} q
      JOIN {quiz_slots} qs ON qs.quizid = q.id
      JOIN {question_set_references} qsr ON qsr.itemid = qs.id AND qsr.component = 'mod_quiz'
     WHERE q.course BETWEEN 2 AND 8
  ORDER BY q.course, q.id");
echo "\nDraws per attempt once applied (easy/medium/hard):\n";
foreach ($quizzes as $quiz) {
    $bank = question_pools::bank_for_quiz($quiz->id);
    if (!$bank || !isset($plan[$bank->id])) {
        cli_error("quiz {$quiz->id} {$quiz->name}: bank not in the plan");
    }
    $sizes = [];
    foreach (question_pools::LEVELS as $level => $label) {
        $sizes[$level] = count($plan[$bank->id]['levels'][$level]) + ($level === 'hard' ? count($advanced[$bank->id] ?? []) : 0);
    }
    $kind = question_pools::quiz_kind($quiz->name);
    $alloc = question_pools::allocate(question_pools::TARGETS[$kind], $sizes);
    printf("  course %d quiz %-3d %-18s %s  %s%s\n", $quiz->course, $quiz->id, $quiz->name,
        implode('/', $alloc), $kind === 'summative' ? 'time limit 50 min' : '',
        $alloc == question_pools::TARGETS[$kind] ? '' : '   <- short pool, spilled');
}

// Open summative attempts would be put on the clock by the new limit and
// auto-submitted. Each gets a user override keeping that student untimed on
// that quiz (timelimit 0 = no limit; quiz_update_effective_access uses a set
// override value even when it is 0).
$summativeids = array_keys(array_filter($quizzes, fn($q) => question_pools::quiz_kind($q->name) === 'summative'));
[$insql, $inparams] = $DB->get_in_or_equal($summativeids);
$open = $DB->get_records_sql("
    SELECT qa.id, qa.quiz, qa.userid, u.username, qa.timestart
      FROM {quiz_attempts} qa JOIN {user} u ON u.id = qa.userid
     WHERE qa.state IN ('inprogress', 'overdue') AND qa.quiz $insql", $inparams);
foreach ($open as $a) {
    echo "\nOpen summative attempt {$a->id} ({$a->username}, quiz {$a->quiz}, started "
        . userdate($a->timestart) . ") - will get an untimed user override.";
}
echo "\n";

if (!$apply) {
    echo "\nDry run. Pass --apply to make these changes.\n";
    exit(0);
}

// --------------------------------------------------------------- apply --

$transaction = $DB->start_delegated_transaction();
$qtype = question_bank::get_qtype('multichoice');
foreach ($plan as $catid => $p) {
    $bank = $p['bank'];
    $sortorder = 1;
    $pools = [];
    foreach (question_pools::LEVELS as $level => $label) {
        $idnumber = question_pools::IDNUMBER_PREFIX . $level;
        $cat = $DB->get_record('question_categories',
            ['contextid' => $bank->contextid, 'idnumber' => $idnumber]);
        if (!$cat) {
            $cat = (object) [
                'name' => $label,
                'contextid' => $bank->contextid,
                'info' => "$label questions for this quiz. Each attempt draws from this pool at random. "
                    . 'Do not rename or delete this category: the quiz is wired to it.',
                'infoformat' => FORMAT_HTML,
                'stamp' => make_unique_id_code(),
                'parent' => $bank->id,
                'sortorder' => $sortorder,
                'idnumber' => $idnumber,
            ];
            $cat->id = $DB->insert_record('question_categories', $cat);
        }
        $sortorder++;
        $pools[$level] = $cat;

        // Same context, so no files move with the questions.
        if ($p['levels'][$level]) {
            [$insql, $inparams] = $DB->get_in_or_equal($p['levels'][$level]);
            $DB->execute("UPDATE {question_bank_entries} SET questioncategoryid = ? WHERE id $insql",
                array_merge([$cat->id], $inparams));
        }
    }

    // New questions, copying the presentation settings of the bank's own.
    $model = $DB->get_record_sql("
        SELECT o.* FROM {qtype_multichoice_options} o
          JOIN {question_versions} qv ON qv.questionid = o.questionid
          JOIN {question_bank_entries} qbe ON qbe.id = qv.questionbankentryid
         WHERE qbe.questioncategoryid IN (SELECT id FROM {question_categories} WHERE parent = ?)
      ORDER BY o.id", [$catid], IGNORE_MULTIPLE);
    $next = 41;
    foreach ($advanced[$catid] ?? [] as [$text, $choices, $correct]) {
        $name = 'Question ' . $next++;
        if ($DB->record_exists_sql("
                SELECT 1 FROM {question} q
                  JOIN {question_versions} qv ON qv.questionid = q.id
                  JOIN {question_bank_entries} qbe ON qbe.id = qv.questionbankentryid
                 WHERE qbe.questioncategoryid = ? AND q.name = ?", [$pools['hard']->id, $name])) {
            continue;
        }
        $form = (object) [
            'category' => $pools['hard']->id . ',' . $bank->contextid,
            'name' => $name,
            'questiontext' => ['text' => $text, 'format' => FORMAT_HTML],
            'generalfeedback' => ['text' => '', 'format' => FORMAT_HTML],
            'defaultmark' => 1,
            'penalty' => 0.3333333,
            'status' => \core_question\local\bank\question_version_status::QUESTION_STATUS_READY,
            'single' => 1,
            'shuffleanswers' => $model->shuffleanswers,
            'answernumbering' => $model->answernumbering,
            'showstandardinstruction' => $model->showstandardinstruction,
            'shownumcorrect' => $model->shownumcorrect,
            'correctfeedback' => ['text' => $model->correctfeedback, 'format' => FORMAT_HTML],
            'partiallycorrectfeedback' => ['text' => $model->partiallycorrectfeedback, 'format' => FORMAT_HTML],
            'incorrectfeedback' => ['text' => $model->incorrectfeedback, 'format' => FORMAT_HTML],
            'answer' => [], 'fraction' => [], 'feedback' => [],
        ];
        foreach ($choices as $i => $choice) {
            $form->answer[] = ['text' => $choice, 'format' => FORMAT_HTML];
            $form->fraction[] = $i === $correct ? '1.0' : '0.0';
            $form->feedback[] = ['text' => '', 'format' => FORMAT_HTML];
        }
        $question = (object) ['category' => $pools['hard']->id, 'qtype' => 'multichoice'];
        $qtype->save_question($question, $form);
        echo "  added $name to {$bank->name} / Hard\n";
    }
}

// Summative time limit, with the open attempts protected first.
foreach ($open as $a) {
    if (!$DB->record_exists('quiz_overrides', ['quiz' => $a->quiz, 'userid' => $a->userid])) {
        $DB->insert_record('quiz_overrides', (object) [
            'quiz' => $a->quiz, 'userid' => $a->userid, 'timelimit' => 0,
        ]);
    }
}
foreach ($summativeids as $quizid) {
    $DB->set_field('quiz', 'timelimit', question_pools::SUMMATIVE_TIMELIMIT, ['id' => $quizid]);
}
$transaction->allow_commit();

// Outside the transaction: these read what was just committed.
foreach ($plan as $catid => $p) {
    $added = question_pools::tag_untagged($p['bank']->contextid);
    if ($added) {
        echo "  tagged $added question(s) in {$p['bank']->name}\n";
    }
}
$changed = 0;
foreach ($quizzes as $quiz) {
    $changed += question_pools::rebalance_quiz($quiz);
}
echo "  retargeted $changed random slot(s)\n";

quiz_update_open_attempts(['quizid' => $summativeids]);
cache::make('core', 'questiondata')->purge();
cache::make('mod_quiz', 'overrides')->purge();
foreach (array_unique(array_map(fn($q) => $q->course, $quizzes)) as $courseid) {
    rebuild_course_cache($courseid, true);
}
echo "Done.\n";
