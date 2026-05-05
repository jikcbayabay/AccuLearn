import { useEffect, useState } from 'react';
import Navbar from '../../components/common/Navbar.jsx';
import Spinner from '../../components/common/Spinner.jsx';
import StudentTable from '../../components/teacher/StudentTable.jsx';
import teacherService from '../../services/teacherService.js';

export default function StudentProgress() {
  const [students, setStudents] = useState(null);

  useEffect(() => {
    teacherService.getStudents().then(setStudents);
  }, []);

  return (
    <div className="min-h-screen bg-slate-50">
      <Navbar />
      <main className="p-6">
        <h1 className="mb-4 text-2xl font-semibold">Student Progress</h1>
        {!students ? <Spinner /> : <StudentTable students={students} />}
      </main>
    </div>
  );
}
