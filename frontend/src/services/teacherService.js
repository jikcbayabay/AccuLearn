// MOCK: Replace with real axios calls when backend is running.
import { LEARNING_PATHS, MASTERY_LEVELS } from '../utils/constants.js';

const teacherService = {
  getOverview: () =>
    Promise.resolve({
      totalStudents: 42,
      averageMastery: 76,
      pendingFeedback: 8,
    }),

  getStudents: () =>
    Promise.resolve([
      {
        id: 1,
        name: 'Maria Santos',
        email: 'maria.santos@example.com',
        averageMastery: 88,
        activeLp: LEARNING_PATHS.ADVANCEMENT,
      },
      {
        id: 2,
        name: 'Juan Dela Cruz',
        email: 'juan.delacruz@example.com',
        averageMastery: 72,
        activeLp: LEARNING_PATHS.GUIDED_REMEDIATION,
      },
      {
        id: 3,
        name: 'Ana Reyes',
        email: 'ana.reyes@example.com',
        averageMastery: 80,
        activeLp: LEARNING_PATHS.REINFORCEMENT,
      },
    ]),

  getMasteryByCompetency: () =>
    Promise.resolve([
      {
        competencyId: 'comp-1',
        title: 'Identify Components of a Balance Sheet',
        averageScore: 84,
        level: MASTERY_LEVELS.DEVELOPING,
        studentsMastered: 22,
        studentsTotal: 42,
      },
    ]),

  getFeedbackQueue: () =>
    Promise.resolve([
      {
        id: 'fb-1',
        studentName: 'Juan Dela Cruz',
        competencyTitle: 'Prepare Journal Entries',
        feedbackText:
          'Juan struggles with debit/credit direction. Suggested LP2 with targeted practice.',
        giScore: 0.15,
        cmiScore: 0.4,
        lpAssigned: LEARNING_PATHS.GUIDED_REMEDIATION,
        createdAt: '2026-05-04 09:31',
      },
    ]),
};

export default teacherService;
