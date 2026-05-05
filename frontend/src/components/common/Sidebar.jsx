import { NavLink } from 'react-router-dom';

export default function Sidebar({ items = [] }) {
  return (
    <aside className="h-full w-56 shrink-0 border-r bg-white p-4">
      <nav className="flex flex-col gap-1">
        {items.map((item) => (
          <NavLink
            key={item.to}
            to={item.to}
            end={item.end}
            className={({ isActive }) =>
              `rounded-md px-3 py-2 text-sm ${
                isActive
                  ? 'bg-brand-50 text-brand-700 font-medium'
                  : 'text-slate-700 hover:bg-slate-100'
              }`
            }
          >
            {item.label}
          </NavLink>
        ))}
      </nav>
    </aside>
  );
}
