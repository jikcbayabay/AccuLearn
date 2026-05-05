import { useEffect, useState } from 'react';
import Navbar from '../../components/common/Navbar.jsx';
import Spinner from '../../components/common/Spinner.jsx';
import adminService from '../../services/adminService.js';

export default function SystemLogs() {
  const [logs, setLogs] = useState(null);

  useEffect(() => {
    adminService.getSystemLogs().then(setLogs);
  }, []);

  return (
    <div className="min-h-screen bg-slate-50">
      <Navbar />
      <main className="p-6">
        <h1 className="mb-4 text-2xl font-semibold">System Logs</h1>
        {!logs ? (
          <Spinner />
        ) : (
          <div className="overflow-x-auto rounded-lg border bg-white">
            <table className="w-full text-sm">
              <thead className="bg-slate-50 text-left text-xs uppercase text-slate-500">
                <tr>
                  <th className="px-4 py-2">Time</th>
                  <th className="px-4 py-2">User</th>
                  <th className="px-4 py-2">Action</th>
                  <th className="px-4 py-2">Context</th>
                </tr>
              </thead>
              <tbody>
                {logs.map((log) => (
                  <tr key={log.id} className="border-t">
                    <td className="px-4 py-2 text-slate-600">{log.loggedAt}</td>
                    <td className="px-4 py-2">{log.userName}</td>
                    <td className="px-4 py-2 font-mono text-xs">{log.action}</td>
                    <td className="px-4 py-2 text-slate-500">{log.context}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </main>
    </div>
  );
}
