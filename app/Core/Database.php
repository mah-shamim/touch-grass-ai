<?php
namespace TouchGrass\Core;

use PDO;

final class Database {
    private PDO $pdo;
    public function __construct(string $path) {
        $dir = dirname($path);
        if (!is_dir($dir)) mkdir($dir, 0775, true);
        $this->pdo = new PDO('sqlite:' . $path);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $this->pdo->exec('PRAGMA foreign_keys = ON');
        $this->migrate();
    }
    public function pdo(): PDO { return $this->pdo; }
    private function migrate(): void {
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS outings (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            activity TEXT NOT NULL,
            title TEXT NOT NULL,
            location TEXT NOT NULL,
            duration_minutes INTEGER NOT NULL DEFAULT 45,
            difficulty TEXT NOT NULL DEFAULT 'easy',
            notes TEXT NOT NULL DEFAULT '',
            ai_plan TEXT NOT NULL DEFAULT '',
            completed INTEGER NOT NULL DEFAULT 0,
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        )");
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS nature_logs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            kind TEXT NOT NULL,
            name TEXT NOT NULL,
            location TEXT NOT NULL DEFAULT '',
            notes TEXT NOT NULL DEFAULT '',
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        )");
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS garden_plans (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            crop TEXT NOT NULL,
            location TEXT NOT NULL,
            frost_date TEXT NOT NULL DEFAULT '',
            season TEXT NOT NULL DEFAULT '',
            plan TEXT NOT NULL DEFAULT '',
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        )");
    }
}
