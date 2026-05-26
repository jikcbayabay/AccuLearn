// Login + Register page

import React from 'react';
import Icon from '../../components/common/Icons.jsx';
import { Button, Input, Spinner } from '../../components/common/UI.jsx';
import { Logo } from '../../components/layout/Shell.jsx';
import { useAuth } from '../../context/AuthContext.jsx';

const LoginPage = ({ onLogin }) => {
  const { login, register } = useAuth();
  const [mode, setMode] = React.useState('login'); // 'login' | 'register'

  // shared
  const [email, setEmail]       = React.useState('');
  const [password, setPassword] = React.useState('');
  const [loading, setLoading]   = React.useState(false);
  const [err, setErr]           = React.useState('');

  // register-only
  const [name, setName]                     = React.useState('');
  const [section, setSection]               = React.useState('');
  const [passwordConfirm, setPasswordConfirm] = React.useState('');

  const switchMode = (next) => {
    setMode(next);
    setErr('');
  };

  const submitLogin = async (e) => {
    e.preventDefault();
    setErr('');
    setLoading(true);
    try {
      const user = await login(email, password);
      onLogin(user);
    } catch (error) {
      const msg = error?.response?.data?.message || 'Invalid email or password. Please try again.';
      setErr(msg);
    } finally {
      setLoading(false);
    }
  };

  const submitRegister = async (e) => {
    e.preventDefault();
    setErr('');
    if (password !== passwordConfirm) {
      setErr('Passwords do not match.');
      return;
    }
    setLoading(true);
    try {
      const user = await register({
        name,
        email,
        password,
        password_confirmation: passwordConfirm,
        section: section || null,
      });
      onLogin(user);
    } catch (error) {
      const data = error?.response?.data;
      const firstFieldError =
        data?.errors && Object.values(data.errors)[0]?.[0];
      setErr(firstFieldError || data?.message || 'Registration failed. Please try again.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="min-h-screen flex">
      {/* Left brand panel */}
      <div className="hidden lg:flex lg:w-1/2 relative overflow-hidden text-white"
           style={{ background: 'linear-gradient(140deg, #1d4870 0%, #24598a 55%, #2f6fa6 100%)' }}>
        <div className="absolute inset-0 opacity-[0.10]"
             style={{ backgroundImage:
              'radial-gradient(circle at 20% 30%, white 0 1px, transparent 1px), radial-gradient(circle at 70% 70%, white 0 1px, transparent 1px)',
              backgroundSize: '36px 36px, 60px 60px' }}/>
        <div className="relative p-12 flex flex-col w-full">
          <Logo light/>
          <div className="mt-auto max-w-md">
            <div className="inline-flex items-center gap-2 px-2.5 py-1 rounded-full bg-white/15 text-[11.5px] uppercase tracking-wider font-semibold">
              <span className="w-1.5 h-1.5 rounded-full bg-brand-green"/> Mastery-based learning
            </div>
            <h2 className="text-4xl font-semibold mt-5 leading-tight tracking-tight">
              Learn at your pace.<br/>Master every competency.
            </h2>
            <p className="text-white/75 mt-4 text-[15px] leading-relaxed">
              AccuLearn adapts to each Grade 11 ABM student with personalized learning
              paths, AI-guided feedback, and clear signals on what to study next.
            </p>

            <div className="grid grid-cols-3 gap-3 mt-10">
              {[
                { k: 'Adaptive', v: 'LP1–LP4', sub: 'paths' },
                { k: 'AI', v: 'Feedback', sub: 'on every quiz' },
                { k: 'Mastery', v: 'Tracked', sub: 'per competency' },
              ].map((s,i) => (
                <div key={i} className="bg-white/10 backdrop-blur-sm rounded-xl p-3.5 border border-white/10">
                  <div className="text-[10.5px] uppercase tracking-wider text-white/60 font-semibold">{s.k}</div>
                  <div className="text-[15px] font-semibold mt-1">{s.v}</div>
                  <div className="text-[11.5px] text-white/60">{s.sub}</div>
                </div>
              ))}
            </div>
          </div>
          <div className="text-[12px] text-white/50 mt-12">© 2026 AccuLearn · A prototype for ABM Senior High</div>
        </div>
      </div>

      {/* Right form */}
      <div className="flex-1 flex items-center justify-center p-6 bg-white">
        <div className="w-full max-w-sm">
          <div className="lg:hidden mb-8"><Logo/></div>

          {mode === 'login' ? (
            <>
              <h1 className="text-[26px] font-semibold tracking-tight text-ink-900">Welcome back</h1>
              <p className="text-ink-500 mt-1">Sign in to continue your learning journey.</p>

              <form onSubmit={submitLogin} className="mt-7 space-y-4">
                <Input label="Email" type="email" required
                       icon={<Icon.Mail size={16}/>}
                       value={email} onChange={e => setEmail(e.target.value)}
                       placeholder="you@acculearn.test"/>
                <Input label="Password" type="password" required
                       icon={<Icon.Lock size={16}/>}
                       value={password} onChange={e => setPassword(e.target.value)}
                       placeholder="••••••••"/>
                {err && <div className="text-[13px] text-mastery-needs">{err}</div>}
                <Button type="submit" variant="primary" className="w-full justify-center" size="lg" disabled={loading}>
                  {loading ? <Spinner size={16}/> : null}
                  {loading ? 'Signing in…' : 'Sign in'}
                </Button>
              </form>

              <p className="text-[13px] text-ink-500 mt-6 text-center">
                New to AccuLearn?{' '}
                <button type="button" onClick={() => switchMode('register')}
                  className="text-brand-blue font-semibold hover:underline">
                  Create a student account
                </button>
              </p>
            </>
          ) : (
            <>
              <h1 className="text-[26px] font-semibold tracking-tight text-ink-900">Create your account</h1>
              <p className="text-ink-500 mt-1">Sign up as a student. Teachers and admins are provisioned by your school.</p>

              <form onSubmit={submitRegister} className="mt-7 space-y-4">
                <Input label="Full name" type="text" required
                       value={name} onChange={e => setName(e.target.value)}
                       placeholder="Juan Dela Cruz"/>
                <Input label="Email" type="email" required
                       icon={<Icon.Mail size={16}/>}
                       value={email} onChange={e => setEmail(e.target.value)}
                       placeholder="you@school.edu"/>
                <Input label="Section (optional)" type="text"
                       value={section} onChange={e => setSection(e.target.value)}
                       placeholder="Grade 11 - St. Aquinas"/>
                <Input label="Password" type="password" required
                       icon={<Icon.Lock size={16}/>}
                       value={password} onChange={e => setPassword(e.target.value)}
                       placeholder="At least 8 characters"/>
                <Input label="Confirm password" type="password" required
                       icon={<Icon.Lock size={16}/>}
                       value={passwordConfirm} onChange={e => setPasswordConfirm(e.target.value)}
                       placeholder="Repeat your password"/>
                {err && <div className="text-[13px] text-mastery-needs">{err}</div>}
                <Button type="submit" variant="primary" className="w-full justify-center" size="lg" disabled={loading}>
                  {loading ? <Spinner size={16}/> : null}
                  {loading ? 'Creating account…' : 'Create account'}
                </Button>
              </form>

              <p className="text-[13px] text-ink-500 mt-6 text-center">
                Already have an account?{' '}
                <button type="button" onClick={() => switchMode('login')}
                  className="text-brand-blue font-semibold hover:underline">
                  Sign in
                </button>
              </p>
            </>
          )}
        </div>
      </div>
    </div>
  );
};

export default LoginPage;
