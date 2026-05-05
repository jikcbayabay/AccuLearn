// MOCK: Replace with real axios calls when backend is running.
import { LEARNING_PATHS, MASTERY_LEVELS } from '../utils/constants.js';

const studentService = {
  getDashboard: () =>
    Promise.resolve({
      studentName: 'Maria Santos',
      activeLp: LEARNING_PATHS.REINFORCEMENT,
      recommendedLessons: [
        {
          id: 'lesson-1',
          title: 'Introduction to Financial Statements',
          type: 'video',
          description: 'Overview of the four core financial statements.',
        },
        {
          id: 'lesson-2',
          title: 'Journal Entries Basics',
          type: 'text',
          description: 'How to record transactions using debits and credits.',
        },
      ],
    }),

  getModules: () =>
    Promise.resolve([
      {
        id: 1,
        title: 'Fundamentals of Accountancy, Business and Management 1',
        description: 'Introduces ABM core concepts including the accounting cycle.',
      },
      {
        id: 2,
        title: 'Business Math',
        description: 'Covers ratio, proportion, percentages, and interest.',
      },
    ]),

  getLesson: (lessonId) =>
    Promise.resolve({
      id: lessonId,
      title: 'Introduction to Financial Statements',
      type: 'text',
      content:
        'A financial statement is a formal record of the financial activities and position of a business. The four primary statements are the balance sheet, income statement, cash flow statement, and statement of changes in equity.',
    }),

  getQuiz: (quizId) =>
    Promise.resolve({
      id: quizId,
      title: 'Quiz: Financial Statements Basics',
      passingScore: 75,
      questions: [
        {
          id: 'q1',
          text: 'Which statement reports a company\'s revenues and expenses?',
          choices: [
            { id: 'a', label: 'Balance Sheet' },
            { id: 'b', label: 'Income Statement' },
            { id: 'c', label: 'Cash Flow Statement' },
            { id: 'd', label: 'Statement of Equity' },
          ],
        },
        {
          id: 'q2',
          text: 'Assets equal liabilities plus what?',
          choices: [
            { id: 'a', label: 'Revenue' },
            { id: 'b', label: 'Equity' },
            { id: 'c', label: 'Expenses' },
            { id: 'd', label: 'Income' },
          ],
        },
      ],
    }),

  submitQuiz: (_quizId, _answers) =>
    Promise.resolve({
      attemptId: 'attempt-101',
      competencyId: 'comp-1',
      score: 80,
      passed: true,
    }),

  getFeedback: (competencyId) =>
    Promise.resolve({
      competencyId,
      competencyTitle: 'Identify the Components of a Balance Sheet',
      text: "Great work! You correctly identified the major components, but watch out for confusing 'liabilities' with 'equity' — review pages 22–25 of your handout for a quick refresher.",
      errorPattern: 'Mislabeling equity as a liability',
      lpAssigned: LEARNING_PATHS.REINFORCEMENT,
      giScore: 0.12,
      cmiScore: 0.05,
    }),

  getProgress: () =>
    Promise.resolve({
      overallMastery: 78,
      competencies: [
        {
          id: 'comp-1',
          title: 'Identify Components of a Balance Sheet',
          masteryScore: 88,
          level: MASTERY_LEVELS.MASTERED,
        },
        {
          id: 'comp-2',
          title: 'Prepare Journal Entries',
          masteryScore: 72,
          level: MASTERY_LEVELS.NEEDS_IMPROVEMENT,
        },
        {
          id: 'comp-3',
          title: 'Compute Net Income',
          masteryScore: 81,
          level: MASTERY_LEVELS.DEVELOPING,
        },
      ],
    }),
};

export default studentService;
