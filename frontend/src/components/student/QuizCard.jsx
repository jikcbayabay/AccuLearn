import { Link } from 'react-router-dom';

export default function QuizCard({ quiz }) {
  return (
    <Link
      to={`/student/quizzes/${quiz.id}`}
      className="block rounded-lg border bg-white p-4 transition hover:border-brand-500 hover:shadow"
    >
      <h3 className="text-base font-semibold">{quiz.title}</h3>
      <div className="mt-1 text-sm text-slate-600">
        Passing score: {quiz.passingScore}%
      </div>
    </Link>
  );
}
