import React from 'react';
import Icon from '../../components/common/Icons.jsx';
import {
  Badge, Button, Card, Loading, ProgressBar, cls,
} from '../../components/common/UI.jsx';
import { PageHeader } from '../../components/layout/Shell.jsx';
import { api, FORMATIVES } from '../../mockData.js';

const LessonView = ({ ctx, onBack, onOpenQuiz }) => {
  const [lessons, setLessons] = React.useState(null);
  const [active, setActive] = React.useState(ctx.lessonId || 'l5');
  const [completed, setCompleted] = React.useState({});
  // formative quiz state per lesson: { [lessonId]: { passed: bool, score, total, attempts } }
  const [formativeResults, setFormativeResults] = React.useState({});
  // current formative answer state
  const [formAnswers, setFormAnswers] = React.useState({});
  const [formSubmitted, setFormSubmitted] = React.useState(false);

  React.useEffect(() => {
    api.getLessons(ctx.moduleId).then(ls => {
      setLessons(ls);
      const map = {}; ls.forEach(l => { if (l.completed) map[l.id] = true; });
      setCompleted(map);
      // seed formative passes for already-completed lessons (demo)
      const seed = {};
      ls.forEach(l => {
        if (l.completed) {
          const fq = FORMATIVES[l.id];
          if (fq) seed[l.id] = { passed: true, score: fq.questions.length, total: fq.questions.length, attempts: 1 };
        }
      });
      setFormativeResults(seed);
    });
  }, [ctx.moduleId]);

  // reset formative state when lesson changes
  React.useEffect(() => { setFormAnswers({}); setFormSubmitted(false); }, [active]);

  if (!lessons) return <Loading/>;
  const lesson = lessons.find(l => l.id === active) || lessons[0];
  const idx = lessons.findIndex(l => l.id === lesson.id);
  const prev = lessons[idx - 1];
  const next = lessons[idx + 1];

  const fq = FORMATIVES[lesson.id];
  const fResult = formativeResults[lesson.id];
  const passedAllFormatives = lessons.every(l => {
    const r = formativeResults[l.id];
    return r && r.passed;
  });
  const passedCount = lessons.filter(l => formativeResults[l.id]?.passed).length;

  const submitFormative = () => {
    if (!fq) return;
    setFormSubmitted(true);
    let correct = 0;
    fq.questions.forEach((q, i) => { if (formAnswers[i] === q.answer) correct++; });
    const passed = correct / fq.questions.length >= fq.passing;
    setFormativeResults(r => ({
      ...r,
      [lesson.id]: {
        passed,
        score: correct,
        total: fq.questions.length,
        attempts: (r[lesson.id]?.attempts || 0) + 1,
      },
    }));
    if (passed) setCompleted(c => ({ ...c, [lesson.id]: true }));
  };

  const retryFormative = () => { setFormAnswers({}); setFormSubmitted(false); };

  const goToModuleQuiz = () => {
    if (passedAllFormatives) onOpenQuiz(ctx.moduleId);
  };

  return (
    <div>
      <PageHeader
        title={lesson.title}
        breadcrumbs={[
          { label: 'Modules', onClick: onBack },
          { label: 'Fundamentals of Accountancy I', onClick: onBack },
          { label: `Lesson ${idx + 1}` },
        ]}
        action={<Button variant="secondary" size="sm" onClick={onBack} icon={<Icon.ChevL size={14}/>}>Back to modules</Button>}
      />

      <div className="grid lg:grid-cols-[1fr_280px] gap-6">
        <div>
          {/* Video / content placeholder */}
          <Card className="overflow-hidden">
            <div className="aspect-video ph-stripes relative flex items-center justify-center">
              <button className="w-16 h-16 rounded-full bg-white/95 shadow-pop flex items-center justify-center text-brand-blue hover:scale-105 transition">
                <Icon.Play size={26}/>
              </button>
              <div className="absolute bottom-3 left-4 text-[12px] text-ink-700 font-mono bg-white/85 px-2 py-1 rounded-md">
                video · {lesson.duration} · placeholder
              </div>
            </div>
          </Card>

          <Card className="mt-5 p-6">
            <div className="text-[12px] uppercase tracking-wider text-ink-500 font-semibold mb-2">Reading</div>
            <h3 className="text-[18px] font-semibold text-ink-900 mb-3">{lesson.title}</h3>
            <pre className="whitespace-pre-wrap font-sans text-[14.5px] leading-[1.7] text-ink-700">
{lesson.body || `This is a placeholder lesson body. In a real lesson, you'd find a short video, a written explanation, an interactive example, and a few practice questions to check your understanding before continuing.

Pass the formative check below to mark this lesson complete and unlock the next one.`}
            </pre>
          </Card>

          {/* Formative quiz */}
          {fq && (
            <Card className="mt-5 p-6 border-brand-blue/20">
              <div className="flex items-start justify-between gap-3 mb-4">
                <div>
                  <div className="flex items-center gap-2 mb-1">
                    <Badge tone="blue">Formative check</Badge>
                    {fResult?.passed && <Badge tone="completed"><Icon.Check size={11}/>Passed</Badge>}
                    {fResult && !fResult.passed && <Badge tone="needs">Not yet passed</Badge>}
                  </div>
                  <h3 className="text-[16px] font-semibold text-ink-900">{fq.title}</h3>
                  <p className="text-[13px] text-ink-500 mt-0.5">
                    Quick {fq.questions.length}-question check. Pass at {Math.round(fq.passing * 100)}% to complete the lesson and unlock the module quiz.
                  </p>
                </div>
              </div>

              <div className="space-y-4">
                {fq.questions.map((q, i) => (
                  <div key={q.id}>
                    <div className="text-[14px] font-medium text-ink-900 mb-2 flex items-start gap-2">
                      <span className="w-6 h-6 rounded-md bg-brand-blue-50 text-brand-blue text-[12px] font-bold flex items-center justify-center shrink-0">{i+1}</span>
                      <span>{q.prompt}</span>
                    </div>
                    <div className="grid gap-1.5 ml-8">
                      {q.options.map((opt, j) => {
                        const selected = formAnswers[i] === j;
                        const correct = formSubmitted && j === q.answer;
                        const wrong = formSubmitted && selected && j !== q.answer;
                        return (
                          <label key={j}
                            className={cls('flex items-start gap-2.5 p-2.5 rounded-lg border cursor-pointer transition text-[13.5px]',
                              correct ? 'border-brand-green bg-brand-green-50 text-brand-green-700' :
                              wrong ? 'border-mastery-needs bg-rose-50 text-mastery-needs' :
                              selected ? 'border-brand-blue bg-brand-blue-50' :
                              'border-ink-200 hover:border-ink-300 bg-white')}>
                            <span className={cls('mt-0.5 w-4 h-4 rounded-full border flex items-center justify-center shrink-0',
                              selected ? 'border-brand-blue bg-brand-blue' : 'border-ink-300 bg-white')}>
                              {selected && <span className="w-1.5 h-1.5 rounded-full bg-white"/>}
                            </span>
                            <input type="radio" className="hidden" name={`f-${q.id}`}
                                   disabled={formSubmitted}
                                   checked={selected}
                                   onChange={() => setFormAnswers(a => ({ ...a, [i]: j }))}/>
                            <span>{opt}</span>
                          </label>
                        );
                      })}
                    </div>
                  </div>
                ))}
              </div>

              <div className="flex flex-wrap items-center gap-3 mt-5 pt-4 border-t border-ink-200">
                {!formSubmitted ? (
                  <Button variant="primary" size="sm"
                          disabled={Object.keys(formAnswers).length !== fq.questions.length}
                          onClick={submitFormative}>
                    Submit check
                  </Button>
                ) : (
                  <>
                    <div className={cls('text-[14px] font-semibold flex items-center gap-2',
                      fResult?.passed ? 'text-brand-green-700' : 'text-mastery-needs')}>
                      {fResult?.passed ? <Icon.Check size={16}/> : <Icon.AlertTri size={16}/>}
                      {fResult?.passed
                        ? `Passed — ${fResult.score}/${fResult.total}. Lesson marked complete.`
                        : `Not yet — ${fResult.score}/${fResult.total}. Review the lesson and try again.`}
                    </div>
                    <div className="ml-auto flex gap-2">
                      <Button variant="secondary" size="sm" onClick={retryFormative}>Try again</Button>
                      {fResult?.passed && next && (
                        <Button variant="primary" size="sm" onClick={() => setActive(next.id)}>
                          Next lesson <Icon.Chevron size={14}/>
                        </Button>
                      )}
                    </div>
                  </>
                )}
              </div>
            </Card>
          )}

          {/* Lesson controls */}
          <div className="flex flex-wrap items-center justify-between gap-3 mt-5">
            <Button variant="secondary" size="md" disabled={!prev}
                    icon={<Icon.ChevL size={14}/>}
                    onClick={() => prev && setActive(prev.id)}>
              {prev ? prev.title : 'Previous'}
            </Button>
            <div className="flex gap-2">
              <Button variant="primary" disabled={!next}
                      onClick={() => next && setActive(next.id)}>
                {next ? 'Next lesson' : 'Last lesson'} <Icon.Chevron size={14}/>
              </Button>
            </div>
          </div>
        </div>

        {/* Lesson list sidebar */}
        <Card className="p-3 self-start sticky top-4">
          <div className="px-2 py-2 text-[12px] uppercase tracking-wider text-ink-500 font-semibold">
            Module lessons
          </div>
          <div className="space-y-1">
            {lessons.map((l, i) => {
              const isActive = l.id === lesson.id;
              const r = formativeResults[l.id];
              const done = !!r?.passed || completed[l.id];
              return (
                <button key={l.id} onClick={() => setActive(l.id)}
                  className={cls('w-full text-left px-2.5 py-2 rounded-lg flex items-start gap-2.5 transition',
                    isActive ? 'bg-brand-blue-50 text-brand-blue' : 'hover:bg-ink-50 text-ink-700')}>
                  <div className={cls('w-5 h-5 mt-0.5 rounded-full flex items-center justify-center text-[10px] font-bold shrink-0',
                    done ? 'bg-brand-green text-white' : isActive ? 'bg-brand-blue text-white' : 'bg-ink-200 text-ink-500')}>
                    {done ? <Icon.Check size={11}/> : i + 1}
                  </div>
                  <div className="min-w-0 flex-1">
                    <div className="text-[13.5px] font-medium leading-tight">{l.title}</div>
                    <div className="text-[11.5px] text-ink-500 mt-0.5 flex items-center gap-1.5">
                      <span>{l.duration}</span>
                      {r && (
                        <>
                          <span>·</span>
                          <span className={cls(r.passed ? 'text-brand-green-700' : 'text-mastery-needs', 'font-semibold')}>
                            {r.passed ? 'Check passed' : 'Retake check'}
                          </span>
                        </>
                      )}
                    </div>
                  </div>
                </button>
              );
            })}
          </div>
          <div className="border-t border-ink-200 mt-2 pt-3 px-2">
            <div className="text-[11.5px] uppercase tracking-wider text-ink-500 font-semibold mb-2">Module quiz</div>
            <div className="flex items-center justify-between text-[12px] mb-1.5">
              <span className="text-ink-500">Checks passed</span>
              <span className="font-semibold tnum">{passedCount}/{lessons.length}</span>
            </div>
            <ProgressBar value={(passedCount / lessons.length) * 100} color={passedAllFormatives ? 'green' : 'blue'}/>
            {passedAllFormatives ? (
              <button onClick={goToModuleQuiz}
                className="w-full mt-3 px-3 py-2.5 rounded-xl bg-brand-green text-white text-[13px] font-semibold hover:bg-brand-green-700 transition flex items-center justify-center gap-2">
                <Icon.Check size={14}/> Take module quiz
              </button>
            ) : (
              <div className="mt-3 px-3 py-2.5 rounded-xl bg-ink-100 text-ink-500 text-[12.5px] font-medium flex items-center justify-center gap-2 cursor-not-allowed"
                   title="Pass every lesson check to unlock">
                <Icon.Lock size={13}/> Locked — pass all checks
              </div>
            )}
          </div>
        </Card>
      </div>
    </div>
  );
};

export default LessonView;
