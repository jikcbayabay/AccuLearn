import React from 'react';
import {
  Button, Card, Loading, MasteryBadge, ProgressBar, Spinner, cls,
} from '../../components/common/UI.jsx';
import { PageHeader } from '../../components/layout/Shell.jsx';
import { api } from '../../mockData.js';

const QuizView = ({ ctx, onBack, onSeeFeedback }) => {
  const [quiz, setQuiz] = React.useState(null);
  const [answers, setAnswers] = React.useState({});
  const [submitting, setSubmitting] = React.useState(false);
  const [result, setResult] = React.useState(null);

  React.useEffect(() => { api.getQuiz(ctx.moduleId).then(setQuiz); }, [ctx.moduleId]);
  if (!quiz) return <Loading/>;

  const allAnswered = quiz.questions.every((_, i) => answers[i] !== undefined);

  const submit = () => {
    setSubmitting(true);
    api.submitQuiz(quiz.id, answers).then(r => {
      setSubmitting(false);
      setResult(r);
    });
  };

  if (result) {
    const pct = Math.round((result.score / result.total) * 100);
    const tone = pct >= 80 ? 'mastered' : pct >= 50 ? 'developing' : 'needs';
    return (
      <div>
        <PageHeader title="Quiz submitted"
                    breadcrumbs={[{ label: 'Modules', onClick: onBack }, { label: quiz.title }]}/>
        <Card className="p-8 max-w-2xl">
          <div className="text-[12px] uppercase tracking-wider text-ink-500 font-semibold">Your score</div>
          <div className="flex items-end gap-3 mt-1">
            <div className="text-6xl font-semibold tnum text-ink-900">{result.score}<span className="text-3xl text-ink-500">/{result.total}</span></div>
            <MasteryBadge level={tone}/>
          </div>
          <div className="mt-4"><ProgressBar value={pct} color={pct >= 80 ? 'green' : pct >= 50 ? 'amber' : 'red'} size="lg"/></div>
          <p className="text-ink-700 mt-5 leading-relaxed">
            Your responses have been sent to the AI tutor and your teacher.
            You can review detailed feedback now or come back later.
          </p>
          <div className="flex flex-wrap gap-2 mt-6">
            <Button variant="primary" onClick={onSeeFeedback}>See detailed feedback →</Button>
            <Button variant="secondary" onClick={onBack}>Back to modules</Button>
            <Button variant="ghost" onClick={() => { setResult(null); setAnswers({}); }}>Retake quiz</Button>
          </div>
        </Card>
      </div>
    );
  }

  return (
    <div>
      <PageHeader title={quiz.title}
                  subtitle={`${quiz.questions.length} questions · multiple choice`}
                  breadcrumbs={[{ label: 'Modules', onClick: onBack }, { label: quiz.title }]}
                  action={<Button variant="secondary" size="sm" onClick={onBack}>Cancel</Button>}/>

      <div className="grid lg:grid-cols-[1fr_280px] gap-6">
        <div className="space-y-4">
          {quiz.questions.map((q, i) => (
            <Card key={q.id} className="p-6">
              <div className="flex items-start gap-3">
                <div className="w-7 h-7 rounded-lg bg-brand-blue-50 text-brand-blue flex items-center justify-center text-[13px] font-bold shrink-0">{i+1}</div>
                <div className="flex-1">
                  <div className="text-[15px] font-medium text-ink-900 leading-relaxed">{q.prompt}</div>
                  <div className="grid gap-2 mt-4">
                    {q.options.map((opt, j) => {
                      const selected = answers[i] === j;
                      return (
                        <label key={j}
                          className={cls('flex items-start gap-3 p-3.5 rounded-xl border cursor-pointer transition',
                            selected ? 'border-brand-blue bg-brand-blue-50' : 'border-ink-200 hover:border-ink-300 bg-white')}>
                          <span className={cls('mt-0.5 w-5 h-5 rounded-full border flex items-center justify-center shrink-0 transition',
                            selected ? 'border-brand-blue bg-brand-blue' : 'border-ink-300 bg-white')}>
                            {selected && <span className="w-2 h-2 rounded-full bg-white"/>}
                          </span>
                          <input type="radio" className="hidden" name={q.id}
                                 checked={selected}
                                 onChange={() => setAnswers(a => ({ ...a, [i]: j }))}/>
                          <span className="text-[14px] text-ink-700">{opt}</span>
                        </label>
                      );
                    })}
                  </div>
                </div>
              </div>
            </Card>
          ))}
        </div>

        <Card className="p-5 self-start sticky top-4">
          <div className="text-[12px] uppercase tracking-wider text-ink-500 font-semibold">Progress</div>
          <div className="text-2xl font-semibold mt-1 tnum">
            {Object.keys(answers).length}<span className="text-ink-500">/{quiz.questions.length}</span>
          </div>
          <div className="mt-2"><ProgressBar value={Object.keys(answers).length / quiz.questions.length * 100} color="blue"/></div>
          <div className="grid grid-cols-5 gap-1.5 mt-4">
            {quiz.questions.map((_, i) => (
              <div key={i} className={cls('h-1.5 rounded-full', answers[i] !== undefined ? 'bg-brand-blue' : 'bg-ink-200')}/>
            ))}
          </div>
          <Button variant="success" className="w-full justify-center mt-5"
                  disabled={!allAnswered || submitting}
                  onClick={submit}>
            {submitting ? <><Spinner size={14}/>Submitting…</> : 'Submit quiz'}
          </Button>
          {!allAnswered && <p className="text-[12px] text-ink-500 mt-2 text-center">Answer all questions to submit.</p>}
        </Card>
      </div>
    </div>
  );
};

export default QuizView;
