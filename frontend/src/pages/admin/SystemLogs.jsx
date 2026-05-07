import React from 'react';
import Icon from '../../components/common/Icons.jsx';
import {
  Badge, Card, Loading, Table,
} from '../../components/common/UI.jsx';
import { PageHeader } from '../../components/layout/Shell.jsx';
import apiClient from '../../services/api.js';

// Map backend log shape → UI shape used by the table.
const toUiLog = (l) => ({
  id:     l.id,
  ts:     l.logged_at,
  actor:  l.user?.email ?? 'system',
  action: l.action,
  target: l.module_id ?? l.competency_id ?? '—',
});

const SystemLogs = () => {
  const [logs, setLogs] = React.useState(null);
  const [q, setQ] = React.useState('');

  React.useEffect(() => {
    apiClient.get('/admin/logs').then(res => setLogs(res.data.logs.map(toUiLog)));
  }, []);

  if (!logs) return <Loading/>;

  const filtered = logs.filter(l => q
    ? (l.actor + l.action + l.target).toLowerCase().includes(q.toLowerCase())
    : true);

  return (
    <div>
      <PageHeader title="System Logs" subtitle="A chronological record of activity across AccuLearn."/>

      <div className="mb-4 max-w-md relative">
        <span className="absolute left-3 top-1/2 -translate-y-1/2 text-ink-500"><Icon.Search size={15}/></span>
        <input value={q} onChange={e => setQ(e.target.value)} placeholder="Search logs…"
          className="w-full bg-white border border-ink-200 rounded-xl pl-9 pr-3 py-2 text-sm focus:border-brand-blue outline-none"/>
      </div>

      <Card>
        <Table
          columns={[
            { key: 'id', label: 'ID', cellClass: 'font-mono text-[12.5px] text-ink-500 w-24' },
            { key: 'ts', label: 'Timestamp', cellClass: 'font-mono text-[12.5px] text-ink-700 w-44' },
            { key: 'actor', label: 'Actor', cellClass: 'font-mono text-[12.5px] text-ink-700' },
            { key: 'action', label: 'Action',
              render: r => {
                const tone = /Failed|Deleted/i.test(r.action) ? 'needs'
                  : /Approved|Completed|Created/i.test(r.action) ? 'green'
                  : 'blue';
                return <Badge tone={tone}>{r.action}</Badge>;
              }
            },
            { key: 'target', label: 'Target', cellClass: 'text-ink-700' },
          ]}
          rows={filtered}
        />
      </Card>
    </div>
  );
};

export default SystemLogs;
