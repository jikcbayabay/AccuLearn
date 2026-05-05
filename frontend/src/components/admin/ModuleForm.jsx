import { useState } from 'react';
import Button from '../common/Button.jsx';

export default function ModuleForm({ initial = {}, onSubmit }) {
  const [form, setForm] = useState({
    title: initial.title || '',
    description: initial.description || '',
    moodleCourseId: initial.moodleCourseId || '',
    order: initial.order || 0,
  });

  const handleChange = (e) => {
    setForm({ ...form, [e.target.name]: e.target.value });
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    onSubmit?.(form);
  };

  return (
    <form onSubmit={handleSubmit} className="space-y-4">
      <div>
        <label className="mb-1 block text-sm font-medium">Title</label>
        <input
          name="title"
          value={form.title}
          onChange={handleChange}
          className="w-full rounded-md border px-3 py-2 text-sm"
          required
        />
      </div>
      <div>
        <label className="mb-1 block text-sm font-medium">Description</label>
        <textarea
          name="description"
          value={form.description}
          onChange={handleChange}
          rows={3}
          className="w-full rounded-md border px-3 py-2 text-sm"
        />
      </div>
      <div className="grid grid-cols-2 gap-4">
        <div>
          <label className="mb-1 block text-sm font-medium">Moodle Course ID</label>
          <input
            name="moodleCourseId"
            value={form.moodleCourseId}
            onChange={handleChange}
            className="w-full rounded-md border px-3 py-2 text-sm"
          />
        </div>
        <div>
          <label className="mb-1 block text-sm font-medium">Order</label>
          <input
            type="number"
            name="order"
            value={form.order}
            onChange={handleChange}
            className="w-full rounded-md border px-3 py-2 text-sm"
          />
        </div>
      </div>
      <Button type="submit">Save Module</Button>
    </form>
  );
}
