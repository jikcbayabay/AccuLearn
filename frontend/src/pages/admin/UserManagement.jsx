import { useEffect, useState } from 'react';
import Navbar from '../../components/common/Navbar.jsx';
import Spinner from '../../components/common/Spinner.jsx';
import UserTable from '../../components/admin/UserTable.jsx';
import adminService from '../../services/adminService.js';

export default function UserManagement() {
  const [users, setUsers] = useState(null);

  useEffect(() => {
    adminService.getUsers().then(setUsers);
  }, []);

  return (
    <div className="min-h-screen bg-slate-50">
      <Navbar />
      <main className="p-6">
        <h1 className="mb-4 text-2xl font-semibold">User Management</h1>
        {!users ? <Spinner /> : <UserTable users={users} />}
      </main>
    </div>
  );
}
