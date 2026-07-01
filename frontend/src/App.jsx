// Top-level App: routes pages by user.role + current key, manages context state for lesson/quiz

import React from 'react';
import Shell from './components/layout/Shell.jsx';
import { EmptyState, Spinner } from './components/common/UI.jsx';
import LoginPage from './pages/auth/Login.jsx';

import StudentDashboard from './pages/student/Dashboard.jsx';
import ModulesPage from './pages/student/Modules.jsx';
import LessonView from './pages/student/LessonView.jsx';
import QuizView from './pages/student/QuizView.jsx';
import FeedbackView from './pages/student/FeedbackView.jsx';
import ProgressPage from './pages/student/Progress.jsx';

import TeacherDashboard from './pages/teacher/Dashboard.jsx';
import StudentProgressPage from './pages/teacher/StudentProgress.jsx';
import FeedbackReviewPage from './pages/teacher/FeedbackReview.jsx';

import AdminDashboard from './pages/admin/Dashboard.jsx';
import UserManagement from './pages/admin/UserManagement.jsx';
import ModuleManagement from './pages/admin/ModuleManagement.jsx';
import SystemLogs from './pages/admin/SystemLogs.jsx';

import SettingsPage from './pages/settings/Settings.jsx';

import { useAuth } from './context/AuthContext.jsx';

const App = () => {
  const { user, logout, isLoading } = useAuth();
  const [page, setPage] = React.useState('dashboard');
  const [lessonCtx, setLessonCtx] = React.useState({ moduleId: null });
  const [quizCtx, setQuizCtx]   = React.useState({ moduleId: null });

  const [courses, setCourses] = React.useState([]);

  React.useEffect(() => {
    fetch('http://localhost:8080/api/moodle/courses')
      .then((res) => res.json())
      .then((data) => {
        console.log('Moodle Courses:', data);
        setCourses(data);
      })
      .catch((err) => console.error(err));
  }, []);

  const onLogin = () => { setPage('dashboard'); };

  const onLogout = async () => {
    try {
      await logout();
    } finally {
      setPage('dashboard');
    }
  };

  if (isLoading) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-ink-50">
        <Spinner size={32}/>
      </div>
    );
  }

  if (!user) return <LoginPage onLogin={onLogin}/>;

  // Page routing per role
  let body = null;
  if (page === 'settings') {
    body = <SettingsPage user={user}/>;
  } else if (user.role === 'student') {
    if (page === 'dashboard') body = <StudentDashboard user={user} onNavigate={setPage}
                                                       setLessonCtx={setLessonCtx}/>;
    else if (page === 'modules')  body = <ModulesPage
                                          onOpenLesson={(id) => { setLessonCtx({ moduleId: id }); setPage('lesson'); }}
                                          onOpenQuiz={(id)   => { setQuizCtx({ moduleId: id }); setPage('quiz'); }}/>;
    else if (page === 'lesson')   body = <LessonView ctx={lessonCtx}
                                          onBack={() => setPage('modules')}
                                          onOpenQuiz={(id) => { setQuizCtx({ moduleId: id }); setPage('quiz'); }}/>;
    else if (page === 'quiz')     body = <QuizView ctx={quizCtx}
                                          onBack={() => setPage('modules')}
                                          onSeeFeedback={() => setPage('feedback')}/>;
    else if (page === 'feedback') body = <FeedbackView/>;
    else if (page === 'progress') body = <ProgressPage/>;
  } else if (user.role === 'teacher') {
    if (page === 'dashboard') body = <TeacherDashboard onNavigate={setPage}/>;
    else if (page === 'students') body = <StudentProgressPage/>;
    else if (page === 'feedback') body = <FeedbackReviewPage/>;
    else if (page === 'modules')  body = <ModuleManagement role="teacher"/>;
  } else if (user.role === 'admin') {
    if (page === 'dashboard') body = <AdminDashboard onNavigate={setPage}/>;
    else if (page === 'users')   body = <UserManagement/>;
    else if (page === 'modules') body = <ModuleManagement role="admin"/>;
    else if (page === 'logs')    body = <SystemLogs/>;
  }

  return (
    <Shell user={user} current={page}
           onNavigate={setPage}
           onLogout={onLogout}>
            <div className="p-4">
            <h2 className="text-xl font-bold mb-2">Moodle Courses Test</h2>

            {courses.map((course) => (
              <div key={course.id}>
                {course.fullname}
              </div>
            ))}
          </div>

          {body || <EmptyState title="Page not found"/>}
    </Shell>
  );
};

export default App;
