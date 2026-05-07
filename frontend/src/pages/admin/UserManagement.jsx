import React from 'react';
import Icon from '../../components/common/Icons.jsx';
import {
  Badge, Button, Card, Input, Loading, Modal, Select, Table, cls,
} from '../../components/common/UI.jsx';
import { PageHeader } from '../../components/layout/Shell.jsx';
import apiClient from '../../services/api.js';

const DEFAULT_PASSWORD = 'AccuLearn@2026';

const blankForm = { name: '', email: '', role: 'student', section: '' };

const UserManagement = () => {
  const [users, setUsers] = React.useState(null);
  const [q, setQ] = React.useState('');
  const [roleFilter, setRoleFilter] = React.useState('all');
  const [statusFilter, setStatusFilter] = React.useState('all');
  const [editing, setEditing] = React.useState(null);
  const [adding, setAdding] = React.useState(false);
  const [confirm, setConfirm] = React.useState(null);
  const [form, setForm] = React.useState(blankForm);
  const [submitting, setSubmitting] = React.useState(false);
  const [toast, setToast] = React.useState(null);

  const showError = React.useCallback((msg) => {
    setToast(msg);
    setTimeout(() => setToast(null), 3000);
  }, []);

  const fetchUsers = React.useCallback(() => {
    apiClient.get('/admin/users')
      .then(res => setUsers(res.data.users))
      .catch(() => showError('Failed to load users'));
  }, [showError]);

  React.useEffect(() => { fetchUsers(); }, [fetchUsers]);

  // sync form whenever opening the Edit modal
  React.useEffect(() => {
    if (editing) {
      setForm({
        name: editing.name ?? '',
        email: editing.email ?? '',
        role: editing.role ?? 'student',
        section: editing.section ?? '',
      });
    } else if (adding) {
      setForm(blankForm);
    }
  }, [editing, adding]);

  if (!users) return <Loading/>;

  const filtered = users
    .filter(u => roleFilter === 'all' ? true : u.role === roleFilter)
    .filter(u => statusFilter === 'all' ? true : (statusFilter === 'active' ? u.active !== false : !u.active))
    .filter(u => q ? (u.name + u.email).toLowerCase().includes(q.toLowerCase()) : true);

  const closeModal = () => { setAdding(false); setEditing(null); };

  const submitForm = async () => {
    setSubmitting(true);
    try {
      if (adding) {
        await apiClient.post('/admin/users', {
          name: form.name,
          email: form.email,
          password: DEFAULT_PASSWORD,
          role: form.role,
          section: form.section || null,
        });
      } else if (editing) {
        await apiClient.put(`/admin/users/${editing.id}`, {
          name: form.name,
          email: form.email,
          role: form.role,
          section: form.section || null,
        });
      }
      closeModal();
      fetchUsers();
    } catch (err) {
      const msg = err?.response?.data?.message
        || (err?.response?.data?.errors && Object.values(err.response.data.errors)[0]?.[0])
        || 'Failed to save user';
      showError(msg);
    } finally {
      setSubmitting(false);
    }
  };

  const deactivate = async (id) => {
    setSubmitting(true);
    try {
      await apiClient.delete(`/admin/users/${id}`);
      setConfirm(null);
      fetchUsers();
    } catch (err) {
      const msg = err?.response?.data?.message || 'Failed to deactivate user';
      showError(msg);
    } finally {
      setSubmitting(false);
    }
  };

  return (
    <div>
      <PageHeader title="User Management"
                  subtitle={`${users.length} accounts · ${users.filter(u => u.active !== false).length} active`}
                  action={<Button variant="primary" icon={<Icon.Plus size={14}/>} onClick={() => setAdding(true)}>Add user</Button>}/>

      <div className="flex flex-wrap items-center gap-2 mb-4">
        <div className="relative flex-1 max-w-xs">
          <span className="absolute left-3 top-1/2 -translate-y-1/2 text-ink-500"><Icon.Search size={15}/></span>
          <input value={q} onChange={e => setQ(e.target.value)} placeholder="Search by name or email…"
            className="w-full bg-white border border-ink-200 rounded-xl pl-9 pr-3 py-2 text-sm focus:border-brand-blue outline-none"/>
        </div>
        {['all','student','teacher','admin'].map(r => (
          <button key={r} onClick={() => setRoleFilter(r)}
            className={cls('px-3.5 py-1.5 rounded-full text-[13px] font-medium transition border capitalize',
              roleFilter === r ? 'bg-brand-blue text-white border-brand-blue' : 'bg-white text-ink-700 border-ink-200 hover:border-brand-blue')}>
            {r}
          </button>
        ))}
        <div className="ml-auto">
          <select value={statusFilter} onChange={e => setStatusFilter(e.target.value)}
                  className="bg-white border border-ink-200 rounded-xl px-3 py-2 text-sm focus:border-brand-blue outline-none">
            <option value="all">All statuses</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
          </select>
        </div>
      </div>

      <Card>
        <Table
          columns={[
            { key: 'name', label: 'Name',
              render: r => (
                <div className={cls('flex items-center gap-3', !r.active && 'opacity-60')}>
                  <div className="w-8 h-8 rounded-full bg-brand-blue-50 text-brand-blue flex items-center justify-center text-[11px] font-bold">
                    {r.name.split(' ').map(x=>x[0]).slice(0,2).join('')}
                  </div>
                  <span className="font-medium text-ink-900">{r.name}</span>
                </div>
              )
            },
            { key: 'email', label: 'Email', cellClass: 'text-ink-500 font-mono text-[12.5px]' },
            { key: 'role', label: 'Role',
              render: r => (
                <Badge tone={r.role === 'admin' ? 'blue' : r.role === 'teacher' ? 'green' : 'neutral'}>
                  {r.role}
                </Badge>
              )
            },
            { key: 'section', label: 'Section', cellClass: 'text-ink-700',
              render: r => r.section || <span className="text-ink-300">—</span> },
            { key: 'status', label: 'Status',
              render: r => r.active !== false
                ? <span className="inline-flex items-center gap-1.5 text-[12.5px] font-semibold text-brand-green-700">
                    <span className="w-1.5 h-1.5 rounded-full bg-brand-green"/>Active
                  </span>
                : <span className="inline-flex items-center gap-1.5 text-[12.5px] font-semibold text-ink-500">
                    <span className="w-1.5 h-1.5 rounded-full bg-ink-300"/>Inactive
                  </span>
            },
            { key: 'created_at', label: 'Joined', cellClass: 'text-ink-500 text-[13px]',
              render: r => r.created_at ? new Date(r.created_at).toLocaleDateString() : '—' },
            { key: 'actions', label: '', cellClass: 'text-right',
              render: r => (
                <div className="flex justify-end gap-1.5">
                  <button onClick={(e) => { e.stopPropagation(); setEditing(r); }}
                          title="Edit user"
                          className="p-1.5 rounded-lg text-ink-500 hover:text-brand-blue hover:bg-brand-blue-50">
                    <Icon.Edit size={15}/>
                  </button>
                  {r.active !== false
                    ? <button onClick={(e) => { e.stopPropagation(); setConfirm(r); }}
                              title="Make inactive"
                              className="px-2.5 py-1 rounded-lg text-[12px] font-semibold text-ink-700 border border-ink-200 hover:bg-ink-100">
                        Make inactive
                      </button>
                    : <button title="Reactivate user"
                              disabled
                              className="px-2.5 py-1 rounded-lg text-[12px] font-semibold text-ink-400 border border-ink-200 cursor-not-allowed">
                        Inactive
                      </button>}
                </div>
              )
            },
          ]}
          rows={filtered}
        />
      </Card>

      <Modal open={adding || !!editing} onClose={closeModal}
             title={adding ? 'Add user' : 'Edit user'}
             footer={<>
               <Button variant="secondary" onClick={closeModal} disabled={submitting}>Cancel</Button>
               <Button variant="primary" onClick={submitForm} disabled={submitting}>
                 {submitting ? 'Saving…' : (adding ? 'Add user' : 'Save')}
               </Button>
             </>}>
        <div className="grid gap-4">
          <Input label="Full name" value={form.name}
                 onChange={e => setForm(f => ({ ...f, name: e.target.value }))}
                 placeholder="e.g. Maria Santos"/>
          <Input label="Email" type="email" value={form.email}
                 onChange={e => setForm(f => ({ ...f, email: e.target.value }))}
                 placeholder="email@acculearn.test"/>
          <div className="grid grid-cols-2 gap-3">
            <Select label="Role" value={form.role}
                    onChange={e => setForm(f => ({ ...f, role: e.target.value }))}>
              <option value="student">Student</option>
              <option value="teacher">Teacher</option>
              <option value="admin">Admin</option>
            </Select>
            <Input label="Section" value={form.section}
                   onChange={e => setForm(f => ({ ...f, section: e.target.value }))}
                   placeholder="ABM 11-A"/>
          </div>
          {adding && (
            <div className="text-[12.5px] text-ink-500 bg-ink-50 rounded-lg px-3 py-2">
              Default password: <span className="font-mono text-ink-700">{DEFAULT_PASSWORD}</span>
            </div>
          )}
        </div>
      </Modal>

      <Modal open={!!confirm} onClose={() => setConfirm(null)} title="Make user inactive" size="sm"
             footer={<>
               <Button variant="secondary" onClick={() => setConfirm(null)} disabled={submitting}>Cancel</Button>
               <Button variant="primary" onClick={() => deactivate(confirm.id)} disabled={submitting}>
                 {submitting ? 'Working…' : 'Make inactive'}
               </Button>
             </>}>
        {confirm && (
          <div className="text-ink-700 space-y-2">
            <p>Mark <span className="font-semibold">{confirm.name}</span> as inactive?</p>
            <p className="text-[13px] text-ink-500">They will no longer be able to sign in or appear in active rosters, but their record, progress, and feedback history are kept.</p>
          </div>
        )}
      </Modal>

      {toast && (
        <div className="fixed bottom-5 right-5 z-50 bg-mastery-needs text-white px-4 py-2.5 rounded-xl shadow-pop text-[13px] font-medium fade-in"
             role="alert">
          {toast}
        </div>
      )}
    </div>
  );
};

export default UserManagement;
