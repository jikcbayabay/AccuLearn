import React from 'react';
import Icon from '../../components/common/Icons.jsx';
import {
  Card, LearningPathBadge, Loading, MasteryBadge, Modal, Button,
  ProgressBar, Table, cls,
} from '../../components/common/UI.jsx';
import { PageHeader } from '../../components/layout/Shell.jsx';
import { api, COMPETENCIES } from '../../mockData.js';

const StudentProgressPage = () => {
  const [items, setItems] = React.useState(null);
  const [filter, setFilter] = React.useState('all');
  const [q, setQ] = React.useState('');
  const [selected, setSelected] = React.useState(null);

  React.useEffect(() => { api.getStudentsProgress().then(setItems); }, []);
  if (!items) return <Loading/>;

  const filtered = items
    .filter(s => filter === 'all' ? true : s.level === filter)
    .filter(s => q ? s.name.toLowerCase().includes(q.toLowerCase()) : true);

  return (
    <div>
      <PageHeader title="Student Progress" subtitle="Track mastery and learning paths across your students."/>

      <div className="flex flex-wrap items-center gap-2 mb-4">
        <div className="relative flex-1 max-w-xs">
          <span className="absolute left-3 top-1/2 -translate-y-1/2 text-ink-500"><Icon.Search size={15}/></span>
          <input value={q} onChange={e => setQ(e.target.value)} placeholder="Search students…"
            className="w-full bg-white border border-ink-200 rounded-xl pl-9 pr-3 py-2 text-sm focus:border-brand-blue outline-none"/>
        </div>
        {[
          { k: 'all', label: 'All' },
          { k: 'mastered', label: 'Mastered' },
          { k: 'developing', label: 'Developing' },
          { k: 'needs', label: 'Needs work' },
        ].map(f => (
          <button key={f.k} onClick={() => setFilter(f.k)}
            className={cls('px-3.5 py-1.5 rounded-full text-[13px] font-medium transition border',
              filter === f.k ? 'bg-brand-blue text-white border-brand-blue' : 'bg-white text-ink-700 border-ink-200 hover:border-brand-blue')}>
            {f.label}
          </button>
        ))}
      </div>

      <Card>
        <Table
          columns={[
            { key: 'name', label: 'Name',
              render: r => (
                <div className="flex items-center gap-3">
                  <div className="w-8 h-8 rounded-full bg-brand-blue-50 text-brand-blue flex items-center justify-center text-[11px] font-bold">
                    {r.name.split(' ').map(x=>x[0]).slice(0,2).join('')}
                  </div>
                  <div>
                    <div className="font-medium text-ink-900">{r.name}</div>
                    <div className="text-[12px] text-ink-500">{r.section}</div>
                  </div>
                </div>
              )
            },
            { key: 'mastery', label: 'Mastery',
              render: r => (
                <div className="flex items-center gap-3 max-w-[220px]">
                  <div className="flex-1"><ProgressBar value={r.mastery}
                    color={r.level === 'mastered' ? 'green' : r.level === 'developing' ? 'amber' : 'red'}/></div>
                  <span className="tnum text-[13px] font-semibold w-9 text-right">{r.mastery}%</span>
                </div>
              )
            },
            { key: 'level', label: 'Mastery level', render: r => <MasteryBadge level={r.level}/> },
            { key: 'path', label: 'Learning Path', render: r => <LearningPathBadge path={r.path}/> },
            { key: 'lastActive', label: 'Last active', cellClass: 'text-ink-500 text-[13px]' },
            { key: 'alerts', label: 'Alerts',
              render: r => r.alerts > 0
                ? <span className="inline-flex items-center gap-1 text-mastery-needs font-semibold text-[13px]"><Icon.AlertTri size={14}/>{r.alerts}</span>
                : <span className="text-ink-300">—</span>
            },
          ]}
          rows={filtered}
          onRowClick={setSelected}
        />
      </Card>

      <Modal open={!!selected} onClose={() => setSelected(null)} title="Student details" size="lg"
             footer={<>
               <Button variant="secondary" onClick={() => setSelected(null)}>Close</Button>
               <Button variant="primary">Open full profile →</Button>
             </>}>
        {selected && (
          <div>
            <div className="flex items-center gap-3 pb-4 border-b border-ink-200">
              <div className="w-12 h-12 rounded-full bg-brand-blue text-white flex items-center justify-center text-[14px] font-semibold">
                {selected.name.split(' ').map(x=>x[0]).slice(0,2).join('')}
              </div>
              <div>
                <div className="font-semibold text-ink-900">{selected.name}</div>
                <div className="text-[13px] text-ink-500">{selected.section}</div>
              </div>
              <div className="ml-auto flex gap-2">
                <MasteryBadge level={selected.level}/>
                <LearningPathBadge path={selected.path}/>
              </div>
            </div>
            <div className="grid grid-cols-3 gap-3 mt-4">
              <div className="rounded-xl bg-ink-50 p-3">
                <div className="text-[11px] uppercase tracking-wider text-ink-500 font-semibold">Mastery</div>
                <div className="text-2xl font-semibold tnum">{selected.mastery}%</div>
              </div>
              <div className="rounded-xl bg-ink-50 p-3">
                <div className="text-[11px] uppercase tracking-wider text-ink-500 font-semibold">Last active</div>
                <div className="text-[15px] font-semibold mt-1">{selected.lastActive}</div>
              </div>
              <div className="rounded-xl bg-ink-50 p-3">
                <div className="text-[11px] uppercase tracking-wider text-ink-500 font-semibold">Alerts</div>
                <div className="text-2xl font-semibold tnum text-mastery-needs">{selected.alerts}</div>
              </div>
            </div>
            <div className="mt-5">
              <div className="text-[12px] uppercase tracking-wider text-ink-500 font-semibold mb-3">Top competencies</div>
              <div className="space-y-3">
                {COMPETENCIES.slice(0,5).map(c => (
                  <div key={c.id}>
                    <div className="flex justify-between text-[13px] mb-1">
                      <span>{c.name}</span><span className="tnum text-ink-500">{c.mastery}%</span>
                    </div>
                    <ProgressBar value={c.mastery}
                      color={c.level === 'mastered' ? 'green' : c.level === 'developing' ? 'amber' : 'red'}/>
                  </div>
                ))}
              </div>
            </div>
          </div>
        )}
      </Modal>
    </div>
  );
};

export default StudentProgressPage;
