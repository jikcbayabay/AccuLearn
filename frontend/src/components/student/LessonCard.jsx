import { Link } from 'react-router-dom';

export default function LessonCard({ lesson }) {
  return (
    <Link
      to={`/student/lessons/${lesson.id}`}
      className="block rounded-lg border bg-white p-4 transition hover:border-brand-500 hover:shadow"
    >
      <div className="text-xs uppercase text-slate-500">{lesson.type}</div>
      <h3 className="mt-1 text-base font-semibold text-slate-900">
        {lesson.title}
      </h3>
      {lesson.description && (
        <p className="mt-2 text-sm text-slate-600">{lesson.description}</p>
      )}
    </Link>
  );
}
