import React from 'react';
import Icon from '../../components/common/Icons.jsx';
import {
  Badge, Button, Card, SectionTitle, Stat, ProgressBar, cls,
} from '../../components/common/UI.jsx';
import { PageHeader } from '../../components/layout/Shell.jsx';
import apiClient from '../../services/api.js';

const PACE_LABEL = {
  fast:   { label: 'Fast learner', icon: '⚡' },
  medium: { label: 'Steady pace',  icon: '🚶' },
  slow:   { label: 'Take your time', icon: '🐢' },
};

const GAP_LABEL = {
  repeated_difficulty:     { severity: 'red',   badgeTone: 'needs',      label: 'Repeated difficulty' },
  confident_misconception: { severity: 'amber', badgeTone: 'developing', label: 'Confidence mismatch' },
};

const StudentDashboard = ({ user, onNavigate, setLessonCtx }) => {
  const [modules, setModules] = React.useState(null);
  const [profile, setProfile] = React.useState(null);
  const [gaps, setGaps]       = React.useState([]);

  React.useEffect(() => {
    apiClient.get('/student/dashboard').then(res => {
      setModules(res.data.modules);
      setProfile(res.data.learner_profile ?? null);
      setGaps(res.data.knowledge_gaps ?? []);
    }).catch(() => {
      setModules([]);
    });
  }, []);

  const current = modules && modules.length > 0 ? modules[0] : null;
  const hasProfileData = profile && (profile.avg_mastery > 0 || profile.lessons_completed > 0);

  return (
    <div>
      <PageHeader
        title={`Hi, ${user.name.split(' ')[0]} 👋`}
        subtitle="Here's where you left off and what to focus on next."
      />

      {/* Current module */}
      <Card className="p-6 relative overflow-hidden mb-5">
        <div className="absolute -right-12 -top-12 w-56 h-56 rounded-full opacity-[0.08]" style={{ background: '#24598a' }}/>
        <div className="relative">
          <div className="flex items-center gap-2">
            <Badge tone="blue">Current module</Badge>
          </div>
          <h2 className="text-[22px] font-semibold mt-3 text-ink-900">
            {modules === null
              ? <span className="inline-block w-48 h-6 rounded bg-ink-100 animate-pulse"/>
              : current ? current.title : 'No active module'
            }
          </h2>
          <p className="text-ink-500 mt-1.5 text-[14px]">
            {modules === null
              ? <span className="inline-block w-72 h-4 rounded bg-ink-100 animate-pulse"/>
              : current ? current.description : 'Start a module to begin tracking your mastery.'
            }
          </p>
          <div className="flex flex-wrap gap-2 mt-6">
            <Button variant="primary" icon={<Icon.Play size={14}/>}
                    disabled={!current}
                    onClick={() => { setLessonCtx({ moduleId: current?.id }); onNavigate('lesson'); }}>
              Start lesson
            </Button>
            <Button variant="secondary" onClick={() => onNavigate('modules')}>All modules</Button>
          </div>
        </div>
      </Card>

      {/* Learning insights — derived learner profile */}
      {hasProfileData && (
        <>
          <SectionTitle title="Learning insights" subtitle="How you're learning, based on your activity"/>
          <div className="grid sm:grid-cols-3 gap-4 mb-6">
            <Stat
              label="Your pace"
              value={`${PACE_LABEL[profile.learning_pace]?.icon ?? ''} ${PACE_LABEL[profile.learning_pace]?.label ?? profile.learning_pace}`}
              icon={<Icon.Sparkle size={16}/>}
              accent="blue"
            />
            <Card className="p-4">
              <div className="text-[12px] uppercase tracking-wider text-ink-500 font-semibold">Average mastery</div>
              <div className="text-2xl font-bold text-ink-900 mt-1 tnum">{Math.round(profile.avg_mastery)}%</div>
              <div className="mt-2">
                <ProgressBar
                  value={profile.avg_mastery}
                  color={profile.avg_mastery >= 85 ? 'green' : profile.avg_mastery >= 75 ? 'amber' : 'blue'}
                />
              </div>
            </Card>
            <Stat
              label="Confidence alignment"
              value={`${Math.round(profile.confidence_alignment * 100)}%`}
              sub={profile.confidence_alignment >= 0.7 ? 'Well calibrated' : 'Watch for over/under-confidence'}
              icon={<Icon.Chart size={16}/>}
              accent={profile.confidence_alignment >= 0.7 ? 'green' : 'amber'}
            />
          </div>
        </>
      )}

      {/* Knowledge gaps — detected misconceptions */}
      {gaps.length > 0 && (
        <>
          <SectionTitle title="Focus areas" subtitle="Topics to revisit before moving on"/>
          <div className="space-y-3 mb-6">
            {gaps.map((g, i) => {
              const meta = GAP_LABEL[g.gap_type] ?? { severity: 'amber', badgeTone: 'neutral', label: g.gap_type };
              return (
                <Card key={i} className="p-4">
                  <div className="flex items-start gap-3">
                    <div className={cls('w-9 h-9 rounded-lg flex items-center justify-center shrink-0',
                      meta.severity === 'red' ? 'bg-rose-50 text-mastery-needs' : 'bg-amber-50 text-[#a4751f]')}>
                      <Icon.AlertTri size={16}/>
                    </div>
                    <div className="min-w-0 flex-1">
                      <div className="flex items-center gap-2 flex-wrap">
                        <span className="font-semibold text-ink-900 text-[14px]">{g.competency}</span>
                        <Badge tone={meta.badgeTone}>{meta.label}</Badge>
                      </div>
                      {g.detail && <p className="text-[13px] text-ink-600 mt-1">{g.detail}</p>}
                    </div>
                    <Button variant="secondary" size="sm" onClick={() => onNavigate('modules')}>Review</Button>
                  </div>
                </Card>
              );
            })}
          </div>
        </>
      )}

      {/* Recent feedback */}
      <SectionTitle title="Recent feedback" subtitle="AI-generated notes from your latest quiz"/>
      <Card className="p-10 flex flex-col items-center justify-center text-center">
        <div className="w-12 h-12 rounded-full bg-ink-100 flex items-center justify-center mb-3">
          <Icon.Sparkle className="text-ink-400"/>
        </div>
        <p className="text-[14px] font-medium text-ink-700">No feedback yet</p>
        <p className="text-[13px] text-ink-400 mt-1 max-w-xs">
          Complete a module quiz to receive AI-generated feedback on your performance.
        </p>
      </Card>
    </div>
  );
};

export default StudentDashboard;
