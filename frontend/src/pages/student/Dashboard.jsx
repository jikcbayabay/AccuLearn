import { useEffect, useState } from 'react';
import Navbar from '../../components/common/Navbar.jsx';
import Sidebar from '../../components/common/Sidebar.jsx';
import LessonCard from '../../components/student/LessonCard.jsx';
import LearningTrackBadge from '../../components/student/LearningTrackBadge.jsx';
import Spinner from '../../components/common/Spinner.jsx';
import studentService from '../../services/studentService.js';

const NAV = [
  { to: '/student', label: 'Dashboard', end: true },
  { to: '/student/modules', label: 'Modules' },
  { to: '/student/progress', label: 'My Progress' },
];

export default function Dashboard() {
  const [data, setData] = useState(null);

  useEffect(() => {
    studentService.getDashboard().then(setData);
  }, []);

  return (
    <div className="min-h-screen bg-slate-50">
      <Navbar />
      <div className="flex">
        <Sidebar items={NAV} />
        <main className="flex-1 p-6">
          {!data ? (
            <Spinner />
          ) : (
            <>
              <header className="mb-6">
                <h1 className="text-2xl font-semibold">
                  Welcome back, {data.studentName}
                </h1>
                <div className="mt-2">
                  <LearningTrackBadge lp={data.activeLp} />
                </div>
              </header>

              <section>
                <h2 className="mb-3 text-lg font-semibold">Recommended Lessons</h2>
                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                  {data.recommendedLessons.map((lesson) => (
                    <LessonCard key={lesson.id} lesson={lesson} />
                  ))}
                </div>
              </section>
            </>
          )}
        </main>
      </div>
    </div>
  );
}
