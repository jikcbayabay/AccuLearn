/**
 * AccuLearn FABM 1 — Quiz REST API
 * Standalone Express server (or require() as a router module).
 *
 * Endpoints
 *   GET  /api/quizzes                              — list all 7 module quizzes
 *   GET  /api/quizzes/competency/:competencyId     — quiz for one competency
 *   GET  /api/quizzes/:quizId/questions            — all questions for a quiz
 *   GET  /api/questions/:questionId/answers        — all answers for one question
 *   POST /api/quizzes/:quizId/submit               — grade a quiz attempt
 *
 * Usage (standalone):
 *   npm install express mysql2 dotenv
 *   node quiz-api.js
 *
 * Usage (as Express router):
 *   const quizRouter = require('./quiz-api');
 *   app.use(quizRouter);
 *
 * Environment variables (or .env file):
 *   DB_HOST     (default: 127.0.0.1)
 *   DB_PORT     (default: 3306)
 *   DB_USER     (default: root)
 *   DB_PASSWORD (default: )
 *   DB_DATABASE (default: acculearn)
 *   PORT        (default: 3001)
 */

'use strict';

require('dotenv').config();

const express = require('express');
const mysql   = require('mysql2/promise');

// ─── Database pool ────────────────────────────────────────────────────────────

const pool = mysql.createPool({
  host     : process.env.DB_HOST     || '127.0.0.1',
  port     : Number(process.env.DB_PORT) || 3306,
  user     : process.env.DB_USER     || 'root',
  password : process.env.DB_PASSWORD || '',
  database : process.env.DB_DATABASE || 'acculearn',
  charset  : 'utf8mb4',
  waitForConnections: true,
  connectionLimit   : 10,
});

// ─── Router setup ─────────────────────────────────────────────────────────────

const router = express.Router();
router.use(express.json());

// ─── Helper ───────────────────────────────────────────────────────────────────

function notFound(res, entity = 'Resource') {
  return res.status(404).json({ error: `${entity} not found` });
}

// ─── GET /api/quizzes ─────────────────────────────────────────────────────────
// Returns all assessments that have at least one question, joined with
// their module/competency context.

router.get('/api/quizzes', async (req, res) => {
  const [rows] = await pool.query(`
    SELECT
      a.id                AS quiz_id,
      a.title             AS quiz_title,
      a.passing_score,
      c.id                AS competency_id,
      c.deped_code,
      c.title             AS competency_title,
      m.id                AS module_id,
      m.title             AS module_title,
      m.order             AS module_order,
      COUNT(aq.id)        AS question_count
    FROM assessments a
    JOIN competencies  c  ON a.competency_id = c.id
    JOIN modules       m  ON c.module_id     = m.id
    JOIN assessment_questions aq ON aq.assessment_id = a.id AND aq.is_active = 1
    GROUP BY a.id
    ORDER BY m.order, c.order
  `);

  res.json({ quizzes: rows });
});

// ─── GET /api/quizzes/competency/:competencyId ────────────────────────────────
// Returns the quiz (assessment) linked to a specific competency.

router.get('/api/quizzes/competency/:competencyId', async (req, res) => {
  const [rows] = await pool.query(`
    SELECT
      a.id                AS quiz_id,
      a.title             AS quiz_title,
      a.passing_score,
      c.id                AS competency_id,
      c.deped_code,
      c.title             AS competency_title,
      m.id                AS module_id,
      m.title             AS module_title,
      COUNT(aq.id)        AS question_count
    FROM assessments a
    JOIN competencies  c  ON a.competency_id = c.id
    JOIN modules       m  ON c.module_id     = m.id
    LEFT JOIN assessment_questions aq ON aq.assessment_id = a.id AND aq.is_active = 1
    WHERE a.competency_id = ?
    GROUP BY a.id
    LIMIT 1
  `, [req.params.competencyId]);

  if (!rows.length) return notFound(res, 'Quiz');
  res.json({ quiz: rows[0] });
});

// ─── GET /api/quizzes/:quizId/questions ───────────────────────────────────────
// Returns all active questions for a quiz.
// Answers are NOT included (students fetch them per-question or on submit).

router.get('/api/quizzes/:quizId/questions', async (req, res) => {
  const [quiz] = await pool.query(
    'SELECT id, title, passing_score FROM assessments WHERE id = ? LIMIT 1',
    [req.params.quizId]
  );
  if (!quiz.length) return notFound(res, 'Quiz');

  const [questions] = await pool.query(`
    SELECT
      id              AS question_id,
      question_text,
      question_type,
      sequence_order
    FROM assessment_questions
    WHERE assessment_id = ? AND is_active = 1
    ORDER BY sequence_order
  `, [req.params.quizId]);

  res.json({
    quiz     : quiz[0],
    questions: questions,
  });
});

// ─── GET /api/questions/:questionId/answers ───────────────────────────────────
// Returns all answer options for a single question.
// is_correct is intentionally EXCLUDED to prevent cheating during active quizzes.
// Include ?reveal=1 (server-side only / admin) to expose correct answers.

router.get('/api/questions/:questionId/answers', async (req, res) => {
  const [qRows] = await pool.query(
    'SELECT id, question_text, question_type FROM assessment_questions WHERE id = ? LIMIT 1',
    [req.params.questionId]
  );
  if (!qRows.length) return notFound(res, 'Question');

  const reveal = req.query.reveal === '1';
  const select = reveal
    ? 'id AS answer_id, answer_text, sequence_order, is_correct, explanation'
    : 'id AS answer_id, answer_text, sequence_order';

  const [answers] = await pool.query(
    `SELECT ${select} FROM assessment_answers WHERE question_id = ? ORDER BY sequence_order`,
    [req.params.questionId]
  );

  res.json({
    question: qRows[0],
    answers : answers,
  });
});

// ─── POST /api/quizzes/:quizId/submit ─────────────────────────────────────────
// Grades a submitted quiz attempt.
//
// Request body:
//   { answers: [ { question_id: 1, answer_id: 5 }, ... ] }
//
// Response:
//   {
//     score          : 86.67,        // percentage
//     passed         : true,
//     passing_score  : 75,
//     correct_count  : 13,
//     total_questions: 15,
//     results: [
//       {
//         question_id     : 1,
//         question_text   : "...",
//         submitted_answer: "...",
//         correct_answer  : "...",
//         is_correct      : true,
//         explanation     : "..."
//       }, ...
//     ]
//   }

router.post('/api/quizzes/:quizId/submit', async (req, res) => {
  const { answers } = req.body;

  if (!Array.isArray(answers) || answers.length === 0) {
    return res.status(400).json({ error: 'answers array is required' });
  }

  // Fetch quiz meta
  const [quizRows] = await pool.query(
    'SELECT id, title, passing_score FROM assessments WHERE id = ? LIMIT 1',
    [req.params.quizId]
  );
  if (!quizRows.length) return notFound(res, 'Quiz');
  const quiz = quizRows[0];

  // Fetch all questions + correct answers for this quiz in one query
  const [correctRows] = await pool.query(`
    SELECT
      aq.id             AS question_id,
      aq.question_text,
      aq.sequence_order,
      aa.id             AS answer_id,
      aa.answer_text,
      aa.is_correct,
      aa.explanation
    FROM assessment_questions aq
    JOIN assessment_answers   aa ON aa.question_id = aq.id
    WHERE aq.assessment_id = ? AND aq.is_active = 1
    ORDER BY aq.sequence_order, aa.sequence_order
  `, [req.params.quizId]);

  // Build lookup: question_id → { correct_answer_id, correct_answer_text, explanation, question_text }
  const questionMap = {};
  for (const row of correctRows) {
    if (!questionMap[row.question_id]) {
      questionMap[row.question_id] = {
        question_text  : row.question_text,
        correct_id     : null,
        correct_text   : null,
        explanation    : null,
        all_answers    : [],
      };
    }
    questionMap[row.question_id].all_answers.push({
      id  : row.answer_id,
      text: row.answer_text,
    });
    if (row.is_correct) {
      questionMap[row.question_id].correct_id   = row.answer_id;
      questionMap[row.question_id].correct_text = row.answer_text;
      questionMap[row.question_id].explanation  = row.explanation;
    }
  }

  // Build lookup: answer_id → answer_text (for submitted answers)
  const answerTextMap = {};
  for (const row of correctRows) {
    answerTextMap[row.answer_id] = row.answer_text;
  }

  // Grade each submitted answer
  let correctCount = 0;
  const results = answers.map(sub => {
    const q       = questionMap[sub.question_id];
    const correct = q && sub.answer_id === q.correct_id;
    if (correct) correctCount++;
    return {
      question_id     : sub.question_id,
      question_text   : q ? q.question_text   : null,
      submitted_answer: answerTextMap[sub.answer_id] || null,
      correct_answer  : q ? q.correct_text    : null,
      is_correct      : correct,
      explanation     : q ? q.explanation     : null,
    };
  });

  const totalQuestions = Object.keys(questionMap).length;
  const score          = totalQuestions > 0
    ? Math.round((correctCount / totalQuestions) * 10000) / 100   // 2 decimal places
    : 0;

  res.json({
    quiz_id        : quiz.id,
    quiz_title     : quiz.title,
    score          : score,
    passed         : score >= quiz.passing_score,
    passing_score  : quiz.passing_score,
    correct_count  : correctCount,
    total_questions: totalQuestions,
    results        : results,
  });
});

// ─── Standalone server (node quiz-api.js) ─────────────────────────────────────

if (require.main === module) {
  const app  = express();
  const PORT = process.env.PORT || 3001;
  app.use(router);
  app.listen(PORT, () => {
    console.log(`AccuLearn Quiz API running on http://localhost:${PORT}`);
    console.log('Endpoints:');
    console.log('  GET  /api/quizzes');
    console.log('  GET  /api/quizzes/competency/:competencyId');
    console.log('  GET  /api/quizzes/:quizId/questions');
    console.log('  GET  /api/questions/:questionId/answers');
    console.log('  POST /api/quizzes/:quizId/submit');
  });
}

module.exports = router;
