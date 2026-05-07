import React from 'react';
import Icon from '../../components/common/Icons.jsx';
import {
  Badge, Button, Card, EmptyState, Loading, Modal, Textarea, cls,
} from '../../components/common/UI.jsx';
import { PageHeader } from '../../components/layout/Shell.jsx';
import { api, USERS } from '../../mockData.js';

// Reddit-style row: collapsed by default, expands inline. Vote-rail on the left
// is repurposed as a status/score gauge.
const FeedbackThreadRow = ({ f, expanded, onToggle, onApprove, onEdit }) => {
  const pct = Math.round((f.score / f.total) * 100);
  const tone = pct >= 80 ? 'mastered' : pct >= 50 ? 'developing' : 'needs';
  const toneColor = tone === 'mastered' ? 'text-brand-green-700' : tone === 'developing' ? 'text-[#a4751f]' : 'text-mastery-needs';
  const toneBg = tone === 'mastered' ? 'bg-brand-green-50' : tone === 'developing' ? 'bg-amber-50' : 'bg-rose-50';
  const initials = f.studentName.split(' ').map(x => x[0]).slice(0,2).join('');

  return (
    <div className={cls('transition', expanded && 'bg-ink-50/40')}>
      {/* Header row — always visible, click to expand */}
      <button onClick={onToggle}
              className="w-full text-left flex items-stretch gap-0 hover:bg-ink-50 transition">
        {/* Score rail */}
        <div className={cls('shrink-0 w-16 flex flex-col items-center justify-center py-3 border-r border-ink-200', toneBg)}>
          <div className={cls('text-[18px] font-semibold tnum leading-none', toneColor)}>{f.score}<span className="text-[12px] text-ink-500">/{f.total}</span></div>
          <div className={cls('text-[10.5px] uppercase tracking-wider font-semibold mt-1', toneColor)}>{pct}%</div>
        </div>

        {/* Main */}
        <div className="flex-1 min-w-0 py-3 px-4 flex items-start gap-3">
          <div className="w-8 h-8 rounded-full bg-brand-blue text-white flex items-center justify-center text-[11px] font-semibold shrink-0 mt-0.5">
            {initials}
          </div>
          <div className="min-w-0 flex-1">
            <div className="flex flex-wrap items-center gap-x-2 gap-y-1 text-[12.5px] text-ink-500">
              <span className="font-semibold text-ink-900">{f.studentName}</span>
              <span>·</span>
              <span>{f.section}</span>
              <span>·</span>
              <span>{f.module}</span>
              <span>·</span>
              <span className="text-ink-300 tnum">{f.generatedAt}</span>
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
              <p className="text-[13px] text-ink-500 leading-relaxed mt-1.5 line-clamp-1">
                {f.summary}
              </p>
            )}
            <div className="flex items-center gap-3 mt-2 text-[12px] text-ink-500">
              <span className="flex items-center gap-1">
                <Icon.Chevron size={12} className={cls('transition', expanded && 'rotate-180')}/>
                {expanded ? 'Hide details' : 'Show details'}
              </span>
              <span>·</span>
              <span className="flex items-center gap-1"><span className="w-1 h-1 rounded-full bg-mastery-needs"/>{f.mistakes.length} mistake{f.mistakes.length === 1 ? '' : 's'}</span>
              <span>·</span>
              <span className="flex items-center gap-1"><span className="w-1 h-1 rounded-full bg-brand-green"/>{f.suggestions.length} suggestion{f.suggestions.length === 1 ? '' : 's'}</span>
              <span>·</span>
              <span className="font-mono tnum">GI {f.gi.toFixed(2)} · CMI {f.cmi.toFixed(2)}</span>
            </div>
          </div>
        </div>
      </button>

      {/* Expanded body */}
      {expanded && (
        <div className="pl-16 pr-4 pb-5 -mt-1">
          <div className="ml-4 pl-4 border-l-2 border-ink-200">
            <p className="text-[14px] text-ink-700 leading-relaxed">{f.summary}</p>

            <div className="grid sm:grid-cols-3 gap-3 mt-4">
              <div className="rounded-xl bg-white border border-ink-200 p-3">
                <div className="text-[11px] uppercase tracking-wider text-ink-500 font-semibold">Score</div>
                <div className="text-[18px] font-semibold tnum">{f.score}/{f.total}</div>
              </div>
              <div className="rounded-xl bg-white border border-ink-200 p-3">
                <div className="text-[11px] uppercase tracking-wider text-ink-500 font-semibold">Guessing Index</div>
                <div className="text-[18px] font-semibold tnum">{f.gi.toFixed(2)}</div>
                <div className="text-[11px] text-ink-500 mt-0.5">{f.gi < 0.2 ? 'Confident' : f.gi < 0.35 ? 'Moderate' : 'Many guesses'}</div>
              </div>
              <div className="rounded-xl bg-white border border-ink-200 p-3">
                <div className="text-[11px] uppercase tracking-wider text-ink-500 font-semibold">Confidence Misc.</div>
                <div className="text-[18px] font-semibold tnum">{f.cmi.toFixed(2)}</div>
                <div className="text-[11px] text-ink-500 mt-0.5">{f.cmi < 0.25 ? 'Calibrated' : f.cmi < 0.45 ? 'Slightly off' : 'Overconfident'}</div>
              </div>
            </div>

            <div className="grid md:grid-cols-2 gap-4 mt-4">
              <div>
                <div className="flex items-center gap-2 mb-2">
                  <span className="w-1.5 h-1.5 rounded-full bg-mastery-needs"/>
                  <div className="text-[12.5px] font-semibold uppercase tracking-wider text-ink-700">Mistakes</div>
                </div>
                {f.mistakes.length === 0
                  ? <div className="text-[13px] text-ink-500 italic">No mistakes flagged.</div>
                  : (
                    <ul className="space-y-1.5">
                      {f.mistakes.map((m, i) => (
                        <li key={i} className="text-[13.5px] text-ink-700 leading-relaxed flex gap-2">
                          <span className="text-mastery-needs mt-1">•</span>{m}
                        </li>
                      ))}
                    </ul>
                  )}
              </div>
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
            </div>

            {/* Action bar — Reddit-style horizontal row */}
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
              <button className="flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-[12.5px] font-semibold text-ink-700 hover:bg-ink-100 transition">
                <Icon.Sparkle size={14}/> Regenerate
              </button>
              <button className="flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-[12.5px] font-semibold text-ink-700 hover:bg-ink-100 transition">
                Reply to student
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

const FeedbackReviewPage = () => {
  const [items, setItems] = React.useState(null);
  const [editing, setEditing] = React.useState(null);
  const [tab, setTab] = React.useState('pending');
  const [q, setQ] = React.useState('');
  const [section, setSection] = React.useState('all');
  const [moduleFilter, setModuleFilter] = React.useState('all');
  const [sort, setSort] = React.useState('newest');
  const [expanded, setExpanded] = React.useState({});

  React.useEffect(() => { api.getFeedback().then(setItems); }, []);
  if (!items) return <Loading/>;

  // attach section info from USERS
  const enriched = items.map(f => {
    const u = USERS.find(x => x.id === f.studentId);
    return { ...f, section: u ? u.section : '—' };
  });

  const counts = {
    pending: enriched.filter(f => f.status === 'pending').length,
    approved: enriched.filter(f => f.status === 'approved').length,
    all: enriched.length,
  };

  const sectionsList = ['all', ...Array.from(new Set(enriched.map(f => f.section))).filter(s => s && s !== '—')];
  const modulesList  = ['all', ...Array.from(new Set(enriched.map(f => f.module)))];

  const matchesQ = (f) => {
    if (!q) return true;
    const hay = (f.studentName + ' ' + f.module + ' ' + f.quiz + ' ' + f.summary).toLowerCase();
    return hay.includes(q.toLowerCase());
  };

  let filtered = enriched
    .filter(f => tab === 'all' ? true : f.status === tab)
    .filter(f => section === 'all' ? true : f.section === section)
    .filter(f => moduleFilter === 'all' ? true : f.module === moduleFilter)
    .filter(matchesQ);

  filtered = [...filtered].sort((a, b) => {
    if (sort === 'newest') return b.generatedAt.localeCompare(a.generatedAt);
    if (sort === 'oldest') return a.generatedAt.localeCompare(b.generatedAt);
    if (sort === 'lowest') return (a.score / a.total) - (b.score / b.total);
    if (sort === 'highest') return (b.score / b.total) - (a.score / a.total);
    if (sort === 'risk')   return (b.gi + b.cmi) - (a.gi + a.cmi);
    return 0;
  });

  const approve = (id) => setItems(xs => xs.map(x => x.id === id ? { ...x, status: 'approved' } : x));
  const toggle  = (id) => setExpanded(e => ({ ...e, [id]: !e[id] }));
  const expandAll = () => setExpanded(Object.fromEntries(filtered.map(f => [f.id, true])));
  const collapseAll = () => setExpanded({});

  const clearAll = () => { setQ(''); setSection('all'); setModuleFilter('all'); setSort('newest'); };
  const hasFilter = q || section !== 'all' || moduleFilter !== 'all' || sort !== 'newest';

  return (
    <div>
      <PageHeader title="Feedback Review"
                  subtitle="Review and approve AI-generated feedback before students see it."/>

      {/* Tab pills */}
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
              {t.label} <span className={cls('ml-1.5 tnum', tab === t.k ? 'text-white/70' : 'text-ink-500')}>{counts[t.k]}</span>
            </button>
          ))}
        </div>
        <div className="ml-auto flex items-center gap-2 text-[12.5px] text-ink-500">
          <button onClick={expandAll} className="hover:text-brand-blue font-medium">Expand all</button>
          <span className="text-ink-300">·</span>
          <button onClick={collapseAll} className="hover:text-brand-blue font-medium">Collapse all</button>
        </div>
      </div>

      {/* Search + filters */}
      <Card className="p-3 mb-4">
        <div className="flex flex-wrap items-center gap-2">
          <div className="relative flex-1 min-w-[220px]">
            <span className="absolute left-3 top-1/2 -translate-y-1/2 text-ink-500"><Icon.Search size={15}/></span>
            <input value={q} onChange={e => setQ(e.target.value)}
                   placeholder="Search by student, module, quiz…"
                   className="w-full bg-ink-50 border border-ink-200 rounded-xl pl-9 pr-8 py-2 text-sm focus:bg-white focus:border-brand-blue outline-none"/>
            {q && (
              <button onClick={() => setQ('')}
                      className="absolute right-2.5 top-1/2 -translate-y-1/2 text-ink-300 hover:text-ink-500">
                <Icon.X size={14}/>
              </button>
            )}
          </div>
          <select value={section} onChange={e => setSection(e.target.value)}
                  className="bg-white border border-ink-200 rounded-xl px-3 py-2 text-sm focus:border-brand-blue outline-none">
            {sectionsList.map(s => <option key={s} value={s}>{s === 'all' ? 'All sections' : s}</option>)}
          </select>
          <select value={moduleFilter} onChange={e => setModuleFilter(e.target.value)}
                  className="bg-white border border-ink-200 rounded-xl px-3 py-2 text-sm focus:border-brand-blue outline-none max-w-[260px]">
            {modulesList.map(m => <option key={m} value={m}>{m === 'all' ? 'All modules' : m}</option>)}
          </select>
          <select value={sort} onChange={e => setSort(e.target.value)}
                  className="bg-white border border-ink-200 rounded-xl px-3 py-2 text-sm focus:border-brand-blue outline-none">
            <option value="newest">Newest first</option>
            <option value="oldest">Oldest first</option>
            <option value="lowest">Lowest score</option>
            <option value="highest">Highest score</option>
            <option value="risk">Highest risk (GI + CMI)</option>
          </select>
          {hasFilter && (
            <button onClick={clearAll}
                    className="text-[12.5px] text-ink-500 hover:text-brand-blue font-medium px-2 py-1">
              Clear
            </button>
          )}
        </div>
      </Card>

      <div className="text-[12.5px] text-ink-500 mb-3">
        Showing <span className="font-semibold text-ink-700">{filtered.length}</span> of {counts[tab]} {tab === 'all' ? '' : tab} {filtered.length === 1 ? 'item' : 'items'}
      </div>

      {filtered.length === 0
        ? <Card><EmptyState title="No feedback matches" sub="Try clearing filters or switching tabs."/></Card>
        : (
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
            <Textarea label="Mistakes (one per line)" rows={3} defaultValue={editing.mistakes.join('\n')}/>
            <Textarea label="Suggestions (one per line)" rows={3} defaultValue={editing.suggestions.join('\n')}/>
          </div>
        )}
      </Modal>
    </div>
  );
};

export default FeedbackReviewPage;
