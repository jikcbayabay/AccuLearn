// Mock data + mock service functions.
// All "API" calls are wrapped in Promise.resolve to simulate async fetching.

export const mockDelay = (data, ms = 300) => new Promise(res => setTimeout(() => res(data), ms));

export const USERS = [
  { id: 'u1', name: 'Maria Santos',     email: 'maria.s@acculearn.ph',   role: 'student', section: 'ABM 11-A', joined: '2025-08-12', active: true },
  { id: 'u2', name: 'Juan Dela Cruz',   email: 'juan.dc@acculearn.ph',   role: 'student', section: 'ABM 11-A', joined: '2025-08-12', active: true },
  { id: 'u3', name: 'Ella Reyes',       email: 'ella.r@acculearn.ph',    role: 'student', section: 'ABM 11-B', joined: '2025-08-14', active: true },
  { id: 'u4', name: 'Marco Villanueva', email: 'marco.v@acculearn.ph',   role: 'student', section: 'ABM 11-B', joined: '2025-08-14', active: false },
  { id: 'u5', name: 'Patricia Lim',     email: 'patricia.l@acculearn.ph',role: 'student', section: 'ABM 11-A', joined: '2025-08-15', active: true },
  { id: 'u6', name: 'Daniel Cruz',      email: 'daniel.c@acculearn.ph',  role: 'student', section: 'ABM 11-C', joined: '2025-08-15', active: true },
  { id: 'u7', name: 'Ms. Andrea Torres',email: 'a.torres@acculearn.ph',  role: 'teacher', section: 'ABM 11-A', joined: '2025-06-01', active: true },
  { id: 'u8', name: 'Mr. Robert Yu',    email: 'r.yu@acculearn.ph',      role: 'teacher', section: 'ABM 11-B', joined: '2025-06-01', active: true },
  { id: 'u9', name: 'Admin Office',     email: 'admin@acculearn.ph',     role: 'admin',   section: '—',        joined: '2025-05-01', active: true },
];

export const MODULES = [
  {
    id: 'm1', order: 1, title: 'Introduction to Business',
    description: 'Foundations of business, types of organizations, and stakeholders.',
    status: 'completed', progress: 100, lessons: 5, quizzes: 2,
  },
  {
    id: 'm2', order: 2, title: 'Forms of Business Organization',
    description: 'Sole proprietorship, partnership, corporation, cooperatives.',
    status: 'completed', progress: 100, lessons: 6, quizzes: 2,
  },
  {
    id: 'm3', order: 3, title: 'Fundamentals of Accountancy I',
    description: 'Accounting equation, debits, credits, and the journal entry.',
    status: 'in-progress', progress: 62, lessons: 8, quizzes: 3,
  },
  {
    id: 'm4', order: 4, title: 'Business Mathematics',
    description: 'Markups, discounts, interest, and basic statistics for business.',
    status: 'in-progress', progress: 24, lessons: 7, quizzes: 3,
  },
  {
    id: 'm5', order: 5, title: 'Organization & Management',
    description: 'Functions of management, organizational structure, leadership.',
    status: 'locked', progress: 0, lessons: 6, quizzes: 2,
  },
  {
    id: 'm6', order: 6, title: 'Applied Economics',
    description: 'Supply, demand, market structures, and the Philippine economy.',
    status: 'locked', progress: 0, lessons: 7, quizzes: 3,
  },
];

export const LESSONS = {
  m3: [
    { id: 'l1', title: 'The Accounting Equation', duration: '8 min', completed: true },
    { id: 'l2', title: 'Assets, Liabilities, and Equity', duration: '12 min', completed: true },
    { id: 'l3', title: 'Debits and Credits', duration: '14 min', completed: true },
    { id: 'l4', title: 'Recording Transactions', duration: '10 min', completed: true },
    { id: 'l5', title: 'The Journal Entry', duration: '15 min', completed: true,
      body: `A journal entry is the formal record of a business transaction. Each entry must reflect the dual-entry rule: total debits equal total credits.

In this lesson we will:
  • Identify the accounts affected by a transaction.
  • Determine whether each account is debited or credited.
  • Record the entry in the general journal with proper formatting.

Example. On May 5, the business purchased office supplies worth ₱2,400 in cash.
  Dr.  Office Supplies       2,400
       Cr.  Cash                       2,400
  (To record purchase of office supplies for cash.)` },
    { id: 'l6', title: 'Posting to the Ledger', duration: '11 min', completed: false },
    { id: 'l7', title: 'Trial Balance', duration: '13 min', completed: false },
    { id: 'l8', title: 'Worksheet & Adjustments', duration: '16 min', completed: false },
  ],
};

export const QUIZZES = {
  m3: {
    id: 'q-m3-1',
    title: 'Quiz 3.2 — Journal Entries',
    questions: [
      {
        id: 'q1',
        prompt: 'A business receives ₱10,000 cash from a client for services rendered. Which entry is correct?',
        options: [
          'Dr. Cash 10,000 / Cr. Service Revenue 10,000',
          'Dr. Service Revenue 10,000 / Cr. Cash 10,000',
          'Dr. Accounts Receivable 10,000 / Cr. Cash 10,000',
          'Dr. Cash 10,000 / Cr. Accounts Payable 10,000',
        ],
        answer: 0,
      },
      {
        id: 'q2',
        prompt: 'The owner invests ₱50,000 cash into the business. The correct journal entry is:',
        options: [
          'Dr. Owner’s Equity 50,000 / Cr. Cash 50,000',
          'Dr. Cash 50,000 / Cr. Owner’s Equity 50,000',
          'Dr. Cash 50,000 / Cr. Loans Payable 50,000',
          'Dr. Drawings 50,000 / Cr. Cash 50,000',
        ],
        answer: 1,
      },
      {
        id: 'q3',
        prompt: 'Which of the following is NOT a real (permanent) account?',
        options: ['Cash', 'Accounts Payable', 'Service Revenue', 'Equipment'],
        answer: 2,
      },
      {
        id: 'q4',
        prompt: 'Increases in liabilities are recorded as:',
        options: ['Debits', 'Credits', 'Either, depending on the account', 'Memo entries'],
        answer: 1,
      },
      {
        id: 'q5',
        prompt: 'The accounting equation is:',
        options: [
          'Assets = Liabilities − Equity',
          'Assets + Liabilities = Equity',
          'Assets = Liabilities + Equity',
          'Equity = Assets + Liabilities',
        ],
        answer: 2,
      },
    ],
  },
};

export const FEEDBACK = [
  {
    id: 'f1', studentId: 'u1', studentName: 'Maria Santos', module: 'Fundamentals of Accountancy I',
    quiz: 'Quiz 3.2 — Journal Entries', score: 3, total: 5,
    gi: 0.18, cmi: 0.42, status: 'pending',
    mistakes: [
      'Reversed debit/credit on owner investment (Q2).',
      'Misclassified Service Revenue as a permanent account (Q3).',
    ],
    suggestions: [
      'Review Lesson 5: The Journal Entry, focusing on the dual-entry rule.',
      'Practice 5 more entries on owner contributions and revenue recognition.',
      'Try the flashcard set on temporary vs. permanent accounts.',
    ],
    summary: 'Maria shows solid grasp of the accounting equation but is over-confident on entries involving equity and revenue. Suggested re-practice before moving to posting.',
    generatedAt: '2026-05-04 14:21',
  },
  {
    id: 'f2', studentId: 'u2', studentName: 'Juan Dela Cruz', module: 'Business Mathematics',
    quiz: 'Quiz 4.1 — Markups & Discounts', score: 4, total: 5,
    gi: 0.10, cmi: 0.18, status: 'approved',
    mistakes: ['Computed trade discount on net price instead of list price (Q4).'],
    suggestions: ['Re-read worked example 4.3 on trade discount sequencing.'],
    summary: 'Strong overall performance with calibrated confidence.',
    generatedAt: '2026-05-03 09:12',
  },
  {
    id: 'f3', studentId: 'u3', studentName: 'Ella Reyes', module: 'Fundamentals of Accountancy I',
    quiz: 'Quiz 3.1 — Debits & Credits', score: 2, total: 5,
    gi: 0.31, cmi: 0.55, status: 'pending',
    mistakes: [
      'Treated all increases as debits (Q1, Q4).',
      'Confused asset and liability normal balances.',
    ],
    suggestions: [
      'Repeat Lesson 3 with the normal-balance reference card.',
      'Drop back to LP2 supplemental drills before retake.',
    ],
    summary: 'Ella may benefit from a supplemental learning path before continuing.',
    generatedAt: '2026-05-02 16:40',
  },
  {
    id: 'f4', studentId: 'u4', studentName: 'Marco Villanueva', module: 'Business Mathematics',
    quiz: 'Quiz 4.2 — Simple Interest', score: 1, total: 5,
    gi: 0.44, cmi: 0.62, status: 'pending',
    mistakes: [
      'Used the compound formula for simple interest (Q2, Q5).',
      'Dropped the time conversion from days to years (Q3).',
    ],
    suggestions: [
      'Re-watch Lesson 4.2 and complete the worked-example set.',
      'Schedule a 1:1 review with Ms. Torres before retake.',
    ],
    summary: 'Marco is bouncing between formulas — recommend a slower, scaffolded path with paired practice.',
    generatedAt: '2026-05-02 11:08',
  },
  {
    id: 'f5', studentId: 'u5', studentName: 'Patricia Lim', module: 'Fundamentals of Accountancy I',
    quiz: 'Quiz 3.2 — Journal Entries', score: 4, total: 5,
    gi: 0.14, cmi: 0.22, status: 'approved',
    mistakes: ['Skipped the explanatory memo line on Q4.'],
    suggestions: ['Practice formatting the full journal entry, including the memo.'],
    summary: 'Strong conceptual grasp. Just needs to tighten formatting before posting.',
    generatedAt: '2026-05-01 13:55',
  },
  {
    id: 'f6', studentId: 'u6', studentName: 'Daniel Cruz', module: 'Forms of Business Organization',
    quiz: 'Quiz 2.1 — Sole Prop & Partnership', score: 3, total: 5,
    gi: 0.22, cmi: 0.30, status: 'pending',
    mistakes: [
      'Confused unlimited liability for general vs. limited partners (Q3).',
      'Picked sole proprietorship as the default for raising capital (Q5).',
    ],
    suggestions: [
      'Re-read the comparison table in Lesson 2.3.',
      'Try the matching drill on partnership types.',
    ],
    summary: 'Conceptual gaps around partnership liability — short targeted review should close it.',
    generatedAt: '2026-05-01 09:30',
  },
  {
    id: 'f7', studentId: 'u1', studentName: 'Maria Santos', module: 'Business Mathematics',
    quiz: 'Quiz 4.1 — Markups & Discounts', score: 5, total: 5,
    gi: 0.05, cmi: 0.10, status: 'approved',
    mistakes: [],
    suggestions: ['Try the LP4 stretch problem set on chained discounts.'],
    summary: 'Excellent calibration and accuracy. Ready to advance to LP4 stretch material.',
    generatedAt: '2026-04-29 15:12',
  },
  {
    id: 'f8', studentId: 'u2', studentName: 'Juan Dela Cruz', module: 'Fundamentals of Accountancy I',
    quiz: 'Quiz 3.2 — Journal Entries', score: 4, total: 5,
    gi: 0.12, cmi: 0.20, status: 'pending',
    mistakes: ['Reversed credit/debit on a withdrawal entry (Q4).'],
    suggestions: ['Quick refresher on Drawings vs. Owner’s Equity.'],
    summary: 'Solid performance overall — minor confusion on owner withdrawal direction.',
    generatedAt: '2026-04-29 10:21',
  },
];

export const COMPETENCIES = [
  { id: 'c1', name: 'Accounting Equation',         mastery: 92, level: 'mastered' },
  { id: 'c2', name: 'Debits & Credits',            mastery: 81, level: 'mastered' },
  { id: 'c3', name: 'Journal Entries',             mastery: 64, level: 'developing' },
  { id: 'c4', name: 'Posting & Ledgers',           mastery: 48, level: 'developing' },
  { id: 'c5', name: 'Trial Balance',               mastery: 22, level: 'needs' },
  { id: 'c6', name: 'Markups & Discounts',         mastery: 76, level: 'mastered' },
  { id: 'c7', name: 'Simple & Compound Interest',  mastery: 41, level: 'developing' },
  { id: 'c8', name: 'Statistics for Business',     mastery: 18, level: 'needs' },
];

export const STUDENTS_PROGRESS = [
  { id: 'u1', name: 'Maria Santos',     section: 'ABM 11-A', mastery: 78, level: 'mastered',   path: 'LP3', lastActive: '2h ago',  alerts: 0 },
  { id: 'u2', name: 'Juan Dela Cruz',   section: 'ABM 11-A', mastery: 84, level: 'mastered',   path: 'LP4', lastActive: '1h ago',  alerts: 0 },
  { id: 'u3', name: 'Ella Reyes',       section: 'ABM 11-B', mastery: 41, level: 'developing', path: 'LP2', lastActive: '5h ago',  alerts: 2 },
  { id: 'u4', name: 'Marco Villanueva', section: 'ABM 11-B', mastery: 23, level: 'needs',      path: 'LP1', lastActive: '2d ago',  alerts: 3 },
  { id: 'u5', name: 'Patricia Lim',     section: 'ABM 11-A', mastery: 67, level: 'developing', path: 'LP3', lastActive: '30m ago', alerts: 1 },
  { id: 'u6', name: 'Daniel Cruz',      section: 'ABM 11-C', mastery: 55, level: 'developing', path: 'LP2', lastActive: '1d ago',  alerts: 1 },
];

export const LOGS = [
  { id: 'L0001', ts: '2026-05-05 08:14', actor: 'a.torres@acculearn.ph',  action: 'Approved AI feedback', target: 'Maria Santos / Quiz 3.2' },
  { id: 'L0002', ts: '2026-05-05 08:02', actor: 'maria.s@acculearn.ph',   action: 'Submitted quiz',       target: 'Quiz 3.2 — Journal Entries' },
  { id: 'L0003', ts: '2026-05-05 07:55', actor: 'juan.dc@acculearn.ph',   action: 'Completed lesson',     target: 'Markups & Discounts' },
  { id: 'L0004', ts: '2026-05-04 22:31', actor: 'admin@acculearn.ph',     action: 'Created module',       target: 'Applied Economics' },
  { id: 'L0005', ts: '2026-05-04 19:08', actor: 'ella.r@acculearn.ph',    action: 'Logged in',            target: '—' },
  { id: 'L0006', ts: '2026-05-04 17:22', actor: 'r.yu@acculearn.ph',      action: 'Edited feedback',      target: 'Juan / Quiz 4.1' },
  { id: 'L0007', ts: '2026-05-04 14:21', actor: 'system',                 action: 'Generated AI feedback',target: 'Maria / Quiz 3.2' },
  { id: 'L0008', ts: '2026-05-04 11:09', actor: 'admin@acculearn.ph',     action: 'Added user',           target: 'patricia.l@acculearn.ph' },
  { id: 'L0009', ts: '2026-05-04 09:44', actor: 'marco.v@acculearn.ph',   action: 'Failed quiz',          target: 'Quiz 3.1' },
];

export const SYSTEM_STATS = {
  totalUsers: 248, totalStudents: 226, totalTeachers: 18, totalAdmins: 4,
  totalModules: 6, activeSessions: 41, avgMastery: 64,
};

// Formative quizzes — short check-for-understanding at the end of each lesson.
// Students must pass (>=60%) every formative quiz to unlock the main module quiz.
export const FORMATIVES = {
  l1: { id: 'fq-l1', title: 'Lesson 1 check — The Accounting Equation', passing: 0.6,
    questions: [
      { id: 'q1', prompt: 'The accounting equation is:', options: ['Assets = Liabilities − Equity', 'Assets = Liabilities + Equity', 'Equity = Assets + Liabilities', 'Liabilities = Assets − Cash'], answer: 1 },
      { id: 'q2', prompt: 'If Assets = ₱150,000 and Liabilities = ₱60,000, then Equity is:', options: ['₱210,000', '₱60,000', '₱90,000', '₱150,000'], answer: 2 },
    ]},
  l2: { id: 'fq-l2', title: 'Lesson 2 check — Assets, Liabilities, Equity', passing: 0.6,
    questions: [
      { id: 'q1', prompt: 'Which is a liability?', options: ['Cash', 'Accounts Payable', 'Equipment', 'Inventory'], answer: 1 },
      { id: 'q2', prompt: 'Which increases owner’s equity?', options: ['Withdrawals', 'Expenses', 'Investments by owner', 'Liabilities'], answer: 2 },
    ]},
  l3: { id: 'fq-l3', title: 'Lesson 3 check — Debits & Credits', passing: 0.6,
    questions: [
      { id: 'q1', prompt: 'Increases in liabilities are recorded as:', options: ['Debits', 'Credits', 'Either', 'Memo'], answer: 1 },
      { id: 'q2', prompt: 'The normal balance of an asset account is on the:', options: ['Debit side', 'Credit side', 'Either side', 'Neither — assets have no balance'], answer: 0 },
    ]},
  l4: { id: 'fq-l4', title: 'Lesson 4 check — Recording Transactions', passing: 0.6,
    questions: [
      { id: 'q1', prompt: 'Each transaction affects at least how many accounts?', options: ['One', 'Two', 'Three', 'It depends'], answer: 1 },
      { id: 'q2', prompt: 'Recording a transaction in the general journal is called:', options: ['Posting', 'Journalizing', 'Balancing', 'Closing'], answer: 1 },
    ]},
  l5: { id: 'fq-l5', title: 'Lesson 5 check — The Journal Entry', passing: 0.6,
    questions: [
      { id: 'q1', prompt: 'In a journal entry, total debits must equal:', options: ['Total assets', 'Total credits', 'Total revenues', 'Total expenses'], answer: 1 },
      { id: 'q2', prompt: 'A purchase of supplies for cash is recorded as:', options: ['Dr. Cash / Cr. Supplies', 'Dr. Supplies / Cr. Cash', 'Dr. Supplies / Cr. Accounts Payable', 'Dr. Accounts Payable / Cr. Cash'], answer: 1 },
    ]},
  l6: { id: 'fq-l6', title: 'Lesson 6 check — Posting to the Ledger', passing: 0.6,
    questions: [
      { id: 'q1', prompt: 'Posting moves entries from:', options: ['Ledger to journal', 'Journal to ledger', 'Trial balance to journal', 'Worksheet to ledger'], answer: 1 },
      { id: 'q2', prompt: 'A T-account shows:', options: ['Only debits', 'Only credits', 'Both debits and credits', 'Trial balance only'], answer: 2 },
    ]},
  l7: { id: 'fq-l7', title: 'Lesson 7 check — Trial Balance', passing: 0.6,
    questions: [
      { id: 'q1', prompt: 'A trial balance lists:', options: ['Only revenues', 'All ledger account balances', 'Only assets', 'Only liabilities'], answer: 1 },
      { id: 'q2', prompt: 'When debits = credits in the trial balance, it confirms:', options: ['No errors at all', 'Mathematical equality of postings', 'Profitability', 'Cash balance'], answer: 1 },
    ]},
  l8: { id: 'fq-l8', title: 'Lesson 8 check — Worksheet & Adjustments', passing: 0.6,
    questions: [
      { id: 'q1', prompt: 'Adjusting entries are typically made:', options: ['Daily', 'Before preparing financial statements', 'Only at year-end', 'Never'], answer: 1 },
      { id: 'q2', prompt: 'An accrued expense is one that:', options: ['Has been paid but not incurred', 'Has been incurred but not yet paid', 'Will never be paid', 'Was paid in advance'], answer: 1 },
    ]},
};

// Mock service surface used by pages
export const api = {
  login:        ({ email }) => mockDelay({ ok: true, user: USERS.find(u => u.email === email) || USERS[0] }, 500),
  getModules:   () => mockDelay(MODULES),
  getModule:    (id) => mockDelay(MODULES.find(m => m.id === id)),
  getLessons:   (moduleId) => mockDelay(LESSONS[moduleId] || []),
  getQuiz:      (moduleId) => mockDelay(QUIZZES[moduleId]),
  submitQuiz:   (quizId, answers) => {
    const q = Object.values(QUIZZES).find(x => x.id === quizId);
    let correct = 0;
    q.questions.forEach((qq, i) => { if (answers[i] === qq.answer) correct++; });
    return mockDelay({ score: correct, total: q.questions.length }, 600);
  },
  getFeedback:  () => mockDelay(FEEDBACK),
  getCompetencies: () => mockDelay(COMPETENCIES),
  getStudentsProgress: () => mockDelay(STUDENTS_PROGRESS),
  getUsers:     () => mockDelay(USERS),
  getLogs:      () => mockDelay(LOGS),
  getStats:     () => mockDelay(SYSTEM_STATS),
};
