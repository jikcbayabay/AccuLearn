import { useEffect, useState } from 'react';
import Navbar from '../../components/common/Navbar.jsx';
import Spinner from '../../components/common/Spinner.jsx';
import ModuleForm from '../../components/admin/ModuleForm.jsx';
import adminService from '../../services/adminService.js';

export default function ModuleManagement() {
  const [modules, setModules] = useState(null);

  useEffect(() => {
    adminService.getModules().then(setModules);
  }, []);

  const handleSubmit = async (form) => {
    const created = await adminService.createModule(form);
    setModules((prev) => (prev ? [...prev, created] : [created]));
  };

  return (
    <div className="min-h-screen bg-slate-50">
      <Navbar />
      <main className="grid gap-6 p-6 md:grid-cols-2">
        <section>
          <h1 className="mb-4 text-2xl font-semibold">Modules</h1>
          {!modules ? (
            <Spinner />
          ) : (
            <ul className="space-y-2">
              {modules.map((m) => (
                <li
                  key={m.id}
                  className="rounded-md border bg-white p-3 text-sm"
                >
                  <div className="font-medium">{m.title}</div>
                  <div className="text-xs text-slate-500">{m.description}</div>
                </li>
              ))}
            </ul>
          )}
        </section>
        <section>
          <h2 className="mb-4 text-lg font-semibold">Create Module</h2>
          <ModuleForm onSubmit={handleSubmit} />
        </section>
      </main>
    </div>
  );
}
