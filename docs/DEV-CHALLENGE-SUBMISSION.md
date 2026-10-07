# TouchGrass AI: Local AI that gets you outside

*This is a submission for the [Hacktoberfest Open-Source AI Challenge Week 1: Touch Grass](https://dev.to/challenges/hacktoberfest-week1-2026-10-05).*

## What I Built

TouchGrass AI is a local-first outdoor companion for people who want help getting started—and a reason to put their phone away once they do.

It turns a few details, such as activity, location, and available time, into a practical outdoor mission. It also includes garden and beginner birding plans, plus a field journal for saving observations locally.

The idea is simple: AI should help you get to the park, garden, or trail—not keep you chatting with a screen. Plans include a safety check and a reminder to put the phone away. The app does not provide live weather or trail conditions, so users are prompted to verify current local conditions themselves.

## Demo

- **Video demo:** [60-second TouchGrass AI walkthrough](docs/touchgrass-ai-data-entry-demo.mp4)
- **Animated SVG:** [Animated demo](docs/touchgrass-ai-data-entry-demo.svg)
- **Live app:** No public deployment yet. The app currently runs locally at `http://localhost:8081/`; that address is not accessible to other readers.

The recording shows the actual site, a 45-minute birding mission, and saving a field observation. It also includes clearly labeled concept scenes for bird-photo capture and analysis; those features are not implemented. The offline scene simulates disconnecting the recording browser tab, not a full offline field test.

## Code

[GitHub repository](https://github.com/mah-shamim/touch-grass-ai.git)

The project is MIT-licensed and runs with Docker Compose. Missions and field notes are stored in a local SQLite database.

## How I Built It

The app uses PHP 8.5, a browser-based PWA shell, SQLite, Docker Compose, and Ollama-compatible local inference. The default model is Gemma 3 (`gemma3:4b`). The PHP app sends prompts to Ollama over the local Docker network rather than calling a hosted AI API.

```text
Browser / PWA
    |
    v
PHP 8.5 app ---- SQLite
    |
    v
Local Ollama runtime ---- Gemma 3
```

The model can be changed through configuration without rewriting the app's AI integration. Once the model is downloaded, the app and inference service can run without internet access, provided they are available on the user's device or local network.

## Why Does Open Innovation Matter?

Outdoor plans and field notes can reveal personal routines and locations. Running inference locally gives users the option to keep that data on infrastructure they control instead of sending every request to a hosted AI provider.

Open-weight models and an Ollama-compatible interface also make experimentation accessible: developers can inspect and adapt the prompts, try different models, and improve the project without depending on a single AI vendor or API key. That flexibility matters for a tool intended to work beyond reliable connectivity.

## My Agent Session

Optional: add a saved DevRelay session link or embed it here:

`{% agent_session YOUR_SESSION_ID_OR_SLUG %}`

## Prize Categories

Overall challenge entry. No partner category claimed.

---

**Before submitting:** confirm that the project and its repository were started and completed within the challenge window, as required by the challenge rules. Replace the GitHub placeholder with the public repository URL. The demo links above are relative to this repository and will work for readers once the repository is public.
