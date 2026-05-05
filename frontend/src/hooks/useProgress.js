import { useEffect, useState } from 'react';
import studentService from '../services/studentService.js';

export default function useProgress() {
  const [progress, setProgress] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    let active = true;
    studentService.getProgress().then((data) => {
      if (active) {
        setProgress(data);
        setLoading(false);
      }
    });
    return () => {
      active = false;
    };
  }, []);

  return { progress, loading };
}
