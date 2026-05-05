import { useEffect, useState } from 'react';
import Navbar from '../../components/common/Navbar.jsx';
import Sidebar from '../../components/common/Sidebar.jsx';
import Spinner from '../../components/common/Spinner.jsx';
import teacherService from '../../services/teacherService.js';

const NAV = [
  { to: '/teacher', label: 'Overview', end: true },
  { to: '/teacher/students', label: 'Student Progress' },
  { to: '/teacher/feedback', label: 'AI Feedback Review' },
];

export default function Dashboard() {
  const [stats, setStats] = useState(null);

  useEffect(() => {
    teacherService.getOverview().then(setStats);
  }, []);

  return (
    <div className="min-h-screen bg-slate-50">
      <Navbar />
      <div className="flex">
        <Sidebar items={NAV} />
        <main className="flex-1 p-6">
          <h1 className="mb-4 text-2xl font-semibold">Teacher Dashboard</h1>
          {!stats ? (
            <Spinner />
          ) : (
            <div className="grid gap-4 md:grid-cols-3">
              <Stat label="Total Students" value={stats.totalStudents} />
              <Stat label="Avg Mastery" value={`${stats.averageMastery}%`} />
              <Stat label="Pending Feedback" value={stats.pendingFeedback} />
            </div>
          )}
        </main>
      </div>
    </div>
  );
}

function Stat({ label, value }) {
  return (
    <div className="rounded-lg border bg-white p-4">
      <div className="text-xs uppercase text-slate-500">{label}</div>
      <div className="mt-1 text-2xl font-semibold">{value}</div>
    </div>
  );
}
