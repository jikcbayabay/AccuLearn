// Reusable UI primitives: Button, Card, Badge, ProgressBar, Table, Modal, Spinner, EmptyState

import Icon from './Icons.jsx';

export const cls = (...a) => a.filter(Boolean).join(' ');

export const Button = ({ variant = 'primary', size = 'md', icon, children, className = '', ...rest }) => {
  const base = 'inline-flex items-center gap-2 font-medium rounded-xl transition active:scale-[.98] focus:outline-none ring-brand disabled:opacity-50 disabled:cursor-not-allowed';
  const sizes = { sm: 'px-3 py-1.5 text-[13px]', md: 'px-4 py-2 text-sm', lg: 'px-5 py-2.5 text-[15px]' };
  const variants = {
    primary:  'bg-brand-blue text-white hover:bg-brand-blue-700 shadow-sm',
    success:  'bg-brand-green text-white hover:bg-brand-green-700 shadow-sm',
    secondary:'bg-white text-ink-700 border border-ink-200 hover:bg-ink-50',
    ghost:    'text-ink-700 hover:bg-ink-100',
    danger:   'bg-mastery-needs text-white hover:brightness-95',
    subtle:   'bg-brand-blue-50 text-brand-blue hover:bg-brand-blue-50/70',
  };
  return (
    <button className={cls(base, sizes[size], variants[variant], className)} {...rest}>
      {icon}{children}
    </button>
  );
};

export const Card = ({ children, className = '', as: As = 'div', ...rest }) => (
  <As className={cls('bg-white rounded-2xl shadow-card border border-ink-200/70', className)} {...rest}>
    {children}
  </As>
);

export const SectionTitle = ({ title, subtitle, action }) => (
  <div className="flex items-end justify-between mb-4">
    <div>
      <h2 className="text-[19px] font-semibold text-ink-900 tracking-tight">{title}</h2>
      {subtitle && <p className="text-sm text-ink-500 mt-0.5">{subtitle}</p>}
    </div>
    {action}
  </div>
);

export const Badge = ({ children, tone = 'neutral', className = '' }) => {
  const tones = {
    neutral:    'bg-ink-100 text-ink-700',
    blue:       'bg-brand-blue-50 text-brand-blue',
    green:      'bg-brand-green-50 text-brand-green-700',
    mastered:   'bg-brand-green-50 text-brand-green-700',
    developing: 'bg-amber-50 text-[#a4751f]',
    needs:      'bg-rose-50 text-[#a04a40]',
    locked:     'bg-ink-100 text-ink-500',
    inProgress: 'bg-brand-blue-50 text-brand-blue',
    completed:  'bg-brand-green-50 text-brand-green-700',
    LP1:        'bg-rose-50 text-[#a04a40]',
    LP2:        'bg-amber-50 text-[#a4751f]',
    LP3:        'bg-brand-blue-50 text-brand-blue',
    LP4:        'bg-brand-green-50 text-brand-green-700',
  };
  return (
    <span className={cls('inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[11.5px] font-semibold tracking-wide uppercase', tones[tone], className)}>
      {children}
    </span>
  );
};

export const MasteryBadge = ({ level }) => {
  const map = {
    mastered:   { tone: 'mastered',   label: 'Mastered' },
    developing: { tone: 'developing', label: 'Developing' },
    needs:      { tone: 'needs',      label: 'Needs Improvement' },
  }[level] || { tone: 'neutral', label: level };
  return (
    <Badge tone={map.tone}>
      <span className="w-1.5 h-1.5 rounded-full" style={{
        background: level === 'mastered' ? '#72b579' : level === 'developing' ? '#d9a441' : '#c97064'
      }}/>
      {map.label}
    </Badge>
  );
};

export const LearningPathBadge = ({ path }) => (
  <Badge tone={path}>{path}</Badge>
);

export const ProgressBar = ({ value = 0, color = 'green', size = 'md', showLabel = false }) => {
  const colors = {
    green: 'bg-brand-green',
    blue:  'bg-brand-blue',
    amber: 'bg-mastery-developing',
    red:   'bg-mastery-needs',
  };
  const heights = { sm: 'h-1.5', md: 'h-2', lg: 'h-2.5' };
  return (
    <div className="flex items-center gap-3">
      <div className={cls('flex-1 bg-ink-100 rounded-full overflow-hidden', heights[size])}>
        <div className={cls(colors[color], 'h-full rounded-full transition-[width] duration-500')}
             style={{ width: `${Math.max(0, Math.min(100, value))}%` }} />
      </div>
      {showLabel && <span className="text-[12.5px] text-ink-500 tnum w-9 text-right">{Math.round(value)}%</span>}
    </div>
  );
};

export const Stat = ({ label, value, sub, icon, accent = 'blue', onClick }) => {
  const accents = {
    blue:  'bg-brand-blue-50 text-brand-blue',
    green: 'bg-brand-green-50 text-brand-green-700',
    amber: 'bg-amber-50 text-[#a4751f]',
    red:   'bg-rose-50 text-[#a04a40]',
  };
  const inner = (
    <div className="flex items-start justify-between">
      <div>
        <div className="text-[12.5px] font-medium text-ink-500 uppercase tracking-wider">{label}</div>
        <div className="text-3xl font-semibold text-ink-900 tnum mt-1.5">{value}</div>
        {sub && <div className="text-[12.5px] text-ink-500 mt-1">{sub}</div>}
      </div>
      {icon && <div className={cls('w-9 h-9 rounded-xl flex items-center justify-center', accents[accent])}>{icon}</div>}
    </div>
  );
  if (onClick) {
    return (
      <button onClick={onClick}
        className="text-left bg-white rounded-2xl shadow-card border border-ink-200 p-5 transition hover:-translate-y-0.5 hover:shadow-pop hover:border-brand-blue/40 focus:outline-none focus:ring-2 focus:ring-brand-blue/30">
        {inner}
      </button>
    );
  }
  return <Card className="p-5">{inner}</Card>;
};

export const Spinner = ({ size = 18 }) => (
  <svg width={size} height={size} viewBox="0 0 24 24" fill="none" className="animate-spin">
    <circle cx="12" cy="12" r="9" stroke="#e5e7eb" strokeWidth="3"/>
    <path d="M21 12a9 9 0 0 0-9-9" stroke="#24598a" strokeWidth="3" strokeLinecap="round"/>
  </svg>
);

export const Loading = ({ label = 'Loading…' }) => (
  <div className="flex items-center gap-3 text-ink-500 text-sm py-12 justify-center">
    <Spinner /> {label}
  </div>
);

export const EmptyState = ({ title = 'Nothing here yet', sub, action }) => (
  <div className="text-center py-14 px-6">
    <div className="w-12 h-12 rounded-2xl bg-ink-100 mx-auto mb-3 flex items-center justify-center text-ink-500">
      <Icon.Sparkle size={22}/>
    </div>
    <div className="font-semibold text-ink-700">{title}</div>
    {sub && <div className="text-sm text-ink-500 mt-1 max-w-sm mx-auto">{sub}</div>}
    {action && <div className="mt-4">{action}</div>}
  </div>
);

export const Modal = ({ open, onClose, title, children, footer, size = 'md' }) => {
  if (!open) return null;
  const sizes = { sm: 'max-w-md', md: 'max-w-lg', lg: 'max-w-2xl' };
  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4 fade-in">
      <div className="absolute inset-0 bg-ink-900/40 backdrop-blur-[1px]" onClick={onClose}/>
      <div className={cls('relative bg-white rounded-2xl shadow-pop w-full', sizes[size])}>
        <div className="px-5 py-4 border-b border-ink-200 flex items-center justify-between">
          <div className="font-semibold text-ink-900">{title}</div>
          <button onClick={onClose} className="text-ink-500 hover:text-ink-700 p-1 rounded-md hover:bg-ink-100"><Icon.X/></button>
        </div>
        <div className="p-5">{children}</div>
        {footer && <div className="px-5 py-3 border-t border-ink-200 bg-ink-50/40 rounded-b-2xl flex justify-end gap-2">{footer}</div>}
      </div>
    </div>
  );
};

export const Input = ({ label, hint, icon, className = '', ...rest }) => (
  <label className={cls('block', className)}>
    {label && <span className="block text-[13px] font-medium text-ink-700 mb-1.5">{label}</span>}
    <div className="relative">
      {icon && <span className="absolute left-3 top-1/2 -translate-y-1/2 text-ink-500">{icon}</span>}
      <input
        className={cls(
          'w-full rounded-xl border border-ink-200 bg-white px-3.5 py-2.5 text-sm text-ink-900 placeholder:text-ink-500',
          'focus:border-brand-blue focus:ring-2 focus:ring-brand-blue/20 outline-none transition',
          icon && 'pl-9'
        )}
        {...rest}
      />
    </div>
    {hint && <span className="block text-[12px] text-ink-500 mt-1">{hint}</span>}
  </label>
);

export const Select = ({ label, children, className = '', ...rest }) => (
  <label className={cls('block', className)}>
    {label && <span className="block text-[13px] font-medium text-ink-700 mb-1.5">{label}</span>}
    <select
      className="w-full rounded-xl border border-ink-200 bg-white px-3.5 py-2.5 text-sm text-ink-900 focus:border-brand-blue focus:ring-2 focus:ring-brand-blue/20 outline-none transition"
      {...rest}>
      {children}
    </select>
  </label>
);

export const Textarea = ({ label, className = '', ...rest }) => (
  <label className={cls('block', className)}>
    {label && <span className="block text-[13px] font-medium text-ink-700 mb-1.5">{label}</span>}
    <textarea
      className="w-full rounded-xl border border-ink-200 bg-white px-3.5 py-2.5 text-sm text-ink-900 focus:border-brand-blue focus:ring-2 focus:ring-brand-blue/20 outline-none transition"
      {...rest}
    />
  </label>
);

export const Table = ({ columns, rows, onRowClick, empty }) => (
  <div className="overflow-x-auto">
    <table className="w-full text-sm">
      <thead>
        <tr className="text-left text-[12px] font-semibold uppercase tracking-wider text-ink-500 border-b border-ink-200 bg-ink-50/60">
          {columns.map(c => (
            <th key={c.key} className={cls('px-5 py-3', c.className)}>{c.label}</th>
          ))}
        </tr>
      </thead>
      <tbody>
        {rows.length === 0 && (
          <tr><td colSpan={columns.length}><EmptyState title="No records" sub={empty}/></td></tr>
        )}
        {rows.map((row, i) => (
          <tr key={row.id || i}
              onClick={() => onRowClick && onRowClick(row)}
              className={cls('border-b border-ink-200/70 last:border-0 transition',
                onRowClick && 'hover:bg-brand-blue-50/40 cursor-pointer')}>
            {columns.map(c => (
              <td key={c.key} className={cls('px-5 py-3.5 text-ink-700', c.cellClass)}>
                {c.render ? c.render(row) : row[c.key]}
              </td>
            ))}
          </tr>
        ))}
      </tbody>
    </table>
  </div>
);
