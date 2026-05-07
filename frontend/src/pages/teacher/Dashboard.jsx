import React from 'react';
import Icon from '../../components/common/Icons.jsx';
import {
  Badge, Button, Card, EmptyState, Loading, SectionTitle, Stat, cls,
} from '../../components/common/UI.jsx';
import { PageHeader } from '../../components/layout/Shell.jsx';
import { api } from '../../mockData.js';

const TeacherDashboard = ({ onNavigate }) => {
  const [students, setStudents] = React.useState(null);
  const [feedback, setFeedback] = React.useState(null);
  React.useEffect(() => {
    api.getStudentsProgress().then(setStudents);
    api.getFeedback().then(setFeedback);
  }, []);
  if (!students || !feedback) return <Loading/>;

  const avgMastery = Math.round(students.reduce((s, x) => s + x.mastery, 0) / students.length);
  const needAttn = students.filter(s => s.alerts > 0);
  const pending = feedback.filter(f => f.status === 'pending');

  const distribution = [
    { k: 'mastered',   label: 'Mastered',   count: students.filter(s => s.level === 'mastered').length, color: 'bg-brand-green' },
    { k: 'developing', label: 'Developing', count: students.filter(s => s.level === 'developing').length, color: 'bg-mastery-developing' },
    { k: 'needs',      label: 'Needs Improvement', count: students.filter(s => s.level === 'needs').length, color: 'bg-mastery-needs' },
  ];

  return (
    <div>
      <PageHeader title="Teacher dashboard"
                  subtitle="Section ABM 11-A · Spring term 2026"
                  action={<Button variant="primary" icon={<Icon.Plus size={14}/>}>New announcement</Button>}/>

      <div className="grid sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-6">
        <Stat label="Total students" value={students.length} sub="active across 3 sections" icon={<Icon.Users size={18}/>} accent="blue"
              onClick={() => onNavigate('students')}/>
        <Stat label="Avg mastery" value={`${avgMastery}%`} sub="↑ 4% from last week" icon={<Icon.Chart size={18}/>} accent="green"
              onClick={() => onNavigate('students')}/>
        <Stat label="Needs attention" value={needAttn.length} sub="students with active alerts" icon={<Icon.AlertTri size={18}/>} accent="red"
              onClick={() => onNavigate('students')}/>
        <Stat label="Pending feedback" value={pending.length} sub="awaiting your review" icon={<Icon.ClipCheck size={18}/>} accent="amber"
              onClick={() => onNavigate('feedback')}/>
      </div>

      <div className="grid lg:grid-cols-3 gap-5">
        <Card className="lg:col-span-2 p-6">
          <SectionTitle title="Mastery distribution" subtitle="How your section is performing right now"
                        action={<Button size="sm" variant="ghost" onClick={() => onNavigate('students')}>View all →</Button>}/>
          <div className="space-y-4">
            {distribution.map(d => {
              const pct = Math.round((d.count / students.length) * 100);
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

          <div className="mt-6 pt-5 border-t border-ink-200">
            <div className="text-[12.5px] font-semibold uppercase tracking-wider text-ink-500 mb-3">Learning Path placement</div>
            <div className="grid grid-cols-4 gap-3">
              {['LP1','LP2','LP3','LP4'].map(p => {
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
          <SectionTitle title="Needs attention" subtitle="Students with active alerts"/>
          {needAttn.length === 0
            ? <EmptyState title="All clear" sub="No students currently flagged."/>
            : (
              <div className="space-y-3">
                {needAttn.slice(0, 5).map(s => (
                  <button key={s.id} onClick={() => onNavigate('students')}
                    className="w-full flex items-center gap-3 p-3 rounded-xl hover:bg-ink-50 text-left transition">
                    <div className="w-9 h-9 rounded-full bg-brand-blue text-white flex items-center justify-center text-[12px] font-semibold shrink-0">
                      {s.name.split(' ').map(x=>x[0]).slice(0,2).join('')}
                    </div>
                    <div className="flex-1 min-w-0">
                      <div className="font-semibold text-[14px] text-ink-900 truncate">{s.name}</div>
                      <div className="text-[12px] text-ink-500">{s.section} · {s.path}</div>
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
