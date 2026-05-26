import React from 'react';
import Icon from '../../components/common/Icons.jsx';
import {
  Badge, Button, Card, SectionTitle,
} from '../../components/common/UI.jsx';
import { PageHeader } from '../../components/layout/Shell.jsx';
import apiClient from '../../services/api.js';

const StudentDashboard = ({ user, onNavigate, setLessonCtx }) => {
  const [modules, setModules] = React.useState(null);

  React.useEffect(() => {
    apiClient.get('/student/dashboard').then(res => {
      setModules(res.data.modules);
    }).catch(() => {
      setModules([]);
    });
  }, []);

  const current = modules && modules.length > 0 ? modules[0] : null;

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
