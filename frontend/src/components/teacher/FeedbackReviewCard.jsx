export default function FeedbackReviewCard({ entry }) {
  return (
    <article className="rounded-lg border bg-white p-4">
      <header className="flex items-center justify-between">
        <div>
          <h3 className="text-sm font-semibold">{entry.studentName}</h3>
          <p className="text-xs text-slate-500">{entry.competencyTitle}</p>
        </div>
        <span className="text-xs text-slate-400">{entry.createdAt}</span>
      </header>
      <p className="mt-2 whitespace-pre-line text-sm text-slate-700">
        {entry.feedbackText}
      </p>
      <div className="mt-3 flex gap-3 text-xs text-slate-500">
        <span>GI: {entry.giScore}</span>
        <span>CMI: {entry.cmiScore}</span>
        <span>LP{entry.lpAssigned}</span>
      </div>
    </article>
  );
}
