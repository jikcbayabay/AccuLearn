# AccuLearn

A smart supplementary e-learning system for Grade 11 ABM (Accountancy, Business, and Management) students. AccuLearn delivers adaptive learning paths driven by mastery scoring, prerequisite checks, and AI-generated feedback, designed to integrate with Moodle Web Services and OpenAI (GPT-4o mini).

> **Status:** Early scaffold. Moodle and OpenAI integrations are mocked. Replace mocks once the Moodle token and OpenAI API key are available.

## Stack

- **Frontend:** React 18, Vite, Tailwind CSS, React Router, Axios
- **Backend:** Laravel 11, PHP 8.3, Sanctum (planned)
- **Database:** MySQL 8

## Project Layout

```
acculearn/
├── frontend/   # React + Vite client
└── backend/    # Laravel 11 API
```

## Running Locally

### Backend

```bash
cd backend
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate
php artisan serve
```

The API will be available at `http://localhost:8000`.

### Frontend

```bash
cd frontend
cp .env.example .env
npm install
npm run dev
```

The app will be available at `http://localhost:5173`.

## Mocked Integrations

The following services return hardcoded data until real credentials are wired up:

- `backend/app/Services/Moodle/MoodleService.php` — Moodle Web Services
- `backend/app/Services/AI/OpenAIService.php` — GPT-4o mini feedback generator
- `frontend/src/services/*.js` — All HTTP calls return `Promise.resolve(mock)`

## Core Domain Concepts

- **Mastery Score** = (correct answers / total items) × 100
- **Mastery Levels:** Mastered (≥85%), Developing (75–84%), Needs Improvement (<75%)
- **Learning Paths:**
  - LP1 — Prerequisite Review
  - LP2 — Guided Remediation
  - LP3 — Reinforcement
  - LP4 — Advancement
- **GI (Guessing Index)** = correct-with-low-confidence / total-correct
- **CMI (Confident Misconception Index)** = incorrect-with-high-confidence / total-incorrect
