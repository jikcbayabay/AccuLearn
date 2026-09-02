# AccuLearn — System Test Evidence

Every figure below was produced against the live deployment at `acculearn.online`
on **29 August 2026**. Nothing here is carried forward from an earlier draft, and
nothing is an estimate. Where a test could not be completed without a human at a
browser, that is stated rather than filled in.

This document covers Part B, Tier 2 (items 8–12) and Tier 3 (item 16) of the
adviser's revision list.

---

## 1. Role and permission testing

### 1.1 Roles as implemented

| Role | Shortname | Held by | Purpose |
|---|---|---|---|
| Student | `student` | 20 accounts | Own lessons, quizzes, feedback and learning track only |
| Teacher | `acculearnteacher` | 2 accounts | Reviews AI output, grades, sees students pseudonymously |
| IT Admin | `itadmin` | 1 account (`itadmin1`) | Platform operation; walled off from academic data |
| Super Admin | `superadmin` | 2 accounts (`admin`, `testadmin`) | Full control; also Moodle site administrators |

The IT Admin account is deliberately **not** a Moodle site administrator. A site
administrator bypasses every capability check in Moodle, so an IT Admin who was also
a site admin could not demonstrate being refused anything.

### 1.2 Permission matrix

Produced by evaluating `has_capability()` at system context for one real account
per role.

| Capability | Student | Teacher | IT Admin | Super Admin |
|---|:--:|:--:|:--:|:--:|
| View own learning track | yes | — | yes | yes |
| Review AI output | — | yes | — | yes |
| Reveal student names | — | — | — | yes |
| Manage competency sequence | — | — | — | yes |
| View usage logs | — | — | yes | yes |
| View confidence report | — | — | — | yes |
| Edit grades | — | — | — | yes |
| View all grades | — | yes | — | yes |
| Create users | — | — | yes | yes |
| Delete users | — | — | — | yes |
| View system logs | — | yes | yes | yes |
| Site configuration | — | — | — | yes |
| Edit course content | — | — | — | yes |
| Back up user data | — | — | — | yes |

The IT Admin role holds 23 explicitly granted capabilities and 13 explicitly
**prohibited** ones. Prohibition rather than simple non-grant means the boundary
survives the role ever being combined with another.

### 1.3 Live access-control test

Each request below was made from a real authenticated browser session (cookie jar,
real login), not by inspecting code.

**Student (`demostudent1`)**

| Page requested | Result |
|---|---|
| `/local/acculearnfeedback/my_track.php` | 200 — rendered |
| `/local/acculearnfeedback/help.php` | 200 — rendered |
| `/local/acculearnfeedback/teacher_review.php` | Refused — "you do not currently have permissions" |
| `/local/acculearnfeedback/usage_logs.php` | Refused |
| `/local/acculearnfeedback/admin_sequence.php` | Refused |
| `/local/acculearnfeedback/db_browser.php` | Refused |
| `/local/acculearnfeedback/pretest_entry.php` | Refused |
| `/local/acculearnfeedback/data_retention.php` | Refused |

**IT Admin (`itadmin1`)**

| Page requested | Result |
|---|---|
| `/local/acculearnfeedback/usage_logs.php` | 200 — allowed |
| `/admin/user.php` (user management) | 200 — allowed |
| `/report/log/index.php` (system logs) | 200 — allowed |
| `/local/acculearnfeedback/teacher_review.php` | Refused |
| `/local/acculearnfeedback/admin_sequence.php` | Refused |
| `/local/acculearnfeedback/db_browser.php` | Refused |
| `/local/acculearnfeedback/pretest_entry.php` | Refused |
| `/local/acculearnfeedback/data_retention.php` | Refused |
| `/admin/search.php` (site configuration) | Refused — "Access denied" |
| `/grade/report/user/index.php?id=2&userid=6` (a student's grades) | Refused |
| `/grade/report/overview/index.php` | 200, but shows only the IT Admin's own empty record — no student name or grade appears in the response |

**Suspended account (`demostudent3`)** — login rejected with "Invalid login", confirming
that suspension blocks access while preserving the account and its records.

---

## 2. Performance and stress testing

Measured from an external client over the public internet, so the figures include
real network latency — this is what a user experiences, not a server-local benchmark.

### 2.1 Page response times

Seven samples per page, authenticated student session.

| Page | Median | Fastest | Slowest |
|---|---:|---:|---:|
| Login page (anonymous) | 926 ms | 793 ms | 1,113 ms |
| Dashboard | 1,598 ms | 1,429 ms | 3,375 ms |
| Course view | 1,816 ms | 1,511 ms | 2,027 ms |
| My Learning Track | 1,691 ms | 1,207 ms | 2,207 ms |
| Help / instructions | 1,819 ms | 1,222 ms | 4,074 ms |
| User profile | 1,316 ms | 1,221 ms | 1,699 ms |
| Grades overview | 1,388 ms | 1,229 ms | 2,103 ms |

### 2.2 Concurrent load

Each simulated user has its **own session**. This matters: Moodle takes a per-session
lock for the duration of each request, so firing many requests through one shared
session measures lock queuing rather than server capacity.

| Concurrent users | Wall clock | Mean response | Slowest | Throughput | Errors |
|---:|---:|---:|---:|---:|---:|
| 5 | 1.02 s | 0.97 s | 0.99 s | 4.9 req/s | 0 |
| 10 | 0.99 s | 0.89 s | 0.97 s | 10.1 req/s | 0 |
| 20 | 1.65 s | 1.13 s | 1.60 s | 12.1 req/s | 0 |
| 20 (course page) | 1.30 s | 0.98 s | 1.26 s | 15.4 req/s | 0 |

**Result:** every request succeeded at every concurrency level. Response time is
essentially flat from 5 to 10 users and degrades only mildly at 20 — comfortably
adequate for a class of the size this study used, on a single free-tier ARM VM.

**One measured caveat worth stating.** Twenty requests issued through a *single*
shared session took 13.6 s wall clock with a mean of 6.1 s, against 1.65 s for the
same twenty requests across independent sessions. This is Moodle's session locking,
not a capacity limit: one user opening many tabs at once will see them load
sequentially. It does not affect multiple students working simultaneously.

### 2.3 AI generation timing

| Operation | Time | Note |
|---|---:|---|
| OpenAI feedback generation, live call | 4,546 ms | Real attempt, 30 responses, 2 incorrect |
| Delivery to student | up to 60 s | Queued as an ad-hoc task, processed by cron on a one-minute interval |

The live call was made on 29 August 2026 against attempt 465 and returned a
schema-valid response with all five expected keys (`summary`, `misconceptions`,
`corrective_guidance`, `encouragement`, `question_notes`), containing corrective
guidance specific to the two questions actually answered incorrectly. This confirms
the AI integration, the API key and the quota are all live. No record was written —
`generateFeedback()` does not persist; the caller does.

---

## 3. Vulnerability and security testing

**Method:** Moodle's own security check suite (`\core\check\manager::get_checks('security')`,
19 checks), plus direct HTTP probing of the 29 internal paths Moodle's `core_publicpaths`
check knows about, plus the live access-control tests in section 1.3.

### 3.1 Findings and remediation

| # | Finding | Severity | Status |
|---|---|---|---|
| 1 | Debugging enabled and displayed to users (`debug=30112`, `debugdisplay=1`) — PHP notices and stack traces with file paths rendered on-page | High | **Fixed** — both set to 0 |
| 2 | Git metadata served over HTTPS (`/.git/config`, `/.git/HEAD`, `/.git/logs/HEAD`) | High | **Fixed** — all dot-directories denied |
| 3 | Directory listing enabled site-wide (`Options Indexes`) | High | **Fixed** — `-Indexes` on the document root |
| 4 | Internal source files publicly readable — 22 of 29 probed paths returned 200, including behat features, test fixtures, `composer.json`/`.lock`, `db/install.xml`, `admin/environment.xml`, upgrade notes and readmes | Medium | **Fixed** — all 29 now return 403 |
| 5 | No account lockout (`lockoutthreshold=0`) against 1,466 recorded failed logins, 264 in the preceding 7 days | Medium | **Fixed** — 8 attempts per 30-minute window |
| 6 | Eight-hour session timeout in a shared computer laboratory | Medium | **Fixed** — reduced to 2 hours |
| 7 | `config.php` writable by the web server user | Low | **Fixed** — now read-only, root-owned |
| 8 | Executable paths settable through the admin GUI | Low | **Fixed** — `$CFG->preventexecpath = true` |
| 9 | Web service token missing from the database; REST integration returned "Invalid token — token expired" | Medium | **Fixed** — token reissued, `test_connection.php` now returns SUCCESS |

### 3.2 Verified sound, no change required

- HTTPS with a valid Let's Encrypt certificate; plain HTTP 301-redirects; automatic renewal timer active.
- Security headers present on every response: `Strict-Transport-Security`, `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy`, `Permissions-Policy`.
- Session cookie carries `Secure`, `HttpOnly` and `SameSite=Lax`.
- Anonymous visitors forced to the login page; guest access disabled; username enumeration protection on; 8-character password policy enforced; cron restricted to the command line. (Self-registration was disabled at the time of this audit; it was enabled on 2 September 2026 — see section 7.)
- All ten custom plugin pages gate on `require_login()` **and** an explicit `require_capability()`; every state-changing form calls `require_sesskey()`.
- The AI middleware directory sits outside the document root and is additionally denied in Apache, so `.env` is not retrievable over HTTP.
- Cloud instance metadata address (`169.254.169.254`) is blocked in `curlsecurityblockedhosts`.
- HTTP access logging is active (`other_vhosts_access.log`), with two months of rotated history.

### 3.3 Security check suite result

After remediation: **16 OK, 1 informational, 2 warnings.** The two remaining warnings
(`core_riskxss`, `core_riskbackup`) report that trusted users exist who can post HTML
and back up user data — these are inherent to having teacher and administrator roles at
all, and Moodle raises them on every site.

### 3.4 Privacy disclosure corrected

A live test of the feedback path showed that the generated prose contains the student's
real first name. Tracing it back: `generate_feedback.php` passes the student's full name
to the OpenAI API so the feedback can address them directly, and the name is stripped
again by `local_acculearnfeedback_deidentify()` before a teacher sees the text.

The display-layer anonymisation works as documented. The **plugin's own privacy
statement did not**: it described the data sent to OpenAI as "anonymized performance
data", omitting the name entirely. That string has been rewritten to state exactly what
is transmitted. Any privacy or consent wording in the paper needs the same correction.

---

## 4. Copyright and asset licensing

**Method:** enumerated every file inside all 29 SCORM packages from Moodle's file
table, then read the licence headers out of the shipped files themselves rather than
inferring from filenames.

### 4.1 Inventory

| Type | Count | Size |
|---|---:|---:|
| PNG images | 1,537 | 1.99 MB |
| JavaScript | 261 | 11.3 MB |
| CSS | 116 | 7.34 MB |
| XML | 116 | 3.30 MB |
| HTML | 65 | 354 KB |
| Source maps | 58 | 25.5 MB |
| GIF / ICO / SVG / DTD / ZIP | 145 | 13.9 MB |

### 4.2 The important result

**Every image in the lesson content appears exactly 29 times — once per package.**
There is not a single unique image anywhere in the 1,624 media files. They are all
eXeLearning's own template icons (`technology.png`, `roadmap.png`, `think.png`,
`competencies.png`, `exe_powered_logo.png` and similar).

This means **the instructional content contains no third-party photographs, diagrams
or illustrations at all** — it is authored text plus the authoring tool's icon set.
There is nothing to replace.

### 4.3 Licences, read from the shipped files

| Asset | Licence | Evidence |
|---|---|---|
| eXeLearning (authoring tool runtime, template icons) | GNU GPL v2 or later | `common.js`: "Copyright 2004–2005, University of Auckland / Copyright 2004–2008 eXe Project… free software… GNU General Public License… version 2" |
| jQuery 3.7.1 | MIT | `jquery.min.js`: "(c) OpenJS Foundation and other contributors \| jquery.org/license" |
| Bootstrap | MIT | `bootstrap.min.css`: "Copyright 2011–2025 The Bootstrap Authors / Licensed under MIT" |
| Inter typeface (site UI) | SIL Open Font License | Noted in `styles.css`; self-hosted |

All four are permissive or copyleft licences that permit this use. **What they require
is attribution**, which the system now gives — see 4.4.

### 4.4 Status

- **Done, 1 September 2026.** An attribution page (`local/acculearnfeedback/attribution.php`,
  reachable from the toolbar as *Credits* by every logged-in user) names eXeLearning, jQuery,
  Bootstrap, Inter, Moodle and Font Awesome with their licences, quoting the licence header
  read from each shipped file as evidence. Verified rendering under a student account.
- Ship the actual SIL OFL licence text alongside the Inter font file, rather than only a comment in the stylesheet.
- Optional cleanup: 25.5 MB of JavaScript source maps are shipped inside the SCORM packages and serve no purpose in a published course.

---

## 5. Browser and device compatibility — NOT YET DONE

These two items cannot be completed without a person operating real browsers and real
devices. The matrices below are ready to fill; the function list matches the eight
functional requirements so the results line up with Table 4.1.

**Do not fill these in from memory.** Run each function, note what actually happened,
and screenshot anything that fails.

### 5.1 Browser matrix

| Browser | Version | OS | Function | Expected | Actual | Pass/Fail |
|---|---|---|---|---|---|---|
| Google Chrome | | | Login / logout | Authenticates, reaches dashboard | | |
| Google Chrome | | | Lesson (SCORM) launch | Package opens, pages navigate | | |
| Google Chrome | | | Quiz with confidence rating | Both answer and confidence accepted | | |
| Google Chrome | | | Quiz submission | Submits, returns to review | | |
| Google Chrome | | | AI feedback display | Feedback appears within a minute | | |
| Google Chrome | | | My Learning Track | Mastery, pathway and narrative render | | |
| Google Chrome | | | Teacher review (as teacher) | Queue lists records, approve/flag work | | |
| Google Chrome | | | Admin functions (as admin) | Sequence, entry and retention pages work | | |
| Microsoft Edge | | | *(repeat all eight)* | | | |
| Mozilla Firefox | | | *(repeat all eight)* | | | |
| Safari | | | *(repeat all eight, if available)* | | | |

### 5.2 Mobile compatibility — audited and remediated 29 August 2026

Everything that can be established without physical hardware has been done, and four
real defects were found and fixed. What remains genuinely needs a person holding a
phone; that part is the table in §5.3.

#### What was verified, and how

| Check | Method | Result |
|---|---|---|
| Pages serve correctly to mobile clients | Authenticated session with Android Chrome and iOS Safari user agents against dashboard, course, learning track and help pages | All HTTP 200 with full content |
| Viewport declaration | Inspected served markup | `width=device-width, initial-scale=1.0` — correct |
| Separate mobile theme | Compared stylesheet URL across UAs | Same Boost stylesheet for both; responsiveness is pure CSS, so there is no second code path to test |
| Horizontal overflow | Scanned rendered HTML of six pages for fixed pixel widths ≥ 320px | None. The one hit (`1100px`) is a `max-width`, which cannot overflow |
| Table overflow | Cross-checked every table against its scroll container | All 12 plugin tables sit inside `al-table-wrap` or `alb__wrap`, both `overflow-x: auto`. The only unwrapped table is Moodle's own calendar, which Boost handles natively |
| Theme-layer widths | Parsed `theme_boost/scss` for fixed widths and media queries | No fixed width ≥ 300px; login card already uses `min(440px, calc(100vw - 2rem))` |
| Touch target sizes | Computed rendered heights from the CSS | Two controls were below recommended size — both fixed, see below |

#### Defects found and fixed

| # | Defect | Why it mattered | Fix |
|---|---|---|---|
| M-01 | The spacer reserving room for the fixed toolbar was a hardcoded `height:56px` | Right for a single row (~57px desktop, ~54px mobile) but badly short when the bar wraps, which it does on a phone: at 360px roughly six icon-only links fit per row, so a teacher (four links) stays on one row but an administrator (eight) takes two, about 108px. The last cards on a page were then covered by the second row | Replaced the inline height with a `.al-toolbar-spacer` class: 76px, rising to `calc(120px + env(safe-area-inset-bottom))` below 640px |
| M-02 | No safe-area handling anywhere | On an iPhone with a home indicator, the bottom row of toolbar icons sat under it and was awkward to tap | `padding-bottom: calc(6px + env(safe-area-inset-bottom, 0px))` on the toolbar. Resolves to 0 where there is no inset, so no desktop cost |
| M-03 | The reveal/hide-names row on the teacher review and usage log pages was `display: flex` **without** `flex-wrap` | Button label plus status text runs to roughly 390px, overflowing a phone viewport and forcing the whole page to scroll sideways | Added `flex-wrap: wrap`, matching every other flex row on those pages |
| M-04 | The confidence pill rendered ~33px tall; the `al-radio-pills` control ~23px | The confidence pill is **the most-tapped control in the system** — once per question, thirty times in a summative quiz, usually on a phone. 33px clears WCAG 2.2's 24px minimum but falls short of the 44px both Apple's HIG and Material recommend. The 23px control failed the WCAG minimum outright | `@media (pointer: coarse)` block raising pills, buttons and answer radios to ~44px; `al-radio-pills` base padding raised to clear 24px on every device |

`(pointer: coarse)` was chosen over a width query deliberately: it keys on the input
device rather than the viewport, so a narrow desktop window keeps the compact layout and
a large tablet still gets the bigger target.

All four fixes were deployed and confirmed being served — plugin stylesheet re-fetched
with a bumped cache-buster, and the compiled Boost stylesheet verified through
`theme_config::load('boost')->get_css_content()` rather than over HTTP, because there is
a known few-second lag between resetting theme caches and `theme/styles.php` reflecting a
change. Desktop and iOS smoke tests after the change: all pages HTTP 200, Moodle status
checks green.

**Correction, 30 August.** M-01 was first recorded as "nothing reserved space for the
toolbar", and fixed by padding `<body>`. That was wrong on both counts. A 56px spacer
already existed, emitted by `lib.php` immediately after the toolbar; the real defect was
that its height was fixed and therefore short whenever the bar wrapped. Worse, the body
padding stacked on top of the spacer — 132px of dead space at the bottom on desktop,
which is precisely the "excessive white space" Tier 3 item 13 warns against. The body
rule has been removed and the spacer itself made responsive, which also scopes it
correctly: it is emitted only on pages that render the toolbar, whereas a `body` rule
also padded the login page and everywhere else the toolbar does not appear.

#### Also relevant

The Moodle mobile **app** will not work against this site — `enablemobilewebservice` is
`0`. Mobile testing therefore means the mobile *browser*, which is what the study
actually used. State that explicitly rather than leaving a panellist to wonder.

### 5.3 Physical device matrix — still to fill

The checks above cannot establish how the layout actually *looks* and *feels* in the
hand, which is what this item ultimately asks for. Run each function on real hardware and
record what happened.

| Device | OS / version | Screen | Browser | Function | Expected | Actual | Pass/Fail |
|---|---|---|---|---|---|---|---|
| *(Android phone)* | | | Chrome | Login | Card fits, no sideways scroll | | |
| *(Android phone)* | | | Chrome | Lesson (SCORM) launch | Renders and scrolls; eXeLearning pages readable | | |
| *(Android phone)* | | | Chrome | Quiz + confidence rating | Pills easy to tap first time, no mis-taps | | |
| *(Android phone)* | | | Chrome | Quiz submission | Submits, review page readable | | |
| *(Android phone)* | | | Chrome | AI feedback display | Feedback readable without zooming | | |
| *(Android phone)* | | | Chrome | My Learning Track | Cards stack, progress bars render, toolbar shows icons only | | |
| *(Android phone)* | | | Chrome | Page bottom not hidden | Last card fully visible above the toolbar *(verifies M-01)* | | |
| *(iOS phone)* | | | Safari | *(repeat all seven)* | Also check the toolbar clears the home indicator *(verifies M-02)* | | |
| *(Tablet)* | | | | *(repeat)* | | | |

Worth checking explicitly, because they are the behaviours most likely to be wrong:
the login card below 440px, the toolbar collapsing to icons below 640px, the toolbar
wrapping to two rows when logged in as an administrator, and the confidence pills in a
real thirty-question summative quiz.

---

## 6. Testing iterations

The development history contains a real, documented run → fail → fix → retest cycle.
It is recorded as a changelog in `THESIS_DOCUMENT_CHANGES.md` and in the "what changed
and why" table of `CHAPTER4_TEST_TABLES.md` rather than as an iteration table. To
satisfy item 8, reshape those existing entries into: initial result, defect found, fix
applied, retest result. Roughly a dozen rows already exist, including:

- Mastery accumulated across every attempt ever, so a student who failed once could never reach 85% → scoped to the best finished attempt per quiz → verified against real data.
- Multichoice answer order is shuffled per attempt; the raw submitted value was being read as a row position → resolved through the `_order` variable → verified.
- The restudy prompt could not fire in four courses because those quizzes were left at unlimited attempts → all 29 set to 3 attempts → verified.
- "Hints for fast learners" had no code path at all → implemented on the LP = 4 branch, gated to summative quizzes → verified.
- Flagging AI output changed nothing the student saw → flagged feedback now withheld → verified.
- The teacher review queue anonymised the heading but printed the student's name in the AI prose beneath it → prose de-identified → verified.

Adding the nine security findings in section 3.1, each with a before state, a fix and a
verified after state, gives a second complete iteration cycle dated 29 August 2026.
