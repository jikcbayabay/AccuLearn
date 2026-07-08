// Minimal stroke-icon set. 1.5px strokes, currentColor.
const I = ({ children, size = 18, className = '' }) => (
  <svg width={size} height={size} viewBox="0 0 24 24" fill="none" stroke="currentColor"
       strokeWidth="1.6" strokeLinecap="round" strokeLinejoin="round" className={className}>
    {children}
  </svg>
);

const Icon = {
  Home:        (p) => <I {...p}><path d="M3 11.5 12 4l9 7.5"/><path d="M5 10v10h14V10"/></I>,
  Book:        (p) => <I {...p}><path d="M4 5a2 2 0 0 1 2-2h13v16H6a2 2 0 0 0-2 2z"/><path d="M19 19H6a2 2 0 0 1-2-2"/></I>,
  Path:        (p) => <I {...p}><path d="M5 5h6a4 4 0 0 1 0 8H8a4 4 0 0 0 0 8h11"/></I>,
  Spark:       (p) => <I {...p}><path d="M12 3v4M12 17v4M3 12h4M17 12h4M5.6 5.6l2.8 2.8M15.6 15.6l2.8 2.8M5.6 18.4l2.8-2.8M15.6 8.4l2.8-2.8"/></I>,
  Chart:       (p) => <I {...p}><path d="M4 20V10M10 20V4M16 20v-6M22 20H2"/></I>,
  Users:       (p) => <I {...p}><circle cx="9" cy="8" r="3.5"/><path d="M2 20a7 7 0 0 1 14 0"/><path d="M17 11a3 3 0 0 0 0-6"/><path d="M22 19a5 5 0 0 0-5-5"/></I>,
  Cog:         (p) => <I {...p}><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.8-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1.1-1.5 1.7 1.7 0 0 0-1.8.3l-.1.1A2 2 0 1 1 4.3 17l.1-.1a1.7 1.7 0 0 0 .3-1.8 1.7 1.7 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.5-1.1 1.7 1.7 0 0 0-.3-1.8L4.2 7A2 2 0 1 1 7 4.2l.1.1a1.7 1.7 0 0 0 1.8.3H9a1.7 1.7 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.8-.3l.1-.1A2 2 0 1 1 19.7 7l-.1.1a1.7 1.7 0 0 0-.3 1.8V9a1.7 1.7 0 0 0 1.5 1H21a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1z"/></I>,
  Logs:        (p) => <I {...p}><path d="M4 6h16M4 12h16M4 18h10"/></I>,
  ClipCheck:   (p) => <I {...p}><rect x="6" y="4" width="12" height="16" rx="2"/><path d="M9 4V3a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v1"/><path d="m9 13 2 2 4-4"/></I>,
  Bell:        (p) => <I {...p}><path d="M6 8a6 6 0 1 1 12 0c0 7 3 8 3 8H3s3-1 3-8"/><path d="M10 21a2 2 0 0 0 4 0"/></I>,
  Search:      (p) => <I {...p}><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></I>,
  Chevron:     (p) => <I {...p}><path d="m9 6 6 6-6 6"/></I>,
  ChevL:       (p) => <I {...p}><path d="m15 6-6 6 6 6"/></I>,
  Plus:        (p) => <I {...p}><path d="M12 5v14M5 12h14"/></I>,
  Edit:        (p) => <I {...p}><path d="M4 20h4l11-11-4-4L4 16z"/><path d="m14 6 4 4"/></I>,
  Trash:       (p) => <I {...p}><path d="M4 7h16M9 7V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2M6 7l1 13a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2l1-13"/></I>,
  Check:       (p) => <I {...p}><path d="m5 12 5 5 9-11"/></I>,
  X:           (p) => <I {...p}><path d="m6 6 12 12M18 6 6 18"/></I>,
  Lock:        (p) => <I {...p}><rect x="5" y="11" width="14" height="9" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/></I>,
  Play:        (p) => <I {...p}><path d="M7 5v14l12-7z"/></I>,
  Logout:      (p) => <I {...p}><path d="M15 4h3a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-3"/><path d="M10 17 5 12l5-5"/><path d="M5 12h12"/></I>,
  Mail:        (p) => <I {...p}><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m4 7 8 6 8-6"/></I>,
  Eye:         (p) => <I {...p}><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></I>,
  AlertTri:    (p) => <I {...p}><path d="M12 4 2 21h20z"/><path d="M12 10v5M12 18h.01"/></I>,
  Sparkle:     (p) => <I {...p}><path d="M12 3v4M3 12h4M21 12h-4M12 21v-4M5.6 5.6l2.8 2.8M18.4 18.4l-2.8-2.8M5.6 18.4l2.8-2.8M18.4 5.6l-2.8 2.8"/></I>,
};

export default Icon;
