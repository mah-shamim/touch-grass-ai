# 🌿 TouchGrass AI

**A local-first outdoor companion that uses open-weight AI to make the screen the shortest part of the experience.**

> Hackathon theme: **Touch Grass**

TouchGrass AI is intentionally not an endless chatbot. It creates a small real-world mission, gives the user a safety/packing checklist, adds one observation challenge, and tells the user when to put the phone away.

## Why this project fits the challenge

- **Open-weight AI at the core:** the default documented model is Gemma 3 through an Ollama-compatible local runtime.
- **Local inference:** PHP sends prompts to the Ollama container on the same Docker network instead of a hosted AI API.
- **Private field notes:** SQLite data is stored in a Docker volume.
- **Model freedom:** change `OLLAMA_MODEL` without changing the PHP application.
- **Offline-first shell:** the PWA caches the application shell. After the model is downloaded, AI generation can continue without internet access.
- **Low-screen behavior:** generated plans include a deliberate phone-away challenge.
- **No mandatory paid AI API:** the core workflow can run on your own machine.

## Features

### Implemented

1. Outdoor micro-adventure planner
   - walking, running, hiking, cycling, photography, family outings
   - duration and difficulty
   - location/area and constraints
   - AI-generated step-by-step mission
   - packing/safety/return-home guidance
2. Garden planner
   - crop, area, frost date, season/month
   - weekly outdoor tasks
3. Birding mission
   - observation-first prompts
   - habitat/field-mark questions
   - uncertainty-aware identification guidance
4. Local field log
   - birds, plants, insects, landscapes, weather and other observations
5. SQLite persistence
6. PWA service worker
7. Browser geolocation permission helper
8. Local AI health endpoint
9. Docker + Docker Compose
10. Linux/macOS shell and Windows PowerShell setup scripts
11. PHP database smoke test
12. AI-agent instructions for PhpStorm, VS Code and Sublime Text workflows

### Demo video

The 60-second narrated walkthrough is available at [`docs/touchgrass-ai-data-entry-demo.mp4`](docs/touchgrass-ai-data-entry-demo.mp4). It records the actual TouchGrass AI website at `http://localhost:8081/` in Microsoft Edge and follows six 10-second beats: introduce the site, enter and save a 45-minute birding mission, take the recording tab offline, present a clearly labeled bird-photo concept, present a clearly labeled photo-analysis concept, and enter and save a field observation in the real site.

An animated SVG version is available at [`docs/touchgrass-ai-data-entry-demo.svg`](docs/touchgrass-ai-data-entry-demo.svg). It loops the 60-second demo using 240 embedded JPEG frames at 4 frames per second, resized to 960×540. This preserves the original screen recording inside a self-contained SVG; the video imagery is raster, not converted into vector paths.

The offline step applies only to the isolated recording browser tab, not the host's internet connection. Bird-photo capture and photo analysis are concept overlays: those features are not implemented in the app, so no photo is analyzed by Gemma. Mission and field-observation details are submitted to the app's existing SQLite-backed save endpoints.

To regenerate the video on Windows, start the app and its local Ollama service with Docker Compose, ensure Microsoft Edge and Node.js 18+ are installed, then run:

```powershell
.\scripts\make-demo-video.ps1
```

The script checks app and local AI health, installs Playwright Core into the system temporary directory if needed, records the real website while entering example mission and observation data, creates spoken narration with Windows text-to-speech, encodes the MP4 with FFmpeg in Docker, and removes temporary recording files when finished. Regenerating the demo adds one example mission and one example field observation to the local database.

## Architecture

```text
Browser / PWA
     |
     | HTTP
     v
PHP 8.5 + Apache
     |
     +---- SQLite --------------------> local field notes / missions
     |
     +---- Ollama HTTP API -----------> open-weight local model
                                      (default: gemma3:4b)
```

The PHP application does **not** require an OpenAI/Anthropic/etc. API key.

Local AI output is capped at 256 tokens and prompts request concise plans so CPU-only Ollama responses are less likely to exceed the 120-second request timeout. The loaded model is kept warm for 10 minutes between requests. If a request still times out on slower hardware, shorten the notes or choose a smaller Ollama-compatible model.

## Requirements

- Docker Desktop 4.x+ (Windows/macOS/Linux) or Docker Engine + Compose plugin
- 8 GB RAM recommended for the default 4B model; more RAM/VRAM improves latency
- Internet only for the initial Docker image/model download
- After the model is downloaded, the core application + AI runtime can operate without internet

## 1. Quick start — Windows

```powershell
git clone <your-repository-url>
cd touchgrass-ai
Copy-Item .env.example .env
.\scripts\init.ps1
```

Open:

```text
http://localhost:8081
```

## 2. Quick start — Linux/macOS

```bash
git clone <your-repository-url>
cd touchgrass-ai
cp .env.example .env
./scripts/init.sh
```

Open:

```text
http://localhost:8081
```

## 3. Manual Docker steps

If you prefer to understand every command:

```bash
docker compose build
docker compose up -d

docker compose ps
```

Pull the default model:

```bash
docker compose exec ollama ollama pull gemma3:4b
```

Verify AI runtime:

```bash
curl http://localhost:8081/api/health
```

Open the app:

```text
http://localhost:8081
```

## 4. Change the model

Set this in `.env`:

```dotenv
OLLAMA_MODEL=gemma3:4b
```

Then pull it:

```bash
docker compose exec ollama ollama pull gemma3:4b
docker compose restart app
```

The application only expects the Ollama chat API. This makes the model/runtime boundary replaceable.

## 5. Stop / reset

Stop containers:

```bash
docker compose down
```

Remove containers **and local model/data volumes**:

```bash
docker compose down -v
```

Use `down -v` only when you intentionally want to delete the local model cache and SQLite volume.

## 6. GPU option

For an NVIDIA Docker environment, merge the GPU override:

```bash
docker compose -f docker-compose.yml -f docker-compose.gpu.yml up -d
```

Confirm that the host's NVIDIA Container Toolkit is configured before using this mode.

## 7. Development without changing the architecture

The application has intentionally few dependencies:

- PHP 8.5
- Apache
- PDO SQLite
- cURL
- Composer autoloading
- Vanilla HTML/CSS/JavaScript
- Ollama-compatible local HTTP API

No frontend build system is required.

### PHP syntax check

Inside the app container:

```bash
docker compose exec app php -l public/index.php
docker compose exec app php -l app/Services/OllamaService.php
```

### Database test

Inside the app container:

```bash
docker compose exec app php tests/run.php
```

Expected:

```text
PASS: database migrations + write/read test
```

## Feature roadmap

The project is intentionally built for incremental upgrades. The next wave of ideas focuses on field recording, local AI assistance, better birding workflows, and a stronger mobile/offline experience.

- GPS track recording
- GPX export
- Offline maps
- Weather integration
- Bird photo analysis
- Bird-call recognition
- Local speech-to-text
- Local text-to-speech
- Voice outdoor assistant
- QR mission sharing
- Run-club route sharing
- Garden frost-date database
- Local RAG field guide
- Embedding search
- Family profiles
- Accessibility mode
- FunctionGemma tool calling
- Local AI agent actions
- Outdoor-domain LoRA fine-tuning
- Model evaluation framework
- Mobile/PWA packaging

These are presented in-app as roadmap suggestions and can be expanded into concrete features as the project matures.

## API

### Health

```http
GET /api/health
```

### Generate outdoor mission

```http
POST /api/ai/outing
Content-Type: application/json

{
  "activity":"hike",
  "location":"local forest trail",
  "duration":"60",
  "difficulty":"moderate",
  "notes":"shade, birds, no phone"
}
```

### Garden plan

```http
POST /api/ai/garden
Content-Type: application/json
```

### Birding mission

```http
POST /api/ai/birding
Content-Type: application/json
```

### Save field observation

```http
POST /api/logs
Content-Type: application/json
```

## Security / privacy notes

- Do not expose port `11434` to the public internet in production.
- The Ollama port is exposed in Compose for local development; remove the host port mapping for a stricter deployment.
- Do not commit `.env` or personal SQLite files.
- The app should be placed behind HTTPS when deployed publicly.
- Add authentication before making personal field logs remotely accessible.
- Treat AI output as suggestions, not live safety/navigation data.

## Important safety boundary

TouchGrass AI intentionally does **not** claim to know live trail closures, current emergency conditions, live weather, poisonous plants, or guaranteed species identification. Before leaving, users should check appropriate official local sources and use ordinary outdoor safety practices.

## Roadmap / possible feature integrations

| Feature | Status | Suggested implementation |
|---|---|---|
| Offline PWA | ✅ | Service worker + local shell |
| Local LLM | ✅ | Ollama-compatible runtime |
| Outdoor missions | ✅ | PHP prompt service |
| Garden planner | ✅ | Local AI planner |
| Birding guide | ✅ | Observation-first AI |
| Field journal | ✅ | SQLite |
| GPS trail recording | 🔜 | Browser Geolocation + GPX exporter |
| Offline maps | 🔜 | Bundled vector tiles / MBTiles |
| Weather-aware planning | 🔜 | Optional API adapter; cache latest forecast |
| Bird photo analysis | 🔜 | Gemma multimodal / local vision model |
| Bird-call recognition | 🔜 | Local audio classifier/Whisper-style pipeline |
| Voice assistant | 🔜 | Local speech-to-text + TTS |
| Calendar integration | 🔜 | ICS export first, provider connectors later |
| Run club route sharing | 🔜 | GPX + QR code + local peer sharing |
| Garden frost database | 🔜 | User-supplied/local region dataset |
| RAG field guide | 🔜 | Local embeddings + SQLite/vector store |
| Family mode | 🔜 | Multiple local profiles |
| Accessibility mode | 🔜 | Large controls, voice prompts, high contrast |
| Agent actions | 🔜 | FunctionGemma/structured local tool calling |
| Model evaluation | 🔜 | Prompt test fixtures + latency/quality report |

## Why open innovation worked better here

A closed hosted model would make the prototype easy to demo, but it would undermine the core story: someone on a trail may have poor connectivity, and field notes can contain private information. A local open-weight model makes the AI boundary portable. Developers can inspect the application, change prompts, swap models, run inference on their own machine, and later fine-tune or add a specialized model.

The project also separates the **outdoor product behavior** from the **model choice**. That is important: the app is not locked to one provider's API, SDK, pricing, or hosted data path.

## Hackathon demo script

1. Start Docker.
2. Open TouchGrass AI.
3. Enter: `hike / local park / 45 minutes / easy / bird spotting`.
4. Generate the mission.
5. Show the result and the phone-away challenge.
6. Save one field observation.
7. Disconnect the computer from the internet.
8. Reload the cached PWA shell.
9. Explain that the local model remains available because its weights are stored in the Ollama Docker volume.
10. Show `/api/health` and the Docker containers.

## Suggested 90-second pitch

> “TouchGrass AI is a local-first outdoor companion. Instead of keeping you in an AI chat, it uses open-weight AI to generate a tiny real-world mission: where to focus, what to bring, what to observe, and when to put the phone away. The PHP application stores field notes locally in SQLite and talks to a local Ollama runtime, so there is no required hosted AI API. Once the model is downloaded, the core experience can continue without internet. Open AI matters here because privacy, offline use, model choice, and the ability to modify the system are not side benefits — they are the product.”

## Credits / model note

The project is designed to work with open-weight models available through an Ollama-compatible runtime. The README uses Gemma 3 as the default documented model. Review the selected model's current terms before redistribution or commercial deployment.

## License

Application source: MIT. Model weights are **not** included in this repository and remain subject to their own model license/terms.
