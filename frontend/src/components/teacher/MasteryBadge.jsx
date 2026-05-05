import { MASTERY_LEVELS } from '../../utils/constants.js';

const STYLES = {
  [MASTERY_LEVELS.MASTERED]: 'bg-emerald-100 text-emerald-800',
  [MASTERY_LEVELS.DEVELOPING]: 'bg-amber-100 text-amber-800',
  [MASTERY_LEVELS.NEEDS_IMPROVEMENT]: 'bg-red-100 text-red-800',
};

const LABELS = {
  [MASTERY_LEVELS.MASTERED]: 'Mastered',
  [MASTERY_LEVELS.DEVELOPING]: 'Developing',
  [MASTERY_LEVELS.NEEDS_IMPROVEMENT]: 'Needs Improvement',
};

export default function MasteryBadge({ level }) {
  return (
    <span
      className={`inline-block rounded-full px-2.5 py-0.5 text-xs font-medium ${STYLES[level] || 'bg-slate-100 text-slate-700'}`}
    >
      {LABELS[level] || level}
    </span>
  );
}
