import React from 'react';
import Icon from '../../components/common/Icons.jsx';
import {
  Badge, Button, Card, Loading, ProgressBar, cls,
} from '../../components/common/UI.jsx';
import { PageHeader } from '../../components/layout/Shell.jsx';
import apiClient from '../../services/api.js';

// Backend currently returns { id, title, description, order, competencies: [...] }.
// Status / progress / lesson counts are not yet computed server-side, so we
// degrade gracefully with sensible defaults until those endpoints exist.
const normalize = (m) => ({
  ...m,
  status:   m.status   ?? 'in-progress',
  progress: m.progress ?? 0,
  lessons:  m.lessons  ?? (m.competencies?.length ?? 0),
  quizzes:  m.quizzes  ?? (m.competencies?.length ?? 0),
});

const ModulesPage = ({ onOpenLesson, onOpenQuiz }) => {
  const [modules, setModules] = React.useState(null);
  const [filter, setFilter] = React.useState('all');

  React.useEffect(() => {
    apiClient.get('/student/modules').then(res => {
      setModules(res.data.modules.map(normalize));
    });
  }, []);

  if (!modules) return <Loading/>;
  const filtered = filter === 'all' ? modules : modules.filter(m => m.status === filter);

  return (
    <div>
      <PageHeader title="Modules" subtitle="Modules tailored to ABM Grade 11."/>

      <div className="flex flex-wrap items-center gap-2 mb-5">
        {[
          { k: 'all',         label: 'All' },
          { k: 'in-progress', label: 'In progress' },
          { k: 'completed',   label: 'Completed' },
          { k: 'locked',      label: 'Locked' },
        ].map(f => (
          <button key={f.k} onClick={() => setFilter(f.k)}
            className={cls('px-3.5 py-1.5 rounded-full text-[13px] font-medium transition border',
              filter === f.k ? 'bg-brand-blue text-white border-brand-blue' : 'bg-white text-ink-700 border-ink-200 hover:border-brand-blue')}>
            {f.label}
          </button>
        ))}
      </div>

      <div className="grid sm:grid-cols-2 xl:grid-cols-3 gap-5">
        {filtered.map(m => {
          const locked = m.status === 'locked';
          return (
            <Card key={m.id} className={cls('p-5 flex flex-col transition hover:shadow-pop hover:-translate-y-0.5', locked && 'opacity-80')}>
              <div className="flex items-start justify-between gap-3 mb-2">
                <div className="text-[12px] uppercase tracking-wider font-semibold text-ink-500">Module {m.order}</div>
                {m.status === 'completed' && <Badge tone="completed">Completed</Badge>}
                {m.status === 'in-progress' && <Badge tone="inProgress">In progress</Badge>}
                {m.status === 'locked' && <Badge tone="locked"><Icon.Lock size={11}/>Locked</Badge>}
              </div>
              <h3 className="font-semibold text-[16px] text-ink-900 leading-snug">{m.title}</h3>
              <p className="text-[13.5px] text-ink-500 mt-1 leading-relaxed line-clamp-2">{m.description}</p>

              <div className="mt-4 flex items-center gap-4 text-[12px] text-ink-500">
                <span className="flex items-center gap-1.5"><Icon.Book size={14}/> {m.lessons} lessons</span>
                <span className="flex items-center gap-1.5"><Icon.ClipCheck size={14}/> {m.quizzes} quizzes</span>
              </div>

              <div className="mt-4">
                <div className="flex justify-between text-[12px] mb-1.5">
                  <span className="text-ink-500">Progress</span>
                  <span className="font-semibold tnum">{m.progress}%</span>
                </div>
                <ProgressBar value={m.progress} color={m.status === 'completed' ? 'green' : 'blue'}/>
              </div>

              <div className="mt-5 flex gap-2">
                <Button
                  variant={locked ? 'secondary' : (m.status === 'completed' ? 'secondary' : 'primary')}
                  size="sm"
                  disabled={locked}
                  onClick={() => onOpenLesson(m.id)}>
                  {locked ? 'Locked' : m.status === 'completed' ? 'Review' : 'Continue'}
                </Button>
                {!locked && <Button size="sm" variant="ghost" onClick={() => onOpenQuiz(m.id)}>Quiz →</Button>}
              </div>
            </Card>
          );
        })}
      </div>
    </div>
  );
};

export default ModulesPage;
