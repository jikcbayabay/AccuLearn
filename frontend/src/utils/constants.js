export const USER_ROLES = Object.freeze({
  STUDENT: 'student',
  TEACHER: 'teacher',
  ADMIN: 'admin',
});

export const MASTERY_LEVELS = Object.freeze({
  MASTERED: 'mastered',
  DEVELOPING: 'developing',
  NEEDS_IMPROVEMENT: 'needs_improvement',
});

export const MASTERY_THRESHOLDS = Object.freeze({
  MASTERED_MIN: 85,
  DEVELOPING_MIN: 75,
});

export const LEARNING_PATHS = Object.freeze({
  PREREQUISITE_REVIEW: 1,
  GUIDED_REMEDIATION: 2,
  REINFORCEMENT: 3,
  ADVANCEMENT: 4,
});
