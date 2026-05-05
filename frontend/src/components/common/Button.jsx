export default function Button({
  children,
  type = 'button',
  variant = 'primary',
  disabled = false,
  onClick,
  className = '',
}) {
  const base =
    'inline-flex items-center justify-center px-4 py-2 rounded-md text-sm font-medium transition disabled:opacity-50 disabled:cursor-not-allowed';
  const variants = {
    primary: 'bg-brand-500 text-white hover:bg-brand-600',
    secondary: 'bg-slate-200 text-slate-800 hover:bg-slate-300',
    danger: 'bg-red-500 text-white hover:bg-red-600',
    ghost: 'bg-transparent text-slate-700 hover:bg-slate-100',
  };

  return (
    <button
      type={type}
      onClick={onClick}
      disabled={disabled}
      className={`${base} ${variants[variant] || variants.primary} ${className}`}
    >
      {children}
    </button>
  );
}
