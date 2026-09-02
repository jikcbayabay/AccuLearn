# Brand assets

Installed on the live site 2026-08-30. **These live in the database, not in any file
Moodle reads from disk** — the same caveat as the Boost theme settings recorded in
PROJECT_HANDOFF.md. They will not survive a fresh Moodle install without being
re-applied, which is why the sources are kept here.

## Files

There are two colourways. **Dark navy** is the default and is used everywhere the
background is white or near-white. **White** is used on dark backgrounds — currently
just the AccuLearn toolbar, which is a blue gradient.

| File | Used for | Notes |
|---|---|---|
| `acculearn-logo-original-500.png` | source, navy | As supplied. 500×500, transparent. |
| `acculearn-logo-original-1024.jpg` | source, navy | As supplied. 1024×1024, white background, no alpha. |
| `acculearn-logo-white-original-500.png` | source, white | As supplied. 500×500, transparent. |
| `acculearn-logo-white-on-navy-1024.jpg` | source, white | As supplied. 1024×1024, navy background baked in, no alpha — useful as a reference, not for the site. |
| `acculearn-logo.png` | `core_admin/logo` | Navy 500px source, trimmed to 338×291. Login page — the login card is white, so navy is correct there. |
| `acculearn-mark.png` | *(not currently installed)* | Navy mark only, 232×229. Kept in case a light-background placement is ever needed. |
| `acculearn-favicon.png` | `core_admin/favicon` | Navy mark, padded square at 128×128. |
| `logo-mark-light.png` | `core_admin/logocompact` **and** plugin `pix/` | **White** mark, 231×229. Used by the Boost navbar, the mobile drawer heading, and the bottom toolbar. |
| `logo-light-full.png` | spare | White full lockup, 338×291. Not currently used; keep for any future dark-background placement. |

## Which surface is dark

Worth stating plainly, because stock Boost is light and this site is not:
`theme_boost/scss` paints `.navbar.fixed-top` with the brand gradient
(`#1d4870 → #24598a → #2f6fa6`) and sets white navbar text. The bottom toolbar uses the
same gradient. So:

- **White mark** — Boost navbar (top left), mobile primary drawer heading, AccuLearn toolbar.
- **Navy logo** — login card, which is white.

The mobile drawer needed one extra change. It renders `logocompact` in its heading, which
sits on `#f8f9fa` — white on near-white. `theme_boost/scss` now gives
`#theme_boost-drawers-primary .drawerheader` the navbar's gradient, which fixes the
contrast and ties the drawer to the bar it slides out from. It is scoped by id so the
right-hand block drawer keeps its plain header.

**Still navy on a possibly-dark surface:** the favicon. Browsers with a dark tab strip
will render it dark-on-dark. Swap it for the white mark if that bothers you — it was left
as-is because most tab strips are light and nobody has complained.

## Why the toolbar has its own copy

The bottom toolbar is a dark blue gradient. It does **not** use
`$OUTPUT->get_compact_logo_url()` like the navbar does, because that returns the navy
logo, which on a dark blue background either disappears or needs a white chip behind it
that matches nothing else in the bar. It uses the white variant shipped at
`local/acculearnfeedback/pix/logo-mark-light.png`, referenced through
`$OUTPUT->image_url('logo-mark-light', 'local_acculearnfeedback')`.

**The consequence to remember:** replacing the logo in Site administration → Appearance
updates the login page, the navbar and the favicon, but **not** the toolbar. Swap
`pix/logo-mark-light.png` as well and bump the plugin version.

## Why the compact version is cropped

Boost renders the navbar logo at about 35px tall. At that height the "ACCULEARN"
wordmark in the full lockup is unreadable, so `logocompact` uses the mark alone, which
still reads clearly. This was checked by rendering the actual file at 35px rather than
assumed.

The trim matters too: Moodle scales the logo to a target height, so surrounding
transparent margin would have been scaled along with the artwork, making the visible
mark smaller than the space it occupies.

## Re-applying after a rebuild

Each of the three settings is an `admin_setting_configstoredfile` (see
`admin/settings/appearance.php`). Setting them needs **both** halves — a stored file in
the system context under component `core_admin` and the matching filearea, and a
`config_plugins` value holding the file path:

```php
$fs = get_file_storage();
$fs->create_file_from_pathname([
    'contextid' => context_system::instance()->id,
    'component' => 'core_admin',
    'filearea'  => 'logo',          // or logocompact / favicon
    'itemid'    => 0,
    'filepath'  => '/',
    'filename'  => 'acculearn-logo.png',
], '/path/to/acculearn-logo.png');
set_config('logo', '/acculearn-logo.png', 'core_admin');
theme_reset_all_caches();           // the admin form's updatedcallback; direct set_config does not fire it
```

A stored file with no config value is invisible; a config value with no stored file
renders a broken image.

Or simply upload all three through Site administration → Appearance → Logos.

## Interaction with the login-page CSS

`theme_boost/scss` contains a CSS-only logo mark and tagline on the login card, added
before a real logo existed. It targets `.loginform > .login-heading:first-child::before`.
Once a real logo is set, Moodle's `loginform.mustache` moves that heading *inside*
`#loginlogo` and marks it `visually-hidden`, so the selector no longer matches and the
CSS mark stops rendering — no duplicate logo. That CSS is now effectively a fallback for
the case where the logo is removed again. It was left in place deliberately.
