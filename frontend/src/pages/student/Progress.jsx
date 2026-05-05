import Navbar from '../../components/common/Navbar.jsx';
import Spinner from '../../components/common/Spinner.jsx';
import ProgressBar from '../../components/student/ProgressBar.jsx';
import useProgress from '../../hooks/useProgress.js';

export default function Progress() {
  const { progress, loading } = useProgress();

  return (
    <div className="min-h-screen bg-slate-50">
      <Navbar />
      <main className="mx-auto max-w-3xl p-6">
        <h1 className="mb-4 text-2xl font-semibold">My Progress</h1>
        {loading ? (
          <Spinner />
        ) : (
          <ul className="space-y-4">
            {progress.competencies.map((c) => (
              <li key={c.id} className="rounded-lg border bg-white p-4">
                <div className="mb-2 flex items-center justify-between">
                  <span className="font-medium">{c.title}</span>
                  <span className="text-sm text-slate-500">{c.level}</span>
                </div>
                <ProgressBar value={c.masteryScore} />
              </li>
            ))}
          </ul>
        )}
      </main>
    </div>
  );
}
