import React from 'react';
import ReactMarkdown from 'react-markdown';
import remarkGfm from 'remark-gfm';
import Icon from '../../components/common/Icons.jsx';
import {
  Button, Card, Loading, Spinner, cls,
} from '../../components/common/UI.jsx';
import { PageHeader } from '../../components/layout/Shell.jsx';
import apiClient from '../../services/api.js';
import { resolveVideo } from '../../utils/video.js';
import '../../styles/lesson-content.css';

// ─── Lesson video player ─────────────────────────────────────────────────────
// Renders a real player for a `video`-type learning material. Supports direct
// files (mp4/webm/ogg), YouTube, and Vimeo. Renders nothing when there is no
// video material — so text-only lessons show reading content with no placeholder.

const LessonVideoPlayer = ({ material }) => {
  const resolved = resolveVideo(material?.content_url);
  if (!resolved) return null;

  return (
    <Card className="overflow-hidden">
      <div className="aspect-video bg-black relative">
        {resolved.kind === 'embed' ? (
          <iframe
            src={resolved.src}
            title={material.title || 'Lesson video'}
            className="absolute inset-0 w-full h-full"
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
            allowFullScreen
          />
        ) : (
          <video
            src={resolved.src}
            controls
            preload="metadata"
            className="absolute inset-0 w-full h-full"
          >
            Your browser does not support the video tag.
          </video>
        )}
      </div>
      {material.title && (
        <div className="px-4 py-2.5 text-[12.5px] text-ink-600 border-t border-ink-100 flex items-center gap-2">
          <Icon.Play size={13} className="text-brand-blue"/> {material.title}
        </div>
      )}
    </Card>
  );
};

// ─── Inline lesson quiz ──────────────────────────────────────────────────────

const LessonQuiz = ({ competencyId }) => {
  const [state, setState]     = React.useState('idle');   // idle | loading | ready | submitting | done | error
  const [quiz, setQuiz]       = React.useState(null);
  const [answers, setAnswers] = React.useState({});       // { question_id: answer_id }
  const [result, setResult]   = React.useState(null);

  const load = () => {
    setState('loading');
    apiClient.get(`/student/competencies/${competencyId}/lesson-quiz`)
      .then(res => {
        if (!res.data.quiz) { setState('error'); return; }
        setQuiz(res.data.quiz);
        setAnswers({});
        setResult(null);
        setState('ready');
      })
      .catch(() => setState('error'));
  };

  const submit = async () => {
    const payload = Object.entries(answers).map(([qid, aid]) => ({
      question_id: Number(qid),
      answer_id: Number(aid),
    }));
    setState('submitting');
    try {
      const res = await apiClient.post('/student/assessments/submit', {
        quiz_id: quiz.id,
        answers: payload,
      });
      setResult(res.data);
      setState('done');
    } catch {
      setState('error');
    }
  };

  const retry = () => {
    setAnswers({});
    setResult(null);
    setState('ready');
  };

  if (state === 'idle') {
    return (
      <Card className="mt-5 p-5 border-brand-blue/30 bg-brand-blue-50/40">
        <div className="flex items-center justify-between gap-4">
          <div>
            <div className="font-semibold text-ink-900 text-[14px]">Lesson Quiz available</div>
            <div className="text-[12.5px] text-ink-500 mt-0.5">
              Test your understanding of this lesson.
            </div>
          </div>
          <Button variant="primary" size="sm" onClick={load} icon={<Icon.Sparkle size={14}/>}>
            Take quiz
          </Button>
        </div>
      </Card>
    );
  }

  if (state === 'loading') {
    return (
      <Card className="mt-5 p-6 flex items-center justify-center gap-2 text-ink-500 text-[13px]">
        <Spinner size={15}/> Loading quiz…
      </Card>
    );
  }

  if (state === 'error') {
    return (
      <Card className="mt-5 p-5 border-mastery-needs/30 bg-rose-50">
        <div className="text-[13px] text-mastery-needs">Could not load lesson quiz. Try again later.</div>
      </Card>
    );
  }

  if (state === 'done' && result) {
    const passed = result.passed;
    return (
      <Card className={cls('mt-5 p-6', passed ? 'bg-brand-green-50 border-brand-green/20' : 'bg-rose-50 border-mastery-needs/20')}>
        <div className={cls('flex items-center gap-2 font-semibold text-[15px]', passed ? 'text-brand-green-700' : 'text-mastery-needs')}>
          {passed ? <Icon.Check size={18}/> : <Icon.AlertTri size={18}/>}
          {passed ? 'Quiz passed!' : 'Not quite — keep studying!'}
        </div>
        <div className="mt-3 flex items-center gap-6">
          <div>
            <div className="text-[11px] uppercase tracking-wider text-ink-500 font-semibold">Score</div>
            <div className={cls('text-3xl font-bold tnum', passed ? 'text-brand-green-700' : 'text-mastery-needs')}>
              {result.score}%
            </div>
          </div>
          <div>
            <div className="text-[11px] uppercase tracking-wider text-ink-500 font-semibold">Correct</div>
            <div className="text-3xl font-bold tnum text-ink-800">
              {result.correct_count}/{result.total_questions}
            </div>
          </div>
        </div>

        <div className="mt-4 space-y-2">
          {quiz.questions.map((q, qi) => {
            const r = result.results.find(r => r.question_id === q.question_id);
            return (
              <div key={q.question_id} className={cls(
                'rounded-xl px-4 py-3 text-[13px]',
                r?.is_correct ? 'bg-brand-green-50 border border-brand-green/20' : 'bg-rose-50 border border-mastery-needs/20'
              )}>
                <div className="flex items-start gap-2">
                  {r?.is_correct
                    ? <Icon.Check size={13} className="text-brand-green-700 mt-0.5 shrink-0"/>
                    : <Icon.AlertTri size={13} className="text-mastery-needs mt-0.5 shrink-0"/>}
                  <span className={cls('font-medium', r?.is_correct ? 'text-brand-green-800' : 'text-mastery-needs')}>
                    Q{qi + 1}. {q.question_text}
                  </span>
                </div>
                {!r?.is_correct && (
                  <div className="mt-1.5 ml-5 text-[12px] text-ink-600">
                    Your answer: <span className="font-medium">
                      {q.answers.find(a => a.answer_id === r?.answer_id)?.answer_text ?? '—'}
                    </span>
                  </div>
                )}
              </div>
            );
          })}
        </div>

        {!passed && (
          <Button variant="secondary" size="sm" className="mt-4" onClick={retry} icon={<Icon.Spark size={13}/>}>
            Retake quiz
          </Button>
        )}
      </Card>
    );
  }

  // ready / submitting
  const allAnswered = quiz.questions.length > 0 &&
    quiz.questions.every(q => answers[q.question_id] !== undefined);

  return (
    <Card className="mt-5 p-6">
      <div className="flex items-center justify-between mb-5">
        <div>
          <div className="font-semibold text-ink-900 text-[15px]">{quiz.title}</div>
          <div className="text-[12.5px] text-ink-500 mt-0.5">
            {quiz.questions.length} questions · passing score {quiz.passing_score}%
          </div>
        </div>
        <div className="text-[13px] text-ink-500 tnum font-medium">
          {Object.keys(answers).length}/{quiz.questions.length} answered
        </div>
      </div>

      <div className="space-y-6">
        {quiz.questions.map((q, qi) => (
          <div key={q.question_id}>
            <div className="text-[14px] font-medium text-ink-900 mb-3">
              {qi + 1}. {q.question_text}
            </div>
            <div className="space-y-2">
              {q.answers.map(a => {
                const selected = answers[q.question_id] === a.answer_id;
                return (
                  <button
                    key={a.answer_id}
                    onClick={() => setAnswers(prev => ({ ...prev, [q.question_id]: a.answer_id }))}
                    className={cls(
                      'w-full text-left px-4 py-3 rounded-xl border text-[13.5px] transition',
                      selected
                        ? 'border-brand-blue bg-brand-blue-50 text-brand-blue font-medium'
                        : 'border-ink-200 hover:border-brand-blue/50 hover:bg-ink-50 text-ink-700'
                    )}>
                    {a.answer_text}
                  </button>
                );
              })}
            </div>
          </div>
        ))}
      </div>

      <div className="flex items-center justify-between mt-6 pt-5 border-t border-ink-200">
        <Button variant="ghost" size="sm" onClick={() => setState('idle')}>Cancel</Button>
        <Button variant="primary" disabled={!allAnswered || state === 'submitting'}
                onClick={submit}
                icon={state === 'submitting' ? <Spinner size={13}/> : <Icon.Check size={14}/>}>
          {state === 'submitting' ? 'Submitting…' : 'Submit answers'}
        </Button>
      </div>
    </Card>
  );
};

// ─── Main LessonView ─────────────────────────────────────────────────────────

const LessonView = ({ ctx, onBack, onOpenQuiz }) => {
  const [lessons, setLessons]     = React.useState(null);
  const [active, setActive]       = React.useState(null);
  const [completed, setCompleted] = React.useState({});
  const [saving, setSaving]       = React.useState(null);

  React.useEffect(() => {
    Promise.all([
      apiClient.get(`/student/modules/${ctx.moduleId}`),
      apiClient.get(`/student/modules/${ctx.moduleId}/completions`),
    ]).then(([modRes, compRes]) => {
      const mod = modRes.data.module;
      const ls = (mod.competencies || []).map(c => ({
        id: c.id,
        title: c.title,
        duration: '~15 min',
        body: c.learning_materials?.find(m => m.type === 'text')?.body ?? null,
        video: c.learning_materials?.find(m => m.type === 'video') ?? null,
      }));
      setLessons(ls);
      setActive(ls[0]?.id ?? null);

      const init = {};
      for (const id of (compRes.data.completed_ids ?? [])) {
        init[id] = true;
      }
      setCompleted(init);
    }).catch(() => setLessons([]));
  }, [ctx.moduleId]);

  const markComplete = async (lessonId) => {
    setCompleted(c => ({ ...c, [lessonId]: true }));
    setSaving(lessonId);
    try {
      await apiClient.post(`/student/competencies/${lessonId}/complete`);
    } catch {
      setCompleted(c => { const n = { ...c }; delete n[lessonId]; return n; });
    } finally {
      setSaving(null);
    }
  };

  if (!lessons) return <Loading/>;
  if (!lessons.length) return <div className="p-8 text-ink-500">No lessons found for this module.</div>;

  const lesson          = lessons.find(l => l.id === active) ?? lessons[0];
  const idx             = lessons.findIndex(l => l.id === lesson.id);
  const prev            = lessons[idx - 1];
  const next            = lessons[idx + 1];
  const completedCount  = lessons.filter(l => completed[l.id]).length;
  const allComplete     = completedCount === lessons.length && lessons.length > 0;

  return (
    <div>
      <PageHeader
        title={lesson.title}
        breadcrumbs={[
          { label: 'Modules', onClick: onBack },
          { label: `Lesson ${idx + 1}` },
        ]}
        action={<Button variant="secondary" size="sm" onClick={onBack} icon={<Icon.ChevL size={14}/>}>Back to modules</Button>}
      />

      <div className="grid lg:grid-cols-[1fr_280px] gap-6">
        <div>
          {/* Lesson video — renders only when a video material exists */}
          <LessonVideoPlayer material={lesson.video}/>

          {/* Reading content */}
          <Card className={cls('p-6', lesson.video ? 'mt-5' : '')}>
            <div className="text-[12px] uppercase tracking-wider text-ink-500 font-semibold mb-4">Reading</div>
            {lesson.body ? (
              <div className="lesson-body">
                <ReactMarkdown remarkPlugins={[remarkGfm]}>
                  {lesson.body}
                </ReactMarkdown>
              </div>
            ) : (
              <p className="text-ink-500 italic text-[14px]">No lesson content available.</p>
            )}
          </Card>

          {/* Complete / completed banner + lesson quiz */}
          {!completed[lesson.id] ? (
            <Card className="mt-5 p-5 border-brand-blue/20">
              <div className="flex items-center justify-between gap-4">
                <p className="text-[13.5px] text-ink-600">
                  Read through the lesson above, then mark it complete to track your progress.
                </p>
                <Button variant="primary" size="sm"
                        disabled={saving === lesson.id}
                        onClick={() => markComplete(lesson.id)}>
                  {saving === lesson.id
                    ? <><Spinner size={13}/>Saving…</>
                    : <><Icon.Check size={14}/>Mark complete</>}
                </Button>
              </div>
            </Card>
          ) : (
            <>
              <Card className="mt-5 p-4 bg-brand-green-50 border-brand-green/20">
                <div className="flex items-center gap-2 text-brand-green-700 font-semibold text-[13.5px]">
                  <Icon.Check size={15}/> Lesson complete — progress saved
                </div>
              </Card>
              <LessonQuiz key={lesson.id} competencyId={lesson.id}/>
            </>
          )}

          {/* Lesson nav */}
          <div className="flex flex-wrap items-center justify-between gap-3 mt-5">
            <Button variant="secondary" size="md" disabled={!prev}
                    icon={<Icon.ChevL size={14}/>}
                    onClick={() => prev && setActive(prev.id)}>
              {prev ? prev.title : 'Previous'}
            </Button>
            <Button variant="primary" disabled={!next}
                    onClick={() => next && setActive(next.id)}>
              {next ? 'Next lesson' : 'Last lesson'} <Icon.Chevron size={14}/>
            </Button>
          </div>
        </div>

        {/* Sidebar */}
        <Card className="p-3 self-start sticky top-4">
          <div className="px-2 py-2 text-[12px] uppercase tracking-wider text-ink-500 font-semibold">
            Module lessons
          </div>
          <div className="space-y-1">
            {lessons.map((l, i) => {
              const isActive = l.id === lesson.id;
              const done     = !!completed[l.id];
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
                    <div className="text-[11.5px] text-ink-500 mt-0.5">{l.duration}</div>
                  </div>
                </button>
              );
            })}
          </div>

          {/* Module quiz section */}
          <div className="border-t border-ink-200 mt-2 pt-3 px-2">
            <div className="text-[11.5px] uppercase tracking-wider text-ink-500 font-semibold mb-2">Module quiz</div>
            <div className="text-[12px] text-ink-500 mb-3">
              Lessons complete: <span className="font-semibold text-ink-700">{completedCount}/{lessons.length}</span>
            </div>
            {allComplete ? (
              <button onClick={() => onOpenQuiz(ctx.moduleId)}
                className="w-full px-3 py-2.5 rounded-xl bg-brand-green text-white text-[13px] font-semibold hover:bg-brand-green-700 transition flex items-center justify-center gap-2">
                <Icon.Check size={14}/> Take module quiz
              </button>
            ) : (
              <div className="px-3 py-2.5 rounded-xl bg-ink-100 text-ink-400 text-[12.5px] font-medium flex items-center justify-center gap-2 cursor-not-allowed select-none">
                <Icon.Lock size={13}/> Complete all lessons to unlock
              </div>
            )}
          </div>
        </Card>
      </div>
    </div>
  );
};

export default LessonView;
