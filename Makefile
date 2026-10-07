up:
	docker compose up -d
build:
	docker compose build
model:
	docker compose exec ollama ollama pull $${OLLAMA_MODEL:-gemma3:4b}
health:
	curl -fsS http://localhost:8081/api/health
shell:
	docker compose exec app bash
logs:
	docker compose logs -f app ollama
test:
	docker compose exec app php tests/run.php
down:
	docker compose down
reset:
	docker compose down -v
