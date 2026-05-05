import { Routes, Route, Navigate } from 'react-router-dom';
import ProtectedRoute from './routes/ProtectedRoute.jsx';

import Login from './pages/auth/Login.jsx';

import StudentDashboard from './pages/student/Dashboard.jsx';
import StudentModules from './pages/student/Modules.jsx';
import LessonView from './pages/student/LessonView.jsx';
import QuizView from './pages/student/QuizView.jsx';
import FeedbackView from './pages/student/FeedbackView.jsx';
import StudentProgress from './pages/student/Progress.jsx';

import TeacherDashboard from './pages/teacher/Dashboard.jsx';
import TeacherStudentProgress from './pages/teacher/StudentProgress.jsx';
import TeacherFeedbackReview from './pages/teacher/FeedbackReview.jsx';

import AdminDashboard from './pages/admin/Dashboard.jsx';
import UserManagement from './pages/admin/UserManagement.jsx';
import ModuleManagement from './pages/admin/ModuleManagement.jsx';
import SystemLogs from './pages/admin/SystemLogs.jsx';

export default function App() {
  return (
    <Routes>
      <Route path="/" element={<Navigate to="/login" replace />} />
      <Route path="/login" element={<Login />} />

      <Route
        path="/student"
        element={
          <ProtectedRoute role="student">
            <StudentDashboard />
          </ProtectedRoute>
        }
      />
      <Route
        path="/student/modules"
        element={
          <ProtectedRoute role="student">
            <StudentModules />
          </ProtectedRoute>
        }
      />
      <Route
        path="/student/lessons/:lessonId"
        element={
          <ProtectedRoute role="student">
            <LessonView />
          </ProtectedRoute>
        }
      />
      <Route
        path="/student/quizzes/:quizId"
        element={
          <ProtectedRoute role="student">
            <QuizView />
          </ProtectedRoute>
        }
      />
      <Route
        path="/student/feedback/:competencyId"
        element={
          <ProtectedRoute role="student">
            <FeedbackView />
          </ProtectedRoute>
        }
      />
      <Route
        path="/student/progress"
        element={
          <ProtectedRoute role="student">
            <StudentProgress />
          </ProtectedRoute>
        }
      />

      <Route
        path="/teacher"
        element={
          <ProtectedRoute role="teacher">
            <TeacherDashboard />
          </ProtectedRoute>
        }
      />
      <Route
        path="/teacher/students"
        element={
          <ProtectedRoute role="teacher">
            <TeacherStudentProgress />
          </ProtectedRoute>
        }
      />
      <Route
        path="/teacher/feedback"
        element={
          <ProtectedRoute role="teacher">
            <TeacherFeedbackReview />
          </ProtectedRoute>
        }
      />

      <Route
        path="/admin"
        element={
          <ProtectedRoute role="admin">
            <AdminDashboard />
          </ProtectedRoute>
        }
      />
      <Route
        path="/admin/users"
        element={
          <ProtectedRoute role="admin">
            <UserManagement />
          </ProtectedRoute>
        }
      />
      <Route
        path="/admin/modules"
        element={
          <ProtectedRoute role="admin">
            <ModuleManagement />
          </ProtectedRoute>
        }
      />
      <Route
        path="/admin/logs"
        element={
          <ProtectedRoute role="admin">
            <SystemLogs />
          </ProtectedRoute>
        }
      />

      <Route path="*" element={<Navigate to="/login" replace />} />
    </Routes>
  );
}
