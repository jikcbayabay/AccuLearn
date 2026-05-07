// Shared Settings page for student / teacher / admin

import React from 'react';
import Icon from '../../components/common/Icons.jsx';
import {
  Button, Card, Input, SectionTitle, Select, cls,
} from '../../components/common/UI.jsx';
import { PageHeader } from '../../components/layout/Shell.jsx';

const SettingsToggle = ({ label, sub, checked, onChange }) => (
  <button onClick={() => onChange(!checked)}
    className="w-full flex items-center justify-between text-left rounded-xl border border-ink-200 px-4 py-3 hover:border-brand-blue/40 transition">
    <div>
      <div className="font-medium text-ink-900">{label}</div>
      {sub && <div className="text-[13px] text-ink-500 mt-0.5">{sub}</div>}
    </div>
    <span className={cls('relative w-10 h-6 rounded-full transition shrink-0',
      checked ? 'bg-brand-blue' : 'bg-ink-200')}>
      <span className={cls('absolute top-0.5 w-5 h-5 rounded-full bg-white shadow-sm transition',
        checked ? 'left-[18px]' : 'left-0.5')}/>
    </span>
  </button>
);

const SettingsPage = ({ user }) => {
  const [tab, setTab] = React.useState('account');
  const [profile, setProfile] = React.useState({ name: user.name, email: user.email });
  const [pw, setPw] = React.useState({ current: '', next: '', confirm: '' });
  const [pwMsg, setPwMsg] = React.useState(null);
  const [saved, setSaved] = React.useState(null);
  const [prefs, setPrefs] = React.useState({
    emailNotifs: true,
    quizReminders: true,
    weeklyDigest: false,
    theme: 'system',
    language: 'English',
  });

  const saveAccount = () => {
    setSaved('account');
    setTimeout(() => setSaved(null), 1800);
  };
  const savePrefs = () => {
    setSaved('prefs');
    setTimeout(() => setSaved(null), 1800);
  };
  const changePassword = () => {
    if (!pw.current || !pw.next || !pw.confirm) {
      setPwMsg({ tone: 'err', text: 'Please fill out all fields.' });
      return;
    }
    if (pw.next.length < 8) {
      setPwMsg({ tone: 'err', text: 'New password must be at least 8 characters.' });
      return;
    }
    if (pw.next !== pw.confirm) {
      setPwMsg({ tone: 'err', text: 'New password and confirmation do not match.' });
      return;
    }
    setPw({ current: '', next: '', confirm: '' });
    setPwMsg({ tone: 'ok', text: 'Password updated successfully.' });
    setTimeout(() => setPwMsg(null), 2400);
  };

  const tabs = [
    { k: 'account',  label: 'Account',       icon: <Icon.Users size={14}/> },
    { k: 'security', label: 'Security',      icon: <Icon.Lock size={14}/> },
    { k: 'prefs',    label: 'Preferences',   icon: <Icon.Cog size={14}/> },
    { k: 'danger',   label: 'Danger zone',   icon: <Icon.AlertTri size={14}/> },
  ];

  return (
    <div>
      <PageHeader title="Settings"
                  subtitle="Manage your account, password, and preferences."/>

      <div className="grid lg:grid-cols-[220px_1fr] gap-6">
        {/* Left rail */}
        <Card className="p-2 self-start">
          <div className="flex lg:flex-col gap-1">
            {tabs.map(t => (
              <button key={t.k} onClick={() => setTab(t.k)}
                className={cls('flex-1 lg:flex-none flex items-center gap-2.5 px-3 py-2 rounded-lg text-[13.5px] font-medium transition text-left',
                  tab === t.k ? 'bg-brand-blue-50 text-brand-blue' : 'text-ink-700 hover:bg-ink-50')}>
                <span className={cls(tab === t.k ? 'text-brand-blue' : 'text-ink-500')}>{t.icon}</span>
                {t.label}
              </button>
            ))}
          </div>
        </Card>

        {/* Right pane */}
        <div className="space-y-5">
          {tab === 'account' && (
            <Card className="p-6">
              <SectionTitle title="Account" subtitle="Your basic profile information."/>
              <div className="flex items-center gap-4 mb-5">
                <div className="w-16 h-16 rounded-full bg-brand-blue text-white flex items-center justify-center text-[18px] font-semibold">
                  {user.name.split(' ').map(s => s[0]).slice(0,2).join('')}
                </div>
                <div>
                  <div className="font-semibold text-ink-900">{user.name}</div>
                  <div className="text-[13px] text-ink-500 capitalize">{user.role} · {user.section}</div>
                  <button className="text-[12.5px] text-brand-blue font-semibold mt-1 hover:underline">Change avatar</button>
                </div>
              </div>
              <div className="grid sm:grid-cols-2 gap-4">
                <Input label="Full name" value={profile.name}
                       onChange={e => setProfile(p => ({ ...p, name: e.target.value }))}/>
                <Input label="Email" type="email" value={profile.email}
                       onChange={e => setProfile(p => ({ ...p, email: e.target.value }))}/>
                <Input label="Section" defaultValue={user.section} disabled/>
                <Input label="Role" defaultValue={user.role} disabled/>
              </div>
              <div className="flex items-center gap-3 mt-6">
                <Button variant="primary" onClick={saveAccount}>Save changes</Button>
                {saved === 'account' && (
                  <span className="text-[13px] text-brand-green-700 font-semibold flex items-center gap-1">
                    <Icon.Check size={14}/> Saved
                  </span>
                )}
              </div>
            </Card>
          )}

          {tab === 'security' && (
            <>
              <Card className="p-6">
                <SectionTitle title="Change password"
                              subtitle="Use at least 8 characters. Mix letters, numbers, and symbols for the best protection."/>
                <div className="grid gap-4 max-w-md">
                  <Input label="Current password" type="password" value={pw.current}
                         onChange={e => setPw(p => ({ ...p, current: e.target.value }))}
                         placeholder="••••••••"/>
                  <Input label="New password" type="password" value={pw.next}
                         onChange={e => setPw(p => ({ ...p, next: e.target.value }))}
                         placeholder="At least 8 characters"/>
                  <Input label="Confirm new password" type="password" value={pw.confirm}
                         onChange={e => setPw(p => ({ ...p, confirm: e.target.value }))}
                         placeholder="Re-type new password"/>
                </div>
                <div className="flex items-center gap-3 mt-5">
                  <Button variant="primary" onClick={changePassword}>Update password</Button>
                  {pwMsg && (
                    <span className={cls('text-[13px] font-semibold flex items-center gap-1',
                      pwMsg.tone === 'ok' ? 'text-brand-green-700' : 'text-mastery-needs')}>
                      {pwMsg.tone === 'ok' ? <Icon.Check size={14}/> : <Icon.AlertTri size={14}/>}
                      {pwMsg.text}
                    </span>
                  )}
                </div>
              </Card>

              <Card className="p-6">
                <SectionTitle title="Sessions & two-factor"/>
                <div className="space-y-3">
                  <div className="flex items-center justify-between rounded-xl border border-ink-200 px-4 py-3">
                    <div>
                      <div className="font-medium text-ink-900">Two-factor authentication</div>
                      <div className="text-[13px] text-ink-500">Add an extra step at sign-in to keep your account secure.</div>
                    </div>
                    <Button variant="secondary" size="sm">Enable</Button>
                  </div>
                  <div className="flex items-center justify-between rounded-xl border border-ink-200 px-4 py-3">
                    <div>
                      <div className="font-medium text-ink-900">Active sessions</div>
                      <div className="text-[13px] text-ink-500">You're signed in on 1 device — this browser.</div>
                    </div>
                    <Button variant="secondary" size="sm">Sign out everywhere</Button>
                  </div>
                </div>
              </Card>
            </>
          )}

          {tab === 'prefs' && (
            <Card className="p-6">
              <SectionTitle title="Preferences" subtitle="How AccuLearn looks and what we email you about."/>
              <div className="grid gap-3 max-w-xl">
                <SettingsToggle label="Email notifications"
                  sub="Get an email when something needs your attention."
                  checked={prefs.emailNotifs}
                  onChange={v => setPrefs(p => ({ ...p, emailNotifs: v }))}/>
                <SettingsToggle label="Quiz reminders"
                  sub="Nudge me when a module quiz is unlocked or overdue."
                  checked={prefs.quizReminders}
                  onChange={v => setPrefs(p => ({ ...p, quizReminders: v }))}/>
                <SettingsToggle label="Weekly digest"
                  sub="A summary of progress and feedback every Monday."
                  checked={prefs.weeklyDigest}
                  onChange={v => setPrefs(p => ({ ...p, weeklyDigest: v }))}/>
                <div className="grid sm:grid-cols-2 gap-4 mt-3">
                  <Select label="Theme" value={prefs.theme}
                          onChange={e => setPrefs(p => ({ ...p, theme: e.target.value }))}>
                    <option value="system">Match system</option>
                    <option value="light">Light</option>
                    <option value="dark">Dark</option>
                  </Select>
                  <Select label="Language" value={prefs.language}
                          onChange={e => setPrefs(p => ({ ...p, language: e.target.value }))}>
                    <option>English</option>
                    <option>Filipino</option>
                  </Select>
                </div>
              </div>
              <div className="flex items-center gap-3 mt-5">
                <Button variant="primary" onClick={savePrefs}>Save preferences</Button>
                {saved === 'prefs' && (
                  <span className="text-[13px] text-brand-green-700 font-semibold flex items-center gap-1">
                    <Icon.Check size={14}/> Saved
                  </span>
                )}
              </div>
            </Card>
          )}

          {tab === 'danger' && (
            <Card className="p-6 border-rose-200">
              <SectionTitle title="Danger zone" subtitle="These actions are permanent. Be sure before you click."/>
              <div className="space-y-3">
                <div className="flex items-center justify-between rounded-xl border border-rose-200 bg-rose-50/50 px-4 py-3">
                  <div>
                    <div className="font-medium text-ink-900">Deactivate my account</div>
                    <div className="text-[13px] text-ink-500">Hide your account and pause all activity. You can reactivate later.</div>
                  </div>
                  <Button variant="secondary" size="sm">Deactivate</Button>
                </div>
                <div className="flex items-center justify-between rounded-xl border border-rose-200 bg-rose-50/50 px-4 py-3">
                  <div>
                    <div className="font-medium text-ink-900">Export my data</div>
                    <div className="text-[13px] text-ink-500">Download a copy of your profile, progress, and feedback.</div>
                  </div>
                  <Button variant="secondary" size="sm">Request export</Button>
                </div>
              </div>
            </Card>
          )}
        </div>
      </div>
    </div>
  );
};

export default SettingsPage;
