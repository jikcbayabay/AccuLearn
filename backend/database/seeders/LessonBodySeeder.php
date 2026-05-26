<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LessonBodySeeder extends Seeder
{
    public function run(): void
    {
        $lessons = $this->bodies();

        foreach ($lessons as $id => $body) {
            DB::table('learning_materials')
                ->where('id', $id)
                ->update(['body' => $body, 'updated_at' => now()]);
        }
    }

    /**
     * Lesson bodies keyed by learning_materials.id.
     * Sourced from the official CO1-CO7 lesson plans (DepEd-aligned).
     */
    private function bodies(): array
    {
        return [

// =====================================================================
// MODULE 1 — CO1 — Accounting Concepts, Principles, Regulatory Framework
// =====================================================================

2 => <<<'MD'
## What Is Accounting?

Accounting is often called the **language of business**. It is the systematic process of identifying, recording, classifying, summarizing, interpreting, and communicating financial information so that owners, investors, banks, and regulators can make informed decisions.

### The Four-Fold Nature of Accounting

| Perspective | Meaning |
|---|---|
| **Science** | A body of established rules and principles (PFRS/GAAP) that can be studied and verified. |
| **Art** | Requires professional judgment in applying those rules to real-world situations. |
| **Process** | A continuous cycle of steps — from analyzing transactions to producing financial reports. |
| **Profession** | In the Philippines, accounting is a licensed profession regulated by the Board of Accountancy (BOA) and the Professional Regulation Commission (PRC). |

### The Philippine Regulatory Ecosystem

Accounting standards in the Philippines do not stand alone — they are enforced by an ecosystem of regulators:

- **FRSC** (Financial Reporting Standards Council) — issues the **Philippine Financial Reporting Standards (PFRS)**, aligned with the global **IFRS**.
- **BOA** (Board of Accountancy) — oversees the practice of Certified Public Accountants (CPAs).
- **SEC** (Securities and Exchange Commission) — regulates corporations and requires PFRS-compliant financial statements.
- **BIR** (Bureau of Internal Revenue) — uses financial statements for tax compliance.
- **BSP** (Bangko Sentral ng Pilipinas) — regulates banks and other financial institutions.
- **PICPA** (Philippine Institute of CPAs) — the professional organization guiding CPA ethics.

### Why Accounting Rules Matter

Just as language needs grammar so that everyone can be understood, accounting needs a common set of rules so that financial statements produced by one business can be read and compared by any stakeholder — owner, investor, bank, or regulator.

> **Connection to Your Life:** Imagine you own a small sari-sari store. For December, you bought goods worth ₱26,000, paid electricity (₱1,280) and water (₱357), and recorded total sales of ₱32,750. Did you earn a profit or a loss? Accounting gives you the tools to answer this question precisely — and to prove your answer to anyone who asks.

### Key Vocabulary

| Term | Definition |
|---|---|
| Accounting | The process of recording, classifying, summarizing, interpreting, and communicating financial information |
| PFRS | Philippine Financial Reporting Standards — the rules governing financial reporting in the Philippines |
| GAAP | Generally Accepted Accounting Principles — the conceptual foundation behind every accounting entry |
| IFRS | International Financial Reporting Standards — the global framework PFRS is aligned with |
| FRSC | Financial Reporting Standards Council — issues PFRS in the Philippines |
| BOA | Board of Accountancy — licenses and regulates CPAs |
MD,

5 => <<<'MD'
## Accounting vs. Bookkeeping

Many people use the words *accounting* and *bookkeeping* interchangeably — but they are not the same. Understanding the difference is essential before learning the accounting cycle.

### Bookkeeping — The Recording Function

**Bookkeeping** is the systematic recording of financial transactions. It is one important part of accounting, but it stops at the recording stage. A bookkeeper:

- Records sales, purchases, receipts, and payments as they occur.
- Maintains the journal and ledger.
- Ensures every transaction is documented with a source document.

### Accounting — The Broader Discipline

**Accounting** includes bookkeeping but goes much further. It also involves:

- **Classifying** transactions into account types (Assets, Liabilities, Equity, Income, Expenses).
- **Summarizing** results in financial statements.
- **Interpreting** what the numbers mean for the business.
- **Communicating** results to owners, investors, banks, and regulators.

### Side-by-Side Comparison

| Aspect | Bookkeeping | Accounting |
|---|---|---|
| Scope | Recording transactions only | Recording + classifying + summarizing + interpreting + communicating |
| Output | Journals and ledgers | Financial statements, analyses, reports |
| Skill Level | Procedural | Requires professional judgment |
| End Goal | Accurate records | Informed decision-making |
| Typical Role | Bookkeeper or clerk | CPA, financial analyst, controller |

### Why the Distinction Matters

A business needs both. Bookkeeping provides the raw data; accounting turns that data into information owners and stakeholders can act on. Without bookkeeping, accounting has nothing to analyze. Without accounting, bookkeeping records sit unused.

> **Real-World Example:** A sari-sari store owner who only records sales and expenses in a notebook is doing bookkeeping. The moment they ask, "Did I earn a profit this month? Should I expand?" — they are doing accounting.

### Common Misconception

> *"Accounting and bookkeeping are the same thing."*

**Correction:** Bookkeeping is the *recording* part of accounting — a single component. Accounting is broader and includes bookkeeping, analysis, interpretation, and reporting for decision-making.

### Key Takeaway

Bookkeeping is a subset of accounting. Every accountant must understand bookkeeping, but not every bookkeeper is an accountant.
MD,

8 => <<<'MD'
## Functions of Accounting in Business Decision-Making

Accounting exists because people need to make decisions about a business — and those decisions require reliable, organized financial information. This lesson explores **who** uses accounting information and **how** it supports their decisions.

### The Five Core Functions

| Function | What It Does |
|---|---|
| **Recording** | Captures every business transaction as it occurs (the bookkeeping foundation). |
| **Classifying** | Groups similar transactions under appropriate account titles (Cash, Sales, Rent, etc.). |
| **Summarizing** | Combines classified data into financial reports (income statement, balance sheet). |
| **Interpreting** | Analyzes what the numbers mean — trends, ratios, profitability. |
| **Communicating** | Presents the information to users in a clear, decision-useful format. |

### Who Uses Accounting Information?

Accounting information is used by both **internal** and **external** users:

**Internal users** (inside the business):
- **Owners and managers** — to plan, budget, and evaluate performance.
- **Employees** — to assess job security and bargain for fair pay.

**External users** (outside the business):
- **Investors** — to decide whether to invest in or stay with the business.
- **Banks and creditors** — to decide whether to lend money and on what terms.
- **Government regulators (SEC, BIR, BSP)** — to enforce compliance and collect taxes.
- **Customers and suppliers** — to assess whether the business is reliable.

### Examples of Accounting-Driven Decisions

| Question | Who Asks | What Accounting Provides |
|---|---|---|
| "Can I afford to hire another staff member?" | Owner | Income statement showing profit margins |
| "Should we extend credit to this customer?" | Supplier | Credit history and current payable balances |
| "Is this business worth investing in?" | Investor | Financial statements showing profitability and growth |
| "How much tax does this business owe?" | BIR | Net income and supporting computations |
| "Is the loan being repaid responsibly?" | Bank | Cash flow statement and outstanding obligations |

### Why This Matters

Without accounting, every stakeholder would have to guess. With accounting, decisions are grounded in objective, verifiable data. This is why accounting is described as the **language of business** — it lets very different parties (an owner in Manila, a bank officer in Cebu, a regulator in Quezon City) understand the same business in the same way.

> **Reflective Prompt:** Think of your school canteen. Who are its "internal users" of financial information? Who are its "external users"? What decisions would each of them make using that information?
MD,

11 => <<<'MD'
## A Brief History of Accounting

Accounting is one of the oldest disciplines in human civilization — older than writing itself in many places. Understanding its history helps you see why modern accounting looks the way it does.

### Mesopotamia (c. 3500 BC) — The Origins

The earliest known accounting records come from ancient Mesopotamia, where Sumerian merchants used clay tokens and cuneiform tablets to track grain, livestock, and silver. These records were essential for trade, taxation, and temple offerings. Long before money or banking existed, people needed a way to keep score.

### Ancient Egypt, Greece, and Rome

- **Egypt**: scribes maintained detailed records of pharaohs' treasuries, temple inventories, and labor accounts for building the pyramids.
- **Greece**: introduced auditing — public officials had to prove their use of public funds.
- **Rome**: developed elaborate household and government bookkeeping; Roman law required citizens to file periodic financial statements.

### The Renaissance — Birth of Modern Accounting

In **1494**, the Italian Franciscan friar **Luca Pacioli** published *Summa de Arithmetica*, the first printed work describing the **double-entry bookkeeping system** used by Venetian merchants. Pacioli is now called the **Father of Accounting**. His system — where every transaction has equal debits and credits — is still the foundation of accounting today, more than 500 years later.

### Industrial Revolution (1700s–1800s)

The rise of factories, railroads, and joint-stock companies created new needs:
- **Cost accounting** — to measure the cost of producing goods.
- **Depreciation** — to allocate the cost of long-lived assets over their useful life.
- **Audit profession** — independent CPAs to verify financial statements for investors.

### The 20th Century — Standardization

As businesses grew global, the need for **consistent accounting rules across countries** became urgent. The United States developed **GAAP** (Generally Accepted Accounting Principles), and later the **IASB** developed **IFRS** (International Financial Reporting Standards) — now used in over 140 countries.

### Accounting in the Philippines

| Year | Milestone |
|---|---|
| 1923 | Republic Act No. 3105 created the **Board of Accountancy (BOA)** — regulating CPAs in the Philippines. |
| 1981 | **PICPA** (Philippine Institute of CPAs) became the official accrediting organization. |
| 2004 | The Philippines adopted **PFRS**, aligning local standards with IFRS. |
| Present | **FRSC** continues to issue and update PFRS to match evolving IFRS. |

### The Big Picture

| Era | Key Idea |
|---|---|
| Mesopotamia | Record economic events |
| Renaissance | Double-entry bookkeeping (Pacioli) |
| Industrial Era | Cost accounting and auditing |
| Modern Era | Global standards (IFRS/PFRS) |

> **Key Insight:** When you study accounting today, you are participating in an unbroken tradition that stretches back 5,000 years — from clay tablets in Sumer to PFRS-compliant statements in Makati.
MD,

14 => <<<'MD'
## Fundamental Accounting Concepts and Assumptions

Before you can apply accounting rules, you must understand the **assumptions** behind them. These are the unwritten "ground rules" that make every financial statement meaningful.

### 1. Economic Entity Assumption

A business is treated as a **separate entity** from its owner. All business transactions are recorded separately from the owner's personal transactions.

> *Dr. Nana runs a pediatric clinic. Her grocery bills and personal mortgage must never be mixed with the clinic's expenses — the clinic is its own economic entity even though she owns it.*

### 2. Going Concern Assumption

It is assumed that a business will **continue to operate** into the foreseeable future and will not be closed down or liquidated. This justifies recording long-term assets at cost rather than at liquidation value.

> *Dr. Nana's accountant records the clinic's MRI machine as a long-term asset and depreciates it over 10 years — possible only because the clinic is presumed to keep operating.*

### 3. Time Period (Periodicity) Assumption

Financial performance is reported at **equal, regular intervals** — usually 12 months. This can be a:
- **Calendar Year** (Jan 1 – Dec 31), or
- **Fiscal Year** (any 12-month period not starting in January), or
- **Natural Business Year** (ending when activity is at its lowest, e.g., after the holiday rush for a toy store).

### 4. Accrual Basis

Income is recorded **when earned**, regardless of when cash is collected. Expenses are recorded **when incurred**, regardless of when cash is paid. This is the **preferred method** under PFRS/GAAP.

| Term | Meaning |
|---|---|
| Accrued Income | Earned but not yet collected |
| Accrued Expense | Incurred but not yet paid |
| Unearned Revenue (Deferred Income) | Collected but not yet earned — a *liability* |
| Prepaid Expense | Paid but not yet incurred — an *asset* |

> *A beach resort that receives advance payment for a guest checking in next month cannot record it as income yet — it is **Unearned Revenue** (a liability) until the stay actually happens.*

### 5. Monetary / Measurement Concept

Only items that can be **measured in money** are recorded. Non-financial information (employee morale, customer loyalty, brand reputation) is not booked — though it may be noted in disclosures if relevant.

### 6. Realization Concept

Income is recognized when **earned** — when the seller has performed their obligation:
- **FOB Shipping Point**: ownership and income transfer when goods leave the seller.
- **FOB Destination**: ownership and income transfer when goods arrive at the buyer.

### 7. Matching Concept

Expenses are recognized in the **same period** as the revenues they help generate. This is why a ₱35,000 commercial oven is *not* expensed all at once — it is depreciated over its useful life to match the revenue it helps produce each year.

### 8. Cash Basis (Compared to Accrual)

The opposite of accrual: income recorded when cash is collected, expenses when cash is paid. **Simpler, but generally NOT accepted under PFRS/GAAP** for formal financial reporting.

### 9. Duality Concept (Double-Entry)

Every transaction has a **two-fold effect** — for every debit, there is an equal credit. This is the foundation of the accounting equation:

> **Assets = Liabilities + Owner's Equity**

### Quick Reference

| Concept | Core Question It Answers |
|---|---|
| Economic Entity | Whose transactions are we recording? |
| Going Concern | Will the business keep operating? |
| Time Period | When does this period end? |
| Accrual | When do we record income/expense? |
| Monetary | Can we measure this in money? |
| Realization | Has the earning process been completed? |
| Matching | Which period bears this cost? |
| Duality | How many accounts are affected? |
MD,

17 => <<<'MD'
## Basic Accounting Principles (GAAP)

Where **concepts** are the assumptions accountants take for granted, **principles** are the rules accountants follow when recording transactions. The principles below come from Generally Accepted Accounting Principles (GAAP) and are codified in PFRS.

### 1. Objectivity Principle

All accounting entries must be supported by **verifiable, objective evidence** — not personal opinion or estimation.

> *An employee asks for reimbursement of ₱1,500 for office supplies but cannot produce the Official Receipt. The accountant correctly refuses — the **Objectivity Principle** requires verifiable evidence.*

### 2. Historical Cost Principle

Assets must be recorded at their **original acquisition cost**, regardless of changes in market value.

> *Land purchased in 2015 for ₱2,000,000 is appraised at ₱5,500,000 in 2024. On the balance sheet, the land remains at ₱2,000,000. The market value may be disclosed in a footnote, but the book value does not change.*

### 3. Accrual / Revenue Recognition Principle

Revenue is recognized when **earned** (goods delivered or service rendered), not when cash is received. Expenses are recognized when **incurred**, not when paid. *(See Accrual Basis under fundamental concepts.)*

### 4. Adequate Disclosure Principle

Financial statements must include **all material information** that could affect a user's decision — either in the statements themselves or in accompanying notes.

> *A company facing a major pending lawsuit must disclose it in the footnotes, even if the outcome is uncertain — because users need to know.*

### 5. Materiality Principle

Strict adherence to accounting rules is not required for items so small they would not affect any user's decision. **Immaterial items can be treated in the simplest way** (e.g., expensed directly instead of capitalized).

> *A school buys a ₱50 whiteboard marker that will last two years. Instead of recording it as an asset and depreciating ₱25 per year, the accountant just records ₱50 as an expense. The **Materiality Principle** allows this shortcut.*

### 6. Consistency Principle

Accounting methods must be applied **uniformly from one period to the next** so that financial statements remain comparable. Any change in method must be clearly disclosed.

> *A company that uses straight-line depreciation in Year 1 must use the same method in Year 2 — unless it discloses the change and its impact.*

### 7. Conservatism (Prudence) Principle

When two equally acceptable alternatives exist, choose the one that results in a **lower asset value or lower net income**. Anticipate losses; do not anticipate gains.

> *A probable lawsuit loss of ₱500,000 should be recorded. A probable lawsuit *gain* of ₱500,000 should **not** be recorded until actually realized.*

### 8. Terminating Concern (Special Basis)

If there is **strong evidence** that a business will cease operations (e.g., court-ordered liquidation), the going concern assumption is abandoned and assets are revalued at their **net realizable (liquidation) value**.

### Quick Reference

| Principle | Core Rule |
|---|---|
| Objectivity | Record only what is verifiable |
| Historical Cost | Record at original cost |
| Adequate Disclosure | Reveal all material facts |
| Materiality | Don't sweat the small stuff |
| Consistency | Same method every period |
| Conservatism | Anticipate losses, not gains |
| Terminating Concern | Liquidation basis when closing is certain |
MD,

20 => <<<'MD'
## Applying Concepts and Principles to Real-World Scenarios

This lesson connects everything you've learned about concepts and principles to actual Philippine business situations. The goal: by the end, you should be able to read any business event and immediately name the concept or principle that governs how it is recorded.

### Walkthrough: Aling Rosa's Bakery

Aling Rosa opens a bakery in Quezon City. Here are eight events that occur in her first year. For each, identify the principle or concept that applies.

| Event | Principle / Concept | How to Handle It |
|---|---|---|
| (a) Aling Rosa uses her personal savings of ₱50,000 to start the bakery. | **Economic Entity Assumption** | Record ₱50,000 as Owner's Capital in the bakery's books. Personal and business funds must be kept separate. |
| (b) Bakery buys an oven for ₱35,000 in 2022. By 2024 it is worth ₱20,000. | **Historical Cost Principle** | The oven stays at ₱35,000 on the books. Market value changes are not booked. |
| (c) Bakery delivers 200 boxes of pandesal to a school on credit in December. | **Accrual / Realization Principle** | Record revenue in December (when delivered), not when cash is collected. |
| (d) Bakery buys 1,000 toothpicks for ₱50. | **Materiality Principle** | Charge directly to expense. The amount is too small to track as an asset. |
| (e) ₱100,000 probable loss from supplier dispute; ₱50,000 probable gain also possible. | **Conservatism Principle** | Record/disclose the ₱100,000 loss. Do NOT record the ₱50,000 gain until realized. |
| (f) Aling Rosa wants to switch depreciation method in 2024 without explanation. | **Consistency Principle** | She cannot switch silently — any change must be clearly disclosed with its impact. |
| (g) Bakery receives ₱10,000 advance payment for a birthday cake order next month. | **Accrual Basis / Realization** | Record as Unearned Revenue (liability). Becomes income only when the cake is delivered. |
| (h) Aling Rosa's outstanding service reputation is widely known but not recorded. | **Monetary / Measurement Concept** | Only peso-denominated transactions are recorded. Reputation has no objective monetary measure. |

### Identify the Principle — Quick Practice

Match each situation to the correct concept or principle:

| Situation | Principle / Concept |
|---|---|
| A company's financial statements show assets of ₱2M; the owner's house is not included. | **Economic Entity Assumption** |
| A business records salaries for December even though payday falls on January 5. | **Accrual / Matching Principle** |
| A firm discloses in the notes that a major customer has gone bankrupt. | **Adequate Disclosure Principle** |
| A business records a computer at its purchase price of ₱45,000 — current market value ₱20,000. | **Historical Cost Principle** |
| A company records a ₱300,000 probable lawsuit loss but not a probable insurance recovery. | **Conservatism Principle** |
| Every peso transaction is recorded; goodwill from community trust is not. | **Monetary / Measurement Concept** |

### Common Misconceptions to Avoid

> *"I received ₱30,000 cash today, so that's revenue."*
> **Correction:** Under the Accrual Principle, revenue is recognized when **earned**, not when cash is received. If the ₱30,000 is an advance, it is Unearned Revenue (a liability) until the service is performed.

> *"My building is now worth ₱5M — I should update my books."*
> **Correction:** Under the Historical Cost Principle, the building stays at its original purchase price. Current market value may be disclosed in a note but does not replace the recorded cost.

> *"This amount is small, so I can ignore it."*
> **Correction:** The Materiality Principle allows shortcuts on truly trivial items — but all transactions must still be properly recorded. "Small" doesn't mean "invisible."

### Reflective Prompt

Pick one event from your own life this past week (e.g., paying for transportation, receiving an allowance, lending a classmate money). Which accounting concept or principle would apply if a business were recording the same event?
MD,

// =====================================================================
// MODULE 2 — CO2 — The Accounting Equation
// =====================================================================

23 => <<<'MD'
## The Fundamental Accounting Equation

The accounting equation is the **backbone of all financial accounting**. It expresses the relationship between what a business owns, what it owes, and what belongs to its owner:

> # **Assets = Liabilities + Owner's Equity**

This equation must **always** remain in balance. Every transaction — no matter how simple or complex — affects at least two elements in a way that preserves equality.

### The Three Elements

#### 1. Assets — What the Business OWNS

**Assets** are economic resources controlled by the business that are expected to provide future economic benefits. They represent everything of value the business possesses.

| Category | Examples |
|---|---|
| Current Assets | Cash, Accounts Receivable, Notes Receivable, Merchandise Inventory, Prepaid Expenses, Office Supplies |
| Non-Current Assets | Land, Building, Office Equipment, Furniture, Delivery Equipment |
| Other Assets | Long-term Investments, Security Deposits |

> *Mang Tomas owns a hardware store. The store's assets include cash on hand (₱45,000), unsold goods on display (₱180,000), shelving (₱60,000), and the lot where the store stands (₱500,000) — all resources that will help generate future income.*

#### 2. Liabilities — What the Business OWES

**Liabilities** are present obligations of the business — amounts owed to outside parties (creditors) that require future settlement.

| Category | Examples |
|---|---|
| Current Liabilities | Accounts Payable, Notes Payable, Accrued Expenses, Unearned Revenue, Income Tax Payable |
| Non-Current Liabilities | Long-Term Loans, Mortgage Payable, Bonds Payable |

> *Mang Tomas purchased ₱80,000 of cement on credit from a supplier and took out a ₱200,000 bank loan to renovate. Both are liabilities — obligations the store must settle in the future.*

#### 3. Owner's Equity — The Owner's NET STAKE

**Owner's Equity** (also called Capital) is the **residual interest** in the assets of the business after deducting all liabilities. It represents the owner's net investment.

> Owner's Equity = Total Assets − Total Liabilities

### Putting It Together

| Element | Question It Answers | Side of Equation |
|---|---|---|
| Assets | What does the business own? | Left |
| Liabilities | What does the business owe? | Right |
| Owner's Equity | What is the owner's net stake? | Right |

> **Key Insight:** Every peso of asset in a business is funded either by a creditor (liability) or by the owner (equity). There is no other source. The equation cannot be "broken" — every transaction simply rearranges the figures while keeping both sides equal.

### A First Example

On Day 1, you invest ₱100,000 personal savings into a printing business. Two things happen simultaneously:

- The business now has Cash of ₱100,000 (an asset).
- The business owes that amount back to you as the owner (equity of ₱100,000).

| Assets | = | Liabilities | + | Owner's Equity |
|---|---|---|---|---|
| ₱100,000 | = | ₱0 | + | ₱100,000 |

The equation balances. ✓
MD,

26 => <<<'MD'
## Performing Operations Using the Accounting Equation

Now that you know **Assets = Liabilities + Owner's Equity**, you can solve for any missing element if the other two are known. This lesson develops that skill through algebraic manipulation and worked examples.

### The Three Forms of the Equation

The same equation, rearranged three ways:

| To Find | Formula |
|---|---|
| Assets | Assets = Liabilities + Owner's Equity |
| Liabilities | Liabilities = Assets − Owner's Equity |
| Owner's Equity | Owner's Equity = Assets − Liabilities |

### Worked Examples

**Example 1.** A business has total Assets of ₱250,000 and total Liabilities of ₱150,000. What is its Owner's Equity?

> Owner's Equity = ₱250,000 − ₱150,000 = **₱100,000**

**Example 2.** A business has Owner's Equity of ₱347,000 and total Assets of ₱665,000. What are its Liabilities?

> Liabilities = ₱665,000 − ₱347,000 = **₱318,000**

**Example 3.** A business has Liabilities of ₱234,000 and Owner's Equity of ₱434,000. What are its total Assets?

> Assets = ₱234,000 + ₱434,000 = **₱668,000**

### Find the Missing Values — Practice Set

Complete the table below using the equation. Show your work in your notebook.

| # | Assets (₱) | Liabilities (₱) | Owner's Equity (₱) |
|---|---|---|---|
| 1 | 250,000 | 150,000 | **100,000** ✓ (guided) |
| 2 | 665,000 | ___ | 347,000 |
| 3 | ___ | 234,000 | 434,000 |
| 4 | 123,000 | 23,000 | ___ |
| 5 | 876,000 | ___ | 500,000 |
| 6 | 15,000 | 5,999 | ___ |
| 7 | 1,089,021 | 396,157 | ___ |

### Advanced Challenge (for ratio-based problems)

For more advanced learners, the equation can be combined with percentages and ratios:

| # | Assets | Liabilities | Owner's Equity |
|---|---|---|---|
| 8 | 68,000 | ___ | 66% of Assets |
| 9 | ___ | 1/3 of Equity | 143,628 |
| 10 | 164% of Liabilities | 26,007 | ___ |

> *For Row 8: Owner's Equity = 0.66 × ₱68,000 = ₱44,880; therefore Liabilities = ₱68,000 − ₱44,880 = **₱23,120**.*

### Why This Matters

Every financial statement — every Balance Sheet, every Statement of Financial Position — is just this equation laid out in a structured report. Mastering it now means you will be able to read and prepare those statements confidently in later lessons.

### Key Skill Check

You should now be able to:
- Identify which element is missing from a problem.
- Choose the correct rearranged form of the equation.
- Solve for the missing value and verify by substituting it back.

> **Reflective Prompt:** If your personal finances were a business, what would your "Assets," "Liabilities," and "Owner's Equity" look like? Try writing them down and check that the equation balances.
MD,

29 => <<<'MD'
## The Expanded Accounting Equation

The basic equation **Assets = Liabilities + Owner's Equity** is correct — but it does not show *what changes* equity over time. The **expanded accounting equation** breaks Owner's Equity into its four components so we can see exactly how the owner's stake moves.

### The Expanded Form

> # Assets = Liabilities + Capital − Withdrawals + Revenue − Expenses

Or equivalently:

> Owner's Equity = Capital − Withdrawals + Revenue − Expenses

### The Four Components of Owner's Equity

| Component | What It Represents | Effect on Equity |
|---|---|---|
| **Capital** | Initial and additional investments by the owner | **Increases** equity |
| **Withdrawals (Drawings)** | Amounts the owner takes out of the business for personal use | **Decreases** equity |
| **Revenue / Income** | Amounts earned from services rendered or goods sold | **Increases** equity |
| **Expenses** | Costs incurred to operate the business | **Decreases** equity |

### Why Each Belongs Where It Does

#### Capital — Owner's Contributions
When Aling Rosa invests ₱50,000 of her savings, she is putting money INTO the business. The business now has more cash, and her stake in the business (capital) goes up.

#### Withdrawals — Owner's Personal Draws
When Aling Rosa takes ₱3,000 out for groceries, she is removing resources for personal use. **Withdrawals are NOT an expense** — they are a reduction of her capital. The business is not poorer because of an operating cost; she is simply taking back part of her own investment.

#### Revenue — Earnings That Belong to the Owner
When Aling Rosa sells ₱10,000 worth of pandesal, the business earns income. Since the owner is the residual claimant, that earning ultimately belongs to her — hence revenue increases equity.

#### Expenses — Costs of Operating
When Aling Rosa pays ₱2,000 in rent, the business gives up resources to earn revenue. That cost reduces the owner's net stake, hence expenses decrease equity.

### A Worked Example

Aling Rosa's bakery for the month:

| Item | Amount (₱) | Effect on Equity |
|---|---|---|
| Initial Capital | 50,000 | +50,000 |
| Additional investment mid-month | 10,000 | +10,000 |
| Revenue from sales | 25,000 | +25,000 |
| Rent expense | (6,000) | −6,000 |
| Salaries expense | (8,000) | −8,000 |
| Owner's withdrawal | (3,000) | −3,000 |
| **Ending Owner's Equity** | | **₱68,000** |

### Common Misconception

> *"Owner withdrawals are an expense of the business."*

**Correction:** Withdrawals are **NOT** expenses. Expenses are costs the business pays to outside parties to operate (rent, salaries, utilities). Withdrawals are the owner taking back part of her own investment. Both reduce equity — but they are recorded in completely different accounts and appear in different sections of the financial statements.

### Quick Reference

| Item | Increases or Decreases Equity? | Is it an Expense? |
|---|---|---|
| Capital | Increase | No |
| Additional Investment | Increase | No |
| Revenue | Increase | No |
| Withdrawal | Decrease | **No** — equity reduction |
| Expense | Decrease | **Yes** — operating cost |
MD,

32 => <<<'MD'
## Verifying the Equation Stays Balanced

Every business transaction affects at least **two accounts**, and the accounting equation must remain balanced after every single one. This lesson shows you exactly how transactions affect the equation — and trains you to verify the balance.

### The Eleven Common Transaction Effects

| Transaction | Assets | Liabilities | Owner's Equity |
|---|---|---|---|
| Owner invests cash | **Increase** | No change | **Increase** |
| Owner invests non-cash asset (e.g., equipment) | **Increase** | No change | **Increase** |
| Purchase asset for cash | **Inc & Dec** (swap) | No change | No change |
| Purchase asset on credit | **Increase** | **Increase** | No change |
| Payment of account payable | **Decrease** | **Decrease** | No change |
| Rendered services for cash | **Increase** | No change | **Increase** (Revenue) |
| Rendered services on credit | **Increase** | No change | **Increase** (Revenue) |
| Collection of A/R | **Inc & Dec** (swap) | No change | No change |
| Paid expense in cash | **Decrease** | No change | **Decrease** (Expense) |
| Owner's withdrawal | **Decrease** | No change | **Decrease** (Drawings) |
| Borrowed cash from bank | **Increase** | **Increase** | No change |

### Visualization — The Balance Scale

Picture a balance scale: ASSETS on the LEFT pan, LIABILITIES + EQUITY on the RIGHT pan. No matter what happens, the scale must always be balanced.

| LEFT (Debit side) | ⇌ | RIGHT (Credit side) |
|---|---|---|
| **ASSETS**<br>Cash · Receivables · Inventory<br>Equipment · Land · Supplies | | **LIABILITIES + EQUITY**<br>A/P · Loans · Capital<br>Revenue − Expenses |

### Walkthrough: Don JPacs Advertising Services

Trace 10 transactions and verify each keeps the equation balanced.

| # | Transaction | Assets | Liabilities | Owner's Equity |
|---|---|---|---|---|
| 1 | Owner invests ₱120,000 cash | +120,000 (Cash) | — | +120,000 (Capital) |
| 2 | Purchased equipment ₱24,000 on account | +24,000 (Equip) | +24,000 (A/P) | — |
| 3 | Paid rent ₱2,000 cash | −2,000 (Cash) | — | −2,000 (Rent Exp) |
| 4 | Received ₱10,000 cash for services | +10,000 (Cash) | — | +10,000 (Revenue) |
| 5 | Paid ₱6,000 to settle A/P | −6,000 (Cash) | −6,000 (A/P) | — |
| 6 | Paid salaries ₱2,400 cash | −2,400 (Cash) | — | −2,400 (Salary Exp) |
| 7 | Owner withdrew ₱3,000 cash | −3,000 (Cash) | — | −3,000 (Drawings) |
| 8 | Services on credit ₱13,200 | +13,200 (A/R) | — | +13,200 (Revenue) |
| 9 | Bought supplies for cash ₱1,400 | +1,400 / −1,400 (swap) | — | — |
| 10 | Collected ₱12,000 from A/R | +12,000 / −12,000 (swap) | — | — |

After all 10 transactions:

| Total Assets | = | Total Liabilities | + | Total Owner's Equity |
|---|---|---|---|---|
| **₱155,800** | = | **₱18,000** | + | **₱137,800** ✓ |

### Discussion Questions

- Why does Transaction 9 (buying supplies for cash) NOT change Owner's Equity?
- Why does Transaction 7 (owner withdrawal) decrease equity but is NOT recorded as an expense?
- If a business borrows ₱50,000 from the bank, does the owner become richer? Why not?
- Can the equation ever become unbalanced? What would that indicate?

> **Key Insight:** When you find your equation unbalanced, you've made a recording error. The discipline of always checking the balance is what makes double-entry bookkeeping self-correcting — and why accountants trust it.
MD,

// =====================================================================
// MODULE 3 — CO3 — Five Major Accounts and Chart of Accounts
// =====================================================================

35 => <<<'MD'
## The Five Major Account Types

Every account used in bookkeeping and financial reporting falls under one of **five major types**. Understanding these categories is the prerequisite for every journal entry you will ever make.

### 1. Assets — What the Business OWNS

**Definition:** Present economic resources controlled by the entity as a result of past events, expected to produce future economic benefits.

| Sub-Category | Examples |
|---|---|
| Current Assets | Cash, Accounts Receivable, Notes Receivable, Merchandise Inventory, Prepaid Expenses, Office Supplies |
| Non-Current Assets | Land, Building, Office Equipment, Store Equipment, Delivery Equipment, Furniture and Fixtures |
| Contra-Asset | Accumulated Depreciation (deducted from related fixed asset) |

**Appears on:** Statement of Financial Position (Balance Sheet)

### 2. Liabilities — What the Business OWES

**Definition:** Present obligations of the entity to transfer an economic resource as a result of past events.

| Sub-Category | Examples |
|---|---|
| Current Liabilities | Accounts Payable, Notes Payable, Accrued Salaries Payable, Taxes Payable, Unearned Revenue, SSS/PhilHealth Contributions Payable |
| Non-Current Liabilities | Mortgage Payable, Bonds Payable, Long-Term Loans Payable |

**Appears on:** Statement of Financial Position

### 3. Capital (Owner's Equity) — The Owner's NET STAKE

**Definition:** The residual interest in the assets of the entity after deducting all liabilities.

| Business Type | Capital Account Form |
|---|---|
| Sole Proprietorship | One capital account (e.g., "Santos, Capital") + one drawings account |
| Partnership | One capital + one drawings account *per partner* |
| Corporation | Stockholders' Equity (Share Capital, Retained Earnings, etc.) |

**Appears on:** Statement of Changes in Equity

### 4. Income (Revenue) — What the Business EARNS

**Definition:** Increases in assets or decreases in liabilities that result in an increase in equity — other than owner contributions.

| Sub-Category | Service Business | Trading Business |
|---|---|---|
| Operating Revenue | Service Fees, Professional Fees, Tuition Fees, Consultation Revenue | Sales Revenue, Sales |
| Other / Non-Operating | Interest Income, Rent Income, Gain on Sale of Assets | Interest Income, Rental Income, Commission Income |

**Appears on:** Income Statement (Statement of Comprehensive Income)

### 5. Expenses — What the Business SPENDS to Operate

**Definition:** Decreases in assets or increases in liabilities that result in a decrease in equity — other than distributions to owners.

| Sub-Category | Examples |
|---|---|
| Cost of Goods Sold | Cost of Sales (for trading businesses) |
| Operating Expenses | Salaries, Rent, Utilities, Supplies, Depreciation, Insurance, Advertising, Repairs |
| Non-Operating Expenses | Interest Expense, Bad Debts Expense, Loss on Disposal of Assets |

**Appears on:** Income Statement

### Quick Reference

| Account Type | What It Represents | Account Series | Financial Statement |
|---|---|---|---|
| Assets | What the business OWNS | 100–199 | Balance Sheet |
| Liabilities | What the business OWES | 200–299 | Balance Sheet |
| Capital | Owner's NET STAKE | 300–399 | Statement of Changes in Equity |
| Income | What the business EARNS | 400–499 | Income Statement |
| Expenses | Costs INCURRED to earn revenue | 500–599 | Income Statement |
MD,

38 => <<<'MD'
## Classifying Accounts and Placing Them on Financial Statements

Once you know the five major account types, the next skill is **rapid classification** — given any account name, identify (a) its type, (b) its normal balance (debit or credit), and (c) which financial statement it appears on.

### The ADE / LCR Memory Aid

Use this every time you classify an account:

> **ADE = Debit normal balance** — **A**ssets, **D**rawings, **E**xpenses
> **LCR = Credit normal balance** — **L**iabilities, **C**apital, **R**evenues

### Classification Rules

| Account Type | Normal Balance | Financial Statement |
|---|---|---|
| Assets | Debit | Statement of Financial Position |
| Liabilities | Credit | Statement of Financial Position |
| Owner's Capital | Credit | Statement of Changes in Equity |
| Owner's Drawings (contra-equity) | Debit | Statement of Changes in Equity |
| Income / Revenues | Credit | Income Statement |
| Expenses | Debit | Income Statement |
| Accumulated Depreciation (contra-asset) | Credit | Statement of Financial Position (deducted from related asset) |

### Practice — Classify Each Account

| # | Account Title | Major Type | Financial Statement |
|---|---|---|---|
| 1 | Cash | **Asset** | Balance Sheet |
| 2 | Accounts Payable | **Liability** | Balance Sheet |
| 3 | Service Fees Earned | **Income** | Income Statement |
| 4 | Salaries Expense | **Expense** | Income Statement |
| 5 | Office Equipment | **Asset** | Balance Sheet |
| 6 | Santos, Capital | **Capital** | Statement of Changes in Equity |
| 7 | Unearned Revenue | **Liability** ⚠️ (not income!) | Balance Sheet |
| 8 | Rent Expense | **Expense** | Income Statement |
| 9 | Merchandise Inventory | **Asset** | Balance Sheet |
| 10 | Notes Payable | **Liability** | Balance Sheet |
| 11 | Advertising Income | **Income** | Income Statement |
| 12 | Santos, Drawings | **Capital** (contra-equity) | Statement of Changes in Equity |
| 13 | Prepaid Insurance | **Asset** | Balance Sheet |
| 14 | Depreciation Expense | **Expense** | Income Statement |
| 15 | Accounts Receivable | **Asset** | Balance Sheet |

### Tricky Cases You Must Know

#### Unearned Revenue is a LIABILITY, not Income
Even though the name contains "Revenue," **Unearned Revenue is a liability** because the business has collected cash but has not yet delivered the service. It becomes income only when the service is performed.

#### Accumulated Depreciation is a CONTRA-ASSET
It has a normal **credit** balance, but it is *not* a liability — it is a deduction from an asset (e.g., Equipment), shown directly below the asset on the balance sheet.

#### Drawings is a CONTRA-EQUITY
It has a normal **debit** balance, but it is *not* an expense — it is a reduction of capital, shown in the Statement of Changes in Equity.

### Common Misconceptions

> *"Drawings is an equity account, so it has a credit balance."*
> **Correction:** Drawings has a normal **DEBIT** balance because it *reduces* equity. Think of it as a "negative" capital account.

> *"Accumulated Depreciation is an asset because it relates to equipment."*
> **Correction:** It is a **contra-asset** with a normal CREDIT balance. It is deducted from the related fixed asset to show its book value.

> *"Unearned Revenue is income because of the word 'revenue.'"*
> **Correction:** It is a **LIABILITY** until the service is rendered. The word "Unearned" is the key — the business has not yet earned it.

### Key Skill Check

You should now be able to:
- Look at any account title and instantly say "Asset," "Liability," "Capital," "Income," or "Expense."
- Identify whether it has a normal debit or normal credit balance.
- Place it on the correct financial statement.
MD,

41 => <<<'MD'
## Examples of Each Account Type — Service vs. Trading Businesses

Different types of businesses use different accounts. A clinic does not have "Merchandise Inventory." A grocery store does not have "Patient Care Revenue." This lesson gives you concrete account names you'll meet in both **service** and **trading** businesses.

### Service Businesses

A **service business** earns revenue by providing services rather than selling goods. Examples: clinics, schools, salons, law firms, consulting agencies.

| Major Type | Sample Accounts |
|---|---|
| Assets | Cash, Accounts Receivable, Office Supplies, Prepaid Insurance, Office Equipment, Service Vehicle |
| Liabilities | Accounts Payable, Notes Payable, Salaries Payable, Unearned Service Revenue |
| Capital | Owner's Capital, Owner's Drawings |
| Income | Service Fees Earned, Professional Fees, Consultation Revenue, Tuition Fees, Advertising Income |
| Expenses | Salaries Expense, Rent Expense, Utilities Expense, Office Supplies Expense, Depreciation Expense |

### Trading / Merchandising Businesses

A **trading business** earns revenue by buying and selling goods. Examples: sari-sari stores, hardware stores, bookstores, online sellers.

| Major Type | Sample Accounts |
|---|---|
| Assets | Cash, Accounts Receivable, **Merchandise Inventory**, Store Supplies, Store Equipment, Delivery Vehicle |
| Liabilities | Accounts Payable, Notes Payable, Sales Tax Payable, Unearned Revenue |
| Capital | Owner's Capital, Owner's Drawings |
| Income | **Sales Revenue / Sales**, Sales Discounts (contra), Sales Returns (contra), Interest Income |
| Expenses | **Cost of Goods Sold / Cost of Sales**, Salaries Expense, Rent Expense, Freight-Out, Advertising Expense |

### Key Differences

| Aspect | Service Business | Trading Business |
|---|---|---|
| Main Asset | Service Equipment, Tools | Merchandise Inventory |
| Main Revenue | Service Fees | Sales Revenue |
| Largest Expense | Salaries (often) | Cost of Goods Sold |
| Unique Account | Unearned Service Revenue | Merchandise Inventory, COGS |

### Side-by-Side Examples

**A clinic's transaction:**
> Dr. Nana renders a consultation worth ₱500 cash.
> → Cash (Asset) +500; Service Fees Earned (Income) +500

**A sari-sari store's transaction:**
> Mang Tomas sells ₱200 worth of canned goods for ₱350 cash.
> → Cash (Asset) +350; Sales Revenue (Income) +350
> → Cost of Goods Sold (Expense) +200; Merchandise Inventory (Asset) −200

Notice how the trading business records **TWO** sets of effects: one for the revenue (sale) and one for the cost (COGS). The service business has only one set.

### Portfolio Activity Prompt

For each major account type, list at least **three accounts** you would expect to see in:
- A computer repair shop (service business)
- A school cafeteria (trading business)
- A salon that also sells beauty products (hybrid)

Write a brief description for each, and identify the financial statement on which it appears. This builds your fluency for the next lesson on building a complete Chart of Accounts.
MD,

44 => <<<'MD'
## Preparing a Chart of Accounts

A **Chart of Accounts (COA)** is a systematic list of all accounts used by a business, organized and numbered for easy identification. It is the master reference that drives the entire bookkeeping system.

### Why the Chart of Accounts Matters

- Provides a **standardized reference** for recording every transaction.
- Ensures **consistency** across all financial records.
- Groups accounts by type, making **financial statements** easier to prepare.
- Allows any bookkeeper or accountant to locate an account quickly by number.

### Standard Numbering Convention (Philippines, Small Business)

| Account Series | Account Type | Appears On |
|---|---|---|
| 100–199 | Assets | Statement of Financial Position |
| 200–299 | Liabilities | Statement of Financial Position |
| 300–399 | Owner's Equity / Capital | Statement of Changes in Equity |
| 400–499 | Income / Revenue | Income Statement |
| 500–599 | Expenses | Income Statement |

Within each series, sub-grouping is common:
- 101–149: Current Assets
- 150–199: Non-Current Assets and Contra-Assets
- 201–249: Current Liabilities
- 250–299: Non-Current Liabilities

### Sample Chart of Accounts — JP Advertising Services (Sole Proprietorship)

| Acct. No. | Account Title | Account Type |
|---|---|---|
| **ASSETS** | | |
| 101 | Cash | Current Asset |
| 102 | Accounts Receivable | Current Asset |
| 103 | Office Supplies | Current Asset |
| 104 | Prepaid Rent | Current Asset |
| 151 | Office Equipment | Non-Current Asset |
| 152 | Accumulated Depreciation — Office Equipment | Contra-Asset |
| **LIABILITIES** | | |
| 201 | Accounts Payable | Current Liability |
| 202 | Notes Payable | Current Liability |
| 203 | Accrued Salaries Payable | Current Liability |
| **OWNER'S EQUITY** | | |
| 301 | JP, Capital | Owner's Equity |
| 302 | JP, Withdrawals | Owner's Drawings |
| **INCOME** | | |
| 401 | Advertising Income | Revenue |
| 402 | Service Fees Earned | Revenue |
| 403 | Interest Income | Other Income |
| **EXPENSES** | | |
| 501 | Salaries Expense | Operating Expense |
| 502 | Rent Expense | Operating Expense |
| 503 | Utilities Expense | Operating Expense |
| 504 | Office Supplies Expense | Operating Expense |
| 505 | Depreciation Expense — Office Equipment | Operating Expense |
| 506 | Miscellaneous Expense | Operating Expense |

### Building Your Own COA — Step by Step

1. **List all accounts** the business will need. Start with assets, then liabilities, then equity, then income, then expenses.
2. **Group accounts by type** following the standard order above.
3. **Assign numbers** within each series (101, 102, 103, …) leaving gaps for future additions.
4. **Maintain alphabetical or logical order** within sub-groupings (e.g., Current Assets ordered by liquidity).
5. **Review and refine** as the business grows. New accounts get new numbers; obsolete accounts are deactivated, not deleted.

### Activity: Build a COA for LUNA Beauty Salon

You are the accountant of LUNA Beauty Salon, a sole proprietorship owned by Ms. Luna Reyes. Using the standard numbering convention, prepare a COA for the following accounts:

> Cash · Accounts Receivable · Supplies Inventory · Salon Equipment · Accounts Payable · Notes Payable · Luna Reyes Capital · Luna Reyes Drawings · Service Revenue · Commission Income · Interest Income · Salaries Expense · Rent Expense · Supplies Expense · Utilities Expense · Depreciation Expense · Advertising Expense · Miscellaneous Expense

Organize them into the five major sections and assign appropriate account numbers (100s for assets, 200s for liabilities, etc.).

> **Key Insight:** A well-built COA is the foundation of every financial statement your business will ever produce. Time spent here pays off in every subsequent step of the accounting cycle.
MD,

// =====================================================================
// MODULE 4 — CO4 — Books of Accounts (Journal, Ledger, Posting)
// =====================================================================

47 => <<<'MD'
## The Two Books of Accounts — Journal and Ledger

Accounting relies on **two main books**: the Journal and the Ledger. Each has a distinct, indispensable role. Understanding the difference between them is the foundation of every step in the accounting cycle.

### The General Journal — Book of ORIGINAL Entry

The **journal** is the first book where every business transaction is recorded. It captures transactions in **chronological order** (by date), regardless of which accounts are affected.

- Records **WHAT** happened, to **WHICH** accounts, in **WHAT** amounts.
- Includes a brief narration of each transaction.
- Cross-references every entry to the ledger via a Posting Reference (PR) column.
- Called the **book of original entry** because every transaction begins here.

### The General Ledger — Book of FINAL Entry

The **ledger** is the second book. Entries from the journal are **transferred (posted)** here, but organized by **account** rather than by date.

- Records the **RUNNING BALANCE** of every account at any point in time.
- Each account (Cash, Accounts Payable, etc.) has its own page.
- Called the **book of final entry** because the final balance of each account is found here.
- The source for preparing the Trial Balance and all financial statements.

### Side-by-Side Comparison

| Aspect | General Journal | General Ledger |
|---|---|---|
| Also Called | Book of original entry | Book of final entry |
| Organizes By | Date (chronological) | Account (one page per account) |
| Step in Cycle | Step 2 — Journalizing | Step 3 — Posting |
| Shows | WHEN something happened | WHAT the running balance is |
| Used to Prepare | (the source for the ledger) | The Trial Balance |
| Why Necessary | Provides complete, dated audit trail | Provides up-to-date balances needed for reports |

### A Simple Example

> **Transaction:** On Jan. 1, the owner invests ₱30,000 cash.

**In the General Journal** (chronological):
> Jan. 1 — Debit Cash ₱30,000 / Credit Owner's Capital ₱30,000 — *To record initial investment by owner.*

**In the General Ledger** (by account):
- **Cash account** page: Date Jan. 1 · Debit ₱30,000 · Running balance ₱30,000
- **Owner's Capital account** page: Date Jan. 1 · Credit ₱30,000 · Running balance ₱30,000

### Why Both Are Necessary

> *"Can you skip the journal and post directly to the ledger?"*

You technically could — but you would lose the chronological audit trail. The journal answers "what happened on Jan. 15?" The ledger answers "how much cash do we have right now?" Both questions matter, so accountants maintain both books.

> *"Can you skip the ledger and just rely on the journal?"*

Also no — the journal has no running balance. To know your current cash, you would have to add every cash entry across hundreds of journal pages. The ledger collects them by account so you have an instant answer.

### Key Vocabulary

| Term | Definition |
|---|---|
| Journal | Book of original entry; records all transactions chronologically |
| Ledger | Book of final entry; organizes transactions by account |
| Posting | Transferring data from the journal to the ledger |
| Posting Reference (PR) | The cross-reference linking journal and ledger |
| Compound Entry | Journal entry involving three or more accounts |
| Simple Entry | Journal entry involving exactly two accounts |
MD,

50 => <<<'MD'
## The General Journal and the Four Special Journals

While the **General Journal** can record every kind of transaction, businesses with high-volume recurring transactions also use **Special Journals** — books of original entry dedicated to a single transaction type. Special journals make recording faster and posting easier.

### The General Journal — Universal Recorder

Every business has a general journal. It records:
- Any transaction that does NOT fit into a special journal.
- Adjusting entries, closing entries, reversing entries.
- Compound entries (3+ accounts).

#### Column Headers

| Column | Purpose |
|---|---|
| Date | When the transaction occurred |
| Account Titles and Explanation | Debited account (left margin), credited account (indented below), brief narration |
| PR | Posting Reference (filled in only after posting) |
| Debit | Peso amount of debit |
| Credit | Peso amount of credit |

#### Sample Journal Entry (Compound Entry)

> **Oct. 28** — Purchased motorcycle ₱110,000; paid ₱80,000 cash, balance ₱30,000 on account.

| Date | Account Titles and Explanation | PR | Debit | Credit |
|---|---|---|---|---|
| Oct. 28 | Motorcycle | 170 | 110,000 | |
| | &nbsp;&nbsp;&nbsp;&nbsp;Cash | 110 | | 80,000 |
| | &nbsp;&nbsp;&nbsp;&nbsp;Accounts Payable | 210 | | 30,000 |
| | *Purchased motorcycle; partial cash, balance on account* | | | |

### The Four Special Journals

| Special Journal | Records | Source Document | Used When |
|---|---|---|---|
| **Sales Journal (SJ)** | Sales on **credit** only | Sales Invoice / Charge Invoice | Business regularly sells on account |
| **Purchases Journal (PJ)** | Purchases on **credit** only | Purchase Invoice from supplier | Business regularly buys on account |
| **Cash Receipts Journal (CRJ)** | ALL cash IN (sales, collections, loans, investments) | Official Receipt | Cash is received regularly |
| **Cash Disbursements Journal (CDJ)** | ALL cash OUT (payments, salaries, rent, utilities) | Check Voucher / Cash Voucher | Cash is paid out regularly |

> **Important Rule:** Transactions that don't fit any of these four still go in the **General Journal**.

### Sales Journal Example

| Date | Customer | PR | Invoice No. | Dr: A/R | Cr: Sales |
|---|---|---|---|---|---|
| Jul 5 | Santos Grocery | 120 | SI-001 | ₱8,000 | ₱8,000 |
| Jul 12 | Cruz Trading | 120 | SI-002 | ₱15,500 | ₱15,500 |
| Jul 20 | Reyes Store | 120 | SI-003 | ₱6,200 | ₱6,200 |
| | | | **TOTAL** | **₱29,700** | **₱29,700** |

> **Posting Rule:** Only the column totals are posted to the General Ledger at month-end. Individual amounts are posted daily to each customer's Accounts Receivable Subsidiary Ledger.

### Cash Receipts Journal Example

| Date | Description | PR | Dr: Cash | Cr: Sales | Cr: A/R | Cr: Sundry |
|---|---|---|---|---|---|---|
| Sept 1 | Investment by owner | 310 | ₱200,000 | | | ₱200,000 |
| Sept 5 | Cash sales | 410 | ₱18,000 | ₱18,000 | | |
| Sept 10 | Collection — Santos | 120 | ₱8,000 | | ₱8,000 | |
| Sept 22 | Cash sales | 410 | ₱12,500 | ₱12,500 | | |

### Why Special Journals Help

Without special journals, a sari-sari store with 50 credit sales per month would write 50 separate journal entries — each repeating "Debit Accounts Receivable / Credit Sales." With a Sales Journal, they fill one line per sale and post one column total at month-end.

### Common Errors

| Error | What's Wrong |
|---|---|
| Recording a cash sale in the Sales Journal | Cash sales belong in the Cash Receipts Journal |
| Recording a cash purchase in the Purchases Journal | Cash purchases belong in the Cash Disbursements Journal |
| Posting individual rows of a special journal instead of totals | Only column totals are posted to the General Ledger |
| Forgetting the Sundry column for capital investments or loans | The Sundry (Other) column captures one-off items |
MD,

53 => <<<'MD'
## The General Ledger and the Subsidiary Ledger

The **ledger** is where account balances live. Most businesses use two levels of ledgers: the **General Ledger** (master accounts) and one or more **Subsidiary Ledgers** (detailed breakdowns of specific control accounts).

### The General Ledger

The General Ledger holds **every account** the business uses — Cash, A/R, Equipment, A/P, Capital, Revenue, all expenses — each on its own page.

#### Balance-Column Form (Standard Format)

> **Account: Cash · Account No. 110**

| Date | Item / Explanation | Ref. | Debit | Credit | Balance |
|---|---|---|---|---|---|
| Jan 1 | Investment by owner | GJ 1 | ₱30,000 | | ₱30,000 |
| Jan 2 | Purchase supplies | GJ 1 | | ₱2,500 | ₱27,500 |
| Jan 3 | Purchase equipment | GJ 1 | | ₱26,000 | ₱1,500 |

Each account has its own page with:
- **Date** — date of the journal entry
- **Reference** — links back to the journal (e.g., GJ 1 means General Journal Page 1)
- **Debit / Credit** — the amount on the appropriate side
- **Balance** — the running balance after each entry

### The Subsidiary Ledger

A **subsidiary ledger** breaks down a single control account in the general ledger into individual sub-accounts. The most common examples:

| Subsidiary Ledger | One Page Per | Controls What in General Ledger |
|---|---|---|
| Accounts Receivable Subsidiary | Customer | Accounts Receivable (control) |
| Accounts Payable Subsidiary | Supplier / Vendor | Accounts Payable (control) |
| Inventory Subsidiary | Inventory item | Merchandise Inventory |
| Fixed Asset Subsidiary | Specific asset | Equipment, Vehicles |

### Sample Subsidiary Ledger — Accounts Receivable

> **Customer: Santos Grocery · Customer No. C-001**

| Date | Explanation | PR | Debit | Credit | Balance |
|---|---|---|---|---|---|
| Jul 5 | Sales on account (SI-001) | SJ1 | ₱8,000 | | ₱8,000 |
| Sept 10 | Collection on account | CRJ1 | | ₱8,000 | — |

> **Customer: Cruz Trading · Customer No. C-002**

| Date | Explanation | PR | Debit | Credit | Balance |
|---|---|---|---|---|---|
| Jul 12 | Sales on account (SI-002) | SJ1 | ₱15,500 | | ₱15,500 |
| Aug 1 | Partial collection | CRJ1 | | ₱5,500 | ₱10,000 |

### Reconciliation: Subsidiary Ledger = Control Account

The **sum of all subsidiary ledger balances must equal the control account balance** in the general ledger. If they don't match, an error must be found.

| Accounts Receivable Subsidiary | Balance (₱) |
|---|---|
| Santos Grocery | — |
| Cruz Trading | 10,000 |
| Reyes Store | 6,200 |
| **Total of all customer balances** | **₱16,200** |
| Accounts Receivable Control (General Ledger) | **₱16,200** |
| **Difference (must be zero)** | **₱0** ✓ |

### Posting Procedure

For each account in a journal entry:

1. **Locate** the account in the ledger.
2. **Enter** the date, journal page reference (e.g., GJ 1), and amount in the appropriate Debit or Credit column.
3. **Compute** the new running balance.
4. **Fill in the account number** in the journal's PR column to confirm posting.

### Differences at a Glance

| General Ledger | Subsidiary Ledger |
|---|---|
| Holds master accounts for ALL types | Holds details of ONE control account |
| One per entity | Multiple possible (A/R, A/P, Inventory) |
| Trial Balance is prepared from this | Trial Balance NOT prepared from this |
| Examples: Cash, A/R control, Capital | Examples: each customer, each supplier |

### Common Misconception

> *"The general ledger and subsidiary ledger record different transactions."*

**Correction:** They record the **same transactions**. The general ledger holds the control account *total*; the subsidiary ledger holds the individual *details*. They must always reconcile.
MD,

// =====================================================================
// MODULE 5 — CO5 — Steps 1-3 of the Accounting Cycle
// =====================================================================

56 => <<<'MD'
## Step 1 — Analyzing Business Transactions

Before any journal entry can be written, you must **analyze** the transaction. Step 1 of the accounting cycle answers four questions:

1. What accounts are affected?
2. What type is each account (Asset, Liability, Equity, Income, Expense)?
3. Is each account increasing or decreasing?
4. Does the accounting equation remain balanced?

### What Is a Business Transaction?

A **business transaction** is any economic event that:
- Affects assets, liabilities, or owner's equity, AND
- Can be measured in monetary terms.

Not every business activity qualifies. Use this test:

| Event | Is It a Transaction? | Why |
|---|---|---|
| Owner invests ₱50,000 cash | ✅ YES | Cash and Capital both increase — measurable financial effect |
| Business hires an employee | ❌ NOT YET | No financial exchange has occurred yet |
| Business buys supplies on credit | ✅ YES | Supplies and Accounts Payable both increase |
| Owner *decides* to buy new equipment | ❌ NOT YET | Decision only — no actual exchange |
| Business earns ₱8,000 from services | ✅ YES | Cash (or A/R) and Revenue both increase |

### Source Documents — The Starting Point

Every transaction begins with a **source document** — written evidence that an exchange has occurred.

| Source Document | Description | What It Evidences |
|---|---|---|
| Official Receipt (OR) | Issued when the business receives payment | Cash received; revenue earned |
| Sales Invoice | Issued when sales are made on credit | Revenue earned; A/R created |
| Purchase Invoice | Received from supplier when buying on credit | Asset/expense acquired; A/P created |
| Check | Written order to bank to pay | Cash payment made |
| Bank Deposit Slip | Record of cash deposited | Cash deposited |
| Voucher / Acknowledgment Receipt | Internal document authorizing payment | Expense recorded; cash disbursed |

### The Analysis Process

For each transaction, ask:

1. **What accounts are affected?**
2. **What is the classification of each?** (Asset, Liability, Equity, Revenue, Expense)
3. **Is each one increasing or decreasing?**
4. **Does Assets = Liabilities + Owner's Equity still hold?**

### Worked Example — Pamilya Services (July 2019)

| # | Transaction | Source Document | Accounts Affected | Effect on Equation |
|---|---|---|---|---|
| T1 | Owner invests ₱60,000 cash | Bank deposit slip | Cash (+Asset); Pamilya Capital (+Equity) | A↑ = OE↑ |
| T2 | Borrowed ₱50,000 from BDO | Promissory note | Cash (+Asset); Loans Payable (+Liability) | A↑ = L↑ |
| T3 | Purchased equipment ₱30,000 cash | Official receipt | Service Equipment (+Asset); Cash (−Asset) | A↑ & A↓ (swap) |
| T4 | Purchased supplies ₱10,000 on credit | Purchase invoice | Supplies (+Asset); A/P (+Liability) | A↑ = L↑ |
| T5 | Rendered services ₱10,000 cash | Official receipt | Cash (+Asset); Service Revenue (+Equity) | A↑ = OE↑ |
| T6 | Paid rent ₱10,000 | Check | Rent Expense (−Equity); Cash (−Asset) | A↓ = OE↓ |

### The Key Rule

> Every transaction must keep **Assets = Liabilities + Owner's Equity** in balance. An increase on one side must be matched by an equal increase or decrease somewhere.

This is the foundation of double-entry bookkeeping — and you've just applied it.

### Common Errors

| Error | Correction |
|---|---|
| Recording the owner's *decision* to buy equipment | Not a transaction until the purchase actually happens |
| Including non-monetary items (employee morale, brand value) | Only measurable monetary events are recorded |
| Failing to identify both sides of a transaction | Every transaction affects at least TWO accounts |
MD,

59 => <<<'MD'
## Step 2 — Journalizing in the General Journal

Once you've analyzed a transaction, **Step 2** is to record it formally in the General Journal — the **book of original entry**. This lesson teaches the format, the debit/credit rules, and how to journalize the transactions you analyzed in Step 1.

### Format of a Journal Entry

Every journal entry contains:
1. **Date** of the transaction
2. **Account(s) Debited** — written first, at the left margin
3. **Account(s) Credited** — written **below and indented**
4. **Amounts** in the Debit and Credit columns
5. **Brief narration** (explanation) on the last line
6. **Posting Reference (PR)** — left blank initially; filled in after posting to the ledger

### The Rules of Debit and Credit

| Account Type | Normal Balance | To Increase | To Decrease |
|---|---|---|---|
| **Assets** | Debit | Debit | Credit |
| **Liabilities** | Credit | Credit | Debit |
| **Owner's Capital** | Credit | Credit | Debit |
| **Owner's Drawings** | Debit | Debit | Credit |
| **Revenues / Income** | Credit | Credit | Debit |
| **Expenses** | Debit | Debit | Credit |

> **Memory Aid:** **ADE = Debit** (Assets, Drawings, Expenses) · **LCR = Credit** (Liabilities, Capital, Revenues)

### Journalizing Pamilya Services (July 2019)

| Date | Account Titles and Explanation | PR | Debit (₱) | Credit (₱) |
|---|---|---|---|---|
| Jul 1 | Cash | 101 | 60,000 | |
| | &nbsp;&nbsp;&nbsp;&nbsp;Pamilya, Capital | 301 | | 60,000 |
| | *T1: Owner's initial investment.* | | | |
| Jul 2 | Cash | 101 | 50,000 | |
| | &nbsp;&nbsp;&nbsp;&nbsp;Loans Payable | 202 | | 50,000 |
| | *T2: Bank loan obtained.* | | | |
| Jul 3 | Service Equipment | 104 | 30,000 | |
| | &nbsp;&nbsp;&nbsp;&nbsp;Cash | 101 | | 30,000 |
| | *T3: Equipment purchased for cash.* | | | |
| Jul 5 | Supplies | 103 | 10,000 | |
| | &nbsp;&nbsp;&nbsp;&nbsp;Accounts Payable | 201 | | 10,000 |
| | *T4: Supplies purchased on account.* | | | |
| Jul 10 | Cash | 101 | 10,000 | |
| | &nbsp;&nbsp;&nbsp;&nbsp;Service Revenue | 401 | | 10,000 |
| | *T5: Services rendered for cash.* | | | |
| Jul 15 | Rent Expense | 504 | 10,000 | |
| | &nbsp;&nbsp;&nbsp;&nbsp;Cash | 101 | | 10,000 |
| | *T6: Monthly rent paid.* | | | |

### Simple vs. Compound Entries

- **Simple Entry**: exactly two accounts (one debit, one credit) — like all the entries above.
- **Compound Entry**: three or more accounts in a single entry.

> Example: *Purchased motorcycle ₱110,000 — paid ₱80,000 cash, balance ₱30,000 on account.*

| Account | Debit | Credit |
|---|---|---|
| Motorcycle | 110,000 | |
| &nbsp;&nbsp;&nbsp;&nbsp;Cash | | 80,000 |
| &nbsp;&nbsp;&nbsp;&nbsp;Accounts Payable | | 30,000 |

### Why the Format Matters

- **Indenting the credit** makes it visually clear which account is being credited.
- **Including the narration** preserves *why* the entry was made — essential for audits years later.
- **Leaving PR blank initially** ensures you can verify which entries have been posted.

### Common Errors

| Error | Correction |
|---|---|
| Writing the credit first | Always debit first, then credit (indented) |
| Forgetting the narration | Every entry needs a brief explanation |
| Mixing up debit/credit rules | Memorize ADE = Debit / LCR = Credit |
| Skipping the date | Every entry must show when it occurred |
MD,

62 => <<<'MD'
## Step 3 — Posting to the General Ledger

After journalizing, **Step 3** is to **post** each journal entry to the General Ledger — transferring each debit and credit to its appropriate account so we can compute running balances.

### What Is Posting?

**Posting** is the process of transferring data from the **General Journal** (chronological) to the **General Ledger** (organized by account).

- Each **debit** in the journal goes to the **debit side** of the same account in the ledger.
- Each **credit** in the journal goes to the **credit side** of the same account in the ledger.
- After every posting, the **running balance** is updated.

### Posting Procedure — Four Steps for Each Account

1. **Locate** the account page in the ledger.
2. **Enter** the date and the journal page reference (e.g., "GJ 1") in the ledger.
3. **Copy** the debit or credit amount to the correct column.
4. **Compute** the new running balance.
5. **Back in the journal**, write the account number in the PR column — this confirms the entry has been posted and links the journal to the ledger.

### Why Cross-Referencing Matters

The Posting Reference (PR) column creates the **audit trail**. From any ledger entry, you can find the original journal entry. From any journal entry, you can find where it was posted. Without PRs, errors become almost impossible to trace.

### Sample Ledger Postings — Pamilya Services

> **Account No. 101 — Cash**

| Date | Explanation | PR | Debit (₱) | Credit (₱) | Balance (₱) |
|---|---|---|---|---|---|
| Jul 1 | Owner's investment (T1) | J1 | 60,000 | | 60,000 Dr |
| Jul 2 | Bank loan received (T2) | J1 | 50,000 | | 110,000 Dr |
| Jul 3 | Equipment purchase (T3) | J1 | | 30,000 | 80,000 Dr |
| Jul 10 | Service revenue earned (T5) | J1 | 10,000 | | 90,000 Dr |
| Jul 15 | Rent expense paid (T6) | J1 | | 10,000 | 80,000 Dr |
| Jul 20 | Salaries paid (T7) | J1 | | 4,000 | 76,000 Dr |
| Jul 25 | Taxes & licenses paid (T8) | J1 | | 2,000 | 74,000 Dr |
| Jul 28 | Utilities paid (T9) | J1 | | 2,500 | 71,500 Dr |
| Jul 31 | Owner's withdrawal (T10) | J1 | | 500 | 71,000 Dr |

> **Account No. 301 — Pamilya, Capital**

| Date | Explanation | PR | Debit | Credit | Balance |
|---|---|---|---|---|---|
| Jul 1 | Owner's initial investment (T1) | J1 | | 60,000 | 60,000 Cr |

### Normal Account Balances (Quick Reference)

| Account Type | Normal Balance | Increases On | Decreases On |
|---|---|---|---|
| Assets | DEBIT | Debit side | Credit side |
| Liabilities | CREDIT | Credit side | Debit side |
| Owner's Capital | CREDIT | Credit side | Debit side |
| Owner's Drawings | DEBIT | Debit side | Credit side |
| Revenues / Income | CREDIT | Credit side | Debit side |
| Expenses | DEBIT | Debit side | Credit side |
| Accumulated Depreciation (contra-asset) | CREDIT | Credit side | Debit side |

### Why Step 3 Is Essential

Without posting:
- You have no running balance for any account.
- You cannot prepare a Trial Balance.
- You cannot prepare any financial statement.

Posting transforms a chronological list of entries into account-level intelligence — *exactly* what the business needs to know its position at any moment.

### Discussion Questions

- After posting all entries, the Cash account shows an ending **debit** balance. What does that mean?
- Why must you write "J1" in the ledger's PR column?
- Could a business skip Step 2 (journalizing) and post transactions directly to the ledger? What would go wrong?

> **Connecting Steps 1–3:** Each step depends on the previous. You cannot journalize without analyzing. You cannot post without journalizing. And you cannot prepare the Trial Balance (Step 4, coming next) without complete, correct postings here in Step 3.
MD,

// =====================================================================
// MODULE 6 — CO6 — Steps 4-5: Trial Balance and Adjusting Entries
// =====================================================================

65 => <<<'MD'
## Step 4 — Preparing the Trial Balance

After every transaction has been journalized (Step 2) and posted (Step 3), the accountant prepares a **Trial Balance** — a list of all open ledger accounts with their balances. Step 4 verifies that **total debits equal total credits** before financial statements are prepared.

### What Is a Trial Balance?

The trial balance is a formal list of all accounts and their balances at a specific date. It serves as a **mathematical check** — confirming that the ledger is in balance.

### Key Characteristics

- Prepared **after** all transactions are journalized and posted.
- Each account appears **only once** — with either a debit or a credit balance (never both).
- Accounts with **zero balances are skipped**.
- The **peso sign (₱)** appears only beside the first figure of each column and beside the totals.
- Totals must be **double-ruled** (two underlines) to indicate they are finalized and balanced.

> **Important Caveat:** A balanced trial balance does NOT guarantee that all transactions were recorded correctly. Errors such as posting to the *wrong account* (with the correct amount) will not cause an imbalance. The trial balance only verifies **mathematical** accuracy.

### Normal Account Balances (Review)

| Account Type | Normal Balance | Examples |
|---|---|---|
| Assets | DEBIT | Cash, A/R, Supplies, Prepaid Rent, Equipment |
| Liabilities | CREDIT | A/P, N/P, Unearned Revenue, Utilities Payable |
| Owner's Capital | CREDIT | Capital account |
| Owner's Drawings | DEBIT | Drawings / Withdrawals |
| Revenues / Income | CREDIT | Service Revenue, Consulting Fees |
| Expenses | DEBIT | Salaries, Rent, Utilities, Supplies Expense |
| Accumulated Depreciation | CREDIT | Contra-asset; deducted from related fixed asset |

> **Memory Aid:** ADE = Debit · LCR = Credit

### Steps to Prepare the Trial Balance

1. **Heading** — center at the top: Name of business · "Trial Balance" · Specific date.
2. **List all open accounts** in ledger order (Assets → Liabilities → Equity → Revenue → Expenses). Skip zero balances.
3. **Place each balance** in the Debit or Credit column based on its normal balance.
4. **Total** both columns separately.
5. **Double-rule** both totals. If Debit total = Credit total, the trial balance is in balance.

### Sample Trial Balance — Pamilya Services (July 31, 2019)

| Acct. No. | Account Title | Debit (₱) | Credit (₱) |
|---|---|---|---|
| 101 | Cash | 63,500 | |
| 103 | Supplies | 10,000 | |
| 104 | Service Equipment | 30,000 | |
| 201 | Accounts Payable | | 2,500 |
| 202 | Loans Payable | | 50,000 |
| 301 | Pamilya, Capital | | 60,000 |
| 302 | Pamilya, Drawings | 500 | |
| 401 | Service Revenue | | 10,000 |
| 501 | Taxes and Licenses Expense | 2,000 | |
| 502 | Salaries Expense | 4,000 | |
| 503 | Utilities Expense | 2,500 | |
| 504 | Rent Expense | 10,000 | |
| | **TOTAL** | **₱122,500** | **₱122,500** |

> *Note: Accounts Receivable is omitted because its balance is zero. Notice how the peso sign appears only beside the first figure and beside totals — not on every line.*

### Errors a Balanced Trial Balance Cannot Detect

| Error | Why It Doesn't Cause Imbalance |
|---|---|
| Posting to the wrong account (with correct amount) | Both columns still equal |
| Omitting an entire transaction | No entry made on either side |
| Compensating errors (two errors that cancel out) | Net effect zero |
| Recording an entry twice | Both sides still equal |

These are why the Trial Balance is only the *first* mathematical check — not the last word on accuracy.

### Common Errors When Preparing the Trial Balance

| Mistake | Correction |
|---|---|
| Listing accounts with zero balance | Skip them entirely |
| Putting Drawings in the credit column | Drawings has a normal DEBIT balance |
| Putting Accumulated Depreciation in the debit column | It is a contra-asset with a normal CREDIT balance |
| Listing Unearned Revenue in the debit column | It is a LIABILITY with a normal CREDIT balance |
| Forgetting to double-rule the totals | Always double-rule when balanced |
MD,

68 => <<<'MD'
## Step 5 — Journalizing and Posting Adjusting Entries

After the unadjusted Trial Balance confirms total debits equal total credits, the cycle moves to **Step 5**: recording **adjusting entries**. These entries update account balances at the end of the period so that revenues and expenses are properly matched **before** financial statements are prepared.

### Why Adjusting Entries Are Needed

Many transactions span multiple periods:
- Rent paid for a year covers many months.
- Supplies bought in bulk are used gradually.
- Employees earn wages every day but get paid only on payday.

The **accrual basis** and the **matching principle** require revenues and expenses to be recorded in the period they are **earned** or **incurred** — not when cash moves. Adjusting entries make sure this happens.

### Important Rule

> Every adjusting entry affects **at least one income statement account** (revenue or expense) and **at least one balance sheet account** (asset or liability). Adjusting entries **never** involve the Cash account.

### The Five Types of Adjusting Entries

#### 1. Prepaid Expenses (Asset Used Up)
Paid in advance; partially used by period end.

> *Paid ₱12,000 for a one-year insurance policy on July 1. By July 31, one month (₱1,000) has expired.*
> **Adjusting entry:** Debit Insurance Expense ₱1,000 / Credit Prepaid Insurance ₱1,000.

#### 2. Accrued Expenses (Expense Incurred but Unpaid)
Expense already incurred but cash not yet paid.

> *Employees earned ₱5,000 in salaries for the last few days of December, but payday is January 5.*
> **Adjusting entry:** Debit Salaries Expense ₱5,000 / Credit Salaries Payable ₱5,000.

#### 3. Unearned Revenues (Liability Earned Off)
Cash collected before the service is performed; partially earned by period end.

> *Client paid ₱6,000 in advance for 3 months of consulting. After 1 month, ₱2,000 is earned.*
> **Adjusting entry:** Debit Unearned Revenue ₱2,000 / Credit Service Revenue ₱2,000.

#### 4. Accrued Revenues (Earned but Not Yet Received)
Revenue already earned but cash not yet collected.

> *Completed a ₱3,000 repair job on July 29; invoice will be sent in August.*
> **Adjusting entry:** Debit Accounts Receivable ₱3,000 / Credit Service Revenue ₱3,000.

#### 5. Depreciation (Allocation of Asset Cost)
Allocates the cost of a fixed asset over its useful life.

> *Equipment costing ₱60,000 has a 5-year useful life with no salvage value. Monthly depreciation = ₱1,000.*
> **Adjusting entry:** Debit Depreciation Expense ₱1,000 / Credit Accumulated Depreciation ₱1,000.

### Summary Table

| Type | Account Affected (BS) | Account Affected (IS) | Cash Involved? |
|---|---|---|---|
| Prepaid Expense | Asset ↓ | Expense ↑ | No |
| Accrued Expense | Liability ↑ | Expense ↑ | No |
| Unearned Revenue | Liability ↓ | Revenue ↑ | No |
| Accrued Revenue | Asset ↑ | Revenue ↑ | No |
| Depreciation | Contra-Asset ↑ | Expense ↑ | No |

### Steps to Journalize and Post Adjusting Entries

1. **Analyze** the unadjusted trial balance and supporting data. Identify accounts needing adjustment.
2. **Determine** the correct adjustment amount (the portion applicable to the current period).
3. **Journalize** the adjusting entry in the General Journal with proper debit/credit and a clear explanation.
4. **Post** to the ledger; update each affected account's running balance.
5. **Prepare** the Adjusted Trial Balance (the next step) using the new balances.

### Adjusting Entries Are NOT Optional

Without them:
- Insurance bought on July 1 would still show ₱12,000 as an asset on December 31 — even though most of it has been used.
- Salaries earned in late December would not appear as an expense — overstating profit.
- Equipment would never depreciate — overstating assets year after year.

Adjusting entries make the financial statements **truthful**. That's why they happen at the end of every accounting period.
MD,

71 => <<<'MD'
## Preparing the Adjusted Trial Balance

After all adjusting entries are journalized and posted (Step 5), the accountant prepares the **Adjusted Trial Balance** — a fresh list of all account balances reflecting those adjustments. This is the foundation for every financial statement.

### What Is the Adjusted Trial Balance?

The Adjusted Trial Balance is a listing of all accounts **after** recording and posting the adjusting entries. It reflects the updated, accurate balances of all accounts before financial statements are prepared.

> The unadjusted trial balance shows what the books said. The adjusted trial balance shows what the books *should* say.

### How to Prepare It (Worksheet Method)

A common technique is the **10-column worksheet**:

| Columns | Purpose |
|---|---|
| 1–2 (Debit/Credit) | Unadjusted Trial Balance |
| 3–4 (Debit/Credit) | Adjustments |
| 5–6 (Debit/Credit) | Adjusted Trial Balance |
| 7–8 (Debit/Credit) | Income Statement |
| 9–10 (Debit/Credit) | Balance Sheet |

For preparing the Adjusted Trial Balance, focus on columns 1–6:

1. **Transfer** all balances from the trial balance into columns 1–2.
2. **Enter** all adjusting entries into columns 3–4 (Adjustments).
3. **Compute** the adjusted balances using these rules:
   - If trial balance and adjustment are on the **SAME** side → **ADD** the amounts.
   - If on **OPPOSITE** sides → **SUBTRACT** the smaller from the larger; place the difference on the side of the greater amount.
4. **Enter** adjusted balances in columns 5–6.
5. **Verify** that total debits equal total credits in columns 5–6.

### Illustration — ABM Accounting Firm (December 31, 2020)

| Account Title | TB Dr | TB Cr | Adj Dr | Adj Cr | Adj TB Dr | Adj TB Cr |
|---|---|---|---|---|---|---|
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

### Key Observations

- **Supplies** dropped from ₱8,000 (trial balance) to ₱2,500 (adjusted) because ₱5,500 was used (expensed).
- **Rent Expense** dropped from ₱40,000 to ₱30,000 because ₱10,000 was reclassified as Prepaid Rent (an asset).
- **New accounts** appeared because of the adjustments: Supplies Expense, Prepaid Rent, Depreciation Expense, Accumulated Depreciation.
- The Adjusted TB total (₱486,750) is **higher** than the unadjusted TB (₱483,000) because some adjustments added new accounts to both sides.

### Worked Computation Rules

| Trial Balance Side | Adjustment Side | Action |
|---|---|---|
| Debit | Debit | Add the two amounts |
| Credit | Credit | Add the two amounts |
| Debit | Credit | Subtract; place difference on side of greater amount |
| Credit | Debit | Subtract; place difference on side of greater amount |

### Why This Step Matters

The Adjusted Trial Balance is the **single source of truth** for preparing the financial statements:
- Revenue and Expense accounts → **Income Statement**
- Asset, Liability, and Capital accounts → **Balance Sheet (SFP)**
- Capital, Drawings, and Net Income → **Statement of Changes in Equity**

If your adjusted trial balance doesn't balance, your financial statements won't either. Always verify totals before moving on.

### Common Errors

| Mistake | Correction |
|---|---|
| Forgetting to post the adjusting entries to the ledger | Adjustments must be posted, not just journalized |
| Adding when amounts are on opposite sides | Subtract opposing amounts; carry difference on greater side |
| Listing a new account in the wrong column | Match its normal balance — new expense in debit, new liability in credit |
| Not double-checking column totals | Always verify Adjusted TB totals match before proceeding |
MD,

// =====================================================================
// MODULE 7 — CO7 — Steps 6-10: Completing the Accounting Cycle
// =====================================================================

74 => <<<'MD'
## Steps 6 & 7 — Adjusted Trial Balance and Financial Statements

With the Adjusted Trial Balance complete, you are ready to produce the documents that owners, investors, and regulators actually use: the **financial statements**. This lesson covers **Step 6** (the adjusted trial balance, in context) and **Step 7** (preparing the three core financial statements).

### Quick Recap: The Adjusted Trial Balance

The Adjusted Trial Balance lists every account *after* adjusting entries — it is the bridge between bookkeeping and reporting. Every figure in every financial statement comes from here.

### The Three Statements You Will Prepare

Under PFRS, businesses prepare five statements, but at the FABM 1 level we focus on three:

1. **Income Statement** (Statement of Comprehensive Income)
2. **Statement of Changes in Equity**
3. **Statement of Financial Position** (Balance Sheet)

### 1. Income Statement

**Purpose:** Shows revenues and expenses for an accounting period; determines net income or net loss.

> **Formula:** Net Income = Total Revenue − Total Expenses

#### Example — ABM Accounting Firm (For the Year Ended Dec. 31, 2020)

| | Amount (₱) |
|---|---|
| Revenue: | |
| &nbsp;&nbsp;&nbsp;&nbsp;Professional Fees | 118,000 |
| Operating Expenses: | |
| &nbsp;&nbsp;&nbsp;&nbsp;Rent Expense | 30,000 |
| &nbsp;&nbsp;&nbsp;&nbsp;Salaries Expense | 16,000 |
| &nbsp;&nbsp;&nbsp;&nbsp;Supplies Expense | 5,500 |
| &nbsp;&nbsp;&nbsp;&nbsp;Depreciation Expense | 3,750 |
| &nbsp;&nbsp;&nbsp;&nbsp;Utilities Expense | 3,000 |
| &nbsp;&nbsp;&nbsp;&nbsp;Taxes and Licenses | 2,000 |
| &nbsp;&nbsp;&nbsp;&nbsp;**Total Operating Expenses** | **(60,250)** |
| **NET INCOME** | **₱57,750** |

### 2. Statement of Changes in Equity

**Purpose:** Reconciles beginning and ending capital balances; shows how net income, additional investments, and withdrawals affect the owner's equity.

> **Formula:** Ending Capital = Beginning Capital + Net Income + Additional Investment − Drawings

#### Example — ABM Accounting Firm

| | Amount (₱) |
|---|---|
| Ms. Co, Capital, January 1, 2020 | 300,000 |
| Add: Net Income for the period | 57,750 |
| | 357,750 |
| Less: Ms. Co, Drawings | (10,000) |
| **Ms. Co, Capital, December 31, 2020** | **₱347,750** |

### 3. Statement of Financial Position (Balance Sheet)

**Purpose:** Shows the balances of assets, liabilities, and owner's equity **as of a specific date**. Proves the accounting equation: Assets = Liabilities + Owner's Equity.

Two acceptable formats: **Account Form** (assets on left, liabilities + equity on right) and **Report Form** (top-to-bottom). The Report Form is shown below.

#### Example — ABM Accounting Firm (As of December 31, 2020)

| | Amount (₱) |
|---|---|
| **ASSETS** | |
| **Current Assets** | |
| &nbsp;&nbsp;&nbsp;&nbsp;Cash | 259,000 |
| &nbsp;&nbsp;&nbsp;&nbsp;Accounts Receivable | 90,000 |
| &nbsp;&nbsp;&nbsp;&nbsp;Prepaid Rent | 10,000 |
| &nbsp;&nbsp;&nbsp;&nbsp;Supplies | 2,500 |
| &nbsp;&nbsp;&nbsp;&nbsp;**Total Current Assets** | **361,500** |
| **Non-Current Assets** | |
| &nbsp;&nbsp;&nbsp;&nbsp;Equipment | 55,000 |
| &nbsp;&nbsp;&nbsp;&nbsp;Less: Accumulated Depreciation | (3,750) |
| &nbsp;&nbsp;&nbsp;&nbsp;Book Value of Equipment | 51,250 |
| **TOTAL ASSETS** | **₱412,750** |
| **LIABILITIES AND OWNER'S EQUITY** | |
| Current Liabilities | |
| &nbsp;&nbsp;&nbsp;&nbsp;Accounts Payable | 65,000 |
| Owner's Equity | |
| &nbsp;&nbsp;&nbsp;&nbsp;Ms. Co, Capital | 347,750 |
| **TOTAL LIABILITIES AND OWNER'S EQUITY** | **₱412,750** |

> The ending capital (₱347,750) comes directly from the Statement of Changes in Equity. Equipment is presented **net of** accumulated depreciation.

### Source of Each Statement

| Statement | Source | Period or Date |
|---|---|---|
| Income Statement | Revenue + Expense accounts | Period (e.g., For the Year Ended Dec. 31) |
| Statement of Changes in Equity | Capital, Drawings, + Net Income | Period |
| Balance Sheet / SFP | Asset, Liability, + updated Capital | Point in time (As of Dec. 31) |
MD,

77 => <<<'MD'
## Steps 8 & 9 — Closing Entries and the Post-Closing Trial Balance

After preparing the financial statements, the accountant must **close** the books to prepare for the next accounting period. This involves two steps: journalizing closing entries (Step 8) and preparing the post-closing trial balance (Step 9).

### Why We Close the Books

Some accounts measure activity *during* a period (revenues, expenses, drawings) — they must restart at zero each period. Others measure *cumulative balances* (assets, liabilities, capital) — they continue from one period to the next.

| Account Type | Temporary or Permanent? | Closed at Year-End? |
|---|---|---|
| Revenues | Temporary / Nominal | YES — closed to Income Summary |
| Expenses | Temporary / Nominal | YES — closed to Income Summary |
| Drawings | Temporary / Nominal | YES — closed directly to Capital |
| Income Summary | Temporary / Nominal | YES — closed to Capital |
| Assets | Permanent / Real | NO — carry to next period |
| Liabilities | Permanent / Real | NO — carry to next period |
| Owner's Capital | Permanent / Real | NO — updated but not closed |

### The Four Closing Steps

| Step | What to Do | Journal Entry Pattern |
|---|---|---|
| 1 | Close all revenue accounts to Income Summary | Debit each Revenue / Credit Income Summary |
| 2 | Close all expense accounts to Income Summary | Debit Income Summary / Credit each Expense |
| 3 | Close Income Summary to Capital | If net income: Debit Income Summary / Credit Capital. If net loss: Debit Capital / Credit Income Summary |
| 4 | Close Drawing/Withdrawal to Capital | Debit Capital / Credit Drawings |

### Worked Example — ABM Accounting Firm (Dec. 31, 2020)

#### Step 1 — Close Revenue
| Account | Debit | Credit |
|---|---|---|
| Professional Fees | 118,000 | |
| &nbsp;&nbsp;&nbsp;&nbsp;Income Summary | | 118,000 |

#### Step 2 — Close Expenses
| Account | Debit | Credit |
|---|---|---|
| Income Summary | 60,250 | |
| &nbsp;&nbsp;&nbsp;&nbsp;Rent Expense | | 30,000 |
| &nbsp;&nbsp;&nbsp;&nbsp;Salaries Expense | | 16,000 |
| &nbsp;&nbsp;&nbsp;&nbsp;Supplies Expense | | 5,500 |
| &nbsp;&nbsp;&nbsp;&nbsp;Depreciation Expense | | 3,750 |
| &nbsp;&nbsp;&nbsp;&nbsp;Utilities Expense | | 3,000 |
| &nbsp;&nbsp;&nbsp;&nbsp;Taxes and Licenses | | 2,000 |

#### Step 3 — Close Income Summary to Capital
Net Income = ₱118,000 − ₱60,250 = ₱57,750

| Account | Debit | Credit |
|---|---|---|
| Income Summary | 57,750 | |
| &nbsp;&nbsp;&nbsp;&nbsp;Ms. Co, Capital | | 57,750 |

#### Step 4 — Close Drawings to Capital
| Account | Debit | Credit |
|---|---|---|
| Ms. Co, Capital | 10,000 | |
| &nbsp;&nbsp;&nbsp;&nbsp;Ms. Co, Drawings | | 10,000 |

After all four steps, every temporary account has a **zero balance** and is ready for the new period.

### Step 9 — The Post-Closing Trial Balance

The **post-closing trial balance** lists only the remaining real (permanent) accounts and their balances. It serves two important functions:
1. Confirms total debits still equal total credits **after** closing.
2. Proves all temporary accounts have been zeroed out.

#### Example — ABM Accounting Firm (Dec. 31, 2020)

| Account Title | Debit (₱) | Credit (₱) |
|---|---|---|
| Cash | 259,000 | |
| Accounts Receivable | 90,000 | |
| Prepaid Rent | 10,000 | |
| Supplies | 2,500 | |
| Equipment | 55,000 | |
| Accumulated Depreciation — Equipment | | 3,750 |
| Accounts Payable | | 65,000 |
| Ms. Co, Capital (after closing) | | 347,750 |
| **TOTALS** | **₱416,500** | **₱416,500** |

The updated capital balance (₱347,750) reflects: Beginning capital (₱300,000) + Net Income (₱57,750) − Drawings (₱10,000).

### Common Misconceptions

> *"Drawings are closed to Income Summary because they reduce equity."*
> **Correction:** Drawings are NOT an expense — they are closed **directly** to Capital, bypassing Income Summary entirely.

> *"Income Summary is a real account that carries to next period."*
> **Correction:** Income Summary is a temporary account used only during closing. After Step 3 it has a zero balance and never appears in the post-closing trial balance.

> *"For a net loss, we still debit Income Summary and credit Capital."*
> **Correction:** For a net loss, the entry is reversed — debit Capital, credit Income Summary. Net loss reduces equity.
MD,

80 => <<<'MD'
## Step 10 — Reversing Entries and Completing the Cycle

The accounting cycle ends with **Step 10**: preparing **reversing entries** at the start of the new accounting period. This optional but powerful step simplifies bookkeeping for the new period and reduces the risk of error.

### What Are Reversing Entries?

**Reversing entries** are journal entries made on the **first day of the new accounting period**. They are the **exact opposite** of certain adjusting entries from the previous period — the debit and credit sides are simply swapped.

They are **optional but strongly recommended** because they eliminate the need to split future cash payments or receipts between two accounts.

### When Are Reversing Entries Used?

| Type of Adjusting Entry | Reverse? |
|---|---|
| Prepayments using the **Expense Method** (originally debited to expense) | ✅ Yes |
| Precollections using the **Revenue Method** (originally credited to revenue) | ✅ Yes |
| **Accrued Expenses** (unpaid expenses at year-end) | ✅ Yes |
| **Accrued Income** (earned but uncollected revenue at year-end) | ✅ Yes |
| Prepayments using the **Asset Method** | ❌ No |
| Precollections using the **Liability Method** | ❌ No |
| **Depreciation Expense** | ❌ No |
| **Bad Debts Expense** | ❌ No |

### Example — ABM Accounting Firm

**Adjusting entry on Dec. 31, 2020** (prepaid rent recorded under the expense method):
| Account | Debit | Credit |
|---|---|---|
| Prepaid Rent | 10,000 | |
| &nbsp;&nbsp;&nbsp;&nbsp;Rent Expense | | 10,000 |

**Reversing entry on Jan. 1, 2021** (the exact opposite):
| Account | Debit | Credit |
|---|---|---|
| Rent Expense | 10,000 | |
| &nbsp;&nbsp;&nbsp;&nbsp;Prepaid Rent | | 10,000 |

This restores the accounts to their pre-adjustment state, so the next rent payment can be recorded normally without splitting it between Prepaid Rent and Rent Expense.

### Why Reverse?

Without a reversing entry, the bookkeeper would need to manually remember which portion of the next cash payment relates to the prior period vs. the new period — a frequent source of error. Reversing entries automate that adjustment so the new period starts cleanly.

### The Complete 10-Step Accounting Cycle

| Step | Activity | Phase |
|---|---|---|
| 1 | Analyzing business transactions | Recording |
| 2 | Journalizing | Recording |
| 3 | Posting to the ledger | Recording |
| 4 | Preparing the trial balance | Recording |
| 5 | Journalizing and posting adjusting entries | Recording |
| 6 | Preparing the adjusted trial balance | Summarizing |
| 7 | Preparing the financial statements | Communicating |
| 8 | Journalizing and posting closing entries | Summarizing |
| 9 | Preparing the post-closing trial balance | Summarizing |
| 10 | Preparing reversing journal entries | Summarizing |

### Key Reminders About Reversing Entries

- Dated the **first day** of the new period (e.g., Jan. 1, 2021).
- An **exact mirror image** of the original adjusting entry.
- **Optional** — but reduces error in future bookkeeping.
- Not all adjustments are reversed — only those involving **future cash transactions** that would need to be split.

### Common Misconceptions

> *"All adjusting entries need a reversing entry."*
> **Correction:** Only adjustments for prepayments (expense method), precollections (revenue method), accrued expenses, and accrued income are reversed. Depreciation and bad debts adjustments do NOT require reversing entries.

> *"Reversing entries are required by law."*
> **Correction:** They are optional. A business may choose to manually account for the split in the next period instead — though most accountants strongly recommend reversing entries to reduce error.

### You've Completed the Cycle

From analyzing the first transaction to reversing entries on Day 1 of the next year, you've walked through every step of the accounting cycle. The same cycle repeats every period — for every business, in every industry, across every country that follows PFRS/GAAP.

> **Closing Insight:** Mastering this cycle means you can now follow the money through any business — from the first peso received to the final report read by an investor, banker, or regulator. That's the language of business, fully learned.
MD,

        ];
    }
}
