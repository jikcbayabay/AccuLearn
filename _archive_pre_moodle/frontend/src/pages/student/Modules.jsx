import React from 'react';
import Icon from '../../components/common/Icons.jsx';
import {
  Badge, Button, Card, Loading, ProgressBar, cls,
} from '../../components/common/UI.jsx';
import { PageHeader } from '../../components/layout/Shell.jsx';
import apiClient from '../../services/api.js';

const ModulesPage = ({ onOpenLesson, onOpenQuiz }) => {
  const [modules, setModules] = React.useState(null);
  const [filter, setFilter]   = React.useState('all');

  React.useEffect(() => {
    apiClient.get('/student/modules').then(res => {
      setModules(res.data.modules);
    }).catch(() => setModules([]));
  }, []);

  if (!modules) return <Loading/>;

  const filtered = filter === 'all'
    ? modules
    : modules.filter(m => m.status === filter);

  return (
    <div>
      <PageHeader title="Modules" subtitle="Modules tailored to ABM Grade 11."/>

      <div className="flex flex-wrap items-center gap-2 mb-5">
        {[
          { k: 'all',         label: 'All' },
          { k: 'in-progress', label: 'In progress' },
          { k: 'completed',   label: 'Completed' },
        ].map(f => (
          <button key={f.k} onClick={() => setFilter(f.k)}
            className={cls('px-3.5 py-1.5 rounded-full text-[13px] font-medium transition border',
              filter === f.k
                ? 'bg-brand-blue text-white border-brand-blue'
                : 'bg-white text-ink-700 border-ink-200 hover:border-brand-blue')}>
            {f.label}
          </button>
        ))}
      </div>

      {filtered.length === 0 ? (
        <div className="text-center py-12 text-ink-500 text-[14px]">No modules match this filter.</div>
      ) : (
        <div className="grid sm:grid-cols-2 xl:grid-cols-3 gap-5">
          {filtered.map(m => (
            <ModuleCard key={m.id} module={m}
                        onOpenLesson={onOpenLesson}
                        onOpenQuiz={onOpenQuiz}/>
          ))}
        </div>
      )}
    </div>
  );
};

const ModuleCard = ({ module: m, onOpenLesson, onOpenQuiz }) => (
  <Card className="p-5 flex flex-col transition hover:shadow-pop hover:-translate-y-0.5">
    <div className="flex items-start justify-between gap-3 mb-2">
      <div className="text-[12px] uppercase tracking-wider font-semibold text-ink-500">
        Module {m.order}
      </div>
      {m.status === 'completed'   && <Badge tone="completed">Completed</Badge>}
      {m.status === 'in-progress' && <Badge tone="inProgress">In progress</Badge>}
    </div>

    <h3 className="font-semibold text-[16px] text-ink-900 leading-snug">{m.title}</h3>
    <p className="text-[13.5px] text-ink-500 mt-1 leading-relaxed line-clamp-2">{m.description}</p>

    <div className="mt-4 flex items-center gap-4 text-[12px] text-ink-500">
      <span className="flex items-center gap-1.5">
        <Icon.Book size={14}/> {m.total_lessons} lessons
      </span>
      <span className="flex items-center gap-1.5">
        <Icon.ClipCheck size={14}/> 1 quiz
      </span>
    </div>

    <div className="mt-4">
      <div className="flex justify-between text-[12px] mb-1.5">
        <span className="text-ink-500">
          {m.completed_lessons}/{m.total_lessons} lessons complete
        </span>
        <span className="font-semibold tnum">{m.progress}%</span>
      </div>
      <ProgressBar value={m.progress} color={m.status === 'completed' ? 'green' : 'blue'}/>
    </div>

    {/* mt-auto pushes buttons to the bottom so all cards align */}
    <div className="mt-auto pt-5 flex gap-2">
      <Button
        variant={m.status === 'completed' ? 'secondary' : 'primary'}
        size="sm"
        onClick={() => onOpenLesson(m.id)}>
        {m.status === 'completed' ? 'Review' : m.completed_lessons > 0 ? 'Continue' : 'Start'}
      </Button>
      <Button size="sm" variant="ghost" onClick={() => onOpenQuiz(m.id)}>
        Quiz →
      </Button>
    </div>
  </Card>
);

export default ModulesPage;
