import React from 'react';
import Icon from '../../components/common/Icons.jsx';
import {
  Badge, Button, Card, EmptyState, Loading,
} from '../../components/common/UI.jsx';
import { PageHeader } from '../../components/layout/Shell.jsx';
import apiClient from '../../services/api.js';

export const FeedbackCard = ({ f, role = 'student', onApprove, onEdit }) => (
  <Card className="p-6">
    <div className="flex items-start gap-4">
      <div className="w-10 h-10 rounded-xl bg-brand-blue-50 text-brand-blue flex items-center justify-center shrink-0">
        <Icon.Sparkle/>
      </div>
      <div className="flex-1 min-w-0">
        <div className="flex flex-wrap items-center gap-2 mb-1.5">
          <div className="font-semibold text-ink-900">{f.quiz}</div>
          <Badge tone="neutral">{f.module}</Badge>
          {role === 'teacher' && <Badge tone="blue">{f.studentName}</Badge>}
          {f.status === 'approved'
            ? <Badge tone="completed">Approved</Badge>
            : <Badge tone="developing">Pending review</Badge>}
        </div>
        <p className="text-[14px] text-ink-700 leading-relaxed">{f.summary}</p>

        <div className="grid sm:grid-cols-3 gap-3 mt-4">
          <div className="rounded-xl bg-ink-50 p-3">
            <div className="text-[11px] uppercase tracking-wider text-ink-500 font-semibold">Score</div>
            <div className="text-[18px] font-semibold tnum">{f.score}/{f.total}</div>
          </div>
          <div className="rounded-xl bg-ink-50 p-3">
            <div className="text-[11px] uppercase tracking-wider text-ink-500 font-semibold flex items-center gap-1">
              Guessing Index <span className="text-ink-300">·</span> <span className="text-ink-700 font-mono">GI</span>
            </div>
            <div className="text-[18px] font-semibold tnum">{(+f.gi).toFixed(2)}</div>
            <div className="text-[11px] text-ink-500 mt-0.5">
              {f.gi < 0.2 ? 'Low — confident answers' : f.gi < 0.35 ? 'Moderate' : 'High — many guesses'}
            </div>
          </div>
          <div className="rounded-xl bg-ink-50 p-3">
            <div className="text-[11px] uppercase tracking-wider text-ink-500 font-semibold flex items-center gap-1">
              Confidence Misc. <span className="text-ink-300">·</span> <span className="text-ink-700 font-mono">CMI</span>
            </div>
            <div className="text-[18px] font-semibold tnum">{(+f.cmi).toFixed(2)}</div>
            <div className="text-[11px] text-ink-500 mt-0.5">
              {f.cmi < 0.25 ? 'Well-calibrated' : f.cmi < 0.45 ? 'Slightly overconfident' : 'Overconfident'}
            </div>
          </div>
        </div>

        {((f.mistakes?.length ?? 0) > 0 || (f.suggestions?.length ?? 0) > 0) && (
          <div className="grid md:grid-cols-2 gap-4 mt-5">
            {f.mistakes?.length > 0 && (
              <div>
                <div className="flex items-center gap-2 mb-2">
                  <span className="w-1.5 h-1.5 rounded-full bg-mastery-needs"/>
                  <div className="text-[12.5px] font-semibold uppercase tracking-wider text-ink-700">Mistakes</div>
                </div>
                <ul className="space-y-1.5">
                  {f.mistakes.map((m, i) => (
                    <li key={i} className="text-[13.5px] text-ink-700 leading-relaxed flex gap-2">
                      <span className="text-mastery-needs mt-1">•</span>{m}
                    </li>
                  ))}
                </ul>
              </div>
            )}
            {f.suggestions?.length > 0 && (
              <div>
                <div className="flex items-center gap-2 mb-2">
                  <span className="w-1.5 h-1.5 rounded-full bg-brand-green"/>
                  <div className="text-[12.5px] font-semibold uppercase tracking-wider text-ink-700">Suggestions</div>
                </div>
                <ul className="space-y-1.5">
                  {f.suggestions.map((m, i) => (
                    <li key={i} className="text-[13.5px] text-ink-700 leading-relaxed flex gap-2">
                      <span className="text-brand-green mt-1">•</span>{m}
                    </li>
                  ))}
                </ul>
              </div>
            )}
          </div>
        )}

        {role === 'teacher' && f.status === 'pending' && (
          <div className="flex gap-2 mt-5">
            <Button variant="success" size="sm" icon={<Icon.Check size={14}/>} onClick={() => onApprove(f.id)}>
              Approve
            </Button>
            <Button variant="secondary" size="sm" icon={<Icon.Edit size={14}/>} onClick={() => onEdit(f)}>
              Edit
            </Button>
          </div>
        )}
        <div className="text-[11.5px] text-ink-500 mt-4">Generated {f.generatedAt}</div>
      </div>
    </div>
  </Card>
);

const FeedbackView = () => {
  const [items, setItems] = React.useState(null);

  React.useEffect(() => {
    apiClient.get('/student/feedback')
      .then(res => setItems(res.data.feedback ?? []))
      .catch(() => setItems([]));
  }, []);

  if (!items) return <Loading/>;

  return (
    <div>
      <PageHeader title="Feedback" subtitle="AI-generated feedback on your quiz attempts."/>
      {items.length === 0 ? (
        <Card>
          <EmptyState title="No feedback yet" sub="Take a quiz to receive AI-generated feedback on your performance."/>
        </Card>
      ) : (
        <div className="space-y-5">
          {items.map(f => <FeedbackCard key={f.id} f={f}/>)}
        </div>
      )}
    </div>
  );
};

export default FeedbackView;
