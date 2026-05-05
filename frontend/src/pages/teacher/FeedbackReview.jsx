import { useEffect, useState } from 'react';
import Navbar from '../../components/common/Navbar.jsx';
import Spinner from '../../components/common/Spinner.jsx';
import FeedbackReviewCard from '../../components/teacher/FeedbackReviewCard.jsx';
import teacherService from '../../services/teacherService.js';

export default function FeedbackReview() {
  const [items, setItems] = useState(null);

  useEffect(() => {
    teacherService.getFeedbackQueue().then(setItems);
  }, []);

  return (
    <div className="min-h-screen bg-slate-50">
      <Navbar />
      <main className="p-6">
        <h1 className="mb-4 text-2xl font-semibold">AI Feedback Review</h1>
        {!items ? (
          <Spinner />
        ) : (
          <div className="grid gap-4 md:grid-cols-2">
            {items.map((entry) => (
              <FeedbackReviewCard key={entry.id} entry={entry} />
            ))}
          </div>
        )}
      </main>
    </div>
  );
}
