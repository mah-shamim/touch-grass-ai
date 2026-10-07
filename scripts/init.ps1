$ErrorActionPreference = "Stop"
docker compose build
docker compose up -d
Start-Sleep -Seconds 5
$model = if ($env:OLLAMA_MODEL) { $env:OLLAMA_MODEL } else { "gemma3:4b" }
docker compose exec ollama ollama pull $model
Write-Host "TouchGrass AI is ready at http://localhost:8081"
