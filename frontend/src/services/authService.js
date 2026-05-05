// MOCK: Replace with real axios calls when backend is running.
import { USER_ROLES } from '../utils/constants.js';

const FAKE_USER = {
  id: 1,
  name: 'Maria Santos',
  email: 'maria.santos@example.com',
  role: USER_ROLES.STUDENT,
  moodleUserId: null,
};

const authService = {
  login: (email, _password) => {
    const role =
      email?.startsWith('admin')
        ? USER_ROLES.ADMIN
        : email?.startsWith('teacher')
          ? USER_ROLES.TEACHER
          : USER_ROLES.STUDENT;

    return Promise.resolve({
      user: { ...FAKE_USER, email: email || FAKE_USER.email, role },
      token: 'mock-token-abc123',
    });
  },

  logout: () => Promise.resolve({ success: true }),

  getCurrentUser: () => Promise.resolve(null),
};

export default authService;
