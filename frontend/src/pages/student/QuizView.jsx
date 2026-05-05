import { useEffect, useState } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import Navbar from '../../components/common/Navbar.jsx';
import Spinner from '../../components/common/Spinner.jsx';
import Button from '../../components/common/Button.jsx';
import studentService from '../../services/studentService.js';

export default function QuizView() {
  const { quizId } = useParams();
  const navigate = useNavigate();
  const [quiz, setQuiz] = useState(null);
  const [answers, setAnswers] = useState({});
  const [submitting, setSubmitting] = useState(false);

  useEffect(() => {
    studentService.getQuiz(quizId).then(setQuiz);
  }, [quizId]);

  const handleSelect = (questionId, choiceId) => {
    setAnswers((prev) => ({ ...prev, [questionId]: choiceId }));
  };

  const handleSubmit = async () => {
    setSubmitting(true);
    const result = await studentService.submitQuiz(quizId, answers);
    setSubmitting(false);
    navigate(`/student/feedback/${result.competencyId}`);
  };

  if (!quiz) {
    return (
      <div className="min-h-screen bg-slate-50">
        <Navbar />
        <main className="p-6">
          <Spinner />
        </main>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-slate-50">
      <Navbar />
      <main className="mx-auto max-w-2xl p-6">
        <h1 className="text-2xl font-semibold">{quiz.title}</h1>

        <ol className="mt-6 space-y-6">
          {quiz.questions.map((q, idx) => (
            <li key={q.id} className="rounded-lg border bg-white p-4">
              <p className="font-medium">
                {idx + 1}. {q.text}
              </p>
              <div className="mt-3 space-y-2">
                {q.choices.map((c) => (
                  <label
                    key={c.id}
                    className="flex items-center gap-2 text-sm"
                  >
                    <input
                      type="radio"
                      name={`q-${q.id}`}
                      checked={answers[q.id] === c.id}
                      onChange={() => handleSelect(q.id, c.id)}
                    />
                    <span>{c.label}</span>
                  </label>
                ))}
              </div>
            </li>
          ))}
        </ol>

        <Button onClick={handleSubmit} disabled={submitting} className="mt-6">
          {submitting ? 'Submitting...' : 'Submit Quiz'}
        </Button>
      </main>
    </div>
  );
}
