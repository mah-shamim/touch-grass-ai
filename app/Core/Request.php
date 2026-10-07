<?php
namespace TouchGrass\Core;
final class Request {
    public function isPost(): bool { return $_SERVER['REQUEST_METHOD'] === 'POST'; }
    public function path(): string { return parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/'; }
    public function input(string $key, mixed $default = null): mixed { return $_POST[$key] ?? $_GET[$key] ?? $default; }
    public function json(): array { $raw = file_get_contents('php://input') ?: ''; $data = json_decode($raw, true); return is_array($data) ? $data : []; }
}
