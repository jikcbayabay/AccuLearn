export default function Spinner({ label = 'Loading...' }) {
  return (
    <div className="flex items-center gap-2 text-slate-500" role="status">
      <span className="h-4 w-4 animate-spin rounded-full border-2 border-slate-300 border-t-brand-500" />
      <span className="text-sm">{label}</span>
    </div>
  );
}
