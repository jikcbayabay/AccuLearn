import React from 'react';
import Icon from '../../components/common/Icons.jsx';
import {
  Button, Card, Loading, SectionTitle, Stat,
} from '../../components/common/UI.jsx';
import { PageHeader } from '../../components/layout/Shell.jsx';
import { api } from '../../mockData.js';

const AdminDashboard = ({ onNavigate }) => {
  const [stats, setStats] = React.useState(null);
  const [logs, setLogs] = React.useState(null);

  React.useEffect(() => {
    api.getStats().then(setStats);
    api.getLogs().then(l => setLogs(l.slice(0, 6)));
  }, []);

  if (!stats || !logs) return <Loading/>;

  return (
    <div>
      <PageHeader title="Admin dashboard" subtitle="System overview & recent activity."/>

      <div className="grid sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-6">
        <Stat label="Total users" value={stats.totalUsers} sub={`${stats.totalStudents} students · ${stats.totalTeachers} teachers`} icon={<Icon.Users size={18}/>} accent="blue"
              onClick={() => onNavigate('users')}/>
        <Stat label="Modules" value={stats.totalModules} sub="published & active" icon={<Icon.Book size={18}/>} accent="green"
              onClick={() => onNavigate('modules')}/>
        <Stat label="Active sessions" value={stats.activeSessions} sub="last 15 minutes" icon={<Icon.Sparkle size={18}/>} accent="amber"
              onClick={() => onNavigate('logs')}/>
        <Stat label="Avg mastery" value={`${stats.avgMastery}%`} sub="across all sections" icon={<Icon.Chart size={18}/>} accent="green"
              onClick={() => onNavigate('users')}/>
      </div>

      <div className="grid lg:grid-cols-[1.5fr_1fr] gap-5">
        <Card className="p-6">
          <SectionTitle title="Activity (last 7 days)" subtitle="Logins, lessons completed, quizzes submitted"/>
          {/* SVG bar chart */}
          <div className="h-44 flex items-end gap-3">
            {[42, 68, 55, 80, 62, 74, 91].map((v, i) => (
              <div key={i} className="flex-1 flex flex-col items-center gap-2">
                <div className="w-full rounded-t-lg transition" style={{
                  height: `${v}%`,
                  background: i === 6 ? '#24598a' : '#cfdce9'
                }}/>
                <div className="text-[11px] text-ink-500">{['M','T','W','T','F','S','S'][i]}</div>
              </div>
            ))}
          </div>
        </Card>

        <Card className="p-6">
          <SectionTitle title="Recent activity"
                        action={<Button size="sm" variant="ghost" onClick={() => onNavigate('logs')}>All logs →</Button>}/>
          <div className="space-y-3">
            {logs.map(l => (
              <div key={l.id} className="flex items-start gap-3">
                <div className="w-1.5 h-1.5 rounded-full bg-brand-blue mt-2 shrink-0"/>
                <div className="min-w-0 flex-1">
                  <div className="text-[13.5px] text-ink-700"><span className="font-medium">{l.actor}</span> {l.action.toLowerCase()} <span className="text-ink-500">— {l.target}</span></div>
                  <div className="text-[11.5px] text-ink-500 mt-0.5 font-mono">{l.ts}</div>
                </div>
              </div>
            ))}
          </div>
        </Card>
      </div>
    </div>
  );
};

export default AdminDashboard;
