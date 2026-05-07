import React from 'react';
import Icon from '../../components/common/Icons.jsx';
import {
  Badge, Button, Card, Input, Loading, Modal, Select, Table, Textarea,
} from '../../components/common/UI.jsx';
import { PageHeader } from '../../components/layout/Shell.jsx';
import { api } from '../../mockData.js';

const ModuleManagement = ({ role = 'admin' }) => {
  const [modules, setModules] = React.useState(null);
  const [editing, setEditing] = React.useState(null);
  const [adding, setAdding] = React.useState(false);

  const isTeacher = role === 'teacher';
  const canCreate = !isTeacher; // only admin can add/delete
  const canDelete = !isTeacher;

  React.useEffect(() => { api.getModules().then(setModules); }, []);
  if (!modules) return <Loading/>;

  return (
    <div>
      <PageHeader title={isTeacher ? 'Modules' : 'Module Management'}
                  subtitle={isTeacher
                    ? 'View and edit module content. Admins manage publishing and lifecycle.'
                    : 'Curate the modules students see in their learning paths.'}
                  action={canCreate && <Button variant="primary" icon={<Icon.Plus size={14}/>} onClick={() => setAdding(true)}>New module</Button>}/>

      {isTeacher && (
        <div className="mb-4 rounded-xl border border-brand-blue/20 bg-brand-blue-50 px-4 py-3 text-[13px] text-ink-700 flex items-start gap-2">
          <Icon.Sparkle size={14} className="text-brand-blue mt-0.5 shrink-0"/>
          <span><span className="font-semibold text-brand-blue">Teacher access:</span> you can edit module content, lessons, and quizzes. Creating, deleting, or publishing a module is reserved for admins.</span>
        </div>
      )}

      <Card>
        <Table
          columns={[
            { key: 'order', label: 'Order', cellClass: 'tnum text-ink-500 w-16',
              render: r => <span className="font-mono">{String(r.order).padStart(2,'0')}</span>
            },
            { key: 'title', label: 'Title', cellClass: 'font-medium text-ink-900' },
            { key: 'description', label: 'Description', cellClass: 'text-ink-500 max-w-sm truncate' },
            { key: 'lessons', label: 'Lessons', cellClass: 'tnum',
              render: r => <span className="text-ink-700">{r.lessons} · {r.quizzes} quizzes</span> },
            { key: 'status', label: 'Status',
              render: r => r.status === 'completed' ? <Badge tone="completed">Completed</Badge>
                : r.status === 'in-progress' ? <Badge tone="inProgress">Active</Badge>
                : <Badge tone="locked">Locked</Badge>
            },
            { key: 'actions', label: '', cellClass: 'text-right',
              render: r => (
                <div className="flex justify-end gap-1.5">
                  <button onClick={() => setEditing(r)}
                          title="Edit module"
                          className="p-1.5 rounded-lg text-ink-500 hover:text-brand-blue hover:bg-brand-blue-50">
                    <Icon.Edit size={15}/>
                  </button>
                  {canDelete && (
                    <button title="Delete module"
                            className="p-1.5 rounded-lg text-ink-500 hover:text-mastery-needs hover:bg-rose-50">
                      <Icon.Trash size={15}/>
                    </button>
                  )}
                </div>
              )
            },
          ]}
          rows={modules}
          onRowClick={setEditing}
        />
      </Card>

      <Modal open={adding || !!editing} onClose={() => { setAdding(false); setEditing(null); }}
             title={adding ? 'New module' : (isTeacher ? 'Edit module content' : 'Edit module')} size="lg"
             footer={<>
               <Button variant="secondary" onClick={() => { setAdding(false); setEditing(null); }}>Cancel</Button>
               <Button variant="primary" onClick={() => { setAdding(false); setEditing(null); }}>{adding ? 'Create module' : 'Save changes'}</Button>
             </>}>
        <div className="grid gap-4">
          <Input label="Title" defaultValue={editing?.title || ''} placeholder="e.g. Business Mathematics"/>
          <Textarea label="Description" rows={3} defaultValue={editing?.description || ''} placeholder="Brief overview of the module…"/>
          <div className="grid grid-cols-3 gap-3">
            <Input label="Order" type="number" defaultValue={editing?.order || (modules.length + 1)} disabled={isTeacher}/>
            <Input label="# Lessons" type="number" defaultValue={editing?.lessons || 6}/>
            <Input label="# Quizzes" type="number" defaultValue={editing?.quizzes || 2}/>
          </div>
          {!isTeacher
            ? <Select label="Status" defaultValue={editing?.status || 'locked'}>
                <option value="locked">Locked (not yet published)</option>
                <option value="in-progress">Active</option>
                <option value="completed">Archived</option>
              </Select>
            : <div className="text-[12.5px] text-ink-500 bg-ink-50 rounded-lg px-3 py-2">
                Status (publishing) is managed by admins.
              </div>}
        </div>
      </Modal>
    </div>
  );
};

export default ModuleManagement;
