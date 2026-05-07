import React from 'react';
import Icon from '../../components/common/Icons.jsx';
import {
  Button, Card, Loading, MasteryBadge, ProgressBar, Stat, Table,
} from '../../components/common/UI.jsx';
import { PageHeader } from '../../components/layout/Shell.jsx';
import apiClient from '../../services/api.js';

// Map backend mastery_level → UI level. UI uses 'needs' for both
// "needs_improvement" and "not_started" (the latter renders at 0%).
const toUiLevel = (lvl) => {
  if (lvl === 'mastered')   return 'mastered';
  if (lvl === 'developing') return 'developing';
  return 'needs';
};

const ProgressPage = () => {
  const [items, setItems] = React.useState(null);
  const [stats, setStats] = React.useState(null);

  React.useEffect(() => {
    apiClient.get('/student/progress').then(res => {
      const all = res.data.progress.flatMap(m => m.competencies).map(c => ({
        id:       c.id,
        name:     c.title,
        mastery:  c.mastery_score === null ? 0 : Number(c.mastery_score),
        level:    toUiLevel(c.mastery_level),
        attempts: c.attempt_count,
      }));
      setItems(all);
      setStats(res.data.stats);
    });
  }, []);

  if (!items || !stats) return <Loading/>;

  return (
    <div>
      <PageHeader title="My Progress"
                  subtitle="Mastery tracked per competency, not per grade."/>

      <div className="grid sm:grid-cols-3 gap-5 mb-6">
        <Stat
          label="Average mastery"
          value={`${Math.round(stats.average_mastery)}%`}
          sub={`across ${stats.total_competencies} competencies`}
          icon={<Icon.Chart size={18}/>}
          accent="green"
        />
        <Stat
          label="Mastered"
          value={stats.mastered}
          sub={`of ${stats.total_competencies} competencies`}
          icon={<Icon.Check size={18}/>}
          accent="green"
        />
        <Stat
          label="Needs work"
          value={stats.needs_improvement + stats.not_started}
          sub="suggested for retake"
          icon={<Icon.AlertTri size={18}/>}
          accent="red"
        />
      </div>

      <Card>
        <Table
          columns={[
            { key: 'name', label: 'Competency', cellClass: 'font-medium text-ink-900' },
            { key: 'mastery', label: 'Mastery score',
              render: r => (
                <div className="flex items-center gap-3 max-w-xs">
                  <div className="flex-1"><ProgressBar value={r.mastery}
                    color={r.level === 'mastered' ? 'green' : r.level === 'developing' ? 'amber' : 'red'}/></div>
                  <span className="tnum text-[13px] font-semibold w-10 text-right">{r.mastery}%</span>
                </div>
              )
            },
            { key: 'level', label: 'Mastery level', render: r => <MasteryBadge level={r.level}/> },
            { key: 'action', label: '', cellClass: 'text-right',
              render: () => <Button size="sm" variant="ghost">Practice →</Button> },
          ]}
          rows={items}
        />
      </Card>
    </div>
  );
};

export default ProgressPage;
