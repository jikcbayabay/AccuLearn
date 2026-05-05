import { useEffect, useState } from 'react';
import { useParams } from 'react-router-dom';
import Navbar from '../../components/common/Navbar.jsx';
import Spinner from '../../components/common/Spinner.jsx';
import FeedbackCard from '../../components/student/FeedbackCard.jsx';
import LearningTrackBadge from '../../components/student/LearningTrackBadge.jsx';
import studentService from '../../services/studentService.js';

export default function FeedbackView() {
  const { competencyId } = useParams();
  const [feedback, setFeedback] = useState(null);

  useEffect(() => {
    studentService.getFeedback(competencyId).then(setFeedback);
  }, [competencyId]);

  return (
    <div className="min-h-screen bg-slate-50">
      <Navbar />
      <main className="mx-auto max-w-2xl p-6">
        <h1 className="mb-4 text-2xl font-semibold">Your Feedback</h1>
        {!feedback ? (
          <Spinner />
        ) : (
          <div className="space-y-4">
            <FeedbackCard feedback={feedback} />
            <div>
              <span className="text-xs text-slate-500">Assigned path: </span>
              <LearningTrackBadge lp={feedback.lpAssigned} />
            </div>
          </div>
        )}
      </main>
    </div>
  );
}
