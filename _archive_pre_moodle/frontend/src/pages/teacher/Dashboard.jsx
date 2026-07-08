import React from 'react';
import Icon from '../../components/common/Icons.jsx';
import {
  Badge, Button, Card, EmptyState, Loading, SectionTitle, Stat, cls,
} from '../../components/common/UI.jsx';
import { PageHeader } from '../../components/layout/Shell.jsx';
import apiClient from '../../services/api.js';

const toUiLevel = (lvl) => {
  if (lvl === 'mastered')   return 'mastered';
  if (lvl === 'developing') return 'developing';
  return 'needs';
};

const TeacherDashboard = ({ onNavigate }) => {
  const [students, setStudents] = React.useState(null);
  const [pendingFeedback, setPendingFeedback] = React.useState(0);
  const [error, setError] = React.useState(null);

  React.useEffect(() => {
    Promise.all([
      apiClient.get('/teacher/students'),
      apiClient.get('/teacher/feedback'),
    ]).then(([studRes, fbRes]) => {
      const raw = studRes.data.students ?? [];
      setStudents(raw.map(s => ({
        ...s,
        mastery : Math.round(s.overall_mastery ?? 0),
        level   : toUiLevel(s.mastery_level),
        path    : s.lp_assigned ?? null,
        alerts  : (s.overall_mastery ?? 0) < 60 && s.overall_mastery !== null ? 1 : 0,
      })));
      const fb = Array.isArray(fbRes.data) ? fbRes.data : (fbRes.data?.feedback ?? []);
      setPendingFeedback(fb.filter(f => f.status === 'pending').length);
    }).catch(() => setError('Failed to load teacher data.'));
  }, []);

  if (!students) return error
    ? <div className="p-8 text-mastery-needs">{error}</div>
    : <Loading/>;

  const avgMastery = students.length > 0
    ? Math.round(students.reduce((s, x) => s + x.mastery, 0) / students.length)
    : 0;
  const needAttn = students.filter(s => s.alerts > 0);

  const distribution = [
    { k: 'mastered',   label: 'Mastered',           count: students.filter(s => s.level === 'mastered').length,   color: 'bg-brand-green' },
    { k: 'developing', label: 'Developing',          count: students.filter(s => s.level === 'developing').length, color: 'bg-mastery-developing' },
    { k: 'needs',      label: 'Needs improvement',   count: students.filter(s => s.level === 'needs').length,      color: 'bg-mastery-needs' },
  ];

  const lpGroups = ['LP1', 'LP2', 'LP3', 'LP4'];

  return (
    <div>
      <PageHeader title="Teacher dashboard"
                  subtitle="FABM 1 — Grade 11 ABM"
                  action={<Button variant="primary" icon={<Icon.Plus size={14}/>}>New announcement</Button>}/>

      <div className="grid sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-6">
        <Stat label="Total students"    value={students.length}   sub="enrolled in this course"         icon={<Icon.Users size={18}/>}    accent="blue"  onClick={() => onNavigate('students')}/>
        <Stat label="Avg mastery"       value={`${avgMastery}%`}  sub="across all competencies"         icon={<Icon.Chart size={18}/>}    accent="green" onClick={() => onNavigate('students')}/>
        <Stat label="Needs attention"   value={needAttn.length}   sub="students with low mastery"       icon={<Icon.AlertTri size={18}/>} accent="red"   onClick={() => onNavigate('students')}/>
        <Stat label="Pending feedback"  value={pendingFeedback}   sub="awaiting your review"            icon={<Icon.ClipCheck size={18}/>} accent="amber" onClick={() => onNavigate('feedback')}/>
      </div>

      <div className="grid lg:grid-cols-3 gap-5">
        <Card className="lg:col-span-2 p-6">
          <SectionTitle title="Mastery distribution" subtitle="How students are performing overall"
                        action={<Button size="sm" variant="ghost" onClick={() => onNavigate('students')}>View all →</Button>}/>

          {students.length === 0 ? (
            <EmptyState title="No students yet" sub="Students will appear here once they enrol."/>
          ) : (
            <div className="space-y-4">
              {distribution.map(d => {
                const pct = students.length > 0 ? Math.round((d.count / students.length) * 100) : 0;
                return (
                  <div key={d.k}>
                    <div className="flex justify-between text-[13px] mb-1.5">
                      <span className="text-ink-700 font-medium">{d.label}</span>
                      <span className="text-ink-500 tnum">{d.count} students · {pct}%</span>
                    </div>
                    <div className="h-2.5 bg-ink-100 rounded-full overflow-hidden">
                      <div className={cls(d.color, 'h-full')} style={{ width: `${pct}%` }}/>
                    </div>
                  </div>
                );
              })}
            </div>
          )}

          <div className="mt-6 pt-5 border-t border-ink-200">
            <div className="text-[12.5px] font-semibold uppercase tracking-wider text-ink-500 mb-3">
              Learning Path placement
            </div>
            <div className="grid grid-cols-4 gap-3">
              {lpGroups.map(p => {
                const c = students.filter(s => s.path === p).length;
                return (
                  <div key={p} className="rounded-xl border border-ink-200 p-3">
                    <Badge tone={p}>{p}</Badge>
                    <div className="text-2xl font-semibold tnum mt-2">{c}</div>
                    <div className="text-[11.5px] text-ink-500">students</div>
                  </div>
                );
              })}
            </div>
          </div>
        </Card>

        <Card className="p-6">
          <SectionTitle title="Needs attention" subtitle="Students with low mastery scores"/>
          {needAttn.length === 0 ? (
            <EmptyState title="All clear" sub="No students currently below threshold."/>
          ) : (
            <div className="space-y-3">
              {needAttn.slice(0, 5).map(s => (
                <button key={s.id} onClick={() => onNavigate('students')}
                  className="w-full flex items-center gap-3 p-3 rounded-xl hover:bg-ink-50 text-left transition">
                  <div className="w-9 h-9 rounded-full bg-brand-blue text-white flex items-center justify-center text-[12px] font-semibold shrink-0">
                    {s.name.split(' ').map(x => x[0]).slice(0, 2).join('')}
                  </div>
                  <div className="flex-1 min-w-0">
                    <div className="font-semibold text-[14px] text-ink-900 truncate">{s.name}</div>
                    <div className="text-[12px] text-ink-500">
                      {s.section ? `${s.section} · ` : ''}{s.mastery}% mastery
                    </div>
                  </div>
                  <div className="flex items-center gap-1.5 text-[12px] text-mastery-needs font-semibold">
                    <Icon.AlertTri size={14}/>{s.alerts}
                  </div>
                </button>
              ))}
            </div>
          )}
        </Card>
      </div>
    </div>
  );
};

export default TeacherDashboard;
