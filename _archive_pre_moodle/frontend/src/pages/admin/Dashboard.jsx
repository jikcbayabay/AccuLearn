import React from 'react';
import Icon from '../../components/common/Icons.jsx';
import {
  Button, Card, Loading, SectionTitle, Stat,
} from '../../components/common/UI.jsx';
import { PageHeader } from '../../components/layout/Shell.jsx';
import apiClient from '../../services/api.js';

const AdminDashboard = ({ onNavigate }) => {
  const [stats, setStats] = React.useState(null);
  const [logs, setLogs]   = React.useState(null);

  React.useEffect(() => {
    Promise.all([
      apiClient.get('/admin/stats'),
      apiClient.get('/admin/logs'),
    ]).then(([statsRes, logsRes]) => {
      setStats(statsRes.data);
      // Normalise log shape to what the template expects
      const rawLogs = logsRes.data.logs ?? [];
      setLogs(rawLogs.slice(0, 6).map(l => ({
        id    : l.id,
        actor : l.user?.name ?? 'System',
        action: l.action,
        target: l.module_id ? `Module ${l.module_id}` : '—',
        ts    : l.logged_at ?? '',
      })));
    }).catch(() => {
      setStats({ total_users: 0, total_students: 0, total_teachers: 0, total_modules: 0, active_sessions: 0, avg_mastery: 0 });
      setLogs([]);
    });
  }, []);

  if (!stats || !logs) return <Loading/>;

  return (
    <div>
      <PageHeader title="Admin dashboard" subtitle="System overview & recent activity."/>

      <div className="grid sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-6">
        <Stat label="Total users"     value={stats.total_users}
              sub={`${stats.total_students} students · ${stats.total_teachers} teachers`}
              icon={<Icon.Users size={18}/>}    accent="blue"  onClick={() => onNavigate('users')}/>
        <Stat label="Modules"         value={stats.total_modules}
              sub="published & active"
              icon={<Icon.Book size={18}/>}     accent="green" onClick={() => onNavigate('modules')}/>
        <Stat label="Active sessions" value={stats.active_sessions}
              sub="last 15 minutes"
              icon={<Icon.Sparkle size={18}/>}  accent="amber" onClick={() => onNavigate('logs')}/>
        <Stat label="Avg mastery"     value={`${stats.avg_mastery}%`}
              sub="across all sections"
              icon={<Icon.Chart size={18}/>}    accent="green" onClick={() => onNavigate('users')}/>
      </div>

      <div className="grid lg:grid-cols-[1.5fr_1fr] gap-5">
        <Card className="p-6">
          <SectionTitle title="Activity (last 7 days)"
                        subtitle="Logins, lessons completed, quizzes submitted"/>
          {/* Placeholder bar chart — time-series not yet tracked */}
          <div className="h-44 flex items-end gap-3">
            {[0, 0, 0, 0, 0, 0, 0].map((v, i) => (
              <div key={i} className="flex-1 flex flex-col items-center gap-2">
                <div className="w-full rounded-t-lg transition"
                     style={{ height: `${Math.max(v, 4)}%`, background: '#cfdce9' }}/>
                <div className="text-[11px] text-ink-500">
                  {['M','T','W','T','F','S','S'][i]}
                </div>
              </div>
            ))}
          </div>
          <p className="text-[12px] text-ink-400 mt-3 text-center">
            Activity chart available once usage data is collected.
          </p>
        </Card>

        <Card className="p-6">
          <SectionTitle title="Recent activity"
                        action={<Button size="sm" variant="ghost" onClick={() => onNavigate('logs')}>All logs →</Button>}/>
          {logs.length === 0 ? (
            <p className="text-[13px] text-ink-500 mt-2">No activity recorded yet.</p>
          ) : (
            <div className="space-y-3">
              {logs.map(l => (
                <div key={l.id} className="flex items-start gap-3">
                  <div className="w-1.5 h-1.5 rounded-full bg-brand-blue mt-2 shrink-0"/>
                  <div className="min-w-0 flex-1">
                    <div className="text-[13.5px] text-ink-700">
                      <span className="font-medium">{l.actor}</span>{' '}
                      {l.action.toLowerCase()}{' '}
                      <span className="text-ink-500">— {l.target}</span>
                    </div>
                    <div className="text-[11.5px] text-ink-500 mt-0.5 font-mono">
                      {l.ts ? new Date(l.ts).toLocaleString() : ''}
                    </div>
                  </div>
                </div>
              ))}
            </div>
          )}
        </Card>
      </div>
    </div>
  );
};

export default AdminDashboard;
