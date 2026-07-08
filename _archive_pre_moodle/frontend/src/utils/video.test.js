import { describe, it, expect } from 'vitest';
import { ytId, vimeoId, resolveVideo } from './video.js';

describe('ytId', () => {
  it('extracts id from watch URLs', () => {
    expect(ytId('https://www.youtube.com/watch?v=dQw4w9WgXcQ')).toBe('dQw4w9WgXcQ');
  });
  it('extracts id from youtu.be short links', () => {
    expect(ytId('https://youtu.be/dQw4w9WgXcQ')).toBe('dQw4w9WgXcQ');
  });
  it('extracts id from embed URLs', () => {
    expect(ytId('https://www.youtube.com/embed/dQw4w9WgXcQ')).toBe('dQw4w9WgXcQ');
  });
  it('returns null for non-YouTube URLs', () => {
    expect(ytId('https://example.com/clip.mp4')).toBeNull();
  });
});

describe('vimeoId', () => {
  it('extracts id from vimeo URLs', () => {
    expect(vimeoId('https://vimeo.com/123456789')).toBe('123456789');
  });
  it('extracts id from vimeo /video/ URLs', () => {
    expect(vimeoId('https://player.vimeo.com/video/987654321')).toBe('987654321');
  });
  it('returns null for non-Vimeo URLs', () => {
    expect(vimeoId('https://youtu.be/dQw4w9WgXcQ')).toBeNull();
  });
});

describe('resolveVideo', () => {
  it('returns null for empty / missing URLs', () => {
    expect(resolveVideo('')).toBeNull();
    expect(resolveVideo('   ')).toBeNull();
    expect(resolveVideo(undefined)).toBeNull();
    expect(resolveVideo(null)).toBeNull();
  });

  it('resolves YouTube to a nocookie embed', () => {
    expect(resolveVideo('https://youtu.be/dQw4w9WgXcQ')).toEqual({
      kind: 'embed',
      src: 'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ',
    });
  });

  it('resolves Vimeo to a player embed', () => {
    expect(resolveVideo('https://vimeo.com/123456789')).toEqual({
      kind: 'embed',
      src: 'https://player.vimeo.com/video/123456789',
    });
  });

  it('treats other URLs as direct files (trimmed)', () => {
    expect(resolveVideo('  https://cdn.example.com/lesson.mp4  ')).toEqual({
      kind: 'file',
      src: 'https://cdn.example.com/lesson.mp4',
    });
  });
});
