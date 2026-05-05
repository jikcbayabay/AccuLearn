import { useEffect, useState } from 'react';
import { useParams } from 'react-router-dom';
import Navbar from '../../components/common/Navbar.jsx';
import Spinner from '../../components/common/Spinner.jsx';
import studentService from '../../services/studentService.js';

export default function LessonView() {
  const { lessonId } = useParams();
  const [lesson, setLesson] = useState(null);

  useEffect(() => {
    studentService.getLesson(lessonId).then(setLesson);
  }, [lessonId]);

  return (
    <div className="min-h-screen bg-slate-50">
      <Navbar />
      <main className="mx-auto max-w-3xl p-6">
        {!lesson ? (
          <Spinner />
        ) : (
          <article>
            <div className="text-xs uppercase text-slate-500">{lesson.type}</div>
            <h1 className="mt-1 text-2xl font-semibold">{lesson.title}</h1>
            <div className="prose prose-slate mt-4 max-w-none">
              <p>{lesson.content}</p>
            </div>
          </article>
        )}
      </main>
    </div>
  );
}
