// MOCK: Replace with real axios calls when backend is running.
import { USER_ROLES } from '../utils/constants.js';

const adminService = {
  getOverview: () =>
    Promise.resolve({
      totalUsers: 120,
      totalModules: 6,
      eventsLast24h: 432,
    }),

  getUsers: () =>
    Promise.resolve([
      {
        id: 1,
        name: 'Maria Santos',
        email: 'maria.santos@example.com',
        role: USER_ROLES.STUDENT,
      },
      {
        id: 2,
        name: 'Mr. Garcia',
        email: 'teacher.garcia@example.com',
        role: USER_ROLES.TEACHER,
      },
      {
        id: 3,
        name: 'Site Admin',
        email: 'admin@example.com',
        role: USER_ROLES.ADMIN,
      },
    ]),

  getModules: () =>
    Promise.resolve([
      {
        id: 1,
        title: 'FABM 1',
        description: 'Fundamentals of Accountancy, Business and Management 1.',
        moodleCourseId: null,
        order: 1,
      },
      {
        id: 2,
        title: 'Business Math',
        description: 'Mathematics applied in business contexts.',
        moodleCourseId: null,
        order: 2,
      },
    ]),

  createModule: (form) =>
    Promise.resolve({ id: Date.now(), ...form }),

  getSystemLogs: () =>
    Promise.resolve([
      {
        id: 1,
        userName: 'Maria Santos',
        action: 'quiz.submitted',
        context: 'Quiz #12 — score 80%',
        loggedAt: '2026-05-05 08:22',
      },
      {
        id: 2,
        userName: 'Mr. Garcia',
        action: 'feedback.reviewed',
        context: 'Approved AI feedback for Juan D.',
        loggedAt: '2026-05-05 08:11',
      },
    ]),
};

export default adminService;
