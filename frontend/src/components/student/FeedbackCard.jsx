export default function FeedbackCard({ feedback }) {
  return (
    <article className="rounded-lg border bg-white p-4">
      <div className="text-xs uppercase text-slate-500">AI Feedback</div>
      <h3 className="mt-1 text-base font-semibold">{feedback.competencyTitle}</h3>
      <p className="mt-2 whitespace-pre-line text-sm text-slate-700">
        {feedback.text}
      </p>
      {feedback.errorPattern && (
        <div className="mt-3 text-xs text-slate-500">
          Pattern: {feedback.errorPattern}
        </div>
      )}
    </article>
  );
}
