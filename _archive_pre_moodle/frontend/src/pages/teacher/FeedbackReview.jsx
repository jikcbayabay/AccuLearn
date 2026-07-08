import React from 'react';
import Icon from '../../components/common/Icons.jsx';
import {
  Badge, Button, Card, EmptyState, Loading, Modal, Textarea, cls,
} from '../../components/common/UI.jsx';
import { PageHeader } from '../../components/layout/Shell.jsx';
import apiClient from '../../services/api.js';

const FeedbackThreadRow = ({ f, expanded, onToggle, onApprove, onEdit }) => {
  const pct   = Math.round((f.score / f.total) * 100);
  const tone  = pct >= 80 ? 'mastered' : pct >= 50 ? 'developing' : 'needs';
  const toneColor = tone === 'mastered' ? 'text-brand-green-700' : tone === 'developing' ? 'text-[#a4751f]' : 'text-mastery-needs';
  const toneBg    = tone === 'mastered' ? 'bg-brand-green-50' : tone === 'developing' ? 'bg-amber-50' : 'bg-rose-50';
  const initials  = f.studentName.split(' ').map(x => x[0]).slice(0, 2).join('');

  return (
    <div className={cls('transition', expanded && 'bg-ink-50/40')}>
      <button onClick={onToggle}
              className="w-full text-left flex items-stretch gap-0 hover:bg-ink-50 transition">
        <div className={cls('shrink-0 w-16 flex flex-col items-center justify-center py-3 border-r border-ink-200', toneBg)}>
          <div className={cls('text-[18px] font-semibold tnum leading-none', toneColor)}>
            {f.score}<span className="text-[12px] text-ink-500">/{f.total}</span>
          </div>
          <div className={cls('text-[10.5px] uppercase tracking-wider font-semibold mt-1', toneColor)}>{pct}%</div>
        </div>
        <div className="flex-1 min-w-0 py-3 px-4 flex items-start gap-3">
          <div className="w-8 h-8 rounded-full bg-brand-blue text-white flex items-center justify-center text-[11px] font-semibold shrink-0 mt-0.5">
            {initials}
          </div>
          <div className="min-w-0 flex-1">
            <div className="flex flex-wrap items-center gap-x-2 gap-y-1 text-[12.5px] text-ink-500">
              <span className="font-semibold text-ink-900">{f.studentName}</span>
              <span>·</span><span>{f.module}</span>
              <span>·</span><span className="text-ink-300 tnum">{f.generatedAt}</span>
            </div>
            <div className="flex items-center gap-2 mt-1">
              <span className="font-semibold text-ink-900 text-[14.5px] truncate">{f.quiz}</span>
              {f.status === 'approved'
                ? <Badge tone="completed">Approved</Badge>
                : <Badge tone="developing">Pending</Badge>}
              {(f.gi >= 0.30 || f.cmi >= 0.45) && (
                <Badge tone="needs"><Icon.AlertTri size={10}/> Risk flag</Badge>
              )}
            </div>
            {!expanded && (
              <p className="text-[13px] text-ink-500 leading-relaxed mt-1.5 line-clamp-1">{f.summary}</p>
            )}
            <div className="flex items-center gap-3 mt-2 text-[12px] text-ink-500">
              <span className="flex items-center gap-1">
                <Icon.Chevron size={12} className={cls('transition', expanded && 'rotate-180')}/>
                {expanded ? 'Hide' : 'Show'} details
              </span>
              <span>·</span>
              <span>{f.mistakes?.length ?? 0} mistake{(f.mistakes?.length ?? 0) === 1 ? '' : 's'}</span>
            </div>
          </div>
        </div>
      </button>

      {expanded && (
        <div className="pl-16 pr-4 pb-5 -mt-1">
          <div className="ml-4 pl-4 border-l-2 border-ink-200">
            <p className="text-[14px] text-ink-700 leading-relaxed">{f.summary}</p>
            <div className="flex items-center gap-1 mt-5 -ml-2">
              {f.status === 'pending' && (
                <button onClick={onApprove}
                  className="flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-[12.5px] font-semibold text-brand-green-700 hover:bg-brand-green-50 transition">
                  <Icon.Check size={14}/> Approve
                </button>
              )}
              <button onClick={onEdit}
                className="flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-[12.5px] font-semibold text-ink-700 hover:bg-ink-100 transition">
                <Icon.Edit size={14}/> Edit
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

const FeedbackReviewPage = () => {
  const [items, setItems]   = React.useState(null);
  const [editing, setEditing] = React.useState(null);
  const [tab, setTab]       = React.useState('pending');
  const [q, setQ]           = React.useState('');
  const [expanded, setExpanded] = React.useState({});

  React.useEffect(() => {
    apiClient.get('/teacher/feedback')
      .then(res => {
        const data = Array.isArray(res.data)
          ? res.data
          : (res.data?.feedback ?? []);
        setItems(data);
      })
      .catch(() => setItems([]));
  }, []);

  if (!items) return <Loading/>;

  const counts = {
    pending:  items.filter(f => f.status === 'pending').length,
    approved: items.filter(f => f.status === 'approved').length,
    all:      items.length,
  };

  const filtered = items
    .filter(f => tab === 'all' ? true : f.status === tab)
    .filter(f => q
      ? ((f.studentName ?? '') + (f.module ?? '') + (f.quiz ?? '')).toLowerCase().includes(q.toLowerCase())
      : true
    );

  const approve = (id) => {
    apiClient.post(`/teacher/feedback/${id}/approve`)
      .catch(() => {});
    setItems(xs => xs.map(x => x.id === id ? { ...x, status: 'approved' } : x));
  };
  const toggle  = (id) => setExpanded(e => ({ ...e, [id]: !e[id] }));

  return (
    <div>
      <PageHeader title="Feedback Review"
                  subtitle="Review and approve AI-generated feedback before students see it."/>

      <div className="flex flex-wrap items-center gap-2 mb-4">
        <div className="flex items-center gap-1 bg-white rounded-xl border border-ink-200 p-1">
          {[
            { k: 'pending',  label: 'Pending' },
            { k: 'approved', label: 'Approved' },
            { k: 'all',      label: 'All' },
          ].map(t => (
            <button key={t.k} onClick={() => setTab(t.k)}
              className={cls('px-3.5 py-1.5 rounded-lg text-[13px] font-medium transition',
                tab === t.k ? 'bg-brand-blue text-white' : 'text-ink-700 hover:bg-ink-50')}>
              {t.label}
              <span className={cls('ml-1.5 tnum', tab === t.k ? 'text-white/70' : 'text-ink-500')}>
                {counts[t.k]}
              </span>
            </button>
          ))}
        </div>
        <div className="relative flex-1 min-w-[200px] max-w-xs">
          <span className="absolute left-3 top-1/2 -translate-y-1/2 text-ink-500"><Icon.Search size={15}/></span>
          <input value={q} onChange={e => setQ(e.target.value)}
            placeholder="Search…"
            className="w-full bg-ink-50 border border-ink-200 rounded-xl pl-9 pr-3 py-2 text-sm focus:bg-white focus:border-brand-blue outline-none"/>
        </div>
      </div>

      {filtered.length === 0 ? (
        <Card>
          <EmptyState
            title="No feedback yet"
            sub="AI feedback will appear here after students complete quizzes."/>
        </Card>
      ) : (
        <div className="bg-white border border-ink-200 rounded-2xl overflow-hidden divide-y divide-ink-200">
          {filtered.map(f => (
            <FeedbackThreadRow key={f.id} f={f}
                               expanded={!!expanded[f.id]}
                               onToggle={() => toggle(f.id)}
                               onApprove={() => approve(f.id)}
                               onEdit={() => setEditing(f)}/>
          ))}
        </div>
      )}

      <Modal open={!!editing} onClose={() => setEditing(null)} title="Edit AI feedback" size="lg"
             footer={<>
               <Button variant="secondary" onClick={() => setEditing(null)}>Cancel</Button>
               <Button variant="primary" onClick={() => setEditing(null)}>Save changes</Button>
             </>}>
        {editing && (
          <div className="space-y-4">
            <Textarea label="Summary" rows={3} defaultValue={editing.summary}/>
            <Textarea label="Mistakes (one per line)" rows={3}
                      defaultValue={(editing.mistakes ?? []).join('\n')}/>
            <Textarea label="Suggestions (one per line)" rows={3}
                      defaultValue={(editing.suggestions ?? []).join('\n')}/>
          </div>
        )}
      </Modal>
    </div>
  );
};

export default FeedbackReviewPage;
