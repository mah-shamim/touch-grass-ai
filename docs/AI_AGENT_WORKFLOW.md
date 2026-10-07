# AI-Agent Development Workflow

This project is deliberately easy to drive from an IDE AI agent while keeping the agent's scope controlled.

## Rules for the agent

1. Read `README.md` and `docs/PROJECT_SPEC.md` before changing code.
2. Do not introduce a hosted AI provider into the core path.
3. Do not hard-code model names in PHP; use environment configuration.
4. Keep outdoor safety disclaimers intact.
5. Do not store secrets in source code.
6. Prefer small, testable PHP services.
7. Run PHP syntax checks after PHP changes.
8. Run `php tests/run.php` after persistence changes.
9. Update README when commands or environment variables change.
10. Do not add a frontend framework unless the feature genuinely requires it.

## PhpStorm setup

1. Open the project folder.
2. Configure PHP CLI interpreter from Docker:
   - Settings → PHP → CLI Interpreter → Docker Compose.
   - Select the `app` service.
3. Set PHP language level to 8.5.
4. Configure Composer using the container interpreter.
5. Mark `app/` as source code if PhpStorm does not detect it.
6. Create a Run Configuration for:
   `php tests/run.php`
7. Create a Docker Compose run configuration for `docker-compose.yml`.
8. If using an AI coding agent, give it `docs/PROJECT_SPEC.md` and `docs/AI_AGENT_WORKFLOW.md` as project instructions.

### Suggested PhpStorm agent prompt

```text
You are working on TouchGrass AI. Read README.md, docs/PROJECT_SPEC.md and docs/AI_AGENT_WORKFLOW.md first. Keep PHP 8.5 compatibility, Docker-first execution, SQLite persistence and local Ollama AI. Never add a hosted AI dependency to the core workflow. Before finishing, run PHP syntax checks and php tests/run.php when applicable. Explain changed files and any trade-offs.
```

## VS Code setup

Recommended extensions:

- PHP Intelephense
- Docker
- PHP Debug
- EditorConfig
- GitLens (optional)

Steps:

1. Open the repository.
2. Open `.env.example` and create `.env`.
3. Start Docker Compose.
4. Use the integrated terminal for `docker compose ...` commands.
5. Configure PHP validation to use the container or local PHP 8.5.
6. Add `.github/copilot-instructions.md` to repository-level AI instructions if GitHub Copilot is used.
7. Keep AI agent changes constrained by `docs/PROJECT_SPEC.md`.

### Suggested VS Code agent prompt

```text
Act as a senior PHP 8.5 developer on TouchGrass AI. Preserve the local-first AI architecture. Use the existing OllamaService abstraction. Do not add external AI APIs. Follow docs/PROJECT_SPEC.md. Make the smallest safe change, then run relevant syntax/tests in Docker.
```

## Sublime Text setup

1. Install Sublime Text 4.
2. Open the repository folder.
3. Install Package Control.
4. Install LSP and LSP-intelephense.
5. Install Dockerfile syntax support if desired.
6. Use the built-in terminal or an external terminal for Docker commands.
7. Use your preferred AI coding extension/agent with the same project prompt above.

## Safe agent task pattern

Use this sequence for larger changes:

```text
1. Inspect existing architecture.
2. State the files that need changing.
3. Implement one feature slice.
4. Run syntax tests.
5. Run application smoke test.
6. Update documentation.
7. Show a concise diff summary.
```
