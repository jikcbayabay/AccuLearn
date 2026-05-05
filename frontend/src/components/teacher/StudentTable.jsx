export default function StudentTable({ students = [] }) {
  return (
    <div className="overflow-x-auto rounded-lg border bg-white">
      <table className="w-full text-sm">
        <thead className="bg-slate-50 text-left text-xs uppercase text-slate-500">
          <tr>
            <th className="px-4 py-2">Name</th>
            <th className="px-4 py-2">Email</th>
            <th className="px-4 py-2">Avg Mastery</th>
            <th className="px-4 py-2">Active LP</th>
          </tr>
        </thead>
        <tbody>
          {students.map((s) => (
            <tr key={s.id} className="border-t">
              <td className="px-4 py-2 font-medium">{s.name}</td>
              <td className="px-4 py-2 text-slate-600">{s.email}</td>
              <td className="px-4 py-2">{s.averageMastery}%</td>
              <td className="px-4 py-2">LP{s.activeLp}</td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}
