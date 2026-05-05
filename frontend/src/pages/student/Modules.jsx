import { useEffect, useState } from 'react';
import Navbar from '../../components/common/Navbar.jsx';
import Spinner from '../../components/common/Spinner.jsx';
import studentService from '../../services/studentService.js';

export default function Modules() {
  const [modules, setModules] = useState(null);

  useEffect(() => {
    studentService.getModules().then(setModules);
  }, []);

  return (
    <div className="min-h-screen bg-slate-50">
      <Navbar />
      <main className="p-6">
        <h1 className="mb-4 text-2xl font-semibold">Modules</h1>
        {!modules ? (
          <Spinner />
        ) : (
          <ul className="space-y-3">
            {modules.map((m) => (
              <li
                key={m.id}
                className="rounded-lg border bg-white p-4 hover:shadow"
              >
                <h2 className="text-base font-semibold">{m.title}</h2>
                <p className="mt-1 text-sm text-slate-600">{m.description}</p>
              </li>
            ))}
          </ul>
        )}
      </main>
    </div>
  );
}
