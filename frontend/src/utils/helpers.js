import { MASTERY_LEVELS, MASTERY_THRESHOLDS } from './constants.js';

export function classifyMastery(score) {
  if (score >= MASTERY_THRESHOLDS.MASTERED_MIN) return MASTERY_LEVELS.MASTERED;
  if (score >= MASTERY_THRESHOLDS.DEVELOPING_MIN) return MASTERY_LEVELS.DEVELOPING;
  return MASTERY_LEVELS.NEEDS_IMPROVEMENT;
}

export function formatPercent(value, digits = 0) {
  if (value == null || Number.isNaN(value)) return '—';
  return `${Number(value).toFixed(digits)}%`;
}

export function clampScore(value, min = 0, max = 100) {
  return Math.max(min, Math.min(max, Number(value) || 0));
}
