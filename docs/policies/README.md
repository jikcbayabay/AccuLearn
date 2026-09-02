# Site policies — how to publish these

Two policy documents, written 2026-08-29 to close Tier 3 item 15 (Terms and Conditions)
of the adviser's Part B checklist. Both are grounded in what the system actually does —
the retention window comes from `AL_RETENTION_DAYS` in `data_retention.php`, and the
"what is sent to OpenAI" section comes from the plugin's own `privacy:metadata` strings —
so keep them in sync if that behaviour changes.

- `terms-of-use.html` — the rules of use. Type: **Site policy**.
- `privacy-notice.html` — what is collected, what leaves the server, retention, rights.
  Type: **Privacy policy**.

## Published — 1 September 2026

**Both documents are live.** `sitepolicyhandler` is set to `tool_policy`, and both are
ACTIVE with audience *All users*, agreement style *Consent page*, and compulsory consent.
Every user is asked to accept both at next login. Verified by logging in as a demo student
and landing on the consent page (`totalpolicies=2`).

One correction was made before publishing: section 6 of the terms claimed performance data
reaches the AI service "in anonymised form", which is false and contradicted the privacy
notice beside it. It now states that the student's name and incorrectly answered questions
are sent, and that username, email, password and user ID are not.

The steps below are retained for republishing or for a revised version.

## How they were published

1. Site administration → Users → Privacy and policies → **Policy settings**, set the
   policy handler to `tool_policy`.
2. Site administration → Users → Privacy and policies → **Manage policies** → *New policy*.
3. For each file: set the name, choose the type above, set audience to **All users**,
   agreement style **Consent page**, and paste the file's contents into the *Full policy*
   field using the editor's **HTML source view** (`<>` button) so the markup is kept.
4. Save as **Active** when you are satisfied with the wording. Saving as *Draft* keeps it
   invisible to users, which is useful while you review it.

Once a policy is active, every user is asked to accept it at their next login, and Moodle
records who accepted which version — which is also useful evidence for the defense.

## Before publishing, check these

- The contact address. The site's configured support email is used elsewhere; the terms
  currently say "contact the site administrator" rather than naming an address, so add one
  if you want it explicit.
- Whether your institution requires specific consent wording for a study involving minors.
  These documents are written for clarity, not to satisfy a particular ethics board.
- Section 3's rules match the live configuration as of 2026-08-29: three attempts per quiz
  on all 29 quizzes, and prerequisite gating enabled per competency. If either changes,
  update the terms.
