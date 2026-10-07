#!/usr/bin/env bash
set -euo pipefail
docker compose build
docker compose up -d
echo 'Waiting for Ollama...'; sleep 5
docker compose exec ollama ollama pull "${OLLAMA_MODEL:-gemma3:4b}"
echo 'TouchGrass AI is ready at http://localhost:8081'
