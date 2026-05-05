import { Link } from 'react-router-dom';
import useAuth from '../../hooks/useAuth.js';
import Button from './Button.jsx';

export default function Navbar() {
  const { user, logout } = useAuth();

  return (
    <header className="flex items-center justify-between border-b bg-white px-6 py-3">
      <Link to="/" className="text-lg font-semibold text-brand-600">
        AccuLearn
      </Link>
      <div className="flex items-center gap-3">
        {user && (
          <span className="text-sm text-slate-600">
            {user.name} ({user.role})
          </span>
        )}
        {user && (
          <Button variant="ghost" onClick={logout}>
            Logout
          </Button>
        )}
      </div>
    </header>
  );
}
