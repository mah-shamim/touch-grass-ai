# First-run verification checklist

The archive was assembled as source code. The current generation environment does not contain Docker or PHP, so run these checks on the target development machine.

## Required checks

- [ ] `docker --version`
- [ ] `docker compose version`
- [ ] `docker compose build`
- [ ] `docker compose up -d`
- [ ] `docker compose exec ollama ollama pull gemma3:4b`
- [ ] Open `http://localhost:8081`
- [ ] `curl http://localhost:8081/api/health`
- [ ] Generate one outdoor mission
- [ ] Save one field observation
- [ ] `docker compose exec app php tests/run.php`
- [ ] Test the PWA shell after disabling network access

## Expected smoke-test output

```text
PASS: database migrations + write/read test
```

## If AI generation fails

Check:

```bash
docker compose ps
docker compose logs ollama
docker compose exec ollama ollama list
```

If the model is absent:

```bash
docker compose exec ollama ollama pull gemma3:4b
```
