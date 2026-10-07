# TouchGrass AI — Project Specification

## Goal
Build an outdoor-first application where AI reduces planning friction instead of increasing screen time.

## Functional requirements

### FR-01 Outdoor mission
User selects activity, area, duration, difficulty and constraints. The system requests a local AI plan and displays it.

### FR-02 Garden planner
User supplies crop, area, frost date and season. System returns a conservative weekly plan.

### FR-03 Birding mission
User supplies location and observations. System generates an observation checklist and uncertainty-aware guidance.

### FR-04 Field log
User can store nature observations locally.

### FR-05 Local AI
AI requests must target the configured local Ollama-compatible service.

### FR-06 Model swapping
Changing `OLLAMA_MODEL` must not require PHP code changes.

### FR-07 Offline shell
The PWA must cache the application shell and remain navigable without network access.

### FR-08 Safety
AI prompts must explicitly avoid pretending to have live environmental/emergency knowledge.

## Non-functional requirements

- PHP 8.5+
- Docker-first setup
- SQLite for zero-service local persistence
- No required frontend build tool
- No required hosted AI key
- Clear separation between controller, service, database and view concerns
- Reproducible setup scripts
- Basic automated smoke test

## Acceptance test

1. `docker compose build` succeeds.
2. `docker compose up -d` starts app + Ollama.
3. Model is pulled successfully.
4. `GET /api/health` returns JSON.
5. Outdoor AI request returns text.
6. Field log is persisted.
7. PWA shell is cached.
8. Removing internet does not stop the local Docker network from reaching Ollama.
9. `php tests/run.php` passes.

## Data model

### outings
- id
- activity
- title
- location
- duration_minutes
- difficulty
- notes
- ai_plan
- completed
- created_at

### nature_logs
- id
- kind
- name
- location
- notes
- created_at

### garden_plans
- id
- crop
- location
- frost_date
- season
- plan
- created_at
