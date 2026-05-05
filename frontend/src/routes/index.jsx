export const ROUTES = {
  LOGIN: '/login',

  STUDENT_DASHBOARD: '/student',
  STUDENT_MODULES: '/student/modules',
  STUDENT_LESSON: (id) => `/student/lessons/${id}`,
  STUDENT_QUIZ: (id) => `/student/quizzes/${id}`,
  STUDENT_FEEDBACK: (competencyId) => `/student/feedback/${competencyId}`,
  STUDENT_PROGRESS: '/student/progress',

  TEACHER_DASHBOARD: '/teacher',
  TEACHER_STUDENTS: '/teacher/students',
  TEACHER_FEEDBACK: '/teacher/feedback',

  ADMIN_DASHBOARD: '/admin',
  ADMIN_USERS: '/admin/users',
  ADMIN_MODULES: '/admin/modules',
  ADMIN_LOGS: '/admin/logs',
};
