// Video URL parsing for the lesson player. Pure functions — unit tested in
// video.test.js. Supports direct files, YouTube, and Vimeo.

export const ytId = (url = '') => {
  const m = String(url).match(
    /(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([\w-]{11})/
  );
  return m ? m[1] : null;
};

export const vimeoId = (url = '') => {
  const m = String(url).match(/vimeo\.com\/(?:video\/)?(\d+)/);
  return m ? m[1] : null;
};

/**
 * Resolve a video URL into a render descriptor.
 * @returns {null | {kind:'embed'|'file', src:string}}
 *   null when there is no usable URL.
 */
export const resolveVideo = (url) => {
  const clean = (url || '').trim();
  if (!clean) return null;

  const yt = ytId(clean);
  if (yt) return { kind: 'embed', src: `https://www.youtube-nocookie.com/embed/${yt}` };

  const vm = vimeoId(clean);
  if (vm) return { kind: 'embed', src: `https://player.vimeo.com/video/${vm}` };

  return { kind: 'file', src: clean };
};
