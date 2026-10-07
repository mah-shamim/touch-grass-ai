# Hackathon Submission Draft — TouchGrass AI

## Title

**TouchGrass AI — Local AI that sends you outside**

## One-line description

A privacy-first outdoor companion that uses open-weight AI locally to turn a few inputs into a real-world mission, then tells you to put the phone away.

## What I built

TouchGrass AI is a PHP 8.5 + Docker application with a local SQLite field journal and a local Ollama-compatible AI runtime. It can create outdoor micro-adventures, garden plans and birding observation missions.

The important product decision is that AI is not the destination. The destination is the park, trail, garden or neighborhood outside the screen.

## Why open innovation matters

The project needs the AI to work where connectivity may be unreliable and where users may not want private field notes sent to a server they do not control. The local model makes that possible.

Because the AI boundary is an Ollama-compatible local HTTP service, the application can swap models without rewriting the PHP product. Developers can inspect prompts, change behavior, evaluate different models and eventually fine-tune a model for outdoor-specific tasks.

## What works better than a closed API here

- No required hosted AI account or API key.
- Field notes can stay local.
- The model remains available after the initial download.
- Model choice is a configuration decision.
- The complete system can be demonstrated on a laptop.
- The application can be forked and modified without being tied to one AI provider.

## Demo flow

1. Start Docker.
2. Generate a 45-minute birding walk.
3. Show the phone-away challenge.
4. Save an observation.
5. Show the local SQLite data path.
6. Disconnect internet.
7. Demonstrate the PWA shell and local AI runtime still running.
8. Show the model can be changed through `OLLAMA_MODEL`.

## Bonus: real-world test

Before submitting, take the app outside. Record:

- location type (not an exact private address)
- activity
- duration
- whether the generated plan was useful
- what was changed in the real world
- how often the phone was actually used
- one thing the model got wrong

Never fabricate this section. The challenge explicitly values taking the project outside, so use real observations.

## Suggested final evidence

- 30–60 second screen recording
- photo of the actual outdoor test (with consent/privacy respected)
- Docker container screenshot
- `/api/health` screenshot
- short DevRelay session link if available
- GitHub repository
