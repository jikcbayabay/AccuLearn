# Chapter 4 — Final Functional and Non-Functional Test Tables

Fills Tables 4.1 and 4.2 of the final Chapter 4 (`FINAL-CHAPTER-45.pdf`), which are currently
empty. Supersedes the Thesis 2 draft (`Thesis2-Bayabay-Ebora-Oliveros.pdf`, pp. 87–92), where
three rows were still "Ongoing" and several "Actual Result" entries were inaccurate.

Every figure below was verified against the live system at `acculearn.online` on 2026-08-18,
not copied forward from the Thesis 2 draft. UEQ-S figures are taken from the final chapter's
own Section 4.4.

---

## Correction needed in Section 4.1 (Testing Overview)

The Testing Overview paragraph enumerates seven functional requirements — "User Authentication,
Instructional Content Delivery, Assessment Administration, Performance Evaluation, Instructional
Decision Logic, Learning Progress Monitoring, and Teacher Monitoring" — but Section 3.1.1.2
defines **eight**, and Table 4.1 below has eight rows. The omitted one is **AI-Assisted
Feedback**, which is the study's central contribution. Insert it after "Instructional Decision
Logic" so the sentence matches the table.

---

## Table 4.1. Functional Test Cases and Results

| Test ID | Functional Requirement | Test Steps | Expected Result | Actual Result | Status |
|---|---|---|---|---|---|
| FT-01 | User Authentication | 1. Navigate to the login page. 2. Enter valid student, teacher, and administrator credentials in turn. 3. Enter invalid credentials. 4. While logged in as a student, request a teacher-only and an administrator-only page. | Valid credentials grant access to the correct role-based portal; invalid credentials are rejected with an error message; students cannot reach teacher or administrator pages. | All three roles reached their correct portal. Invalid credentials were rejected by Moodle's authentication layer with an on-screen error. Anonymous requests to any page were redirected to the login screen, with guest access and self-registration disabled. A student session requesting the teacher review and usage-log pages was refused by the plugin's capability checks. Sixteen student accounts authenticated across the deployment. | Pass |
| FT-02 | Instructional Content Delivery | 1. Log in as a student. 2. Open a FABM 1 competency course. 3. Launch a SCORM lesson package and navigate through its pages. | Lesson content, guided examples, and instructional materials load correctly and are aligned with the selected competency. | All 29 SCORM lesson packages across the seven competency courses — an Overview plus three to five "Lesson Content" packages per course — launched and rendered their pages, worked examples, and embedded media without error. During the observation window the system recorded 2,807 lesson view events and 983 in-lesson page launches from 17 distinct users, confirming delivery under real classroom use. | Pass |
| FT-03 | Assessment Administration | 1. Open a formative quiz and a summative quiz. 2. Answer every item and select a confidence rating for each. 3. Submit through "Finish attempt" and "Submit all and finish". | The quiz loads correctly, accepts responses and confidence ratings, and submits without error. | All 29 quizzes (22 formative, 7 summative) loaded their randomly drawn items — 320 slots drawn from per-competency banks totalling 720 tagged questions — and accepted both the answer and the confidence rating for every item. 452 attempts were submitted and reached the finished state, producing 4,927 stored confidence responses with no lost or partial submissions. | Pass |
| FT-04 | Performance Evaluation | 1. Submit a completed assessment. 2. Compare the system-computed score, Competency Mastery, CMI, and GI against manual computation from the stored responses. | The system accurately computes the score and determines competency mastery based on assessment results. | On submission the system computed Competency Mastery (CM), the Confidence Misconception Index (CMI), and the Guessing Index (GI) from the stored correctness-and-confidence data, scoping evidence to the best finished attempt per quiz so that mastery agrees with the gradebook's "Highest grade" method. Computed values were cross-checked against manual computation of the underlying response set and matched exactly. 447 attempt-level records carrying CM, CMI, GI, and the CBM total were produced. | Pass |
| FT-05 | Instructional Decision Logic | 1. Complete an assessment scoring in the "Mastered" range (≥ 85%). 2. Complete one scoring below 75%. 3. Score ≥ 85% with a high Guessing Index. 4. Attempt a quiz whose prerequisite competency is not yet mastered. 5. Exhaust the three permitted attempts without reaching mastery. | The system correctly applies rule-based progression, allowing advancement for mastered competencies and prompting review or remediation for those needing improvement. | Every branch of the learning-pathway rule fired as specified: CM ≥ 85 with CMI < 20 returned Advancement (LP = 4); CM < 75 returned Guided Remediation (LP = 2); both 75 ≤ CM < 85 and CM ≥ 85 with GI ≥ 20 returned Reinforcement Practice (LP = 3); and an unmet prerequisite returned Prerequisite Review (LP = 1), with the quiz access rule blocking the attempt and naming the required competency. All 29 quizzes carry the three-attempt limit and a configured passing mark (80% formative, 85% summative), so the restudy prompt is reachable in every course. | Pass |
| FT-06 | AI-Assisted Feedback | 1. Submit an assessment containing incorrect responses. 2. Review the generated feedback. 3. Submit a fully correct summative attempt with a low Guessing Index. | The system generates AI-assisted feedback that explains the identified error and provides corrective guidance. | For attempts containing incorrect responses, the system retrieved the specific incorrect items with their confidence ratings, called GPT-4o mini under an enforced JSON schema, and displayed structured corrective feedback comprising a summary, the identified misconceptions, corrective guidance, and encouragement. For a fully correct summative attempt with GI ≤ 20 it generated enrichment hints instead. 452 generation cycles ran, producing 447 stored feedback records, one per attempt. Generation is queued as a Moodle ad-hoc task so it runs after the submission transaction commits, and a schema-invalid response degrades to a fixed message rather than failing the page. | Pass |
| FT-07 | Learning Progress Monitoring | 1. Log in as a student. 2. Open the learning-track dashboard. 3. Submit a further attempt and re-open the dashboard. | The system displays accurate, up-to-date mastery levels and learning progress across competencies. | The dashboard displayed the current mastery percentage, mastery classification, and assigned learning pathway for each of the seven competencies, each with a progress bar and an AI-generated narrative summarising overall progress and next steps. After a new submission the track was recomputed and the display updated; where the recomputed evidence was unchanged, the stored narrative was reused rather than regenerated. Sixteen learning-track records were maintained, and students opened the track 641 times and their feedback 652 times during the observation window. | Pass |
| FT-08 | Teacher Monitoring | 1. Log in as a teacher. 2. Open the AI output review queue. 3. Open a student's performance record. 4. Approve one AI output and flag another. 5. Override a grade. | The teacher can view student performance records and associated AI-generated feedback. | The review interface listed AI-generated post-quiz feedback and learning-track records per student, ordered unreviewed-first and paged, showing the computed CM, CMI, and GI for each. Students appeared pseudonymously by default, with names revealed only to a holder of the dedicated reveal capability, and the AI prose was de-identified so that the narrative could not restore the name. Approving or flagging an output wrote an immutable review-log entry; flagged feedback was withheld from the student, and grade overrides were written back to the Moodle gradebook as overridden values. | Pass |

---

## Table 4.2. Non-Functional Test Cases and Results

| Test ID | Non-Functional Requirement | Expected Result | Actual Result | Status |
|---|---|---|---|---|
| NFT-01 | Usability | The system provides a user interface that is understandable for its intended users. | Formal evaluation using the UEQ-S instrument was completed with the 15 experimental-group students and 5 faculty members after the intervention. AccuLearn scored 1.86 for Pragmatic Quality and 1.65 for Hedonic Quality, giving an overall User Experience score of 1.76 — all three above the 1.17 "Excellent" benchmark. Confusing/Clear was the highest-rated item (M = 2.15), and no scale in either respondent group fell below the "Good" threshold. | Pass |
| NFT-02 | Reliability | The system maintains stable operation during classroom use and simultaneous student access. | The system ran for the full 40-day intervention (22 June – 31 July 2026) with the class working concurrently in the school computer laboratory. 452 of 456 quiz attempts reached the finished state, the remaining four still being in progress rather than failed. 4,927 confidence responses, 447 feedback records, and 7,320 usage-log entries were written with no data loss, and no session was interrupted by a system failure. The AI generation path degrades to a fixed message on an API error rather than blocking submission. | Pass |
| NFT-03 | Performance Efficiency | The system loads lessons, quizzes, and feedback within an acceptable response time. | Lesson, quiz, and dashboard pages loaded without perceptible delay, and the interface font is self-hosted, so rendering does not wait on a third-party host from the school network. A page of the teacher review queue resolves in a fixed number of queries (20 records in 43 queries). AI-generated feedback and learning-track updates complete within several seconds of processing, but because generation is queued as a background task processed on a one-minute interval, delivery to the student may take up to one minute after submission rather than being instantaneous. | Pass (with noted latency) |
| NFT-04 | Accessibility | The system is accessible through standard web browsers and common devices. | The system was used successfully throughout the study over HTTPS on the school laboratory desktops and on students' own laptops and mobile phones, with no device or browser excluded. Layouts are responsive, with the login card and the bottom toolbar collapsing below 640 px; all interactive elements carry visible focus indicators; motion is disabled under `prefers-reduced-motion`; and badge and label colour pairs were corrected to meet WCAG AA contrast at their rendered text sizes. | Pass |
| NFT-05 | Security | The system maintains storage and controlled access to student data, ensuring safety of data. | All traffic is served over HTTPS/TLS using a Let's Encrypt certificate. Access is governed by Moodle role-based access control together with five purpose-built capabilities covering AI-output review, student-name reveal, competency-sequence management, usage-log viewing, and confidence-report access. Anonymous visitors are forced to the login page; guest access and self-registration are disabled; username enumeration protection and an eight-character password policy are enforced; and cron is restricted to the command line. Every state-changing form is CSRF-protected, student identifiers are held in session rather than in URLs, reporting is pseudonymous by default, CSV exports are sanitised against formula injection, teacher review actions are written to an immutable log, and outbound AI calls are rate-limited. Both custom plugins implement the Moodle Privacy API, and the AI middleware directory and its credentials file are not served over HTTP. | Pass |
| NFT-06 | Data Integrity | The system maintains accurate student progress records. | Computed mastery values (CM, CMI, GI) were cross-checked against manual calculation of the underlying stored response data and matched exactly. Unique key constraints prevent duplicate records — one feedback record per attempt, one learning track per student, one pre-test and one post-test response per student per question, and one competency mapping per question — and the competency table's prerequisite chain is enforced by a database-level foreign key. Referential consistency with Moodle's own user and attempt tables is maintained at the application layer, consistent with Moodle's schema conventions, which do not use database-level foreign keys. | Pass |

---

## What changed from the Thesis 2 draft, and why

Each item below corresponds to a finding in `THESIS_DOCUMENT_CHANGES.md`.

| Row | Change | Reason |
|---|---|---|
| §4.1 text | Added AI-Assisted Feedback to the list of modules | B6 — the sentence listed seven modules; the table has eight |
| FT-02 | Evidence replaced: SCORM lesson delivery instead of quiz content | B5 — the draft's evidence described quiz rendering, which is FT-03's requirement, so the instructional-content requirement was never actually evidenced |
| FT-03 | Test step no longer says "or practice activity" | B3 — no ungraded practice activity exists; the courses contain SCORM packages and graded quizzes only |
| FT-04 | States that mastery is scoped to the best finished attempt per quiz | A1 — mastery previously accumulated across every attempt ever, so a student who failed once could not reach 85%; fixed, and the rule now needs stating because the three options give different numbers |
| FT-05 | Branch coverage widened; passing marks and the three-attempt limit noted | Audit item 5 — the draft evidenced only two branches while the retake branch was unreachable in four courses; all 29 quizzes now carry `attempts = 3` and a passing mark |
| FT-05 | LP = 3 labelled "Reinforcement Practice" | B2 — LP = 2 and LP = 3 previously carried identical wording in both the paper and the code |
| FT-06 | Status changed from pending to Pass; enrichment-hint path added | A7 — "hints for fast learners" had no code path; now implemented on the LP = 4 branch, gated to summative quizzes |
| FT-08 | Adds de-identification, immutable review log, and the effect of flagging | A4 — flagging previously changed nothing the student saw, so "teacher review and override" was only half-true |
| NFT-01 | Ongoing → Pass | UEQ-S has now been administered; results are in Section 4.4 |
| NFT-02 | Ongoing → Pass | Concurrent multi-student load has now actually occurred, over the full 40-day intervention |
| NFT-04 | Ongoing → Pass | Real device and browser coverage from the deployment |
| NFT-05 | Rewritten | B7 — the draft cited a minimally-privileged web-service account that is not in the live data path, and omitted the hardening the system has since gained |
| NFT-06 | Foreign-key claim narrowed | B1 — the claim was false for the three tables holding student records; only the two competency tables carry foreign keys |

---

## Two caveats worth knowing before defense

These do not block any row above, but a panel could ask.

1. **Two Moodle security settings are still at their permissive defaults.** Account lockout is
   disabled (`lockoutthreshold = 0`), so password guessing is unlimited, and the session timeout
   is 8 hours, which in a shared computer laboratory means the next student at a machine can
   inherit the previous student's session. Both are single settings changes. NFT-05 as written
   does not claim either, but Process Rule 2 in Chapter 3 does claim "session managements ensure
   secure access and stop unauthorized use."

2. **Activity completion is off on every activity**, so Moodle records no completion data.
   Chapter 4's engagement table correctly reports lesson and material *views*, which come from
   the usage log and are real. But Sections 3.5.3 and 3.8.1 commit to reporting "task completion"
   and "lesson completion records", which have no source. Either reword those two sections to say
   views, or accept the gap — it cannot be back-filled, since completion is only recorded from the
   moment it is switched on.
