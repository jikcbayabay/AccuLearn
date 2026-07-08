// App shell: Topbar (with role switcher), Sidebar, layout

import React from 'react';
import Icon from '../common/Icons.jsx';
import { cls } from '../common/UI.jsx';

export const NAV = {
  student: [
    { key: 'dashboard', label: 'Dashboard', icon: <Icon.Home/> },
    { key: 'modules',   label: 'Modules',   icon: <Icon.Book/> },
    { key: 'progress',  label: 'My Progress', icon: <Icon.Chart/> },
    { key: 'feedback',  label: 'Feedback',  icon: <Icon.Sparkle/> },
    { key: 'settings',  label: 'Settings',  icon: <Icon.Cog/> },
  ],
  teacher: [
    { key: 'dashboard', label: 'Dashboard', icon: <Icon.Home/> },
    { key: 'students',  label: 'Student Progress', icon: <Icon.Users/> },
    { key: 'feedback',  label: 'Feedback Review', icon: <Icon.ClipCheck/> },
    { key: 'modules',   label: 'Modules', icon: <Icon.Book/> },
    { key: 'settings',  label: 'Settings', icon: <Icon.Cog/> },
  ],
  admin: [
    { key: 'dashboard', label: 'Dashboard', icon: <Icon.Home/> },
    { key: 'users',     label: 'User Management', icon: <Icon.Users/> },
    { key: 'modules',   label: 'Module Management', icon: <Icon.Book/> },
    { key: 'logs',      label: 'System Logs', icon: <Icon.Logs/> },
    { key: 'settings',  label: 'Settings', icon: <Icon.Cog/> },
  ],
};

export const Logo = ({ light = false }) => (
  <div className="flex items-center gap-2.5 select-none">
    <div className="relative w-8 h-8 rounded-lg flex items-center justify-center"
         style={{ background: 'linear-gradient(135deg, #24598a 0%, #2e6ba2 100%)' }}>
      {/* Stylized "A" mark */}
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
        <path d="M5 19 12 5l7 14" stroke="white" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round"/>
        <path d="M8.2 14h7.6" stroke="#72b579" strokeWidth="2.4" strokeLinecap="round"/>
      </svg>
    </div>
    <div className="leading-tight">
      <div className={cls('font-bold tracking-tight text-[17px]', light ? 'text-white' : 'text-ink-900')}>
        Acculearn
      </div>
      <div className={cls('text-[10.5px] uppercase tracking-[0.14em] font-medium', light ? 'text-white/60' : 'text-ink-500')}>
        ABM · Grade 11
      </div>
    </div>
  </div>
);

const Topbar = ({ user, onLogout }) => {
  const [open, setOpen] = React.useState(false);
  return (
    <header className="h-14 bg-brand-blue text-white flex items-center px-4 md:px-6 gap-4 shadow-sm relative z-30">
      <div className="flex items-center gap-2.5">
        <div className="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
            <path d="M5 19 12 5l7 14" stroke="white" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round"/>
            <path d="M8.2 14h7.6" stroke="#72b579" strokeWidth="2.4" strokeLinecap="round"/>
          </svg>
        </div>
        <div className="leading-tight">
          <div className="font-bold tracking-tight text-[16px]">Acculearn</div>
        </div>
      </div>

      <div className="hidden md:flex relative ml-6 flex-1 max-w-md">
        <span className="absolute left-3 top-1/2 -translate-y-1/2 text-white/60"><Icon.Search size={16}/></span>
        <input
          placeholder="Search modules, lessons, students…"
          className="w-full bg-white/10 placeholder:text-white/50 text-white rounded-xl pl-9 pr-3 py-2 text-sm border border-white/10 focus:bg-white/15 focus:border-white/30 outline-none"
        />
      </div>

      <div className="ml-auto flex items-center gap-2">
        <button className="w-9 h-9 rounded-xl hover:bg-white/10 flex items-center justify-center relative">
          <Icon.Bell size={18}/>
          <span className="absolute top-2 right-2 w-1.5 h-1.5 rounded-full bg-brand-green"/>
        </button>

        <div className="relative">
          <button onClick={() => setOpen(o => !o)}
                  className="flex items-center gap-2 pl-1 pr-2 py-1 rounded-xl hover:bg-white/10">
            <div className="w-7 h-7 rounded-full bg-white/15 flex items-center justify-center text-[12px] font-semibold">
              {user.name.split(' ').map(s=>s[0]).slice(0,2).join('')}
            </div>
            <div className="hidden sm:block text-left leading-tight">
              <div className="text-[13px] font-medium">{user.name}</div>
              <div className="text-[11px] text-white/60 capitalize">{user.role}</div>
            </div>
            <Icon.Chevron size={14} className="rotate-90 text-white/70"/>
          </button>
          {open && (
            <div className="absolute right-0 mt-1 w-56 bg-white rounded-xl shadow-pop border border-ink-200 text-ink-700 overflow-hidden">
              <div className="px-4 py-3 border-b border-ink-200">
                <div className="text-[13px] font-semibold">{user.name}</div>
                <div className="text-[12px] text-ink-500">{user.email}</div>
              </div>
              <button onClick={() => { setOpen(false); onLogout(); }}
                      className="w-full text-left px-4 py-2.5 text-sm hover:bg-ink-50 flex items-center gap-2">
                <Icon.Logout size={16}/> Sign out
              </button>
            </div>
          )}
        </div>
      </div>
    </header>
  );
};

const Sidebar = ({ role, current, onNavigate }) => {
  const items = NAV[role];
  return (
    <aside className="hidden md:flex md:w-60 lg:w-64 shrink-0 bg-brand-blue text-white/90 flex-col">
      <nav className="flex-1 px-3 py-4 space-y-0.5">
        <div className="px-3 py-2 text-[10.5px] uppercase tracking-[0.14em] text-white/50 font-semibold">
          {role} workspace
        </div>
        {items.map(it => {
          const active = it.key === current;
          return (
            <button key={it.key}
              onClick={() => onNavigate(it.key)}
              className={cls(
                'w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-[14px] transition',
                active
                  ? 'bg-white text-brand-blue font-semibold shadow-sm'
                  : 'hover:bg-white/10 text-white/85'
              )}>
              <span className={cls(active ? 'text-brand-blue' : 'text-white/70')}>{it.icon}</span>
              {it.label}
            </button>
          );
        })}
      </nav>
      <div className="p-3 border-t border-white/10">
        <div className="bg-white/8 rounded-xl p-3.5" style={{ background: 'rgba(255,255,255,0.08)' }}>
          <div className="flex items-center gap-2 text-[12px] font-semibold">
            <Icon.Sparkle size={14}/> Need help?
          </div>
          <div className="text-[12px] text-white/70 mt-1 leading-relaxed">
            Reach your teacher or visit the help center for guides on lessons & quizzes.
          </div>
        </div>
      </div>
    </aside>
  );
};

const MobileNav = ({ role, current, onNavigate }) => {
  const items = NAV[role];
  return (
    <nav className="md:hidden border-t border-ink-200 bg-white grid"
         style={{ gridTemplateColumns: `repeat(${items.length}, 1fr)` }}>
      {items.map(it => {
        const active = it.key === current;
        return (
          <button key={it.key} onClick={() => onNavigate(it.key)}
            className={cls('flex flex-col items-center gap-1 py-2.5 text-[11px]',
              active ? 'text-brand-blue font-semibold' : 'text-ink-500')}>
            {it.icon}
            <span>{it.label.split(' ')[0]}</span>
          </button>
        );
      })}
    </nav>
  );
};

export const PageHeader = ({ title, subtitle, action, breadcrumbs }) => (
  <div className="mb-6 flex flex-wrap items-end justify-between gap-4">
    <div>
      {breadcrumbs && (
        <div className="text-[12.5px] text-ink-500 mb-1.5 flex items-center gap-1.5">
          {breadcrumbs.map((b, i) => (
            <React.Fragment key={i}>
              <span className={cls(i === breadcrumbs.length - 1 ? 'text-ink-700 font-medium' : 'hover:underline cursor-pointer')}
                    onClick={b.onClick}>{b.label}</span>
              {i < breadcrumbs.length - 1 && <Icon.Chevron size={12}/>}
            </React.Fragment>
          ))}
        </div>
      )}
      <h1 className="text-2xl font-semibold tracking-tight text-ink-900">{title}</h1>
      {subtitle && <p className="text-ink-500 mt-1">{subtitle}</p>}
    </div>
    {action}
  </div>
);

const Shell = ({ user, current, onNavigate, onLogout, children }) => (
  <div className="min-h-screen flex flex-col bg-ink-50">
    <Topbar user={user} onLogout={onLogout}/>
    <div className="flex-1 flex">
      <Sidebar role={user.role} current={current} onNavigate={onNavigate}/>
      <main className="flex-1 min-w-0">
        <div className="max-w-[1240px] mx-auto px-5 md:px-8 py-7 fade-in" key={current}>
          {children}
        </div>
      </main>
    </div>
    <MobileNav role={user.role} current={current} onNavigate={onNavigate}/>
  </div>
);

export default Shell;
