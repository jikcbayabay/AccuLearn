import React from 'react';
import Icon from '../../components/common/Icons.jsx';
import {
  Badge, Button, Card, MasteryBadge, ProgressBar, SectionTitle, cls,
} from '../../components/common/UI.jsx';
import { PageHeader } from '../../components/layout/Shell.jsx';
import { FEEDBACK } from '../../mockData.js';
import apiClient from '../../services/api.js';

const StudentDashboard = ({ user, onNavigate, setLessonCtx, setQuizCtx }) => {
  const [modules, setModules] = React.useState(null);
  const [stats, setStats] = React.useState(null);

  React.useEffect(() => {
    apiClient.get('/student/dashboard').then(res => {
      setModules(res.data.modules);
      setStats(res.data.stats);
    });
  }, []);

  const current = modules && modules.length > 0 ? modules[0] : null;
  const overall = stats ? Math.round(stats.average_mastery) : 0;
  // MOCK: feedback endpoint is not yet wired, fall back to mockData FEEDBACK.
  const myFeedback = FEEDBACK[0];

  return (
    <div>
      <PageHeader
        title={`Hi, ${user.name.split(' ')[0]} 👋`}
        subtitle="Here's where you left off and what to focus on next."
      />

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-5">
        {/* Hero: current module */}
        <Card className="lg:col-span-2 p-6 relative overflow-hidden">
          <div className="absolute -right-12 -top-12 w-56 h-56 rounded-full opacity-[0.08]" style={{ background: '#24598a' }}/>
          <div className="flex items-start justify-between gap-4 relative">
            <div className="min-w-0">
              <div className="flex items-center gap-2 flex-wrap">
                <Badge tone="blue">Current module</Badge>
                <Badge tone="LP3">Learning Path · LP3</Badge>
                <MasteryBadge level="developing"/>
              </div>
              <h2 className="text-[22px] font-semibold mt-3 text-ink-900">
                {current ? current.title : 'No active module'}
              </h2>
              <p className="text-ink-500 mt-1.5 text-[14px]">
                {current ? current.description : 'Start a module to begin tracking your mastery.'}
              </p>
              <div className="mt-5 max-w-md">
                <div className="flex items-center justify-between text-[12.5px] mb-1.5">
                  <span className="text-ink-500">Module progress</span>
                  <span className="text-ink-700 font-semibold tnum">{overall}%</span>
                </div>
                <ProgressBar value={overall} color="green"/>
              </div>
            </div>
          </div>
          <div className="flex flex-wrap gap-2 mt-6 relative">
            <Button variant="primary" icon={<Icon.Play size={14}/>}
                    onClick={() => { setLessonCtx({ moduleId: 'm3', lessonId: 'l5' }); onNavigate('lesson'); }}>
              Continue lesson
            </Button>
            <Button variant="secondary" onClick={() => onNavigate('modules')}>All modules</Button>
            <Button variant="ghost"
                    onClick={() => { setQuizCtx({ moduleId: 'm3' }); onNavigate('quiz'); }}>
              Take quiz →
            </Button>
          </div>
        </Card>

        {/* Right column: overall + LP */}
        <div className="grid gap-5">
          <Card className="p-5">
            <div className="text-[12.5px] font-medium text-ink-500 uppercase tracking-wider">Overall mastery</div>
            <div className="flex items-end gap-2 mt-2">
              <div className="text-4xl font-semibold tnum">{overall}<span className="text-2xl text-ink-500">%</span></div>
              <div className="text-[12.5px] text-brand-green-700 mb-1.5 font-semibold">+8% this week</div>
            </div>
            <div className="mt-3"><ProgressBar value={overall}/></div>
            <div className="grid grid-cols-3 gap-3 mt-5 text-center">
              {stats && [
                { k: 'Mastered',    v: stats.mastered },
                { k: 'Developing',  v: stats.developing },
                { k: 'Not started', v: stats.not_started },
              ].map(s => (
                <div key={s.k} className="rounded-xl bg-ink-50 py-2.5">
                  <div className="text-xl font-semibold tnum">{s.v}</div>
                  <div className="text-[11.5px] uppercase tracking-wider text-ink-500 font-medium">{s.k}</div>
                </div>
              ))}
            </div>
          </Card>

          <Card className="p-5">
            <div className="text-[12.5px] font-medium text-ink-500 uppercase tracking-wider">Learning Path</div>
            <div className="flex items-center gap-2 mt-2">
              <div className="text-2xl font-semibold">LP3 · Guided Practice</div>
            </div>
            <div className="flex items-center gap-1.5 mt-4">
              {['LP1','LP2','LP3','LP4'].map((p,i) => (
                <div key={p} className="flex-1">
                  <div className={cls('h-2 rounded-full', i <= 2 ? 'bg-brand-green' : 'bg-ink-200')}/>
                  <div className={cls('text-[11px] mt-1.5 text-center', i === 2 ? 'text-brand-green-700 font-semibold' : 'text-ink-500')}>
                    {p}
                  </div>
                </div>
              ))}
            </div>
            <p className="text-[12.5px] text-ink-500 mt-3 leading-relaxed">
              You're on a guided practice path. Keep mastery above 75% to unlock LP4.
            </p>
          </Card>
        </div>
      </div>

      {/* Recent feedback */}
      <SectionTitle title="Recent feedback"
                    subtitle="AI-generated notes from your latest quiz"
                    action={<Button size="sm" variant="ghost" onClick={() => onNavigate('feedback')}>View all →</Button>}/>
      <Card className="p-5">
        <div className="flex items-start gap-4">
          <div className="w-10 h-10 rounded-xl bg-brand-blue-50 text-brand-blue flex items-center justify-center shrink-0">
            <Icon.Sparkle/>
          </div>
          <div className="min-w-0 flex-1">
            <div className="flex flex-wrap items-center gap-2 mb-1">
              <div className="font-semibold text-ink-900">{myFeedback.quiz}</div>
              <Badge tone="neutral">{myFeedback.module}</Badge>
              <span className="text-[12px] text-ink-500">· {myFeedback.generatedAt}</span>
            </div>
            <p className="text-[14px] text-ink-700 leading-relaxed">{myFeedback.summary}</p>
            <div className="grid sm:grid-cols-3 gap-3 mt-4">
              <div className="rounded-xl bg-ink-50 p-3">
                <div className="text-[11px] uppercase tracking-wider text-ink-500 font-semibold">Score</div>
                <div className="text-[18px] font-semibold tnum">{myFeedback.score}/{myFeedback.total}</div>
              </div>
              <div className="rounded-xl bg-ink-50 p-3">
                <div className="text-[11px] uppercase tracking-wider text-ink-500 font-semibold">Guessing Index</div>
                <div className="text-[18px] font-semibold tnum">{myFeedback.gi.toFixed(2)}</div>
              </div>
              <div className="rounded-xl bg-ink-50 p-3">
                <div className="text-[11px] uppercase tracking-wider text-ink-500 font-semibold">Confidence Misc.</div>
                <div className="text-[18px] font-semibold tnum">{myFeedback.cmi.toFixed(2)}</div>
              </div>
            </div>
          </div>
        </div>
      </Card>
    </div>
  );
};

export default StudentDashboard;
