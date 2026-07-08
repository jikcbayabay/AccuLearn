import React from 'react';
import {
  Button, Card, MasteryBadge, ProgressBar, Spinner, cls,
} from '../../components/common/UI.jsx';
import Icon from '../../components/common/Icons.jsx';
import { PageHeader } from '../../components/layout/Shell.jsx';
import apiClient from '../../services/api.js';

// Status: 'loading' | 'unavailable' | 'ready' | 'submitted'

const QuizView = ({ ctx, onBack, onSeeFeedback }) => {
  const [status, setStatus] = React.useState('loading');
  const [quiz, setQuiz]     = React.useState(null);
  const [answers, setAnswers] = React.useState({});
  const [submitting, setSubmitting] = React.useState(false);
  const [result, setResult] = React.useState(null);

  const load = React.useCallback(() => {
    setStatus('loading');
    setQuiz(null);
    apiClient
      .get(`/student/modules/${ctx.moduleId}/quiz`)
      .then(res => {
        if (res.data?.quiz?.questions?.length) {
          setQuiz(res.data.quiz);
          setStatus('ready');
        } else {
          setStatus('unavailable');
        }
      })
      .catch(err => {
        console.error('Quiz fetch failed:', err);
        setStatus('unavailable');
      });
  }, [ctx.moduleId]);

  React.useEffect(() => { load(); }, [load]);

  const submit = () => {
    setSubmitting(true);
    const payload = quiz.questions.map((q, i) => ({
      question_id: q.question_id,
      answer_id:   answers[i],
    }));
    apiClient
      .post(`/student/assessments/submit`, { quiz_id: quiz.id, answers: payload })
      .then(res => {
        setSubmitting(false);
        setResult(res.data);
      })
      .catch(() => {
        setSubmitting(false);
        setResult(null);
        alert('Submission failed. Please try again.');
      });
  };

  /* ── Loading ─────────────────────────────────────────────────── */
  if (status === 'loading') {
    return (
      <div>
        <PageHeader title="Module quiz"
                    breadcrumbs={[{ label: 'Modules', onClick: onBack }, { label: 'Quiz' }]}/>
        <Card className="p-12 flex flex-col items-center gap-3 text-ink-500">
          <Spinner size={24}/>
          <span className="text-[14px]">Loading quiz…</span>
        </Card>
      </div>
    );
  }

  /* ── Unavailable ─────────────────────────────────────────────── */
  if (status === 'unavailable') {
    return (
      <div>
        <PageHeader title="Module quiz"
                    breadcrumbs={[{ label: 'Modules', onClick: onBack }, { label: 'Quiz' }]}
                    action={<Button variant="secondary" size="sm" onClick={onBack} icon={<Icon.ChevL size={14}/>}>Back</Button>}/>
        <Card className="p-12 flex flex-col items-center text-center max-w-md mx-auto">
          <div className="w-14 h-14 rounded-full bg-amber-50 flex items-center justify-center mb-4">
            <Icon.Lock size={24} className="text-amber-500"/>
          </div>
          <h3 className="text-[17px] font-semibold text-ink-900">Quiz not available yet</h3>
          <p className="text-[13.5px] text-ink-500 mt-2 leading-relaxed">
            The quiz for this module hasn't been set up yet. Check back later or contact your teacher.
          </p>
          <div className="flex gap-2 mt-6">
            <Button variant="primary" onClick={load}>
              Try again
            </Button>
            <Button variant="secondary" onClick={onBack}>Back to modules</Button>
          </div>
        </Card>
      </div>
    );
  }

  /* ── Result screen ───────────────────────────────────────────── */
  if (result) {
    const pct  = result.score ?? 0;
    const tone = pct >= 80 ? 'mastered' : pct >= 50 ? 'developing' : 'needs';
    return (
      <div>
        <PageHeader title="Quiz submitted"
                    breadcrumbs={[{ label: 'Modules', onClick: onBack }, { label: quiz.title }]}/>
        <Card className="p-8 max-w-2xl">
          <div className="text-[12px] uppercase tracking-wider text-ink-500 font-semibold">Your score</div>
          <div className="flex items-end gap-3 mt-1">
            <div className="text-6xl font-semibold tnum text-ink-900">
              {result.correct_count}
              <span className="text-3xl text-ink-500">/{result.total_questions}</span>
            </div>
            <MasteryBadge level={tone}/>
          </div>
          <div className="mt-4">
            <ProgressBar value={pct} color={pct >= 80 ? 'green' : pct >= 50 ? 'amber' : 'red'} size="lg"/>
          </div>
          <p className="text-ink-700 mt-5 leading-relaxed">
            Your responses have been sent to your teacher.
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

  /* ── Active quiz ─────────────────────────────────────────────── */
  const allAnswered = quiz.questions.every((_, i) => answers[i] !== undefined);

  return (
    <div>
      <PageHeader title={quiz.title}
                  subtitle={`${quiz.questions.length} questions · multiple choice`}
                  breadcrumbs={[{ label: 'Modules', onClick: onBack }, { label: quiz.title }]}
                  action={<Button variant="secondary" size="sm" onClick={onBack}>Cancel</Button>}/>

      <div className="grid lg:grid-cols-[1fr_280px] gap-6">
        <div className="space-y-4">
          {quiz.questions.map((q, i) => (
            <Card key={q.question_id} className="p-6">
              <div className="flex items-start gap-3">
                <div className="w-7 h-7 rounded-lg bg-brand-blue-50 text-brand-blue flex items-center justify-center text-[13px] font-bold shrink-0">
                  {i + 1}
                </div>
                <div className="flex-1">
                  <div className="text-[15px] font-medium text-ink-900 leading-relaxed">{q.question_text}</div>
                  <div className="grid gap-2 mt-4">
                    {q.answers.map((opt) => {
                      const selected = answers[i] === opt.answer_id;
                      return (
                        <label key={opt.answer_id}
                          className={cls('flex items-start gap-3 p-3.5 rounded-xl border cursor-pointer transition',
                            selected ? 'border-brand-blue bg-brand-blue-50' : 'border-ink-200 hover:border-ink-300 bg-white')}>
                          <span className={cls('mt-0.5 w-5 h-5 rounded-full border flex items-center justify-center shrink-0 transition',
                            selected ? 'border-brand-blue bg-brand-blue' : 'border-ink-300 bg-white')}>
                            {selected && <span className="w-2 h-2 rounded-full bg-white"/>}
                          </span>
                          <input type="radio" className="hidden" name={`q${i}`}
                                 checked={selected}
                                 onChange={() => setAnswers(a => ({ ...a, [i]: opt.answer_id }))}/>
                          <span className="text-[14px] text-ink-700">{opt.answer_text}</span>
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
          <div className="mt-2">
            <ProgressBar value={Object.keys(answers).length / quiz.questions.length * 100} color="blue"/>
          </div>
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
          {!allAnswered && (
            <p className="text-[12px] text-ink-500 mt-2 text-center">Answer all questions to submit.</p>
          )}
        </Card>
      </div>
    </div>
  );
};

export default QuizView;
