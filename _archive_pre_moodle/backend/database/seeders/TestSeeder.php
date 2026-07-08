<?php

namespace Database\Seeders;

use App\Enums\MasteryLevel;
use App\Models\Assessment;
use App\Models\Competency;
use App\Models\LearningMaterial;
use App\Models\MasteryRecord;
use App\Models\Module;
use App\Models\User;
use Illuminate\Database\Seeder;

class TestSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedUsers();
        [$allCompetencies, $student1] = $this->seedModulesAndCompetencies();
        $this->seedAssessments($allCompetencies);
        $this->seedMasteryRecords($student1, $allCompetencies);
    }

    // ─── Users ────────────────────────────────────────────────────────────────

    private function seedUsers(): void
    {
        User::updateOrCreate(['email' => 'admin@acculearn.test'], [
            'name' => 'Admin User', 'password' => 'password123',
            'role' => User::ROLE_ADMIN, 'active' => true, 'section' => null,
        ]);
        User::updateOrCreate(['email' => 'teacher@acculearn.test'], [
            'name' => 'Teacher User', 'password' => 'password123',
            'role' => User::ROLE_TEACHER, 'active' => true, 'section' => 'ABM 11-A',
        ]);
        User::updateOrCreate(['email' => 'student2@acculearn.test'], [
            'name' => 'Student Two', 'password' => 'password123',
            'role' => User::ROLE_STUDENT, 'active' => true, 'section' => 'ABM 11-A',
        ]);
        User::updateOrCreate(['email' => 'student3@acculearn.test'], [
            'name' => 'Student Three', 'password' => 'password123',
            'role' => User::ROLE_STUDENT, 'active' => true, 'section' => 'ABM 11-B',
        ]);
    }

    // ─── Modules + Competencies + Learning Materials ──────────────────────────

    private function seedModulesAndCompetencies(): array
    {
        $student1 = User::updateOrCreate(['email' => 'student1@acculearn.test'], [
            'name' => 'Student One', 'password' => 'password123',
            'role' => User::ROLE_STUDENT, 'active' => true, 'section' => 'ABM 11-A',
        ]);

        $catalog = $this->moduleCatalog();
        $allCompetencies = collect();

        foreach ($catalog as $moduleDef) {
            $module = Module::updateOrCreate(
                ['title' => $moduleDef['title']],
                [
                    'description' => $moduleDef['description'],
                    'order'       => $moduleDef['order'],
                ]
            );

            $previousId = null;
            foreach ($moduleDef['competencies'] as $idx => $compDef) {
                $competency = Competency::updateOrCreate(
                    ['module_id' => $module->id, 'deped_code' => $compDef['deped_code']],
                    [
                        'title'                      => $compDef['title'],
                        'order'                      => $idx + 1,
                        'prerequisite_competency_id' => $previousId,
                    ]
                );
                $allCompetencies->push($competency);
                $previousId = $competency->id;

                // Video placeholder
                LearningMaterial::updateOrCreate(
                    ['competency_id' => $competency->id, 'type' => 'video'],
                    [
                        'title'       => 'Video: ' . $competency->title,
                        'content_url' => null,
                        'body'        => null,
                        'vark_type'   => 'visual',
                    ]
                );

                // Text reading with real content
                LearningMaterial::updateOrCreate(
                    ['competency_id' => $competency->id, 'type' => 'text'],
                    [
                        'title'       => 'Reading: ' . $competency->title,
                        'content_url' => null,
                        'body'        => $compDef['body'],
                        'vark_type'   => 'readwrite',
                    ]
                );

                // Activity
                LearningMaterial::updateOrCreate(
                    ['competency_id' => $competency->id, 'type' => 'activity'],
                    [
                        'title'       => 'Activity: ' . $competency->title,
                        'content_url' => null,
                        'body'        => $compDef['activity'] ?? null,
                        'vark_type'   => 'kinesthetic',
                    ]
                );
            }
        }

        return [$allCompetencies, $student1];
    }

    private function seedAssessments($allCompetencies): void
    {
        foreach ($allCompetencies as $competency) {
            Assessment::updateOrCreate(
                ['competency_id' => $competency->id],
                ['title' => 'Assessment: ' . $competency->title, 'passing_score' => 75.00, 'moodle_quiz_id' => null]
            );
        }
    }

    private function seedMasteryRecords($student1, $allCompetencies): void
    {
        $progress = [
            ['mastery_score' => 92.00, 'mastery_level' => MasteryLevel::MASTERED->value,          'attempt_count' => 1],
            ['mastery_score' => 80.00, 'mastery_level' => MasteryLevel::MASTERED->value,          'attempt_count' => 2],
            ['mastery_score' => 71.00, 'mastery_level' => MasteryLevel::DEVELOPING->value,        'attempt_count' => 1],
            ['mastery_score' => 60.00, 'mastery_level' => MasteryLevel::NEEDS_IMPROVEMENT->value, 'attempt_count' => 2],
        ];
        foreach ($progress as $idx => $record) {
            MasteryRecord::updateOrCreate(
                ['user_id' => $student1->id, 'competency_id' => $allCompetencies[$idx]->id],
                $record
            );
        }
    }

    // ─── Lesson Content Catalog ───────────────────────────────────────────────

    private function moduleCatalog(): array
    {
        return [
            // ── CO1 ──────────────────────────────────────────────────────────
            [
                'title'       => 'Accounting Concepts, Principles, and the Regulatory Framework',
                'description' => 'The rules that govern financial reporting in the Philippines — PFRS, GAAP, and the conceptual framework that every accountant relies on.',
                'order'       => 1,
                'competencies' => [
                    [
                        'deped_code' => 'ABM_FABM11-IIIa-1',
                        'title'      => 'Define accounting and explain its nature as a science, art, process, and profession',
                        'body'       => <<<'BODY'
## What Is Accounting?

Accounting is often called **the language of business**. It is the systematic process of identifying, recording, classifying, summarizing, interpreting, and communicating financial information so that users can make informed economic decisions.

### The Four Natures of Accounting

| Nature | Explanation | Example |
|--------|-------------|---------|
| **Science** | Accounting follows a systematic body of rules and principles that produce consistent, verifiable results. | Every journal entry follows the double-entry rule — no exceptions. |
| **Art** | Accounting requires skill, judgment, and experience to apply principles correctly to real-world situations. | Estimating the useful life of an asset requires professional judgment. |
| **Process** | Accounting is a continuous cycle of activities: identifying → recording → classifying → summarizing → interpreting → communicating. | The accounting cycle repeats every period. |
| **Profession** | Accounting is a recognized profession requiring education, licensure (CPA), and adherence to a code of ethics. | In the Philippines, CPAs are regulated by the Board of Accountancy (BOA). |

### Accounting vs. Bookkeeping

- **Bookkeeping** is the *recording* phase — the mechanical entry of financial transactions into the books.
- **Accounting** is the *complete process* — it includes bookkeeping plus analysis, interpretation, and reporting.

> Bookkeeping is *part of* accounting, but accounting is much broader.

### Why Accounting Matters

Before a bank lends money to a business, it reviews financial statements. Before a government collects taxes, it examines income reports. Before an investor buys shares, it analyzes profit trends. All of these decisions depend on accounting.
BODY,
                        'activity'   => 'Match each scenario to one of the four natures of accounting (Science / Art / Process / Profession): (1) An accountant estimates that a delivery van will last 5 years. (2) A bookkeeper records all January sales transactions. (3) A CPA signs and certifies a set of financial statements. (4) Debits must always equal credits.',
                    ],
                    [
                        'deped_code' => 'ABM_FABM11-IIIa-2',
                        'title'      => 'Describe the nature of accounting and distinguish it from bookkeeping',
                        'body'       => <<<'BODY'
## Accounting vs. Bookkeeping — A Deeper Look

### Bookkeeping

Bookkeeping is the **clerical phase** of accounting. It involves:
- Systematically recording every business transaction in chronological order.
- Posting entries to ledger accounts.
- Maintaining subsidiary ledgers.

Bookkeepers do not interpret data; they record it faithfully.

### Accounting

Accounting **encompasses bookkeeping** and goes further:

1. **Identifying** — Determining which events qualify as financial transactions.
2. **Recording** — Journalizing each transaction (bookkeeping).
3. **Classifying** — Posting to ledger accounts by category.
4. **Summarizing** — Preparing trial balances and financial statements.
5. **Interpreting** — Analyzing ratios, trends, and variances.
6. **Communicating** — Presenting financial data to decision-makers.

### Key Distinction

| Aspect | Bookkeeping | Accounting |
|--------|-------------|------------|
| Scope | Recording only | Full cycle: record → analyze → report |
| Output | Books of account | Financial statements + analysis |
| Who does it | Bookkeeper | Accountant / CPA |
| Judgment required | Minimal | High |
| Regulatory oversight | None | BOA / PRC (Philippines) |

### Philippine Context

In the Philippines, the practice of public accounting is regulated by **Republic Act 9298** (Philippine Accountancy Act of 2004). Only licensed CPAs may sign audited financial statements.
BODY,
                        'activity'   => 'Read the following job descriptions and decide: Is this person doing bookkeeping, accounting, or both? (1) Ana records all cash received from customers each day. (2) Ben prepares the year-end income statement and explains the ₱50,000 drop in net income to the owner. (3) Cora posts journal entries to the general ledger and reconciles the bank statement.',
                    ],
                    [
                        'deped_code' => 'ABM_FABM11-IIIa-3',
                        'title'      => 'Explain the functions of accounting in business decision-making',
                        'body'       => <<<'BODY'
## How Accounting Supports Decision-Making

Accounting serves six core functions that together enable rational, evidence-based business decisions.

### The Six Functions

**1. Stewardship / Accountability Function**
Accounting ensures that managers are accountable for the resources entrusted to them by owners and investors. Financial reports are the primary tool for evaluating how well management has performed.

**2. Control Function**
Internal controls — budgets, variance reports, audit trails — use accounting data to detect fraud, prevent errors, and ensure that operations follow approved policies.

**3. Planning Function**
Budgets, forecasts, and cost analyses are all accounting outputs. Before a business decides to open a new branch or buy new equipment, it prepares financial projections grounded in accounting data.

**4. Communication Function**
Accounting translates complex economic activity into a standardized language (financial statements) that can be understood by stakeholders worldwide — regardless of whether they are familiar with the business.

**5. Reporting Function**
Financial statements (Income Statement, Balance Sheet, Statement of Changes in Equity) are formal reports submitted to owners, investors, lenders, and government regulators.

**6. Legal Compliance Function**
In the Philippines, businesses are legally required to submit BIR-compliant financial statements, SEC-registered records, and SSS/PhilHealth/Pag-IBIG contribution reports — all of which are products of accounting.

### Users of Accounting Information

| User | What They Need | Why |
|------|---------------|-----|
| Owner / Management | Profitability, cash flow, cost trends | Business decisions |
| Investors / Stockholders | Return on investment, earnings growth | Investment decisions |
| Creditors / Banks | Solvency, liquidity | Lending decisions |
| Government / BIR | Taxable income, compliance | Tax assessment |
| Employees | Job security, wage negotiation | Employment decisions |
BODY,
                        'activity'   => 'For each situation, identify which function of accounting is being served: (1) The owner reviews the monthly budget vs. actual expense report to find overspending. (2) The BIR audits a company\'s income tax return. (3) A bank reviews the company\'s balance sheet before approving a loan. (4) Management prepares a five-year financial forecast before expanding to a new city.',
                    ],
                    [
                        'deped_code' => 'ABM_FABM11-IIIa-4',
                        'title'      => 'Narrate the history and origin of accounting from Mesopotamia to modern PFRS/GAAP',
                        'body'       => <<<'BODY'
## A Brief History of Accounting

### Ancient Origins

- **Mesopotamia (3000 BCE)** — Clay tablets recorded grain and livestock inventories. The earliest known accounting records.
- **Egypt & Rome** — Government scribes maintained records of taxes, tribute, and grain stores.
- **Medieval Europe** — Merchants kept simple tally records; no systematic double-entry method yet.

### The Double-Entry Revolution

- **1494 — Luca Pacioli**, an Italian friar and mathematician, published *Summa de Arithmetica*, which described the double-entry bookkeeping system then used by Venetian merchants. This is considered the birth of modern accounting.
- The core rule: **every transaction has two equal and opposite effects** (debit and credit).

### The Rise of Standards

| Era | Development |
|-----|-------------|
| 1800s | Industrial Revolution creates complex corporations needing formal financial reporting. |
| 1930s | U.S. stock market crash (1929) exposes weak financial reporting; **GAAP** (Generally Accepted Accounting Principles) begins to take shape. |
| 1973 | **IASB** (International Accounting Standards Board) formed to harmonize global standards. |
| 2001 | IASB issues **IFRS** (International Financial Reporting Standards). |
| Philippines | **PFRS** (Philippine Financial Reporting Standards) adopted by the FRSC, aligning Philippine practice with IFRS. |

### Philippine Regulatory Framework

| Body | Role |
|------|------|
| **FRSC** (Financial Reporting Standards Council) | Issues PFRS; sets accounting standards aligned with IFRS |
| **BOA** (Board of Accountancy) | Regulates CPA practice; administers CPA licensure exam |
| **SEC** | Requires PFRS-compliant financial statements from corporations |
| **BIR** | Uses financial statements for tax compliance |
| **BSP** | Regulates banks and financial institutions |
| **PICPA** | Professional organization of CPAs; guides ethical conduct |
BODY,
                        'activity'   => 'Create a timeline with at least 6 key milestones in accounting history. For each milestone, write one sentence explaining its significance to modern accounting practice in the Philippines.',
                    ],
                    [
                        'deped_code' => 'ABM_FABM11-IIIa-5',
                        'title'      => 'Identify and apply fundamental accounting concepts: Economic Entity, Going Concern, Time Period, Accrual, Monetary',
                        'body'       => <<<'BODY'
## Fundamental Accounting Concepts

These five concepts are called **assumptions** — they underlie every financial statement.

---

### 1. Economic Entity Assumption
A business is treated as **completely separate** from its owner. All business transactions are recorded in the business's books only; personal transactions are excluded.

**Example:** Dr. Nana runs a pediatric clinic. Her personal grocery bills, mortgage, and personal savings are never recorded in the clinic's books — even though she owns the clinic.

---

### 2. Going Concern Assumption
It is assumed that the business will **continue to operate indefinitely** into the foreseeable future and will not be liquidated. This justifies recording long-term assets at cost rather than liquidation value.

**Exception:** If strong evidence suggests a business will close (insolvency, court order), a **Terminating Concern** basis is applied — assets are re-valued at **net realizable value**.

**Example:** When an accountant prepares the clinic's balance sheet, she records equipment at historical cost — because she assumes the clinic will keep using it, not sell it tomorrow.

---

### 3. Time Period (Periodicity) Assumption
Financial performance is reported at **regular, equal intervals** — typically one year. A business may use:
- **Calendar Year** — January 1 to December 31
- **Fiscal Year** — Any 12-month period (e.g., July 1 to June 30)
- **Natural Business Year** — Ends when business activity is at its lowest

---

### 4. Accrual Basis
- **Income** is recorded when **earned** (service performed or goods delivered), regardless of when cash is received.
- **Expenses** are recorded when **incurred**, regardless of when cash is paid.

| Accrual Concept | Meaning | Example |
|----------------|---------|---------|
| Accrued Income | Earned but not yet collected | Service rendered in December; cash collected in January |
| Accrued Expense | Incurred but not yet paid | Salaries for December; paid in January |
| Deferred Income | Cash collected but not yet earned | Advance payment for next month's services |
| Prepaid Expense | Cash paid but not yet incurred | Rent paid 3 months in advance |

**Example:** A resort receives ₱30,000 advance payment from a guest checking in next month. This is recorded as *Unearned Revenue* (a liability), not income — the service has not yet been rendered.

---

### 5. Monetary / Measurement Concept
Only transactions that can be **measured in monetary terms** (Philippine Peso) are recorded. Non-financial information — staff morale, brand reputation, management skill — is NOT recorded in the books.

**Example:** Dr. Nana's excellent bedside manner cannot be recorded as an asset. Only her clinic's peso-denominated transactions appear in the books.
BODY,
                        'activity'   => 'For each event, state which accounting concept applies and explain how it should be handled: (a) A business owner uses the company\'s cash to buy personal groceries. (b) The company prepares financial statements for January–December. (c) A client pays ₱50,000 in advance for services to be rendered next quarter. (d) An accountant considers recording the owner\'s strong community reputation as an intangible asset.',
                    ],
                    [
                        'deped_code' => 'ABM_FABM11-IIIa-6',
                        'title'      => 'Identify and apply basic accounting principles: Objectivity, Historical Cost, Matching, Disclosure, Materiality, Consistency, Conservatism',
                        'body'       => <<<'BODY'
## Basic Accounting Principles (GAAP)

These principles govern **how** transactions are recognized, measured, and reported.

---

### 1. Objectivity Principle
All recorded transactions must be supported by **verifiable, objective evidence** — not personal opinion.
- Official Receipts, invoices, purchase orders, and bank deposit slips are acceptable evidence.
- "I think this asset is worth ₱500,000" is NOT sufficient.

---

### 2. Historical Cost Principle
Assets are recorded at their **original acquisition cost** and are NOT adjusted to reflect changes in market value (with limited exceptions under PFRS).

**Example:** Land purchased in 2015 for ₱2,000,000 stays on the books at ₱2,000,000 even if its market value is now ₱5,000,000.

---

### 3. Matching Principle
Expenses are recognized **in the same period as the revenues they help generate** — not necessarily when cash is paid. Capital expenditures benefit future periods and are recorded as *assets*; period expenditures are recognized immediately as *expenses*.

**Example:** A commercial oven purchased for ₱35,000 benefits 5 years of operations — its cost is spread (depreciated) over those 5 years.

---

### 4. Adequate Disclosure Principle
All **material facts** that could affect a user's decision must be disclosed — either in the face of the financial statements or in the accompanying notes.

**Example:** If a company is being sued for ₱5 million, this must be disclosed in the notes even if it has not yet been recorded as a liability.

---

### 5. Materiality Principle
Only information **significant enough to influence a decision** requires full accounting treatment. Trivial amounts may be handled in the most practical way.

**Example:** Paperclips and ballpens can be expensed immediately rather than capitalized as assets — their cost is too small to affect any decision.

---

### 6. Consistency Principle
The **same accounting methods** must be applied from one period to the next. Any change must be disclosed and its effect on financial statements explained.

**Example:** If a company depreciates equipment using the straight-line method in Year 1, it must continue using the same method in Year 2 — unless a change is justified and disclosed.

---

### 7. Conservatism (Prudence) Principle
When two acceptable alternatives exist, choose the one that results in a **lower asset value or lower net income**. Anticipate probable losses; do NOT anticipate probable gains.

**Example:** A probable lawsuit loss of ₱500,000 is recorded. A probable insurance recovery of ₱200,000 is NOT recorded until actually received.

---

### Quick Reference Table

| Principle | Core Rule | Common Violation |
|-----------|-----------|-----------------|
| Objectivity | Evidence-backed entries only | Recording without source documents |
| Historical Cost | Record at original cost | Updating book value to market price |
| Matching | Match expense to revenue period | Expensing a long-term asset immediately |
| Adequate Disclosure | Disclose all material facts | Omitting footnotes about lawsuits/events |
| Materiality | Significant items only need full treatment | Capitalizing trivial items |
| Consistency | Same method each period | Switching methods to minimize taxes |
| Conservatism | Lean toward lower values when in doubt | Recording probable gains before realized |
BODY,
                        'activity'   => 'Identify which principle is violated in each case, and state the correct treatment: (1) A company updates its land from ₱2M to ₱5M because a real estate agent says it\'s now worth that. (2) An accountant records expected lawsuit winnings of ₱1M as income this year. (3) The company switches from straight-line to declining-balance depreciation every year without explanation. (4) A ₱50 box of staples is recorded as a fixed asset with a 5-year depreciation schedule.',
                    ],
                    [
                        'deped_code' => 'ABM_FABM11-IIIa-7',
                        'title'      => 'Apply accounting concepts and principles to real-world Philippine business scenarios',
                        'body'       => <<<'BODY'
## Concepts and Principles in Philippine Business Contexts

### Comprehensive Scenario: Aling Rosa's Bakery

Aling Rosa opens a bakery in Quezon City. Apply the correct accounting concept or principle to each of the following events:

| Event | Concept / Principle | Correct Treatment |
|-------|--------------------|--------------------|
| Rosa uses her ₱50,000 personal savings to start the bakery | Economic Entity Assumption | Record ₱50,000 as Owner's Capital in the bakery's books only. Rosa's personal account is separate. |
| She buys a commercial oven for ₱35,000 in 2022; it is now worth ₱20,000 | Historical Cost Principle | Keep the oven at ₱35,000 in the books. Market value changes are not recognized. |
| She delivers 200 boxes of pandesal to a school on credit in December | Accrual / Realization | Record revenue in December (when delivered), not when payment is received in January. |
| She buys ₱50 worth of toothpicks for the counter | Materiality Principle | Expense the ₱50 immediately. It is immaterial — no need to treat it as an asset. |
| A supplier dispute may result in a ₱100,000 loss; a possible ₱50,000 gain is also pending | Conservatism | Record/disclose the probable ₱100,000 loss. Do NOT record the probable ₱50,000 gain until realized. |
| She uses straight-line depreciation in 2022 and wants to switch in 2024 | Consistency | The change is allowed but must be fully disclosed with its financial effect explained. |
| A client pays ₱10,000 advance for a birthday cake order next month | Accrual / Deferred Income | Record as *Unearned Revenue* (liability). Recognize as income only when the cake is delivered. |
| Rosa's great reputation in the community | Monetary Concept | NOT recorded. Reputation has no objective peso-denominated measure. |

### Key Memory Aids

- **'Receivable' = Asset** | **'Payable' = Liability**
- **'Prepaid' = Asset** | **'Unearned' = Liability**
- **'Earned' → Income** | **'Incurred' → Expense**
- **'Unused portion' = Asset** | **'Used portion' = Expense**

### Summary Table

| Concept / Principle | One-Line Rule | Quick Test |
|---------------------|--------------|------------|
| Economic Entity | Business ≠ Owner | Are these the owner's personal funds? |
| Going Concern | Business will continue | Should we value assets at liquidation? |
| Time Period | Report at regular intervals | Is the period clearly defined? |
| Accrual Basis | Earn it or incur it = record it | Was cash received/paid relevant here? |
| Monetary Concept | Record only in money terms | Can we put a peso value on this? |
| Historical Cost | Record at original cost | Has the market value changed? |
| Matching | Match cost to revenue period | Does this expense belong to this period? |
| Adequate Disclosure | Tell users everything material | Would this change a reader's decision? |
| Materiality | Significant items get full treatment | Is the amount large enough to matter? |
| Consistency | Same method every period | Are we switching methods without reason? |
| Conservatism | Lean toward lower values | Are we recording an uncertain gain? |
BODY,
                        'activity'   => 'Create your own Philippine business scenario (sari-sari store, online shop, repair shop, etc.) with at least 6 transactions. For each transaction, identify the accounting concept or principle that applies and explain how it should be recorded.',
                    ],
                ],
            ],

            // ── CO2 ──────────────────────────────────────────────────────────
            [
                'title'       => 'The Accounting Equation',
                'description' => 'Assets = Liabilities + Owner\'s Equity — the mathematical framework behind every financial statement and journal entry.',
                'order'       => 2,
                'competencies' => [
                    [
                        'deped_code' => 'ABM_FABM11-IIIb-c-17',
                        'title'      => 'Illustrate the accounting equation and its three elements: Assets, Liabilities, and Owner\'s Equity',
                        'body'       => <<<'BODY'
## The Fundamental Accounting Equation

$$\text{ASSETS} = \text{LIABILITIES} + \text{OWNER'S EQUITY}$$

This equation must **always remain in balance**. Every business transaction rearranges these elements without ever breaking the equality.

---

### Element 1 — Assets

**Assets** are economic resources *owned or controlled* by the business that are expected to provide future economic benefits.

| Type | Description | Examples |
|------|-------------|---------|
| Current Assets | Converted to cash or used within 1 year | Cash, Accounts Receivable, Inventory, Prepaid Expenses, Supplies |
| Non-Current (Fixed) Assets | Long-term resources used in operations | Land, Building, Equipment, Vehicles, Furniture |
| Other Assets | Long-term investments, deposits | Security deposits, long-term investments |

**Example:** Mang Tomas's hardware store holds ₱45,000 cash, ₱180,000 in merchandise, ₱60,000 in shelving, and ₱500,000 land. All are assets.

---

### Element 2 — Liabilities

**Liabilities** are present obligations of the business — amounts *owed to external parties* that must be settled in the future.

| Type | Examples |
|------|---------|
| Current Liabilities (due within 1 year) | Accounts Payable, Notes Payable, Unearned Revenue, Accrued Expenses |
| Non-Current Liabilities (due beyond 1 year) | Mortgage Payable, Long-Term Bank Loans, Bonds Payable |

**Example:** Mang Tomas owes ₱80,000 to a supplier (Accounts Payable) and has a ₱200,000 bank loan (Notes Payable). These are liabilities.

---

### Element 3 — Owner's Equity

**Owner's Equity** (also called *Capital*) is the residual interest in the business after deducting all liabilities. It represents the owner's net stake.

$$\text{Owner's Equity} = \text{Assets} - \text{Liabilities}$$

Or rearranged:

$$\text{Assets} = \text{Liabilities} + \text{Owner's Equity}$$

---

### The Balance Scale Analogy

Think of the equation as a balance scale:
- **LEFT side (Debit)** = ASSETS: Cash, Receivables, Inventory, Equipment, Land
- **RIGHT side (Credit)** = LIABILITIES + EQUITY: Accounts Payable, Loans, Capital, Revenue − Expenses

No matter what transaction occurs, the scale must always remain perfectly balanced.
BODY,
                        'activity'   => 'Given the following information, solve for the missing element using A = L + OE: (1) Assets = ₱250,000; Liabilities = ₱150,000; OE = ? (2) Assets = ₱665,000; Liabilities = ?; OE = ₱347,000 (3) Assets = ?; Liabilities = ₱234,000; OE = ₱434,000 (4) Assets = ₱123,000; Liabilities = ₱23,000; OE = ?',
                    ],
                    [
                        'deped_code' => 'ABM_FABM11-IIIb-c-18',
                        'title'      => 'Perform operations involving simple cases using the accounting equation',
                        'body'       => <<<'BODY'
## Transaction Analysis Using A = L + OE

Every business transaction affects at least two elements of the accounting equation — and the equation must always stay balanced.

### Common Transaction Effects

| Transaction Type | Assets | Liabilities | Owner's Equity |
|----------------|--------|-------------|----------------|
| Owner invests cash | +Cash | — | +Capital |
| Owner invests non-cash asset | +Asset | — | +Capital |
| Buy asset for cash | +Asset, −Cash (swap) | — | — |
| Buy asset on credit | +Asset | +Payable | — |
| Pay an account payable | −Cash | −Payable | — |
| Render services for cash | +Cash | — | +Revenue |
| Render services on account | +Receivable | — | +Revenue |
| Collect accounts receivable | +Cash, −Receivable (swap) | — | — |
| Pay expense in cash | −Cash | — | −Expense |
| Owner withdraws cash | −Cash | — | −Drawings |
| Borrow from bank | +Cash | +Loan Payable | — |

### Illustrative Problem — JPacs Advertising Services

Don JPacs opens JPacs Advertising Services. Analyze each transaction:

| # | Transaction | Asset Effect | Liability Effect | Equity Effect |
|---|------------|--------------|-----------------|---------------|
| 1 | Invests ₱120,000 cash | +₱120,000 Cash | — | +₱120,000 Capital |
| 2 | Buys equipment on account ₱24,000 | +₱24,000 Equipment | +₱24,000 A/P | — |
| 3 | Pays rent ₱2,000 cash | −₱2,000 Cash | — | −₱2,000 Rent Expense |
| 4 | Receives ₱10,000 for services | +₱10,000 Cash | — | +₱10,000 Revenue |
| 5 | Pays ₱6,000 to supplier | −₱6,000 Cash | −₱6,000 A/P | — |
| 6 | Pays salaries ₱2,400 | −₱2,400 Cash | — | −₱2,400 Salary Exp. |
| 7 | Withdraws ₱3,000 for personal use | −₱3,000 Cash | — | −₱3,000 Drawings |
| 8 | Bills ₱13,200 on credit | +₱13,200 A/R | — | +₱13,200 Revenue |
| 9 | Buys supplies for cash ₱1,400 | +₱1,400 Supplies, −₱1,400 Cash | — | — |
| 10 | Collects ₱12,000 from T8 | +₱12,000 Cash, −₱12,000 A/R | — | — |

### Critical Observations

- **Asset swap** (T9, T10): Exchanging one asset for another leaves total assets unchanged and has no effect on equity.
- **Withdrawals are NOT expenses** (T7): Owner drawings reduce equity but are not recorded as an operating cost.
- **Borrowing increases both sides** (if present): +Asset AND +Liability; equity unchanged.
- The equation must balance after *every single* transaction — verify before proceeding.
BODY,
                        'activity'   => 'Prepare a running balance table for the following 5 transactions of ABC Repair Shop. Start with zero balances. Show Assets, Liabilities, and Owner\'s Equity after each transaction: (1) Owner invests ₱80,000 cash. (2) Buys tools worth ₱15,000 for cash. (3) Renders repair services for ₱8,000 cash. (4) Pays rent ₱5,000 cash. (5) Buys supplies ₱3,000 on account.',
                    ],
                    [
                        'deped_code' => 'ABM_FABM11-IIIb-c-19',
                        'title'      => 'Explain the expanded accounting equation and the role of Capital, Withdrawals, Revenue, and Expenses',
                        'body'       => <<<'BODY'
## The Expanded Accounting Equation

The basic equation is:
$$\text{Assets} = \text{Liabilities} + \text{Owner's Equity}$$

Owner's Equity can be expanded to show its components:

$$\text{Owner's Equity} = \text{Capital} - \text{Withdrawals} + \text{Revenue} - \text{Expenses}$$

Therefore, the **fully expanded equation** is:

$$\text{Assets} = \text{Liabilities} + \text{Capital} - \text{Withdrawals} + \text{Revenue} - \text{Expenses}$$

---

### The Four Components of Owner's Equity

**Capital (Increases Equity)**
The initial and additional investments made by the owner into the business. When the owner contributes cash or other assets, capital increases.

**Withdrawals / Drawings (Decreases Equity)**
Amounts the owner takes out of the business for personal use. Withdrawals reduce the owner's stake. They are NOT business expenses.

**Revenue / Income (Increases Equity)**
Money earned by the business from its operations (services rendered, goods sold). Revenue increases the owner's equity because the business has grown richer.

**Expenses (Decrease Equity)**
Costs incurred to run the business and generate revenue. Expenses decrease the owner's equity.

### Key Relationships Summary

| Element | Effect on Equity | Examples |
|---------|----------------|---------|
| Capital investment | + Increase | Owner deposits ₱100,000 |
| Owner withdrawal | − Decrease | Owner takes ₱5,000 for personal use |
| Revenue earned | + Increase | Collected ₱15,000 for services |
| Expense incurred | − Decrease | Paid ₱3,000 rent, ₱2,000 utilities |

### Important Distinction: Withdrawals vs. Expenses

Many students confuse these two. The difference:
- **Withdrawals**: The owner takes business resources for PERSONAL use. Goes directly against Capital — bypasses the income statement.
- **Expenses**: Costs incurred to generate BUSINESS revenue. Appears on the Income Statement and reduces net income, which then reduces Capital.

Both ultimately reduce Owner's Equity, but through different accounting treatments.
BODY,
                        'activity'   => 'Compute the ending Owner\'s Equity given: Beginning Capital = ₱200,000; Additional investment = ₱30,000; Total Revenue for the period = ₱95,000; Total Expenses for the period = ₱62,000; Owner\'s Drawings = ₱18,000. Then state whether the business made a profit or loss, and verify that Assets = Liabilities + Ending Equity (assume Liabilities = ₱45,000).',
                    ],
                    [
                        'deped_code' => 'ABM_FABM11-IIIb-c-20',
                        'title'      => 'Verify that the accounting equation remains balanced after each transaction',
                        'body'       => <<<'BODY'
## Verifying Equation Balance — Step by Step

After recording any transaction, you must verify:
$$\text{Total Assets} = \text{Total Liabilities} + \text{Total Owner's Equity}$$

If this check fails, an error exists in the recording.

### What Causes an Imbalance?

1. A transaction was recorded on only one side (single entry instead of double entry).
2. The wrong amounts were used on debit vs. credit sides.
3. An account was debited when it should have been credited (or vice versa).
4. An entry was made to the wrong type of account (e.g., Revenue debited instead of credited).

### Running Balance Verification Exercise

Mang Tomas's Hardware Store — Track the equation through 5 transactions:

| Transaction | Cash | + Other Assets | = A/P | + Capital | Verify |
|------------|------|---------------|-------|-----------|--------|
| Start: Owner invests ₱500,000 | ₱500,000 | ₱0 | ₱0 | ₱500,000 | ✓ 500,000 = 0 + 500,000 |
| Buys shelving ₱60,000 cash | ₱440,000 | ₱60,000 | ₱0 | ₱500,000 | ✓ 500,000 = 0 + 500,000 |
| Buys ₱80,000 inventory on credit | ₱440,000 | ₱140,000 | ₱80,000 | ₱500,000 | ✓ 580,000 = 80,000 + 500,000 |
| Earns ₱30,000 cash sales | ₱470,000 | ₱140,000 | ₱80,000 | ₱530,000 | ✓ 610,000 = 80,000 + 530,000 |
| Pays rent ₱8,000 | ₱462,000 | ₱140,000 | ₱80,000 | ₱522,000 | ✓ 602,000 = 80,000 + 522,000 |

### The Reflective Closing Principle

The accounting equation is not just a formula for solving math problems — it IS the balance sheet. When the business prepares its Statement of Financial Position at year-end, it is simply presenting the accounting equation in a formal, formatted report:

- **Left side** = Total Assets
- **Right side** = Total Liabilities + Total Owner's Equity

These must always be equal. If they are not, something is wrong with the records.
BODY,
                        'activity'   => 'The following account balances are given for Luna Beauty Salon: Cash ₱85,000; Equipment ₱120,000; Accounts Receivable ₱25,000; Accounts Payable ₱40,000; Notes Payable ₱60,000; Owner\'s Capital (beginning) ₱100,000; Net Income for period ₱38,000; Owner\'s Drawings ₱8,000. (1) Compute Ending Owner\'s Equity. (2) Verify: Total Assets = Total Liabilities + Ending Equity. (3) Does the equation balance? If not, find the error.',
                    ],
                ],
            ],

            // ── CO3 ──────────────────────────────────────────────────────────
            [
                'title'       => 'The Five Major Accounts and the Chart of Accounts',
                'description' => 'Every account in financial accounting belongs to one of five major types. Mastering these is the prerequisite for all journal work.',
                'order'       => 3,
                'competencies' => [
                    [
                        'deped_code' => 'ABM_FABM11-IIIb-c-1',
                        'title'      => 'Define and describe the five major account types: Assets, Liabilities, Capital, Income, and Expenses',
                        'body'       => <<<'BODY'
## The Five Major Account Types

Every account in financial accounting belongs to one of five major types. These are the building blocks of the entire accounting system.

---

### Account Type 1 — ASSETS
**Definition:** Present economic resources *controlled by the entity* as a result of past events, with the potential to produce economic benefits.

| Sub-type | Examples |
|----------|---------|
| Current Assets | Cash, Accounts Receivable, Notes Receivable, Merchandise Inventory, Prepaid Expenses, Office Supplies |
| Non-Current (Fixed) Assets | Land, Building, Office Equipment, Store Equipment, Delivery Equipment, Furniture and Fixtures |
| Contra Assets | Accumulated Depreciation (deducted from the related fixed asset) |

**Financial Statement:** Statement of Financial Position (Balance Sheet)

---

### Account Type 2 — LIABILITIES
**Definition:** Present obligations to *transfer an economic resource* as a result of past events — amounts the business OWES to external parties.

| Sub-type | Examples |
|----------|---------|
| Current Liabilities | Accounts Payable, Notes Payable, Accrued Salaries Payable, Unearned Revenue, Taxes Payable |
| Non-Current Liabilities | Mortgage Payable, Bonds Payable, Long-Term Loans Payable |

**Financial Statement:** Statement of Financial Position (Balance Sheet)

---

### Account Type 3 — CAPITAL (Owner's Equity)
**Definition:** The residual interest in assets after deducting all liabilities — the owner's net stake in the business.

Components:
- **Capital Account** — initial and additional investments
- **Withdrawals (Drawings)** — owner takes out resources; reduces equity
- **Income** — increases equity through earned revenue
- **Expenses** — decreases equity through incurred costs

**Financial Statement:** Statement of Changes in Equity

---

### Account Type 4 — INCOME (Revenue)
**Definition:** Increases in assets (or decreases in liabilities) resulting in an increase in equity — other than owner contributions.

| Type | Examples |
|------|---------|
| Operating Revenue | Service Fees, Sales Revenue, Professional Fees, Consulting Income, Tuition Fees |
| Other Income | Interest Income, Rent Income, Gain on Sale of Assets, Commission Income |

**Financial Statement:** Income Statement

---

### Account Type 5 — EXPENSES
**Definition:** Decreases in assets (or increases in liabilities) resulting in a decrease in equity — other than distributions to owners.

| Type | Examples |
|------|---------|
| Operating Expenses | Salaries Expense, Rent Expense, Utilities Expense, Supplies Expense, Depreciation Expense, Insurance Expense |
| Non-Operating Expenses | Interest Expense, Loss on Disposal of Assets, Bad Debts Expense |
| Cost of Sales | Cost of Goods Sold / Cost of Sales (for trading businesses) |

**Financial Statement:** Income Statement

---

### Quick Reference

| Type | What It Represents | Statement | Account Series |
|------|-------------------|-----------|----|
| Assets | What the business OWNS | Balance Sheet | 100–199 |
| Liabilities | What the business OWES | Balance Sheet | 200–299 |
| Capital | The owner's NET STAKE | Statement of Changes in Equity | 300–399 |
| Income | What the business EARNS | Income Statement | 400–499 |
| Expenses | Costs INCURRED to operate | Income Statement | 500–599 |
BODY,
                        'activity'   => 'Classify each account into one of the five major types AND identify which financial statement it appears on: (1) Cash (2) Accounts Payable (3) Service Fees Earned (4) Salaries Expense (5) Office Equipment (6) Santos, Capital (7) Unearned Revenue (8) Rent Expense (9) Merchandise Inventory (10) Notes Payable (11) Advertising Income (12) Santos, Drawings (13) Prepaid Insurance (14) Depreciation Expense (15) Accounts Receivable.',
                    ],
                    [
                        'deped_code' => 'ABM_FABM11-IIIb-c-2',
                        'title'      => 'Classify given accounts into their correct major type and explain their placement in financial statements',
                        'body'       => <<<'BODY'
## Classifying Accounts — Rules and Common Traps

### Classification Rules

1. **Does the business OWN or control this resource?** → Asset
2. **Does the business OWE this to someone outside?** → Liability
3. **Is this the owner's net stake?** → Capital/Equity
4. **Did the business EARN this from operations?** → Income
5. **Was this a cost INCURRED to operate?** → Expense

### Common Classification Errors

| Account | Common Mistake | Correct Classification | Why |
|---------|---------------|----------------------|-----|
| Unearned Revenue | Classified as Income | **Liability** | Service not yet rendered; business owes the service to the customer |
| Accounts Receivable | Confused with income | **Asset** | Money owed TO the business — it's a resource |
| Accounts Payable | Confused with expense | **Liability** | Money the business OWES to others |
| Owner's Drawings | Classified as expense | **Contra-Equity** | It reduces owner's equity directly; it is NOT an operating cost |
| Prepaid Expenses | Classified as expense | **Asset** | The benefit has not yet been received; it's a resource |
| Accumulated Depreciation | Classified as expense | **Contra-Asset** | It is deducted from the related fixed asset on the balance sheet |
| Interest Payable | Classified as expense | **Liability** | The expense has been incurred but cash not yet paid — the unpaid amount is owed |

### Financial Statement Placement Rules

| Financial Statement | Accounts That Appear | Time Reference |
|--------------------|---------------------|---------------|
| Statement of Financial Position (Balance Sheet) | Assets, Liabilities, Owner's Capital | As of a specific date |
| Income Statement | Revenue and Expenses | For a period (month, quarter, year) |
| Statement of Changes in Equity | Capital, Net Income, Drawings | For a period |

### The Equation Connection

The financial statements are connected:
- Net Income from the **Income Statement** flows into the **Statement of Changes in Equity**.
- Ending Capital from the **Statement of Changes in Equity** appears on the **Balance Sheet**.
- The Balance Sheet proves: **Assets = Liabilities + Owner's Equity**.
BODY,
                        'activity'   => 'For each account, state: (a) Major type, (b) Normal balance (debit or credit), (c) Which financial statement it appears on: Prepaid Rent, Salaries Payable, Commission Income, Accumulated Depreciation — Equipment, Notes Receivable, Interest Expense, Owner\'s Drawings, Unearned Service Revenue, Cost of Goods Sold, Land.',
                    ],
                    [
                        'deped_code' => 'ABM_FABM11-IIIb-c-3',
                        'title'      => 'Cite at least three examples of each account type from a service and trading business',
                        'body'       => <<<'BODY'
## Examples by Account Type — Service vs. Trading Business

Understanding the differences between a **service business** (sells services) and a **trading/merchandising business** (buys and resells goods) is essential for proper account identification.

### Assets

| Account | Service Business | Trading Business |
|---------|-----------------|-----------------|
| Cash | Clinic/salon cash collections | Store cash sales and collections |
| Accounts Receivable | Fees billed but not yet collected | Goods sold on credit to customers |
| Merchandise Inventory | *(not applicable)* | Goods purchased for resale |
| Prepaid Insurance | Insurance paid in advance | Insurance paid in advance |
| Equipment | Dental chair, computer, tools | Forklift, delivery truck, store fixtures |
| Land/Building | Clinic/office location | Warehouse/store location |

### Liabilities

| Account | Service Business | Trading Business |
|---------|-----------------|-----------------|
| Accounts Payable | Supplies bought on credit | Merchandise purchased on credit |
| Notes Payable | Bank loan for clinic renovation | Bank loan to finance inventory |
| Unearned Revenue | Advance retainer from client | Layaway deposit from customer |
| Salaries Payable | Unpaid staff wages at month-end | Unpaid store employee wages |

### Income

| Account | Service Business | Trading Business |
|---------|-----------------|-----------------|
| Primary revenue | Service Fees Earned, Professional Fees, Tuition Fees, Consulting Revenue | Sales Revenue, Sales |
| Other income | Interest Income, Rent Income | Interest Income, Commission Income, Gain on Sale |

### Expenses

| Account | Service Business | Trading Business |
|---------|-----------------|-----------------|
| Primary expense | Salaries, Rent, Utilities, Supplies Used, Depreciation | Cost of Goods Sold (COGS), Salaries, Rent, Utilities |
| Other expenses | Interest Expense | Interest Expense, Freight-Out, Bad Debts |

### The Cost of Goods Sold Difference

The key distinction: a trading business has **Cost of Goods Sold (COGS)** — the direct cost of the merchandise sold. A service business has no equivalent because it sells services, not physical goods.

**Trading Business Income Statement:**
> Net Sales − COGS = **Gross Profit** − Operating Expenses = **Net Income**

**Service Business Income Statement:**
> Service Revenue − Operating Expenses = **Net Income**
BODY,
                        'activity'   => 'You are the accountant for two businesses: (A) Luna Beauty Salon (service) and (B) Reyes Hardware Store (trading). For each business, list 5 asset accounts, 3 liability accounts, 2 income accounts, and 4 expense accounts that are typical for that business type. Identify 2 accounts that appear in BOTH businesses and 2 accounts unique to each.',
                    ],
                    [
                        'deped_code' => 'ABM_FABM11-IIIb-c-4',
                        'title'      => 'Prepare a complete and properly numbered Chart of Accounts for a sole proprietorship',
                        'body'       => <<<'BODY'
## The Chart of Accounts (COA)

### What Is a Chart of Accounts?

A **Chart of Accounts** is a systematic, numbered list of all accounts used by a business. It is the master reference that ensures every transaction is recorded in the correct account using a consistent numbering system.

### Why It Matters
- Provides a **standardized reference system** for all transactions
- Ensures **consistency** across all financial records and reports
- Makes it easy to **locate any account quickly**
- Groups accounts by type, which simplifies financial statement preparation

### Standard Numbering System (Philippine Convention for Sole Proprietorships)

| Series | Account Type | Financial Statement |
|--------|-------------|---------------------|
| 100–199 | Assets | Balance Sheet |
| 200–299 | Liabilities | Balance Sheet |
| 300–399 | Owner's Equity / Capital | Statement of Changes in Equity |
| 400–499 | Income / Revenue | Income Statement |
| 500–599 | Expenses | Income Statement |

### Sample Chart of Accounts — JP Advertising Services

| Acct. No. | Account Title | Type |
|-----------|--------------|------|
| **ASSETS** | | |
| 101 | Cash | Current Asset |
| 102 | Accounts Receivable | Current Asset |
| 103 | Office Supplies | Current Asset |
| 104 | Prepaid Rent | Current Asset |
| 151 | Office Equipment | Non-Current Asset |
| 152 | Accumulated Depreciation — Office Equipment | Contra Asset |
| **LIABILITIES** | | |
| 201 | Accounts Payable | Current Liability |
| 202 | Notes Payable | Current Liability |
| 203 | Accrued Salaries Payable | Current Liability |
| **OWNER'S EQUITY** | | |
| 301 | JP, Capital | Owner's Equity |
| 302 | JP, Withdrawals | Drawings |
| **INCOME** | | |
| 401 | Advertising Income | Revenue |
| 402 | Service Fees Earned | Revenue |
| 403 | Interest Income | Other Income |
| **EXPENSES** | | |
| 501 | Salary Expense | Operating Expense |
| 502 | Rent Expense | Operating Expense |
| 503 | Utilities Expense | Operating Expense |
| 504 | Office Supplies Expense | Operating Expense |
| 505 | Depreciation Expense — Office Equipment | Operating Expense |
| 506 | Miscellaneous Expense | Operating Expense |

### Tips for Building a COA

1. Leave gaps in numbering (e.g., use 101, 102, 103 rather than 101, 102 then jump to 110) so new accounts can be inserted later without renumbering.
2. Use consistent naming — if one account is "Salaries Expense," don't use "Salary Expense" elsewhere.
3. Number accounts in the order they appear in financial statements (assets → liabilities → equity → income → expenses).
BODY,
                        'activity'   => 'Prepare a complete Chart of Accounts for LUNA Beauty Salon, a sole proprietorship owned by Ms. Luna Reyes. Use the standard numbering convention (100s–500s). Include at least 20 accounts. The business rents its space, employs 3 staff, sells beauty services, and also earns commission from product referrals. Organize your COA into the five major account types.',
                    ],
                ],
            ],

            // ── CO4 ──────────────────────────────────────────────────────────
            [
                'title'       => 'Books of Accounts — Journal, Ledger, and Posting',
                'description' => 'The two books that make financial recording possible: the journal (book of original entry) and the ledger (book of final entry), and how posting links them.',
                'order'       => 4,
                'competencies' => [
                    [
                        'deped_code' => 'ABM_FABM11-IIIf-22',
                        'title'      => 'Identify and explain the uses of the General Journal and General Ledger',
                        'body'       => <<<'BODY'
## The Two Books of Accounts

Accounting relies on two main books, each with a distinct purpose:

| | General Journal | General Ledger |
|-|----------------|----------------|
| Also called | Book of Original Entry | Book of Final Entry |
| Purpose | Records WHAT happened, to WHICH accounts, and in WHAT amounts | Shows the RUNNING BALANCE of each account |
| Organization | Chronological (by date) | By account title |
| Step in cycle | Step 2: Journalizing | Step 3: Posting |
| Column format | Date, Account Titles, PR, Debit, Credit | Date, Item, PR, Debit, Credit, Balance |
| Used | FIRST — for every transaction | SECOND — data comes from the journal |
| Why needed | Provides a complete dated record; essential for tracing, auditing, correcting errors | Provides the account balances needed for Trial Balance and financial statements |

---

### The General Journal

The general journal is the **starting point** for every transaction. It records transactions in **chronological order** using the double-entry format:

```
Date | Account Debited        | PR | Debit  |        |
     |   Account Credited     | PR |        | Credit |
     | Narration              |    |        |        |
```

Key rules:
- **Debited accounts** are written first, at the left margin.
- **Credited accounts** are written below, indented.
- A brief **narration** (explanation) follows each entry.
- The **PR column** is left blank when journalizing; filled in only after posting.

---

### The General Ledger

The general ledger contains **one account page per account title** in the Chart of Accounts. After a journal entry is recorded, it is **posted** (transferred) to the appropriate ledger accounts.

The standard ledger format (balance-column form):

| Date | Item/Explanation | Ref. | Debit (₱) | Credit (₱) | Balance (₱) |
|------|-----------------|------|-----------|------------|-------------|
| Jan. 1 | Investment by owner | GJ 1 | 30,000 | | 30,000 |
| Jan. 2 | Supplies purchased | GJ 1 | | 2,500 | 27,500 |

The **running balance** column makes it easy to see the current balance of any account at any point in time — without having to search through the journal.

---

### Why Both Books Are Necessary

Neither book alone is sufficient:
- The journal shows *when* and *why* each transaction happened, but you cannot quickly find the total Cash balance by scanning chronological entries.
- The ledger shows the balance of each account, but it does not show the full transaction details or their dates.
- **Together**, they provide a complete, cross-referenced accounting record.
BODY,
                        'activity'   => 'For each scenario, state whether you would primarily consult the General Journal or the General Ledger, and explain why: (1) You need to know the total balance of Accounts Receivable right now. (2) You need to find out what transactions were recorded on March 15. (3) You need to verify the narration for a specific journal entry. (4) You need to prepare a Trial Balance. (5) You need to trace when a specific customer\'s payment was recorded.',
                    ],
                    [
                        'deped_code' => 'ABM_FABM11-IIIf-23',
                        'title'      => 'Illustrate the format of a General Journal and the four Special Journals',
                        'body'       => <<<'BODY'
## Special Journals — Efficient Recording for High-Volume Transactions

When a business has many similar transactions, using only the general journal becomes inefficient. **Special journals** are designed for one specific type of recurring transaction.

### Overview of All Five Journals

| Journal | Transactions Recorded | Source Document | Posting Rule |
|---------|----------------------|----------------|--------------|
| General Journal (GJ) | All non-special transactions; adjusting/closing entries | Any | Post each entry individually |
| Sales Journal (SJ) | Credit sales only | Sales Invoice | Post column totals at month-end; individual amounts to A/R subsidiary |
| Purchases Journal (PJ) | Credit purchases only | Supplier Invoice | Post column totals at month-end; individual amounts to A/P subsidiary |
| Cash Receipts Journal (CRJ) | All cash received | Official Receipt | Post column totals at month-end; sundry items individually |
| Cash Disbursements Journal (CDJ) | All cash paid out | Check/Cash Voucher | Post column totals at month-end; sundry items individually |

---

### Sales Journal (SJ) — Sales on Credit

**Rule:** Debit Accounts Receivable; Credit Sales Revenue.

| Date | Customer | PR | Invoice No. | Dr: A/R | Cr: Sales |
|------|----------|----|-------------|---------|-----------|
| July 5 | Santos Grocery | | SI-001 | ₱8,000 | ₱8,000 |
| July 12 | Cruz Trading | | SI-002 | ₱15,500 | ₱15,500 |
| **TOTAL** | | | | **₱23,500** | **₱23,500** |

> *Cash sales go to the Cash Receipts Journal — NOT here.*

---

### Purchases Journal (PJ) — Purchases on Credit

**Rule:** Debit Purchases/Asset account; Credit Accounts Payable.

| Date | Supplier | PR | Invoice No. | Dr: Purchases | Cr: A/P |
|------|----------|----|-------------|--------------|---------|
| Aug. 2 | ABC Supplies | | PI-101 | ₱25,000 | ₱25,000 |
| Aug. 10 | Metro Wholesale | | PI-102 | ₱40,000 | ₱40,000 |
| **TOTAL** | | | | **₱65,000** | **₱65,000** |

> *Cash purchases go to the Cash Disbursements Journal — NOT here.*

---

### Cash Receipts Journal (CRJ) — All Cash Received

**Rule:** Always Debit Cash. Credit varies (Sales, A/R, Capital, Loans).

| Date | Description | PR | Dr: Cash | Cr: Sales | Cr: A/R | Cr: Sundry |
|------|-------------|----|----------|-----------|---------|------------|
| Sept. 1 | Owner investment | | ₱200,000 | | | ₱200,000 |
| Sept. 5 | Cash sales | | ₱18,000 | ₱18,000 | | |
| Sept. 10 | Collection — Santos | | ₱8,000 | | ₱8,000 | |

> *Sundry = Irregular items without their own column (e.g., capital, loan proceeds). Post individually.*

---

### Cash Disbursements Journal (CDJ) — All Cash Paid Out

**Rule:** Always Credit Cash. Debit varies (A/P, Salaries, Rent, etc.).

| Date | Description | Check No. | Cr: Cash | Dr: A/P | Dr: Salaries | Dr: Sundry |
|------|-------------|-----------|----------|---------|-------------|------------|
| Oct. 1 | Monthly rent | CV-001 | ₱8,000 | | | ₱8,000 |
| Oct. 5 | Employee salaries | CV-002 | ₱15,000 | | ₱15,000 | |
| Oct. 12 | Supplier payment | CV-003 | ₱20,000 | ₱20,000 | | |

---

### Month-End Posting Rule

For SJ, PJ, CRJ, CDJ: post **only column totals** to the General Ledger at month-end. Post individual line items to the appropriate **subsidiary ledger** daily or as they occur.
BODY,
                        'activity'   => 'Classify each transaction into the correct journal (GJ, SJ, PJ, CRJ, or CDJ): (1) Sold goods worth ₱12,000 to a customer who will pay in 30 days. (2) Paid monthly electricity bill ₱3,200 by check. (3) Received ₱8,000 cash from a customer settling their account. (4) Purchased ₱50,000 merchandise from a supplier on 60-day credit terms. (5) Owner invested an additional ₱100,000 cash. (6) Recorded depreciation of equipment for the month. (7) Paid employee salaries ₱25,000 by check.',
                    ],
                    [
                        'deped_code' => 'ABM_FABM11-IIIf-24',
                        'title'      => 'Illustrate the format of a General Ledger and a Subsidiary Ledger and explain reconciliation with the control account',
                        'body'       => <<<'BODY'
## Ledger Formats and Subsidiary Ledger Reconciliation

### Step-by-Step Posting Process

After journalizing, every entry must be posted to the ledger. Follow these four steps for each account in a journal entry:

| Step | Action |
|------|--------|
| 1 | Locate the account page in the general ledger. |
| 2 | Enter the date, journal page reference (e.g., GJ 1), and the debit or credit amount. |
| 3 | Compute the new running balance. |
| 4 | Write the account number in the journal's PR column to confirm posting is complete. |

---

### General Ledger — Balance-Column Format

**Account: Cash — Account No. 110**

| Date | Item | Ref. | Debit (₱) | Credit (₱) | Balance (₱) |
|------|------|------|-----------|------------|-------------|
| Jan. 1 | Investment by owner | GJ 1 | 150,000 | | 150,000 |
| Jan. 3 | Art supplies | GJ 1 | | 8,000 | 142,000 |
| Jan. 10 | Services rendered | GJ 1 | 22,000 | | 164,000 |
| Jan. 20 | Salaries paid | GJ 1 | | 12,000 | 152,000 |
| Jan. 25 | Equipment payment | GJ 1 | | 15,000 | 137,000 |
| Jan. 31 | Rent expense | GJ 1 | | 6,000 | 131,000 |

---

### Subsidiary Ledger — Individual Customer Cards

A **subsidiary ledger** provides detailed, individual records for one control account. The most common subsidiary ledgers:

- **Accounts Receivable Subsidiary Ledger** — one card per customer
- **Accounts Payable Subsidiary Ledger** — one card per supplier

**Customer: Santos Grocery — Customer No. C-001**

| Date | Explanation | PR | Debit (₱) | Credit (₱) | Balance (₱) |
|------|-------------|----|-----------|------------|-------------|
| July 5 | Sales on account (SI-001) | SJ1 | 8,000 | | 8,000 |
| Sept. 10 | Collection on account | CRJ1 | | 8,000 | — |

**Customer: Cruz Trading — Customer No. C-002**

| Date | Explanation | PR | Debit (₱) | Credit (₱) | Balance (₱) |
|------|-------------|----|-----------|------------|-------------|
| July 12 | Sales on account (SI-002) | SJ1 | 15,500 | | 15,500 |
| Aug. 1 | Partial collection | CRJ1 | | 5,500 | 10,000 |

---

### Reconciliation — Subsidiary Ledger vs. Control Account

The **control account** in the General Ledger must always equal the **sum of all individual balances** in the subsidiary ledger.

| Accounts Receivable Subsidiary Ledger Summary | Amount |
|----------------------------------------------|--------|
| Santos Grocery balance | ₱0 |
| Cruz Trading balance | ₱10,000 |
| Reyes Store balance | ₱6,200 |
| **Total of all customers** | **₱16,200** |
| **A/R Control Account (General Ledger)** | **₱16,200** |
| **Difference (must be zero)** | **₱0 ✓** |

> **Key Rule:** If the subsidiary totals ≠ control account, there is a posting error that must be found before the Trial Balance can be prepared.
BODY,
                        'activity'   => 'The following customers have accounts receivable balances: Maria Santos ₱12,000; Pedro Reyes ₱8,500; Ana Cruz ₱5,000. The Accounts Receivable control account in the General Ledger shows ₱27,000. (1) Do the subsidiary and control account reconcile? (2) If not, what is the discrepancy and what might have caused it? (3) Create a sample subsidiary ledger card for Maria Santos showing two transactions: an initial sale of ₱12,000 on May 1 and a partial collection of ₱4,000 on May 15.',
                    ],
                ],
            ],

            // ── CO5 ──────────────────────────────────────────────────────────
            [
                'title'       => 'Business Transactions and Their Analysis — Steps 1–3 of the Accounting Cycle',
                'description' => 'How to analyze business transactions, journalize them in the General Journal, and post them to the General Ledger.',
                'order'       => 5,
                'competencies' => [
                    [
                        'deped_code' => 'ABM_FABM11-IIIa-b-1',
                        'title'      => 'Step 1 — Analyze business transactions using source documents and the accounting equation',
                        'body'       => <<<'BODY'
## Step 1: Analyzing Business Transactions

### What Is a Business Transaction?

A **business transaction** is any economic event that:
1. Affects the assets, liabilities, or owner's equity of a business, **AND**
2. Can be measured in monetary terms.

Not every business activity is a transaction:

| Business Event | Transaction? | Reason |
|---------------|-------------|--------|
| Owner invests ₱60,000 cash | ✅ Yes | Assets and equity both increase — measurable effect |
| Business hires an employee | ❌ Not yet | No financial exchange has occurred yet |
| Buys supplies ₱3,000 on credit | ✅ Yes | Assets increase; liabilities increase |
| Owner decides to buy equipment | ❌ Not yet | Only a decision — no exchange |
| Business earns ₱8,000 from services | ✅ Yes | Assets and revenue increase |

---

### Source Documents — Starting Point of Every Analysis

Every transaction begins with a **source document** — written evidence that it occurred:

| Document | Description | Transaction It Evidences |
|----------|-------------|--------------------------|
| Official Receipt (OR) | Issued when cash is received | Cash received for services |
| Sales Invoice | Issued for goods/services sold on credit | Revenue earned; A/R created |
| Purchase Invoice | Received from supplier | Asset/supplies acquired; A/P created |
| Check | Written order to pay from bank | Cash payment made |
| Bank Deposit Slip | Record of bank deposit | Cash deposited |
| Voucher / Acknowledgment Receipt | Internal payment authorization | Expense recorded |

---

### The Analysis Process — Four Questions

For each transaction, ask:
1. **What accounts are affected?**
2. **What is the type of each account?** (Asset / Liability / Equity / Revenue / Expense)
3. **Is each account increasing or decreasing?**
4. **Does the accounting equation remain balanced?**

### Illustrative Analysis — Pamilya Services (July 2019)

| Transaction | Accounts Affected | Effect on Equation |
|-------------|------------------|--------------------|
| T1: Owner invests ₱60,000 cash | Cash ↑; Capital ↑ | A ↑ = OE ↑ |
| T2: Borrows ₱50,000 from bank | Cash ↑; Loans Payable ↑ | A ↑ = L ↑ |
| T3: Buys equipment ₱30,000 cash | Equipment ↑; Cash ↓ | A ↑ and A ↓ (net: 0) |
| T4: Buys supplies ₱10,000 on credit | Supplies ↑; A/P ↑ | A ↑ = L ↑ |
| T5: Earns ₱10,000 cash for services | Cash ↑; Revenue ↑ | A ↑ = OE ↑ |
| T6: Pays rent ₱10,000 | Rent Exp. ↑; Cash ↓ | A ↓ = OE ↓ |
| T7: Pays salaries ₱4,000 | Salaries Exp. ↑; Cash ↓ | A ↓ = OE ↓ |
| T8: Pays taxes ₱2,000 | Taxes Exp. ↑; Cash ↓ | A ↓ = OE ↓ |
| T9: Pays utilities ₱2,500 | Utilities Exp. ↑; Cash ↓ | A ↓ = OE ↓ |
| T10: Owner withdraws ₱500 | Drawings ↑; Cash ↓ | A ↓ = OE ↓ |

> **T3 is an asset swap**: both accounts are assets — total assets do not change, and equity is unaffected.
BODY,
                        'activity'   => 'Analyze the following transactions for Kabarkada Services. For each, identify: (a) Accounts affected, (b) Type of each account, (c) Whether it increases or decreases, (d) Effect on A = L + OE: (1) Owner invests ₱150,000 cash. (2) Borrows ₱100,000 from BPI. (3) Purchases computer equipment ₱45,000 for cash. (4) Renders IT services on credit ₱28,000. (5) Pays monthly rent ₱12,000. (6) Collects ₱15,000 from a client account.',
                    ],
                    [
                        'deped_code' => 'ABM_FABM11-IIIa-b-2',
                        'title'      => 'Step 2 — Journalize business transactions in the General Journal using proper debit and credit entries',
                        'body'       => <<<'BODY'
## Step 2: Journalizing — Recording in the General Journal

### Rules of Debit and Credit

| Account Type | Normal Balance | To Increase | To Decrease |
|-------------|---------------|-------------|-------------|
| Assets | DEBIT | Debit | Credit |
| Liabilities | CREDIT | Credit | Debit |
| Owner's Capital | CREDIT | Credit | Debit |
| Owner's Drawings | DEBIT | Debit | Credit |
| Revenue / Income | CREDIT | Credit | Debit |
| Expenses | DEBIT | Debit | Credit |

**Memory Aid:** **ADE** = Debit (Assets, Drawings, Expenses) | **LCR** = Credit (Liabilities, Capital, Revenues)

---

### Journal Entry Format

```
Date | Account Debited (flush left)  | PR | Debit  |        |
     |   Account Credited (indented) | PR |        | Credit |
     | Narration                     |    |        |        |
```

Rules:
- Debited accounts are written **first**, flush with the left margin.
- Credited accounts are written **below**, indented.
- A **narration** (explanation) closes each entry.
- The **PR column** is left blank — filled only after posting.

---

### Illustrative Journal Entries — Pamilya Services

| Date | Account / Explanation | PR | Debit | Credit |
|------|----------------------|----|-------|--------|
| July 1 | Cash | 101 | 60,000 | |
| | &nbsp;&nbsp;Pamilya, Capital | 301 | | 60,000 |
| | *T1: Owner's initial investment* | | | |
| July 2 | Cash | 101 | 50,000 | |
| | &nbsp;&nbsp;Loans Payable | 202 | | 50,000 |
| | *T2: Bank loan obtained* | | | |
| July 3 | Service Equipment | 104 | 30,000 | |
| | &nbsp;&nbsp;Cash | 101 | | 30,000 |
| | *T3: Equipment purchased for cash* | | | |
| July 5 | Supplies | 103 | 10,000 | |
| | &nbsp;&nbsp;Accounts Payable | 201 | | 10,000 |
| | *T4: Supplies on account* | | | |
| July 10 | Cash | 101 | 10,000 | |
| | &nbsp;&nbsp;Service Revenue | 401 | | 10,000 |
| | *T5: Services for cash* | | | |

---

### Simple vs. Compound Entries

- **Simple Entry**: Two accounts (one debit, one credit). Most common.
- **Compound Entry**: Three or more accounts. Total debits still = total credits.

**Example of compound entry:**
A business buys a motorcycle for ₱110,000 — pays ₱80,000 cash and the balance on account.

```
Motorcycle          110,000
   Cash                      80,000
   Accounts Payable          30,000
Purchased motorcycle; partial cash, balance on account.
```
BODY,
                        'activity'   => 'Journalize the following transactions for Kabarkada Services (use proper format — accounts, PR column, debit/credit amounts, narration): (1) June 1: Owner invested ₱200,000 cash and a laptop worth ₱35,000. (2) June 3: Purchased office supplies ₱8,000 cash. (3) June 5: Rendered services to ABC Corp. for ₱45,000 on account. (4) June 10: Paid monthly rent ₱18,000 by check. (5) June 15: Collected ₱30,000 from ABC Corp. on account. (6) June 20: Paid salaries ₱22,000 cash.',
                    ],
                    [
                        'deped_code' => 'ABM_FABM11-IIIc-d-3',
                        'title'      => 'Step 3 — Post journal entries to the General Ledger and compute running balances',
                        'body'       => <<<'BODY'
## Step 3: Posting to the General Ledger

### Why Posting Matters

The journal records transactions in time order, but you cannot quickly find "total Cash" by scanning all journal entries. **Posting** transfers each debit and credit to the appropriate ledger account, where a running balance is maintained for every account.

### The Posting Procedure

For each account in a journal entry, follow five steps:

| Step | Action |
|------|--------|
| 1 | Find the account's ledger page. |
| 2 | Enter the date from the journal entry. |
| 3 | Write the journal page reference (e.g., GJ 1) in the ledger's PR column. |
| 4 | Copy the debit or credit amount to the correct column. |
| 5 | Compute and enter the new running balance. |
| 6 | Write the ledger account number in the journal's PR column. (Confirms posting.) |

---

### Illustrated Ledger — Cash Account (Pamilya Services)

**Account No. 101 — Cash**

| Date | Explanation | P.R. | Debit | Credit | Balance |
|------|-------------|------|-------|--------|---------|
| July 1 | Owner investment (T1) | J1 | 60,000 | | 60,000 Dr |
| July 2 | Bank loan (T2) | J1 | 50,000 | | 110,000 Dr |
| July 3 | Equipment purchase (T3) | J1 | | 30,000 | 80,000 Dr |
| July 10 | Services rendered (T5) | J1 | 10,000 | | 90,000 Dr |
| July 15 | Rent paid (T6) | J1 | | 10,000 | 80,000 Dr |
| July 20 | Salaries paid (T7) | J1 | | 4,000 | 76,000 Dr |
| July 25 | Taxes paid (T8) | J1 | | 2,000 | 74,000 Dr |
| July 28 | Utilities paid (T9) | J1 | | 2,500 | 71,500 Dr |
| July 31 | Owner withdrawal (T10) | J1 | | 500 | 71,000 Dr |

---

### The Accounting Cycle So Far

```
Business Event → Source Document → Step 1: Analyze → Step 2: Journalize → Step 3: Post → (Step 4: Trial Balance)
```

Each step feeds the next. You cannot post without journalizing first; you cannot prepare a trial balance without completed posting. After all transactions for the period are posted, the ledger balances are ready for Step 4.

---

### The PR Column — The Audit Trail

The PR column in the journal (account number) and the PR column in the ledger (journal page) create a **cross-reference**:
- From any ledger account → trace back to the original journal entry.
- From any journal entry → find the corresponding ledger account.

This cross-reference is essential for auditing, error detection, and financial verification.
BODY,
                        'activity'   => 'Post the following journal entries to T-accounts (or balance-column form ledger accounts) and compute the ending balance for each account. Chart of Accounts: Cash (101), A/R (102), Equipment (151), A/P (201), Capital (301), Service Revenue (401), Rent Expense (501). Entries: (1) Cash 80,000 / Capital 80,000; (2) Equipment 25,000 / Cash 25,000; (3) Cash 15,000 / Service Revenue 15,000; (4) A/R 10,000 / Service Revenue 10,000; (5) Rent Expense 8,000 / Cash 8,000; (6) Cash 6,000 / A/R 6,000. After posting, verify: Total Debit Balances = Total Credit Balances.',
                    ],
                ],
            ],

            // ── CO6 ──────────────────────────────────────────────────────────
            [
                'title'       => 'The Trial Balance and Adjusting Entries — Steps 4–5 of the Accounting Cycle',
                'description' => 'Verify mathematical accuracy with a Trial Balance, then bring accounts up to date with adjusting entries before preparing financial statements.',
                'order'       => 6,
                'competencies' => [
                    [
                        'deped_code' => 'ABM_FABM11-IVa-d-32',
                        'title'      => 'Step 4 — Prepare a Trial Balance using all ledger account balances',
                        'body'       => <<<'BODY'
## Step 4: Preparing the Trial Balance

### What Is a Trial Balance?

A **trial balance** is a formal listing of all open ledger accounts with their respective debit or credit balances at a given point in time. Its purpose is to verify that **total debits = total credits** — confirming mathematical accuracy before financial statements are prepared.

> ⚠️ A balanced trial balance does NOT mean the records are error-free. It only confirms mathematical equality.

---

### Characteristics of a Trial Balance

- Prepared **after** all journal entries have been journalized and posted.
- Each account appears **only once** with either a debit or credit balance.
- Accounts with **zero balances are skipped**.
- The peso sign appears only beside the **first figure** in each column and beside the **totals**.
- Both column totals must be **double-ruled** when finalized.

---

### Normal Balance Review

| Account Type | Normal Balance | Example Accounts |
|-------------|---------------|-----------------|
| Assets | DEBIT | Cash, A/R, Supplies, Equipment |
| Liabilities | CREDIT | A/P, Notes Payable, Unearned Revenue |
| Owner's Capital | CREDIT | Capital account |
| Owner's Drawings | DEBIT | Drawings / Withdrawals |
| Revenue | CREDIT | Service Revenue, Sales |
| Expenses | DEBIT | Salaries, Rent, Utilities |
| Accumulated Depreciation | CREDIT | Contra-asset (credit balance) |

**Memory Aid: ADE = Debit** (Assets, Drawings, Expenses) | **LCR = Credit** (Liabilities, Capital, Revenue)

---

### Illustrative Trial Balance — Pamilya Services (July 31, 2019)

| Acct. No. | Account Title | Debit (₱) | Credit (₱) |
|-----------|--------------|-----------|------------|
| 101 | Cash | 63,500 | |
| 103 | Supplies | 10,000 | |
| 104 | Service Equipment | 30,000 | |
| 201 | Accounts Payable | | 2,500 |
| 202 | Loans Payable | | 50,000 |
| 301 | Pamilya, Capital | | 60,000 |
| 302 | Pamilya, Drawings | 500 | |
| 401 | Service Revenue | | 10,000 |
| 501 | Taxes and Licenses | 2,000 | |
| 502 | Salaries Expense | 4,000 | |
| 503 | Utilities Expense | 2,500 | |
| 504 | Rent Expense | 10,000 | |
| **TOTAL** | | **₱122,500** | **₱122,500** |

*Note: Accounts Receivable (A/R No. 102) is not listed because its balance is zero.*

---

### Steps to Prepare the Trial Balance

1. Write the **heading** (Business name / "Trial Balance" / Specific date).
2. List all open accounts in ledger order (Assets → Liabilities → Equity → Revenue → Expenses).
3. Place each balance in the correct column based on its normal balance.
4. Total both columns.
5. Double-rule the totals if balanced.
BODY,
                        'activity'   => 'Using the following ledger balances for Kabarkada Services (June 30, 2020), prepare a properly formatted Trial Balance. Verify that total debits = total credits (answer: ₱582,400 each side): Cash ₱22,200; A/R ₱12,000; Supplies ₱18,000; Prepaid Insurance ₱14,400; Prepaid Rent ₱8,000; Office Equipment ₱60,000; Service Vehicle ₱420,000; A/P ₱53,000; Notes Payable ₱205,600; Utilities Payable ₱1,400; Unearned Revenues ₱10,000; Kabarkada Capital ₱250,000; Kabarkada Withdrawals ₱14,000; Service Revenues ₱62,400; Salaries Expense ₱13,800.',
                    ],
                    [
                        'deped_code' => 'ABM_FABM11-IVa-d-33',
                        'title'      => 'Step 5 — Journalize and post adjusting entries (five types)',
                        'body'       => <<<'BODY'
## Step 5: Adjusting Entries

### Why Adjusting Entries Are Needed

The trial balance confirms mathematical accuracy, but some account balances are still not up to date at period-end. **Adjusting entries** update account balances to reflect the correct revenues earned and expenses incurred for the period — following the accrual basis and matching principle.

> **Golden Rule:** Every adjusting entry always affects at least one **income statement account** (revenue or expense) AND at least one **balance sheet account** (asset or liability). Adjusting entries NEVER involve Cash.

---

### The Five Types of Adjusting Entries

**Type 1 — Prepaid Expenses (Deferred Expenses)**
Cash was paid in advance; part of the benefit has now been used up.

*Before adjustment:* Prepaid Insurance ₱12,000 (full year)
*After 1 month:* ₱1,000 has expired

```
Insurance Expense       1,000
   Prepaid Insurance           1,000
To record one month of expired insurance.
```

---

**Type 2 — Accrued Expenses (Accruals)**
Expense has been incurred, but no cash has been paid yet (and no entry recorded).

*Example:* Salaries of ₱5,000 earned by employees for the last 3 days of the month; payday is next month.

```
Salaries Expense        5,000
   Salaries Payable            5,000
To accrue unpaid salaries at month-end.
```

---

**Type 3 — Unearned Revenue (Deferred Revenue)**
Cash was collected in advance; part of the service has now been performed.

*Example:* Client paid ₱6,000 advance for 3 months of consulting. After 1 month, ₱2,000 has been earned.

```
Unearned Revenue        2,000
   Service Revenue             2,000
To recognize one month of earned consulting revenue.
```

---

**Type 4 — Accrued Revenue**
Service has been performed, but cash has not yet been received (and no entry recorded).

*Example:* Completed ₱3,000 repair job on July 29; invoice will be sent in August.

```
Accounts Receivable     3,000
   Service Revenue             3,000
To accrue revenue earned but not yet billed.
```

---

**Type 5 — Depreciation**
Allocates the cost of a long-term asset over its useful life.

*Example:* Equipment ₱60,000 / 5 years / no salvage value = ₱1,000/month depreciation.

```
Depreciation Expense    1,000
   Accumulated Depreciation        1,000
To record monthly depreciation of equipment.
```

---

### Summary Table

| Type | Situation | Dr | Cr |
|------|-----------|----|----|
| Prepaid Expense | Paid in advance; now partially used | Expense ↑ | Asset ↓ |
| Accrued Expense | Incurred but not paid | Expense ↑ | Liability ↑ |
| Unearned Revenue | Collected in advance; now partially earned | Liability ↓ | Revenue ↑ |
| Accrued Revenue | Earned but not yet collected | Asset ↑ | Revenue ↑ |
| Depreciation | Allocate asset cost to expense | Expense ↑ | Contra-Asset ↑ |
BODY,
                        'activity'   => 'Prepare the adjusting entry for each situation (include account titles, amounts, and a brief explanation): (1) Office supplies on hand per physical count = ₱3,200; balance in Supplies account = ₱8,000. (2) Insurance policy ₱24,000 was paid on January 1 for the full year; it is now June 30. (3) Employees\' salaries for the last 5 days of the month total ₱7,500; payday is next month. (4) A client paid ₱18,000 in advance for 6 months of service on March 1; it is now May 31. (5) Equipment costs ₱90,000 with a useful life of 10 years and no salvage value (use straight-line method).',
                    ],
                    [
                        'deped_code' => 'ABM_FABM11-IVa-d-34',
                        'title'      => 'Prepare the Adjusted Trial Balance after posting adjusting entries',
                        'body'       => <<<'BODY'
## The Adjusted Trial Balance

### What Is an Adjusted Trial Balance?

The **adjusted trial balance** is a listing of all account balances **after adjusting entries have been journalized and posted**. It is the final, accurate basis from which financial statements are prepared.

### How to Prepare It (Using the Worksheet)

1. Enter unadjusted trial balance amounts in columns 1 and 2 (Debit and Credit).
2. Enter adjusting entries in columns 3 and 4 (Adjustments Debit and Credit).
3. Compute adjusted balances:
   - Same side: **Add** the amounts.
   - Opposite sides: **Subtract** and place on the side with the larger amount.
4. Enter the result in columns 5 and 6 (Adjusted Trial Balance).
5. Verify: Total Adjusted Debit = Total Adjusted Credit.

---

### Illustrative Worksheet — ABM Accounting Firm (December 31, 2020)

| Account | Trial Balance Dr | Trial Balance Cr | Adj Dr | Adj Cr | Adjusted TB Dr | Adjusted TB Cr |
|---------|-----------------|-----------------|--------|--------|---------------|---------------|
| Cash | 259,000 | | | | 259,000 | |
| Accounts Receivable | 90,000 | | | | 90,000 | |
| Supplies | 8,000 | | | a. 5,500 | 2,500 | |
| Equipment | 55,000 | | | | 55,000 | |
| Accounts Payable | | 65,000 | | | | 65,000 |
| Ms. Co, Capital | | 300,000 | | | | 300,000 |
| Ms. Co, Drawings | 10,000 | | | | 10,000 | |
| Professional Fees | | 118,000 | | | | 118,000 |
| Rent Expense | 40,000 | | | b. 10,000 | 30,000 | |
| Salaries Expense | 16,000 | | | | 16,000 | |
| Taxes and Licenses | 2,000 | | | | 2,000 | |
| Utilities Expense | 3,000 | | | | 3,000 | |
| Supplies Expense | | | a. 5,500 | | 5,500 | |
| Prepaid Rent | | | b. 10,000 | | 10,000 | |
| Depreciation Expense | | | c. 3,750 | | 3,750 | |
| Accum. Dep.—Equipment | | | | c. 3,750 | | 3,750 |
| **TOTALS** | **483,000** | **483,000** | **19,250** | **19,250** | **486,750** | **486,750** |

> **Key Changes:** Supplies reduced from ₱8,000 to ₱2,500 (₱5,500 used). Rent Expense reduced from ₱40,000 to ₱30,000 (₱10,000 reclassified to Prepaid Rent).
BODY,
                        'activity'   => 'Given the following unadjusted trial balance and adjustment data, prepare a worksheet and compute the adjusted trial balance. Unadjusted: Cash ₱45,000; Prepaid Insurance ₱12,000; Equipment ₱80,000; A/P ₱20,000; Capital ₱100,000; Service Revenue ₱65,000; Salaries Expense ₱40,000; Utilities ₱8,000. Adjustments: (a) Insurance expired ₱4,000; (b) Accrued salaries ₱6,000; (c) Monthly depreciation on equipment ₱1,500.',
                    ],
                ],
            ],

            // ── CO7 ──────────────────────────────────────────────────────────
            [
                'title'       => 'Completion of the Accounting Cycle — Steps 6–10',
                'description' => 'Prepare financial statements, record closing entries, and complete the full accounting cycle through the post-closing trial balance and reversing entries.',
                'order'       => 7,
                'competencies' => [
                    [
                        'deped_code' => 'ABM_FABM11-IVe-j-38',
                        'title'      => 'Step 6 — Prepare the Adjusted Trial Balance; Step 7 — Prepare Financial Statements',
                        'body'       => <<<'BODY'
## Steps 6–7: Adjusted Trial Balance and Financial Statements

### Step 6: The Adjusted Trial Balance

The adjusted trial balance (covered in CO6) is the final list of all account balances after adjusting entries. It is the direct source for all three financial statements prepared in Step 7.

---

### Step 7: The Three Financial Statements

**Statement 1 — Income Statement (Statement of Comprehensive Income)**

Shows revenues and expenses for the period; determines **net income or net loss**.

> **Formula:** Net Income = Total Revenue − Total Expenses

**Example — ABM Accounting Firm (December 31, 2020):**

| | ₱ |
|-|---|
| **Revenue:** Professional Fees | 118,000 |
| **Operating Expenses:** | |
| Rent Expense | 30,000 |
| Salaries Expense | 16,000 |
| Supplies Expense | 5,500 |
| Depreciation Expense | 3,750 |
| Utilities Expense | 3,000 |
| Taxes and Licenses | 2,000 |
| **Total Operating Expenses** | (60,250) |
| **NET INCOME** | **₱57,750** |

---

**Statement 2 — Statement of Changes in Equity**

Reconciles beginning and ending capital balances.

> **Formula:** Ending Capital = Beginning Capital + Net Income + Additional Investment − Drawings

**Example:**

| | ₱ |
|-|---|
| Ms. Co, Capital — January 1, 2020 | 300,000 |
| Add: Net Income for the period | 57,750 |
| Total | 357,750 |
| Less: Ms. Co, Drawings | (10,000) |
| **Ms. Co, Capital — December 31, 2020** | **₱347,750** |

---

**Statement 3 — Statement of Financial Position (Balance Sheet)**

Shows balances of assets, liabilities, and equity **as of a specific date**. Proves: **Assets = Liabilities + Owner's Equity**.

**Example (Report Form):**

| | ₱ |
|-|---|
| **ASSETS** | |
| Current Assets: Cash | 259,000 |
| Accounts Receivable | 90,000 |
| Prepaid Rent | 10,000 |
| Supplies | 2,500 |
| **Total Current Assets** | 361,500 |
| Equipment (net of depreciation ₱3,750) | 51,250 |
| **TOTAL ASSETS** | **₱412,750** |
| **LIABILITIES** | |
| Accounts Payable | 65,000 |
| **OWNER'S EQUITY** | |
| Ms. Co, Capital (from SCE) | 347,750 |
| **TOTAL LIABILITIES AND EQUITY** | **₱412,750** |

---

### Statement Linkage

The three statements are connected:
1. **Net Income** from the Income Statement flows into the Statement of Changes in Equity.
2. **Ending Capital** from the Statement of Changes in Equity appears in the Balance Sheet.
3. The Balance Sheet proves: Total Assets = Total Liabilities + Owner's Equity.
BODY,
                        'activity'   => 'Using the adjusted trial balance below for Rapid Test Clinic (December 31, 2020), prepare: (1) Income Statement, (2) Statement of Changes in Equity, (3) Balance Sheet (report form). Beginning capital = ₱350,000. Accounts: Professional Fees ₱150,000; Interest Income ₱340; Medical Supplies Expense ₱50,000; Rent Expense ₱10,000; Salaries Expense ₱24,000; Depreciation ₱6,900; Interest Expense ₱620; Misc. Expense ₱4,000; Drawings ₱20,000; Cash ₱250,000; A/R ₱30,000; Notes Receivable ₱10,000; Equipment (net) ₱171,100; A/P ₱85,000; Notes Payable ₱25,000; Unearned Revenue ₱60,000.',
                    ],
                    [
                        'deped_code' => 'ABM_FABM11-IVe-j-39',
                        'title'      => 'Step 8 — Journalize and post closing entries; Step 9 — Prepare the Post-Closing Trial Balance',
                        'body'       => <<<'BODY'
## Steps 8–9: Closing Entries and Post-Closing Trial Balance

### Step 8: Closing Entries

**Closing entries** are journal entries made at year-end to transfer the balances of all **temporary (nominal) accounts** to the Capital account through an **Income Summary account**.

**Why close?** Revenues and expenses measure performance for ONE period only. At year-end, they are "zeroed out" so the new period begins with clean accounts.

| Account Type | Temporary or Permanent? | Closed? |
|-------------|------------------------|---------|
| Revenue accounts | Temporary | ✅ Yes |
| Expense accounts | Temporary | ✅ Yes |
| Drawing / Withdrawal | Temporary | ✅ Yes |
| Income Summary | Temporary | ✅ Yes (after use) |
| Assets, Liabilities, Capital | Permanent | ❌ No — carry over |

---

### The Four Closing Steps

**Step 1 — Close all Revenue accounts → Income Summary**
```
Professional Fees       118,000
   Income Summary               118,000
```

**Step 2 — Close all Expense accounts → Income Summary**
```
Income Summary           60,250
   Rent Expense                  30,000
   Salaries Expense              16,000
   Supplies Expense               5,500
   Depreciation Expense           3,750
   Utilities Expense              3,000
   Taxes and Licenses             2,000
```

**Step 3 — Close Income Summary → Capital**
*(Net Income = ₱118,000 − ₱60,250 = ₱57,750)*
```
Income Summary           57,750
   Ms. Co, Capital               57,750
```

**Step 4 — Close Drawings → Capital**
```
Ms. Co, Capital          10,000
   Ms. Co, Drawings              10,000
```

---

### Step 9: Post-Closing Trial Balance

After closing entries are posted, prepare the **post-closing trial balance** — a listing of all remaining accounts with their balances.

**Rules:**
- Only **permanent accounts** (Assets, Liabilities, updated Capital) appear.
- All temporary accounts should show **zero balance** — if any show balances, there is a closing error.

**Example — ABM Accounting Firm (December 31, 2020):**

| Account | Debit | Credit |
|---------|-------|--------|
| Cash | ₱259,000 | |
| Accounts Receivable | 90,000 | |
| Prepaid Rent | 10,000 | |
| Supplies | 2,500 | |
| Equipment | 55,000 | |
| Accumulated Depreciation — Equipment | | ₱3,750 |
| Accounts Payable | | 65,000 |
| Ms. Co, Capital (after closing) | | 347,750 |
| **TOTALS** | **₱416,500** | **₱416,500** |

> Capital = ₱300,000 + ₱57,750 − ₱10,000 = **₱347,750** ✓
BODY,
                        'activity'   => 'Using the ABM Accounting Firm data (Net Income ₱57,750, Drawings ₱10,000, Capital beginning ₱300,000, Expenses: Rent ₱30,000; Salaries ₱16,000; Supplies ₱5,500; Depreciation ₱3,750; Utilities ₱3,000; Taxes ₱2,000; Revenue: Professional Fees ₱118,000): (1) Journalize all four closing entries. (2) What is the ending balance of Income Summary after Step 3? (3) Prepare the Post-Closing Trial Balance using the balance sheet accounts from the CO7 lesson.',
                    ],
                    [
                        'deped_code' => 'ABM_FABM11-IVe-j-40',
                        'title'      => 'Step 10 — Prepare reversing entries and complete the full accounting cycle',
                        'body'       => <<<'BODY'
## Step 10: Reversing Entries and the Complete Cycle

### What Are Reversing Entries?

**Reversing entries** are journal entries made at the **beginning of the next accounting period** that are the exact opposite of certain adjusting entries from the previous period. They are *optional* but simplify bookkeeping by eliminating the need to split cash transactions in the new period.

---

### When Are Reversing Entries Required?

| Type of Adjusting Entry | Reversing Entry Required? |
|------------------------|--------------------------|
| Prepaid Expense — recorded using **Expense Method** | ✅ Yes |
| Precollected Revenue — recorded using **Revenue Method** | ✅ Yes |
| **Accrued Expenses** | ✅ Yes |
| **Accrued Revenue** | ✅ Yes |
| Prepaid Expense — recorded using **Asset Method** | ❌ No |
| Precollected Revenue — recorded using **Liability Method** | ❌ No |
| Depreciation Expense | ❌ No |
| Bad Debts Expense | ❌ No |

---

### Illustration — Prepaid Rent (Expense Method)

**Dec. 31 Adjusting Entry** (recorded using expense method — initially debited to Rent Expense):
```
Prepaid Rent       10,000
   Rent Expense           10,000
```

**Jan. 1 Reversing Entry** (exact opposite):
```
Rent Expense       10,000
   Prepaid Rent           10,000
```

*Why?* Without the reversal, when the next cash payment for rent is made, the bookkeeper must remember to split it between Rent Expense and Prepaid Rent. With the reversal, the Jan. 1 entry resets the accounts, and the next rent payment is recorded normally (full debit to Rent Expense).

---

### The Complete Accounting Cycle — All Ten Steps

| Step | Activity | Phase |
|------|----------|-------|
| 1 | Analyze business transactions | Recording |
| 2 | Record (journalize) transactions | Recording |
| 3 | Post journal entries to ledger | Recording |
| 4 | Prepare the trial balance | Recording |
| 5 | Journalize and post adjusting entries | Recording |
| 6 | Prepare the adjusted trial balance | Summarizing |
| 7 | Prepare the financial statements | Communicating |
| 8 | Journalize and post closing entries | Summarizing |
| 9 | Prepare the post-closing trial balance | Summarizing |
| 10 | Prepare reversing entries | Summarizing |

Then → the next period begins at Step 1 again.

---

### Common Misconceptions

| Misconception | Correction |
|--------------|------------|
| "All adjusting entries need reversing entries." | Only expense-method prepayments, revenue-method precollections, accrued expenses, and accrued revenue need reversing entries. |
| "Drawings are closed to Income Summary." | Drawings bypass Income Summary and close **directly to Capital**. |
| "The post-closing trial balance includes all accounts." | Only **permanent accounts** (Assets, Liabilities, Capital). All temporary accounts are zero. |
| "Net loss means we credit Income Summary and debit Capital." | With a net loss, Income Summary has a debit balance — close by debiting Capital and crediting Income Summary. |
BODY,
                        'activity'   => 'For ABM Accounting Firm, determine which adjusting entries (if any) made during the December 31 closing would require reversing entries on January 1: (a) Supplies expense recorded for supplies used. (b) Accrued salaries payable ₱8,000. (c) Depreciation expense. (d) Unearned revenue (recorded using liability method) now partially earned. For those that require reversal, prepare the January 1 reversing entry.',
                    ],
                ],
            ],
        ];
    }
}
