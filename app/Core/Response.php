<?php
namespace TouchGrass\Core;
final class Response {
    public static function json(array $data, int $status=200): never {
        http_response_code($status); header('Content-Type: application/json; charset=utf-8'); echo json_encode($data, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT); exit;
    }
    public static function redirect(string $path): never { header('Location: ' . $path); exit; }
}
