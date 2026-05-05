import { LEARNING_PATHS } from '../../utils/constants.js';

const LABELS = {
  [LEARNING_PATHS.PREREQUISITE_REVIEW]: 'LP1 · Prerequisite Review',
  [LEARNING_PATHS.GUIDED_REMEDIATION]: 'LP2 · Guided Remediation',
  [LEARNING_PATHS.REINFORCEMENT]: 'LP3 · Reinforcement',
  [LEARNING_PATHS.ADVANCEMENT]: 'LP4 · Advancement',
};

const COLORS = {
  [LEARNING_PATHS.PREREQUISITE_REVIEW]: 'bg-amber-100 text-amber-800',
  [LEARNING_PATHS.GUIDED_REMEDIATION]: 'bg-orange-100 text-orange-800',
  [LEARNING_PATHS.REINFORCEMENT]: 'bg-sky-100 text-sky-800',
  [LEARNING_PATHS.ADVANCEMENT]: 'bg-emerald-100 text-emerald-800',
};

export default function LearningTrackBadge({ lp }) {
  return (
    <span
      className={`inline-block rounded-full px-2.5 py-0.5 text-xs font-medium ${COLORS[lp] || 'bg-slate-100 text-slate-700'}`}
    >
      {LABELS[lp] || `LP${lp}`}
    </span>
  );
}
